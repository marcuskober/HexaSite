<?php

namespace App\Factory;

use App\Config\SiteConfig;
use App\ValueObject\Slug;
use Symfony\Component\String\Slugger\SluggerInterface;

final class SlugFactory
{
    public function __construct(
        private SluggerInterface $slugger,
        private SiteConfig $siteConfig,
    )
    {
    }

    public function create(array $data, string $relativePath): Slug
    {
        $slug = $data['slug'] ?? '';

        if (!$slug) {
            $slug = $this->slugger->slug($data['title'], locale: $data['lang'])->lower();
        }

        if ($relativePath) {
            $slug = $relativePath. '/' . $slug;
        }

        if ($this->siteConfig->multilanguage && $this->siteConfig->multilanguage['enabled']) {
            $mainLanguage = $this->siteConfig->multilanguage['main'];

            if ($data['lang'] !== $mainLanguage && str_starts_with($slug, $data['lang'] . '/') === false) {
                $slug = $data['lang'] . '/' . $slug;
            }
        }

        $slug .= '.html';

        return new Slug($slug);
    }
}