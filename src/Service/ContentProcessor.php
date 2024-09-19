<?php

namespace App\Service;

use App\Content\ContentInterface;
use App\Repository\ContentRepository;
use App\ValueObject\MetaData;
use Twig\Environment;

final readonly class ContentProcessor
{
    public function __construct(
        private ContentRepository $contentRepository,
        private Environment       $twig,
    )
    {
    }

    /**
     * @return ContentInterface[]
     */
    public function getAllItems(): array
    {
        return $this->contentRepository->findAll();
    }

    public function processItem(ContentInterface $item): string
    {
        $basePath = $this->getBasePath($item->getMetaData());
        $item = $this->processSubTemplates($item, $basePath);
        $template = $this->getTemplate($item->getMetaData()->getLayout());

        return $this->twig->render($template, [
            'base_path' => $basePath,
            'item' => $item,
            'navigation' => $this->contentRepository->getNavigation(),
        ]);
    }

    private function getBasePath(MetaData $metaData): string
    {
        $slug = $metaData->getSlug();
        $depth = substr_count($slug, '/');
        return str_repeat('../', $depth);
    }

    private function processSubTemplates(ContentInterface $item, string $basePath): ContentInterface
    {
        if ($item->getMetaData()->getArchive()) {
            $archiveConfig = $item->getMetaData()->getArchive();
            $archive = $this->contentRepository->findByLayoutWithParameters($archiveConfig['layout']);

            $archiveContent = $this->twig->render('_archive.html.twig', [
                'base_path' => $basePath,
                'archive' => $archive,
            ]);

            $itemContent = str_replace('<!--: archive :-->', '{{ archive|raw }}', $item->getContent());
            $itemContent = $this->twig->createTemplate($itemContent)->render(['archive' => $archiveContent]);
            $item->setContent($itemContent);
        }

        return $item;
    }

    private function getTemplate(string $layout): string
    {
        return $layout . '.html.twig';
    }
}