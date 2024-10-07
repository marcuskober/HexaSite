<?php

namespace App\Markdown;

use App\Config\SiteConfig;
use League\CommonMark\Extension\CommonMark\Node\Inline\Image;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use League\CommonMark\Util\HtmlElement;

class ImageRenderer implements NodeRendererInterface
{
    private string $relativePath;
    private string $slug;

    public function __construct(
        private readonly SiteConfig $siteConfig
    )
    {
    }

    public function setRelativePath(string $relativePath): void
    {
        $this->relativePath = $relativePath;
    }

    public function setSlug(string $slug): void
    {
        $this->slug = $slug;
    }

    public function render(Node $node, ChildNodeRendererInterface $childRenderer): HtmlElement|string
    {
        if (!$node instanceof Image) {
            throw new \InvalidArgumentException('$node must be an instance of Image');
        }

        $src = $node->getUrl();
        $path = realpath($this->siteConfig->content_dir . '/' . $this->relativePath . '/' . $src);
        if (!$path) {
            return '<p>Missing image: ' . $src . '</p>';
        }

        $depth = substr_count($this->slug, '/');
        $basePath = str_repeat('../', $depth);

        $image = new \App\Content\Image($path, $src, '', $this->siteConfig->build_dir);

        $srcset = [$basePath . $image->src . ' ' . $image->width . 'w'];
        foreach ($image->getSizes() as $size) {
            $srcset[] = $basePath . $size['src'] . ' ' . $size['width'] . 'w';
        }

        $sizes = '(max-width: 30rem) 90vw, (min-width: 30rem) 50rem';

        return sprintf(
            '<img src="%s" alt="%s" width="%s" height="%s" loading="lazy" srcset="%s" sizes="%s">',
            $basePath . $image->src,
            $node->getTitle(),
            $image->width,
            $image->height,
            implode(', ', $srcset),
            $sizes
        );
    }
}