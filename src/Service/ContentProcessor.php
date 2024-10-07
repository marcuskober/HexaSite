<?php

namespace App\Service;

use App\Config\SiteConfig;
use App\Content\ContentInterface;
use App\Content\Image;
use App\Provider\ContentProvider;
use App\ValueObject\MetaData;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Finder\Finder;
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
            'categories' => $this->contentProvider->getCategories(),
        ]);
    }

    public function processCategories(): array
    {
        $categories = [];
        $isMultilanguage = $this->siteConfig->multilanguage && $this->siteConfig->multilanguage['enabled'];
        foreach ($this->contentProvider->getCategories() as $category) {
            $basePath = '../';
            if ($isMultilanguage && $category->getMetaData()->getLang() !== $this->siteConfig->multilanguage['main']) {
                $basePath = str_repeat($basePath, 2);
            }
            $content = $this->twig->render('category.html.twig', context: [
                'base_path' => $basePath,
                'item' => $category,
                'navigation' => $this->contentProvider->getNavigation(),
                'multilanguage' => $isMultilanguage,
                'categories' => $this->contentProvider->getCategories(),
            ]);
            $category->setContent($content);
            $categories[] = $category;
        }

        return $categories;
    }

    private function getBasePath(MetaData $metaData): string
    {
        $slug = $metaData->getSlug();
        $depth = substr_count($slug, '/');
        return str_repeat('../', $depth);
    }

    private function processSubTemplates(ContentInterface $item, string $basePath): ContentInterface
    {
        $item = $this->processArchive($item, $basePath);

        $itemContent = $item->getContent();
        $matched = preg_match_all('#<!--:([^:]+):-->#iU', $itemContent, $matches);

        if ($matched > 0) {
            foreach ($matches[1] as $key => $code) {
                $completeCode = $matches[0][$key];
                $code = explode(' ', trim($code));
                $itemType = array_shift($code);

                $method = 'process' . ucfirst($itemType);
                if (method_exists($this, $method)) {
                    $item = $this->$method($item, $basePath, $code, $completeCode);
                }
            }
        }

        return $item;
    }

    private function processGallery(ContentInterface $item, string $basePath, array $code, string $completeCode): ContentInterface
    {
        $dir = null;
        foreach ($code as $parameter) {
            $parameterDetails = explode('=', $parameter);
            $parameter = trim($parameterDetails[0]);
            $value = trim($parameterDetails[1]);

            switch ($parameter) {
                case 'dir':
                    $dir = str_replace('"', '', $value);
                    break;
            }
        }

        if (!$dir) {
            return $item;
        }

        $realDir = realpath($this->siteConfig->content_dir . '/' . $item->getMetaData()->getPath() . '/' . $dir);

        $finder = new Finder();
        $finder->files()->in($realDir)->name(['*.jpg', '*.jpeg', '*.png', '*.gif', '*.webp']);

        $gallery = [];
        foreach ($finder as $file) {
            $imageSrc = $dir . '/' . $file->getFilename();
            $image = new Image($file->getPathname(), $imageSrc, '', $this->siteConfig->build_dir);
            $gallery[] = $image;
        }

        $galleryContent = $this->twig->render('_gallery.html.twig', [
            'gallery' => $gallery,
            'base_path' => $basePath,
        ]);

        $item->setContent(str_replace($completeCode, $galleryContent, $item->getContent()));
        return $item;
    }

    private function processArchive(ContentInterface $item, string $basePath): ContentInterface
    {
        if ($item->getMetaData()->getArchive()) {
            $archiveConfig = $item->getMetaData()->getArchive();
            $archive = $this->contentProvider->findByLayoutWithParameters(
                $archiveConfig['layout'],
                $item->getMetaData()->getLang(),
                limit: $archiveConfig['count'] ?? -1
            );

            $archiveContent = $this->twig->render('_archive.html.twig', [
                'base_path' => $basePath,
                'archive' => $archive,
                'categories' => $this->contentProvider->getCategories(),
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