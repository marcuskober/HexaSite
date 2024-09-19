<?php

namespace App\Content;

use App\ValueObject\MetaData;

trait ContentTrait
{
    public function __construct(
        private MetaData $metaData,
        private string $content,
    )
    {
    }

    public function getMetaData(): MetaData
    {
        return $this->metaData;
    }

    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    public function getContent(): string
    {
        return $this->content;
    }
}