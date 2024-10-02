<?php

namespace App\Event;

use App\Collector\NavigationCollector;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class NavigationSubscriber implements EventSubscriberInterface
{
    public function __construct(private NavigationCollector $navigationCollector)
    {
    }

    public static function getSubscribedEvents()
    {
        return [
            NavigationItemAddedEvent::class => 'onNavigationItemAdded',
            NavigationCompletedEvent::class => 'onNavigationCompleted',
        ];
    }

    public function onNavigationItemAdded(NavigationItemAddedEvent $event): void
    {
        $metaData = $event->getMetaData();

        $this->navigationCollector->createSlugIfNotExists($metaData->getSlug());
    }

    public function onNavigationCompleted(NavigationCompletedEvent $event): void
    {
        $this->navigationCollector->findDeletions();
    }
}