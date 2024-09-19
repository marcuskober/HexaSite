<?php

namespace App\Command;

use App\Service\ContentProcessor;
use App\Service\ItemWriter;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'static:build',
    description: 'Build the static pages',
)]
class StaticBuildCommand extends Command
{
    public function __construct(
        private readonly ContentProcessor $contentProcessor,
        private readonly ItemWriter       $itemWriter,
    )
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $items = $this->contentProcessor->getAllItems();

        ProgressBar::setFormatDefinition('custom', "\n%message%\n\n%current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s%\n");
        $progressBar = new ProgressBar($output, count($items));
        $progressBar->setFormat('custom');
        $progressBar->setMessage('Start building...');
        $progressBar->start();

        foreach ($items as $item) {
            $renderedItem = $this->contentProcessor->processItem($item);

            $progressBar->setMessage('Converting <fg=green>' . $item->getMetaData()->getMarkdownPath() . '</> to <fg=green>' . $item->getMetaData()->getSlug() . '</>');
            $progressBar->advance();

            $this->itemWriter->writeItem($item->getMetaData()->getSlug(), $renderedItem);
        }

        $progressBar->setMessage('Ready.');
        $progressBar->finish();
        $io->success('Site successfully built!');

        $this->itemWriter->cleanUp();

        return Command::SUCCESS;
    }
}
