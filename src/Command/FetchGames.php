<?php

namespace App\Command;

use App\Repository\GameRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FetchGames extends Command
{
    protected static $defaultName = 'app:fetch-games';
    private $gameRepo;

    public function __construct(GameRepository $gameRepo) {
        $this->gameRepo = $gameRepo;
        parent::__construct();
    }

    protected function configure(): void {
        $this->setDescription("fetches all games")
            ->addArgument('week', InputArgument::REQUIRED, 'Which season week would you like to import?');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int {
        $text = 'Importing Week ' . $input->getArgument('week');

        $output->writeLn($text);

        $this->gameRepo->importWeek($input->getArgument('week'));

        return Command::SUCCESS;
    }
}
