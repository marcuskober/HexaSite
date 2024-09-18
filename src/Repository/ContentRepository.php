<?php

namespace App\Repository;

use App\Content\ContentInterface;
use App\Factory\ContentFactory;
use App\Factory\MetaDataFactory;
use App\Service\CustomLinkRenderer;
use App\ValueObject\MetaData;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\CommonMark\Node\Inline\Link;
use League\CommonMark\MarkdownConverter;
use Spatie\YamlFrontMatter\YamlFrontMatter;
use Symfony\Component\Finder\Finder;

final class ContentRepository
{
    private MarkdownConverter $converter;
    private string $currentUrl;
    private array $metaData = [];

    public function __construct(
        private readonly string $contentPath,
        private readonly MetaDataFactory $metaDataFactory,
        private readonly ContentFactory $contentFactory,
    )
    {
        $environment = new Environment();
        $environment->addExtension(new CommonMarkCoreExtension());
        $environment->addRenderer(Link::class, new CustomLinkRenderer($this));
        $this->converter = new MarkdownConverter($environment);
    }

    public function findAll(): array
    {
        $finder = new Finder();
        $finder->files()->in($this->contentPath)->name('*.md');

        $items = [];

        foreach ($finder as $file) {
            $this->currentUrl = $file->getPath();

            $document = YamlFrontMatter::parseFile($file->getRealPath());
            $mdContent = $this->converter->convert($document->body());

            $relativePath = $file->getRelativePath();

            $metaData = $this->metaDataFactory->create($document->matter(), $relativePath);
            $metaData->setMarkdownPath($file->getRelativePathname());
            $items[] = $this->contentFactory->create($metaData, $mdContent);
        }

        return $items;
    }

    public function findByLayout(string $layout): array
    {
        return array_filter($this->findAll(), function (ContentInterface $content) use ($layout) {
            return $content->getMetaData()->getLayout() === $layout;
        });
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
}