<?php

namespace App\Content;

use App\ValueObject\MetaData;

interface ContentInterface
{
    public function getMetaData(): MetaData;
    public function getContent(): string;
}