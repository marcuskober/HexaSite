<?php

namespace App\Markdown;

use League\CommonMark\Extension\CommonMark\Node\Block\Heading;
use League\CommonMark\Node\Inline\Text;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use League\CommonMark\Util\HtmlElement;
use Symfony\Component\String\Slugger\SluggerInterface;

class HeadingRenderer implements NodeRendererInterface
{
    public function __construct(private readonly SluggerInterface $slugger)
    {
    }

    public function render(Node $node, ChildNodeRendererInterface $childRenderer): HtmlElement
    {
        if (!$node instanceof Heading) {
            throw new \InvalidArgumentException('$node must be an instance of Heading');
        }

        $plainText = $this->getPlainText($node);

        $tag = 'h'.$node->getLevel();
        $attrs = $node->data->get('attributes');
        $attrs['id'] = (string)$this->slugger->slug($plainText)->lower();

        return new HtmlElement($tag, $attrs, $childRenderer->renderNodes($node->children()));
    }

    private function getPlainText(Node $node): string
    {
        $text = '';

        foreach ($node->children() as $child) {
            if ($child instanceof Text) {
                $text .= $child->getLiteral();
            }

            $text .= $this->getPlainText($child);
        }

        return $text;
    }
}