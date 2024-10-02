<?php

namespace App\Collector;

use App\Entity\Navigation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Cache\CacheInterface;

class NavigationCollector
{

    /**
     * @var array<Navigation>
     */
    private array $items;
    private bool $hasChanged = false;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    )
    {
    }

    public function createSlugIfNotExists(string $slug): void
    {
        $existingSlug = $this->entityManager->getRepository(Navigation::class)->findOneBy(['slug' => $slug]);
        if ($existingSlug) {
            $this->items[] = $existingSlug;
            return;
        }

        $this->hasChanged = true;

        $navigation = new Navigation();
        $navigation->setSlug($slug);
        $navigation->setDate(new \DateTimeImmutable());

        $this->items[] = $navigation;

        $this->entityManager->persist($navigation);
        $this->entityManager->flush();
    }

    public function findDeletions(): void
    {
        $allItems = $this->entityManager->getRepository(Navigation::class)->findAll();
        $removedSlugs = array_diff($allItems, $this->items);

        foreach ($removedSlugs as $navigation) {
            $this->hasChanged = true;
            $this->entityManager->remove($navigation);
        }

        $this->entityManager->flush();
    }

    public function hasNavigationChanged(): bool
    {
        return $this->hasChanged;
    }
}