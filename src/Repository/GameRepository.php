<?php

namespace App\Repository;

use App\EspnNFLApi;
use DateInterval;
use DateTime;
use DateTimeZone;
use Abraham\TwitterOAuth\TwitterOAuth;
use App\Entity\Game;
use App\Entity\Play;
use App\Entity\Tweet;
use App\Repository\{TeamRepository, VenueRepository};
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Game|null find($id, $lockMode = null, $lockVersion = null)
 * @method Game|null findOneBy(array $criteria, array $orderBy = null)
 * @method Game[]    findAll()
 * @method Game[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GameRepository extends ServiceEntityRepository
{
    const REGULAR_SEASON_TYPE = 2;

    private $twitter;
    private $teamRepository;
    private $venueRepository;
    private $espnApi;

    public function __construct(
        ManagerRegistry $registry,
        TeamRepository $teamRepository,
        VenueRepository $venueRepository,
        TwitterOAuth $twitter,
        EspnNFLApi $espnApi
    ) {
        $this->teamRepository = $teamRepository;
        $this->venueRepository = $venueRepository;
        $this->twitter = $twitter;
        $this->espnApi = $espnApi;

        parent::__construct($registry, Game::class);
    }

    public function getActiveGames(): array {
        $em = $this->getEntityManager();

        $query = $em->createQuery("
            SELECT game
            FROM " . Game::class . " game
            WHERE game.status != :status
                and game.date <= :date
            order by game.last_analyzed asc
        ")
            ->setParameter('status', 'STATUS_FINAL')
            ->setParameter('date', (new DateTime())->format('Y-m-d H:i:s'));

        return $query->getResult();
    }

    public function getLastWeeksGames(): array {
        $em = $this->getEntityManager();

        $query = $em->createQuery("
            SELECT game
            FROM " . Game::class . " game
            WHERE game.date between :start and :end
            order by game.last_analyzed asc
        ")
            ->setParameter('start', (new DateTime('-7 days'))->format('Y-m-d H:i:s'))
            ->setParameter('end', (new DateTime())->format('Y-m-d H:i:s'));

        return $query->getResult();
    }
    
    public function importWeek(int $week): void {
        $events = $this->espnApi->getEventsFromApi(self::REGULAR_SEASON_TYPE, $week);
        
        foreach ($events as $event) {
            $competition = $event['competitions'][0];

            $gameTime = new DateTime($event['date'], new DateTimeZone('America/New_York'));
            $gameTime->sub(new DateInterval('PT4H'));
            
            $venue = $competition['venue'];
            $teams = $competition['competitors'];

            $homeTeam = $this->getTeamByHomeAway($teams, 'home');
            $awayTeam = $this->getTeamByHomeAway($teams, 'away');

            $venue = $this->parseVenue($venue);
            $homeTeam = $this->parseTeam($homeTeam['team']);
            $awayTeam = $this->parseTeam($awayTeam['team']);

            $venue = $this->venueRepository->createOrUpdateVenue($venue);
            $homeTeam = $this->teamRepository->createOrUpdateTeam($homeTeam);
            $awayTeam = $this->teamRepository->createOrUpdateTeam($awayTeam);

            $game = new Game;
            $game->setHomeTeam($homeTeam);
            $game->setAwayTeam($awayTeam);
            $game->setVenue($venue);

            $game->setEspnId($event['id']);
            $game->setDate($gameTime);
            $game->setName($event['name']);
            $game->setStatus($event['status']['type']['name']);

            $tweet = $awayTeam->getDisplayName() . ' @ ' . $homeTeam->getDisplayName() . "\n";
            $tweet .= $gameTime->format('F jS g:i A') . "\n";
            $tweet .= '#' . $homeTeam->getAbbreviation() . 'vs' . $awayTeam->getAbbreviation();
            $tweet .= ' #' . str_replace(' ', '', $homeTeam->getName());
            $tweet .= ' #' . str_replace(' ', '', $awayTeam->getName());
            $tweet .= "\n\nFollow on https://nflscoreboard.info/game/" . $event['id'];

            $tweetResponse = $this->twitter->post('statuses/update', [
                'status' => $tweet
            ]);

            $game->setTwitterThreadId($tweetResponse->id);

            $this->getEntityManager()->persist($game);
        }

        $this->getEntityManager()->flush();
    }

    public function updateGame(Game $game): void {
        $id = $game->getEspnId();

        $event = $this->espnApi->getEventSummary($id);
        $videos = $this->espnApi->parseVideosFromEvent($event, $game->getLastVideoId());
        $plays = $this->espnApi->parsePlaysFromDrives($event, $game->getLastPlayId());
        
        $em = $this->getEntityManager();

        $lastPlayId = $game->getLastPlayId();
        $lastVideoId = $game->getLastVideoId();

        $gameStatus = $event['header']['competitions'][0]['status']['type']['name'];
        $competitors = $event['header']['competitions'][0]['competitors'];

        $homeScore = 0;
        $awayScore = 0;

        foreach ($competitors as $competitor) {
            if ($competitor['homeAway'] === 'home') {
                $homeScore = $competitor['score'];
            }
            else {
                $awayScore = $competitor['score'];
            }
        }

        $game->setStatus($gameStatus);

        foreach ($plays as $playId => $play) {
            $tweetResponse = null;

            if ($play['espn_id'] > $lastPlayId) {
                $lastPlayId = $play['espn_id'];
            }

            $gameHashTag = '#' . $game->getAwayTeam()->getAbbreviation() . 'vs' . $game->getHomeTeam()->getAbbreviation();
            $gameHashTag .= ' #' . str_replace(' ', '', $play['team']->getName());

            $scoreText = $game->getHomeTeam()->getDisplayName() . ": " . $homeScore .
                " " . $game->getAwayTeam()->getDisplayName() . ": " . $awayScore;

            $entity = new Play;
            $entity->setTeam($play['team']);
            $entity->setEspnId($play['espn_id']);
            $entity->setGame($game);
            $entity->setDown($play['down']);
            $entity->setYards($play['yards']);
            $entity->setTurnover($play['turnover']);
            $entity->setScoringPlay($play['scoring_play']);
            $entity->setPlayType($play['play_type']);
            $entity->setText($play['text']);

            $bigPass = $play['play_type'] === "Pass Reception" && $play['yards'] >= 30;
            $bigRush = $play['play_type'] === "Rush" && $play['yards'] >= 20;

            if ($play['turnover'] || $play['scoring_play'] || $bigPass || $bigRush) {

                if ($play['down'] == 4) {
                    $play['text'] = 'Fourth Down! ' . $play['text'];
                }

                $tweetResponse = $this->twitter->post('statuses/update', [
                    'status' => $play['text'] . "\n\n" . $scoreText . "\n\n" . $gameHashTag,
                    'in_reply_to_status_id' => $game->getTwitterThreadId()
                ]);
            }

            $em->persist($entity);

            if ($tweetResponse && property_exists($tweetResponse, 'id')) {
                $game->setTwitterThreadId($tweetResponse->id);
            }
        }

        foreach ($videos as $videoId => $video) {
            $lastVideoId = $video['id'];
            try {
                $videoLink = 'https://nflscoreboard.info/game/' . $game->getEspnId() . '/videos/' . $video['id'];

                $tweetResponse = $this->twitter->post('statuses/update', [
                    'status' => $video['description'] . "\n\n" . $videoLink,
                    'in_reply_to_status_id' => $game->getTwitterThreadId()
                ]);

                if ($tweetResponse && property_exists($tweetResponse, 'id')) {
                    $game->setTwitterThreadId($tweetResponse->id);
                }
            } catch (\Exception $e) {
            }
        }

        $game->setLastAnalyzed(new DateTime());
        $game->setLastPlayId($lastPlayId);
        $game->setLastVideoId($lastVideoId);

        if ($gameStatus === 'STATUS_FINAL') {
            $tweetResponse = $this->twitter->post('statuses/update', [
                'status' => "End of game!" . "\n\n" . $scoreText . "\n\n" . $gameHashTag,
                'in_reply_to_status_id' => $game->getTwitterThreadId()
            ]);

            if ($tweetResponse && property_exists($tweetResponse, 'id')) {
                $game->setTwitterThreadId($tweetResponse->id);
            }
        }

        $em->flush();

        return;
    }

    private function parseTeam(array $team): array {
        return [
            'espn_id' => $team['id'],
            'location' => $team['location'],
            'name' => $team['name'] ?? '',
            'abbreviation' => $team['abbreviation'],
            'displayName' => $team['displayName'],
            'espn_venue_id' => $team['venue']['id']
        ];
    }

    private function parseVenue(array $venue): array {
        return [
            'name' => $venue['fullName'],
            'indoor' => $venue['indoor'],
            'city' => $venue['address']['city'] ?? "",
            'state' => $venue['address']['state'] ?? "",
            'capacity' => $venue['capacity'] ?? 0,
            'espn_id' => $venue['id']
        ];
    }

    private function getTeamByHomeAway(array $teams, string $search): array {
        foreach ($teams as $team) {
            if ($team['homeAway'] === $search) {
                return $team;
            }
        }

        return [];
    }

    // /**
    //  * @return Game[] Returns an array of Game objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('g.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Game
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
