<?php

namespace App\Command;

use App\Repository\FollowQueueRepository;
use App\TwitterService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Unfollow extends Command
{
    protected static $defaultName = 'app:unfollow';
    private $followQueueRepository;
    private $twitter;

    public function __construct(
        TwitterService $twitter,
        FollowQueueRepository $followQueueRepository
    ) {
        $this->followQueueRepository = $followQueueRepository;
        $this->twitter = $twitter;

        parent::__construct();
    }

    protected function configure(): void {
        $this->setDescription("follows the queued accounts");
    }

    protected function execute(InputInterface $input, OutputInterface $output): int {
        $toUnfollow = $this->followQueueRepository->getAccountsToUnfollow();

        $output->writeLn("Trying to follow");

        foreach ($toUnfollow as $unfollow) {
            /** @var \App\Entity\FollowQueue $unfollow */

            $output->writeLn("Unfollowing " . $unfollow->getTwitterId());

            try {
                $test = $this->twitter->unfollow($unfollow->getTwitterId());
            } catch (\Throwable $e) {
                $output->writeLn("Error: " . $e->getMessage());
            }

            // mark unfollowed even if it errored.
            $this->followQueueRepository->markUnfollowed($unfollow->getTwitterId());

            break;
        }

        return Command::SUCCESS;
    }
}
