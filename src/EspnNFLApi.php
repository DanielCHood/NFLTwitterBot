<?php

namespace App;

use App\Repository\TeamRepository;

class EspnNFLApi
{
    const REGULAR_SEASON_TYPE = 2;

    private $apiBase = 'https://site.api.espn.com/apis/site/v2/sports/football/nfl/';
    private $teamRepository;

    public function __construct(TeamRepository  $teamRepository) {
        $this->teamRepository = $teamRepository;
    }

    public function getEventsFromApi(int $seasonType, int $week): array {
        $data = json_decode(file_get_contents($this->apiBase . 'scoreboard?' . http_build_query([
                'seasontype' => $seasonType,
                'week' => $week
            ])), true);

        return $data['events'];
    }

    public function getEventSummary(string $id): array {
        return json_decode(
            file_get_contents($this->apiBase . 'summary?event=' . $id),
            true
        );
    }

    public function parseVideosFromEvent(array $event, ?int $minVideoId): array {
        if (empty($game['videos'])) {
            return [];
        }

        if (is_null($minVideoId)) {
            $minVideoId = 0;
        }

        $videos = [];

        foreach ($game['videos'] as $video) {
            if ($minVideoId >= $video['id']) {
                continue;
            }

            $videos[$video['id']] = [
                'source' => $video['links']['source']['full']['href'],
                'description' => $video['description'],
                'id' => $video['id']
            ];
        }

        return $videos;
    }

    public function parsePlaysFromDrives(array $event, ?int $minPlay): array {
        $minPlay = $minPlay === null ? 0 : $minPlay;
        $drives = $event['drives'];
        $plays = [];

        foreach (array_keys($drives) as $driveType) {
            foreach ($drives[$driveType] as $drive) {
                // No clue what this drive is but for some reason 401333580 has a corrupt drive
                if (empty($drive['team']['abbreviation'])) {
                    continue;
                }

                $team = $drive['team']['abbreviation'];
                $team = $this->teamRepository->findOneBy(['abbreviation' => $team]);

                foreach ($drive['plays'] as $playKey => $play) {
                    if ($minPlay >= $play['id']) {
                        continue;
                    }

                    // end of period
                    if (!isset($play['type']['id']) || in_array($play['type']['id'], [2, 65, 66])) {
                        continue;
                    }

                    $play['team'] = $team;
                    $play['lastPlayOfDrive'] = !isset($drive['plays'][$playKey+1]);
                    $plays[$play['id']] = $this->parsePlay($play);
                }
            }
        }

        return $plays;
    }

    private function parsePlay(array $play): array {
        $notTurnover = $play['type']['text'] === 'Punt' || $play['type']['text'] === 'Kickoff';

        $turnover = false;
        if (isset($play['end']['team']['id']) && isset($play['start']['team']['id'])) {
            $turnover = $play['end']['team']['id'] !== $play['start']['team']['id'] && $play['lastPlayOfDrive'] && !$notTurnover;
        }

        return [
            'espn_id' => $play['id'],
            'play_type' => $play['type']['text'],
            'text' => $play['text'],
            'scoring_play' => $play['scoringPlay'],
            'yards' => $play['statYardage'],
            'down' => $play['start']['down'],
            'team' => $play['team'],
            'turnover' => $turnover,
        ];
    }
}
