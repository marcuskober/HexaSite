<?php

namespace App\Command;

use App\Repository\ContentRepository;
use App\Service\ItemWriter;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
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

        $items = $this->contentRepository->findAll();

        foreach ($items as $item) {
            $template = $this->getTemplate($item->getMetaData()->getLayout());

            $renderedItem = $this->twig->render($template, [
                'item' => $item,
            ]);

            $this->itemWriter->writeItem($item->getMetaData()->getSlug(), $renderedItem);
        }

        $this->itemWriter->cleanUp();

        return Command::SUCCESS;
    }

    private function getTemplate(string $layout): string
    {
        return $layout . '.html.twig';
    }
}
