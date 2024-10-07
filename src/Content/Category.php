<?php

namespace App\Content;

class Category implements ContentInterface
{
    use ContentTrait;
    private array $items = [];

    public function getItems(): array
    {
        return $this->items;
    }

    public function addItem(ContentInterface $item): void
    {
        $this->items[] = $item;
    }
}