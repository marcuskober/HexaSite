<?php

namespace App\Event;

use App\ValueObject\MetaData;
use Symfony\Contracts\EventDispatcher\Event;

final class NavigationItemAddedEvent extends Event
{
    public function __construct(private readonly MetaData $metaData)
    {
    }

    public function getMetaData(): MetaData
    {
        return $this->metaData;
    }
}