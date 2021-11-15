<?php

namespace App\Command;

use App\Repository\GameRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FetchPlays extends Command
{
    protected static $defaultName = 'app:fetch-plays';
    private $gameRepo;

    public function __construct(GameRepository $gameRepo) {
        $this->gameRepo = $gameRepo;
        parent::__construct();
    }

    protected function configure(): void {
        $this->setDescription("fetches all plays");
    }

    protected function execute(InputInterface $input, OutputInterface $output): int {
        $games = $this->gameRepo->getActiveGames();

        foreach ($games as $game) {
            $output->writeLn("Working on " . $game->getName());

            $this->gameRepo->updateGame($game);
        }

        return Command::SUCCESS;
    }
}
