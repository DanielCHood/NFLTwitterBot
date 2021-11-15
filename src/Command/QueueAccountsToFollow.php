<?php

namespace App\Command;

use App\Repository\FollowQueueRepository;
use App\Repository\GameRepository;
use App\TwitterService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class QueueAccountsToFollow extends Command
{
    protected static $defaultName = 'app:queue-accounts-to-follow';
    private $gameRepo;
    private $followQueueRepository;
    private $twitter;

    public function __construct(
        GameRepository $gameRepo,
        TwitterService $twitter,
        FollowQueueRepository $followQueueRepository
    ) {
        $this->gameRepo = $gameRepo;
        $this->followQueueRepository = $followQueueRepository;
        $this->twitter = $twitter;

        parent::__construct();
    }

    protected function configure(): void {
        $this->setDescription("queue accounts to follow");
    }

    protected function execute(InputInterface $input, OutputInterface $output): int {
        $games = $this->gameRepo->getLastWeeksGames();

        $toFollow = [];

        foreach ($games as $game) {
            /** @var \App\Entity\Game $game */

            foreach ($game->getGameHashTags() as $hashTag) {
                $results = $this->twitter->searchTweets($hashTag);
                foreach ($results as $result) {
                    if (empty($toFollow[$result->author_id])) {
                        $toFollow[$result->author_id] = [];
                    }

                    $toFollow[$result->author_id][] = $hashTag;
                }

                // don't get rate limited by Twitter
                sleep(1);
            }
        }

        // remove already followed accounts
        $followed = $this->followQueueRepository->findBy(['twitterId' => array_keys($toFollow)]);
        foreach ($followed as $account) {
            unset($toFollow[$account->getTwitterId()]);
        }

        foreach ($toFollow as $twitterId => $matchingCriteria) {
            $this->followQueueRepository->create($twitterId, $matchingCriteria);
        }

        return Command::SUCCESS;
    }
}
