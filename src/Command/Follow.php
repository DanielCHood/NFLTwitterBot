<?php

namespace App\Command;

use App\Repository\FollowQueueRepository;
use App\TwitterService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Follow extends Command
{
    protected static $defaultName = 'app:follow';
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
        $toFollow = $this->followQueueRepository->findBy(['DateFollowed' => null]);

        $output->writeLn("Trying to follow");

        foreach ($toFollow as $follow) {
            /** @var \App\Entity\FollowQueue $follow */

            $output->writeLn("Following " . $follow->getTwitterId());

            try {
                $test = $this->twitter->follow($follow->getTwitterId());
            } catch (\Throwable $e) {}

            // mark followed even if it errored.
            $this->followQueueRepository->markFollowed($follow->getTwitterId());

            break;
        }

        return Command::SUCCESS;
    }
}
