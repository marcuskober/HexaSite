<?php

namespace App\Command;

use App\Collector\ItemCollector;
use App\Collector\NavigationCollector;
use App\Config\SiteConfig;
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
        private readonly SiteConfig $siteConfig,
    )
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $contentPath = $this->siteConfig->content_dir;
        $buildPath = $this->siteConfig->build_dir;

        $this->handleAssets($buildPath, $contentPath);

        ProgressBar::setFormatDefinition('custom', "\n%message%\n\n%current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s%\n");

        $progressBarItems = new ProgressBar($output, 100);
        $items = $this->contentProcessor->getAllItems($progressBarItems);

        $progressBar = new ProgressBar($output, count($items));
        $progressBar->setFormat('custom');
        $progressBar->setMessage('Start building...');
        $progressBar->start();

        $this->handleItems($items, $progressBar);
        $this->handleCategories();

        $progressBar->setMessage('Ready.');
        $progressBar->finish();
        $io->success('Site successfully built!');

        $this->itemWriter->cleanUp();

        return Command::SUCCESS;
    }

    private function handleAssets(string $buildPath, string $contentPath): void
    {
        $buildAssetPath = $buildPath . '/assets';
        $contentAssetPath = $contentPath . DIRECTORY_SEPARATOR . 'assets';
        $themeAssetPath = 'assets';

        // Clear build asset folder
        if (is_dir($buildAssetPath)) {
            $this->fileSystem->remove($buildAssetPath);
        }

        // Copy theme files
        if (is_dir($themeAssetPath)) {
            $this->fileSystem->mirror($themeAssetPath, $buildAssetPath);
        }

        // Copy content assets
        if (is_dir($contentAssetPath)) {
            $this->fileSystem->mirror($contentAssetPath, $buildAssetPath);
        }
    }

    private function handleItems(array $items, ProgressBar $progressBar): void
    {
        $navigationHasChanged = $this->navigationCollector->hasNavigationChanged();

        foreach ($items as $item) {
            $slug = $item->getMetaData()->getSlug();

            $renderedItem = $this->contentProcessor->processItem($item);
            $itemContentHash = md5($renderedItem);
            $itemHasChanged = $this->itemCollector->hasItemChanged($slug, $itemContentHash);

            if (!$navigationHasChanged && !$itemHasChanged) {
                $progressBar->setMessage('No changes: <fg=green>' . $item->getMetaData()->getMarkdownPath() . '</>');
                $progressBar->advance();
                continue;
            }

            $progressBar->setMessage('Converting <fg=green>' . $item->getMetaData()->getMarkdownPath() . '</> to <fg=green>' . $item->getMetaData()->getSlug() . '</>');
            $progressBar->advance();

            $this->itemWriter->writeItem($slug, $renderedItem);

            if ($itemHasChanged) {
                $this->itemCollector->updateItem($slug, $itemContentHash);
            }
        }

        $this->itemCollector->findDeletions();
    }

    private function handleCategories(): void
    {
        $this->itemWriter->writeCategories($this->contentProcessor->processCategories());
    }
}
