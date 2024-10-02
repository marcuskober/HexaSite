<?php

namespace App\Collector;

use App\Entity\ItemMetaData;
use App\Entity\Navigation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Cache\CacheInterface;

class ItemCollector
{

    /**
     * @var array<ItemMetaData>
     */
    private array $itemMetaData;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    )
    {
    }

    public function hasItemChanged(string $slug, string $contentMd5): bool
    {
        [$itemMetaData, $status] = $this->createItemIfNotExists($slug, $contentMd5);

        if ($status === 'new') {
            return true;
        }

        return $itemMetaData->getContentMd5() !== $contentMd5;
    }

    public function updateItem(string $slug, string $contentMd5): void
    {
        $existingItem = $this->entityManager->getRepository(ItemMetaData::class)->findOneBy(['slug' => $slug]);
        $existingItem->setContentMd5($contentMd5);
        $existingItem->setDate(new \DateTimeImmutable());
        $this->entityManager->persist($existingItem);
        $this->entityManager->flush();
    }

    public function createItemIfNotExists(string $slug, string $contentMd5): array
    {
        $existingItem = $this->entityManager->getRepository(ItemMetaData::class)->findOneBy(['slug' => $slug]);
        if ($existingItem) {
            $this->itemMetaData[] = $existingItem;
            return [$existingItem, 'exists'];
        }

        $item = new ItemMetaData();
        $item->setSlug($slug);
        $item->setContentMd5($contentMd5);
        $item->setDate(new \DateTimeImmutable());

        $this->itemMetaData[] = $item;

        $this->entityManager->persist($item);
        $this->entityManager->flush();

        return [$item, 'new'];
    }

    public function findDeletions(): void
    {
        $allItems = $this->entityManager->getRepository(ItemMetaData::class)->findAll();
        $removedItems = array_diff($allItems, $this->itemMetaData);

        foreach ($removedItems as $item) {
            $this->entityManager->remove($item);
        }

        $this->entityManager->flush();
    }
}