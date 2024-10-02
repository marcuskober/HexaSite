<?php

namespace App\Service;

use App\Config\SiteConfig;
use App\Content\ContentInterface;
use App\Provider\ContentProvider;
use App\ValueObject\MetaData;
use Symfony\Component\Console\Helper\ProgressBar;
use Twig\Environment;

final readonly class ContentProcessor
{
    public function __construct(
        private ContentProvider $contentProvider,
        private Environment     $twig,
        private SiteConfig      $siteConfig,
    )
    {
    }

    /**
     * @return ContentInterface[]
     */
    public function getAllItems(ProgressBar $progressBar): array
    {
        return $this->contentProvider->findAll($progressBar);
    }

    public function processItem(ContentInterface $item): string
    {
        $basePath = $this->getBasePath($item->getMetaData());
        $item = $this->processSubTemplates($item, $basePath);
        $template = $this->getTemplate($item->getMetaData()->getLayout());

        return $this->twig->render($template, context: [
            'base_path' => $basePath,
            'item' => $item,
            'navigation' => $this->contentProvider->getNavigation(),
            'image' => $item->getMetaData()->getImage(),
            'multilanguage' => $this->siteConfig->multilanguage && $this->siteConfig->multilanguage['enabled'],
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
            $archive = $this->contentProvider->findByLayoutWithParameters($archiveConfig['layout'], $item->getMetaData()->getLang());

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