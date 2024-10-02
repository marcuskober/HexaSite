<?php

namespace App\Provider;

use App\Config\SiteConfig;
use App\Content\ContentInterface;
use App\Event\NavigationCompletedEvent;
use App\Event\NavigationItemAddedEvent;
use App\Factory\ContentFactory;
use App\Factory\MetaDataFactory;
use App\Markdown\CustomLinkRenderer;
use App\Markdown\HeadingRenderer;
use App\Markdown\TorchlightCodeRenderer;
use App\Repository\ItemMetaDataRepository;
use App\ValueObject\MetaData;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\CommonMark\Node\Block\FencedCode;
use League\CommonMark\Extension\CommonMark\Node\Block\Heading;
use League\CommonMark\Extension\CommonMark\Node\Block\IndentedCode;
use League\CommonMark\Extension\CommonMark\Node\Inline\Link;
use League\CommonMark\MarkdownConverter;
use Spatie\YamlFrontMatter\YamlFrontMatter;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\String\Slugger\SluggerInterface;

final class ContentProvider
{
    private MarkdownConverter $converter;
    private string $currentUrl;
    private array $metaData = [];
    private array $items = [];
    private array $navigation = [];

    public function __construct(
        private readonly SiteConfig             $siteConfig,
        private readonly MetaDataFactory        $metaDataFactory,
        private readonly ContentFactory         $contentFactory,
        private readonly TorchlightCodeRenderer $torchlightCodeRenderer,
        private readonly SluggerInterface $slugger,
        private readonly ItemMetaDataRepository $itemMetaDataRepository,
        private readonly EventDispatcherInterface $eventDispatcher,
    )
    {
        $environment = new Environment();
        $environment->addExtension(new CommonMarkCoreExtension());
        $environment->addRenderer(Link::class, new CustomLinkRenderer($this));
        $environment->addRenderer(FencedCode::class, $this->torchlightCodeRenderer);
        $environment->addRenderer(IndentedCode::class, $this->torchlightCodeRenderer);
        $environment->addRenderer(Heading::class, new HeadingRenderer($slugger));

        $this->converter = new MarkdownConverter($environment);
    }

    public function findAll(ProgressBar $progressBar): array
    {
        $finder = new Finder();
        $finder->files()->in($this->siteConfig->content_dir)->name('*.md');

        $this->items = [];

        $progressBar->setFormat('custom');
        $progressBar->setMessage('Parsing files');
        $progressBar->start($finder->count());

        $multilanguageEnabled = $this->siteConfig->multilanguage && $this->siteConfig->multilanguage['enabled'];
        if ($multilanguageEnabled) {
            $languages = $this->siteConfig->multilanguage['languages'];
            foreach ($languages as $language) {
                $this->navigation[$language] = [];
            }
        }

        foreach ($finder as $file) {
            $this->currentUrl = $file->getPath();

            $document = YamlFrontMatter::parseFile($file->getRealPath());
            $mdContent = $document->body();

            $relativePath = $file->getRelativePath();
            $metaData = $this->metaDataFactory->create($document->matter(), $relativePath);
            $metaData->setMarkdownPath($file->getRelativePathname());

            $mdContent = $this->converter->convert($mdContent);

            $item = $this->contentFactory->create($metaData, $mdContent);
            $this->items[$metaData->getContentId().'@'.$metaData->getLang()] = $item;

            if ($this->siteConfig->use_navigation) {
                if ($multilanguageEnabled) {
                    $navgationOrder = array_search($item->getMetaData()->getContentId(), $this->siteConfig->navigation[$metaData->getLang()]);
                    if ($navgationOrder !== false) {
                        $this->navigation[$metaData->getLang()][$navgationOrder] = $metaData;

                        $this->eventDispatcher->dispatch(new NavigationItemAddedEvent($metaData));
                    }
                }
                else {
                    $navgationOrder = array_search($item->getMetaData()->getContentId(), $this->siteConfig->navigation);
                    if ($navgationOrder !== false) {
                        $this->navigation[$navgationOrder] = $metaData;

                        $this->eventDispatcher->dispatch(new NavigationItemAddedEvent($metaData));
                    }
                }
            }

            $progressBar->advance();
        }

        if ($multilanguageEnabled) {
            foreach ($this->items as $item) {
                foreach ($languages as $language) {
                    if (!isset($this->items[$item->getMetaData()->getContentId().'@'.$language])) {
                        continue;
                    }

                    $langItem = $this->items[$item->getMetaData()->getContentId().'@'.$language];
                    $item->getMetaData()->addAlternative($language, $langItem->getMetaData());
                }
            }

            foreach ($languages as $language) {
                ksort($this->navigation[$language]);
            }
        }
        else {
            ksort($this->navigation);
        }

        $progressBar->setMessage('Ready.');
        $progressBar->finish();

        $this->eventDispatcher->dispatch(new NavigationCompletedEvent());

        return $this->items;
    }

    public function findByLayout(string $layout): array
    {
        return array_filter($this->items, function (ContentInterface $content) use ($layout) {
            return $content->getMetaData()->getLayout() === $layout;
        });
    }

    public function findByLayoutWithParameters(string $layout, string $language = 'en', array $sort = ['date' => 'DESC'], int $limit = -1): array
    {
        $multilanguageEnabled = $this->siteConfig->multilanguage && $this->siteConfig->multilanguage['enabled'];

        $items = $multilanguageEnabled ? $this->findByLayoutAndLanguage($layout, $language) : $this->findByLayout($layout);

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
        $absolutePath = str_contains($path, '..') ? $this->currentUrl : $this->siteConfig->content_dir;
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

    private function findByLayoutAndLanguage(string $layout, string $language)
    {
        return array_filter($this->items, function (ContentInterface $content) use ($layout, $language) {
            return $content->getMetaData()->getLayout() === $layout && $content->getMetaData()->getLang() === $language;
        });
    }
}