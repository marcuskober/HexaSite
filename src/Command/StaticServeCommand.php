<?php

namespace App\Command;

use App\Config\SiteConfig;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'static:serve',
    description: 'Spin up a local server',
)]
class StaticServeCommand extends Command
{
    public function __construct(private readonly SiteConfig $siteConfig)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('port', null, InputOption::VALUE_OPTIONAL, 'Port', 8000)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $port = $input->getOption('port');

        $io->writeln('Starting the server at http://localhost:' . $port);

        $cliCommand = sprintf('php -S localhost:%d -t %s', $port, $this->siteConfig->build_dir);
        passthru($cliCommand);

        $io->success('Server is up and running');

        return Command::SUCCESS;
    }
}
