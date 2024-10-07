<?php

namespace App\Factory;

use App\Config\SiteConfig;
use App\Content\Image;
use App\ValueObject\MetaData;

final readonly class MetaDataFactory
{
    public function __construct(
        private SlugFactory $slugFactory,
        private SiteConfig $siteConfig,
    )
    {
    }

    public function create(array $data, string $relativePath): MetaData
    {
        if (!isset($data['lang'])) {
            $data['lang'] = 'en';
        }

        if (isset($data['image'])) {
            $path = realpath($this->siteConfig->content_dir . '/' . $relativePath . '/' . $data['image']);
            $data['image'] = new Image($path, $data['image'], $data['title'], $this->siteConfig->build_dir);
        }

        $data['slug'] = $this->slugFactory->create($data, $relativePath);
        $data['path'] = $relativePath;

        return new MetaData($data);
    }
}