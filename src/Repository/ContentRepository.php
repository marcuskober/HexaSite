<?php

namespace App\Repository;

use App\Config\SiteConfig;
use App\Content\ContentInterface;
use App\Factory\ContentFactory;
use App\Factory\MetaDataFactory;
use App\Service\CustomLinkRenderer;
use App\Torchlight\TorchlightCodeRenderer;
use App\ValueObject\MetaData;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\CommonMark\Node\Block\FencedCode;
use League\CommonMark\Extension\CommonMark\Node\Block\IndentedCode;
use League\CommonMark\Extension\CommonMark\Node\Inline\Link;
use League\CommonMark\MarkdownConverter;
use Spatie\YamlFrontMatter\YamlFrontMatter;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Finder\Finder;

final class ContentRepository
{
    private MarkdownConverter $converter;
    private string $currentUrl;
    private array $metaData = [];
    private array $items = [];
    private array $navigation = [];

    public function __construct(
        private readonly string                 $contentPath,
        private readonly MetaDataFactory        $metaDataFactory,
        private readonly ContentFactory         $contentFactory,
        private readonly SiteConfig             $siteConfig,
        private readonly TorchlightCodeRenderer $torchlightCodeRenderer,
    )
    {
        $environment = new Environment();
        $environment->addExtension(new CommonMarkCoreExtension());
        $environment->addRenderer(Link::class, new CustomLinkRenderer($this));
        $environment->addRenderer(FencedCode::class, $this->torchlightCodeRenderer);
        $environment->addRenderer(IndentedCode::class, $this->torchlightCodeRenderer);
        $this->converter = new MarkdownConverter($environment);
    }

    public function findAll(ProgressBar $progressBar): array
    {
        $finder = new Finder();
        $finder->files()->in($this->contentPath)->name('*.md');

        $this->items = [];

        $progressBar->setFormat('custom');
        $progressBar->setMessage('Parsing files');
        $progressBar->start($finder->count());

        foreach ($finder as $file) {
            $this->currentUrl = $file->getPath();

            $document = YamlFrontMatter::parseFile($file->getRealPath());
            $mdContent = $document->body();

            $relativePath = $file->getRelativePath();
            $metaData = $this->metaDataFactory->create($document->matter(), $relativePath);
            $metaData->setMarkdownPath($file->getRelativePathname());


            $mdContent = $this->converter->convert($mdContent);

            $item = $this->contentFactory->create($metaData, $mdContent);
            $this->items[] = $item;

            if ($this->siteConfig->use_navigation) {
                $navgationOrder = array_search($item->getMetaData()->getContentId(), $this->siteConfig->navigation);
                if ($navgationOrder !== false) {
                    $this->navigation[$navgationOrder] = $metaData;
                }
            }

            $progressBar->advance();
        }

        $progressBar->setMessage('Ready.');
        $progressBar->finish();

        ksort($this->navigation);

        return $this->items;
    }

    public function findByLayout(string $layout): array
    {
        return array_filter($this->items, function (ContentInterface $content) use ($layout) {
            return $content->getMetaData()->getLayout() === $layout;
        });
    }

    public function findByLayoutWithParameters(string $layout, array $sort = ['date' => 'DESC'], int $limit = -1): array
    {
        $items = $this->findByLayout($layout);

        $sortBy = 'get' . ucfirst(array_key_first($sort));
        $sortDir = $sort[array_key_first($sort)];
        usort($items, function (ContentInterface $itemA, ContentInterface $itemB) use ($sortBy, $sortDir) {
            if ($sortDir === 'DESC') {
                return $itemA->getMetaData()->$sortBy() < $itemB->getMetaData()->$sortBy();
            }
            return $itemA->getMetaData()->$sortBy() > $itemB->getMetaData()->$sortBy();
        });

        return $items;
    }

    public function getMetaDataByPath(string $path): ?MetaData
    {
        if (isset($this->metaData[$path])) {
            return $this->metaData[$path];
        }

        [$realPath, $relativePath] = $this->getPaths($path);

        if (!file_exists($realPath)) {
            return null;
        }

        $document = YamlFrontMatter::parseFile($realPath);

        $metaData = $this->metaDataFactory->create($document->matter(), $relativePath);
        $this->metaData[$path] = $metaData;
        return $metaData;
    }

    private function getPaths($path): array
    {
        $absolutePath = str_contains($path, '..') ? $this->currentUrl : $this->contentPath;
        $realPath = $absolutePath . DIRECTORY_SEPARATOR . $path;
        $relativePath = dirname($path);

        return [
            $realPath,
            $relativePath,
        ];
    }

    public function getNavigation(): array
    {
        return $this->navigation;
    }
}