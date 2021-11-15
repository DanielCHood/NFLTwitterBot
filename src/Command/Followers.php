<?php

namespace App\Command;

use App\Repository\FollowQueueRepository;
use App\TwitterService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Followers extends Command
{
    protected static $defaultName = 'app:followers';
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
        $this->setDescription("updates the followers queues results with follow backs");
    }

    protected function execute(InputInterface $input, OutputInterface $output): int {
        $output->writeLn("Pulling followers");

        // gets last 5,000 followers
        $followerIds = [];

        $followerIds = $this->twitter->getFollowers('nflbigplays');
        $queued = $this->followQueueRepository->findBy(['twitterId' => $followerIds]);

        foreach ($queued as $queueEntry) {
            /** @var \App\Entity\FollowQueue $queueEntry */
            $this->followQueueRepository->markFollowedBack($queueEntry);
        }

        return Command::SUCCESS;
    }
}
