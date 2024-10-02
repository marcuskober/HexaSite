<?php

namespace App\Markdown;

use App\Provider\ContentProvider;
use League\CommonMark\Extension\CommonMark\Node\Inline\Link;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use League\CommonMark\Util\HtmlElement;

class CustomLinkRenderer implements NodeRendererInterface
{
    public function __construct(
        private ContentProvider $contentRepository,
    )
    {
    }

    public function render(Node $node, ChildNodeRendererInterface $childRenderer)
    {
        if (!($node instanceof Link)) {
            throw new \InvalidArgumentException('Incompatible node type: ' . get_class($node));
        }

        $url = $node->getUrl();

        if (str_ends_with($url, '.md')) {
            $metaData = $this->contentRepository->getMetaDataByPath($url);
            if (!$metaData) {
                $attrs = $node->data->get('attributes');
                $attrs['href'] = $url;
                return new HtmlElement('a', $attrs, $childRenderer->renderNodes($node->children()));
            }
            $url = $metaData->getSlug();
        }

        $node->setUrl($url);

        $attrs = $node->data->get('attributes');
        $attrs['href'] = $url;

        return new HtmlElement('a', $attrs, $childRenderer->renderNodes($node->children()));
    }
}