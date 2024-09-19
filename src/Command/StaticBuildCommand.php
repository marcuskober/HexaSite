<?php

namespace App\Command;

use App\Config\SiteConfig;
use App\Content\ContentInterface;
use App\Repository\ContentRepository;
use App\Service\ItemWriter;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Twig\Environment;

#[AsCommand(
    name: 'static:build',
    description: 'Build the static pages',
)]
class StaticBuildCommand extends Command
{
    public function __construct(
        private ContentRepository $contentRepository,
        private Environment $twig,
        private ItemWriter $itemWriter,
        private SiteConfig $siteConfig,
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
//        $this
//            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
//            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
//        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        /** @var ContentInterface[] $items */
        $items = $this->contentRepository->findAll();

        ProgressBar::setFormatDefinition('custom', "\n%message%\n\n%current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s%\n");

        $progressBar = new ProgressBar($output, count($items));
        $progressBar->setFormat('custom');
        $progressBar->setMessage('Start building...');
        $progressBar->start();

        foreach ($items as $item) {
            $template = $this->getTemplate($item->getMetaData()->getLayout());

            $slug = $item->getMetaData()->getSlug();
            $depth = substr_count($slug, '/');
            $basePath = str_repeat('../', $depth);

            if ($item->getMetaData()->getArchive()) {
                $archiveConfig = $item->getMetaData()->getArchive();
                $archive = $this->contentRepository->findByLayoutWithParameters($archiveConfig['layout']);

                $archiveContent = $this->twig->render('_archive.html.twig', [
                    'base_path' => $basePath,
                    'archive' => $archive,
                ]);

                $itemContent = str_replace('<p>{{ archive|raw }}</p>', '{{ archive|raw }}', $item->getContent());
                $itemContent = $this->twig->createTemplate($itemContent)->render(['archive' => $archiveContent]);
                $item->setContent($itemContent);
            }

            $renderedItem = $this->twig->render($template, [
                'base_path' => $basePath,
                'item' => $item,
                'navigation' => $this->contentRepository->getNavigation(),
            ]);

            $progressBar->setMessage('Converting <fg=green>' . $item->getMetaData()->getMarkdownPath() . '</> to <fg=green>' . $item->getMetaData()->getSlug() . '</>');
            $progressBar->advance();

            $this->itemWriter->writeItem($item->getMetaData()->getSlug(), $renderedItem);
        }

        $progressBar->finish();

        $this->itemWriter->cleanUp();

        return Command::SUCCESS;
    }

    private function getTemplate(string $layout): string
    {
        return $layout . '.html.twig';
    }
}
