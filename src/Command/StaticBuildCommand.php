<?php

namespace App\Command;

use App\Collector\ItemCollector;
use App\Collector\NavigationCollector;
use App\Service\ContentProcessor;
use App\Service\ItemWriter;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;

#[AsCommand(
    name: 'static:build',
    description: 'Build the static pages',
)]
class StaticBuildCommand extends Command
{
    public function __construct(
        private readonly ContentProcessor    $contentProcessor,
        private readonly ItemWriter          $itemWriter,
        private readonly Filesystem          $fileSystem,
        private readonly NavigationCollector $navigationCollector,
        private readonly ItemCollector       $itemCollector,
        private readonly string              $contentPath,
    )
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $this->fileSystem->remove('build/assets');
        $this->fileSystem->mirror('assets', 'build/assets');
        $contentAssetPath = $this->contentPath . DIRECTORY_SEPARATOR . 'assets';
        if (is_dir($contentAssetPath)) {
            $this->fileSystem->mirror($contentAssetPath, 'build/assets');
        }

        ProgressBar::setFormatDefinition('custom', "\n%message%\n\n%current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s%\n");

        $progressBarItems = new ProgressBar($output, 100);
        $items = $this->contentProcessor->getAllItems($progressBarItems);

        $progressBar = new ProgressBar($output, count($items));
        $progressBar->setFormat('custom');
        $progressBar->setMessage('Start building...');
        $progressBar->start();

        $navigationHasChanged = $this->navigationCollector->hasNavigationChanged();

        foreach ($items as $item) {
            $slug = $item->getMetaData()->getSlug();
            $renderedItem = $this->contentProcessor->processItem($item);

            $itemHasChanged = $this->itemCollector->hasItemChanged($slug, md5($renderedItem));

            if (!$navigationHasChanged && !$itemHasChanged) {
                $progressBar->setMessage('Skipping <fg=green>' . $item->getMetaData()->getMarkdownPath() . '</>');
                $progressBar->advance();
                continue;
            }

            $progressBar->setMessage('Converting <fg=green>' . $item->getMetaData()->getMarkdownPath() . '</> to <fg=green>' . $item->getMetaData()->getSlug() . '</>');
            $progressBar->advance();

            $this->itemWriter->writeItem($slug, $renderedItem);

            if ($itemHasChanged) {
                $this->itemCollector->updateItem($slug, md5($renderedItem));
            }
        }

        $this->itemCollector->findDeletions();

        $progressBar->setMessage('Ready.');
        $progressBar->finish();
        $io->success('Site successfully built!');

        $this->itemWriter->cleanUp();

        return Command::SUCCESS;
    }
}
