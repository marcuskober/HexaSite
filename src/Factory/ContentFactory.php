<?php

namespace App\Factory;

use App\Content\Article;
use App\Content\ContentInterface;
use App\Content\Page;
use App\ValueObject\MetaData;

final class ContentFactory
{
    public function create(MetaData $metaData, string $content): ContentInterface
    {
        return match ($metaData->getLayout()) {
            'page' => new Page($metaData, $content),
            default => new Article($metaData, $content),
        };
    }
}