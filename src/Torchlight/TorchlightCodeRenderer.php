<?php

namespace App\Torchlight;

use League\CommonMark\Extension\CommonMark\Node\Block\FencedCode;
use League\CommonMark\Extension\CommonMark\Node\Block\IndentedCode;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use League\CommonMark\Util\HtmlElement;

class TorchlightCodeRenderer implements NodeRendererInterface
{
    public function __construct(private TorchlightApi $torchlightApi)
    {
    }

    public function render(Node $node, ChildNodeRendererInterface $childRenderer): HtmlElement
    {
        if (!$node instanceof FencedCode && !$node instanceof IndentedCode) {
            throw new \InvalidArgumentException('$node must be an instance of Code');
        }

        $code = $node->getLiteral();
        $language = $node->getInfo(); // Sprache des Code-Blocks

        // Cache-Abfrage oder API-Aufruf
        $highlightedCode = $this->torchlightApi->highlight($code, $language);

        return new HtmlElement('pre', [], new HtmlElement('code', ['class' => "language-$language"], $highlightedCode));
    }
}