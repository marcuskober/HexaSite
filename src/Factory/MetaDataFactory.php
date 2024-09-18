<?php

namespace App\Factory;

use App\ValueObject\MetaData;

final readonly class MetaDataFactory
{
    public function __construct(
        private SlugFactory $slugFactory,
    )
    {
    }

    public function create(array $data, string $relativePath): MetaData
    {
        if (!isset($data['lang'])) {
            $data['lang'] = 'en';
        }

        $data['slug'] = $this->slugFactory->create($data, $relativePath);


        return new MetaData($data);
    }
}