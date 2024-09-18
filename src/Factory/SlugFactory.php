<?php

namespace App\Factory;

use App\ValueObject\Slug;
use Symfony\Component\String\Slugger\SluggerInterface;

final class SlugFactory
{
    public function __construct(
        private SluggerInterface $slugger,
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
            $slug = $relativePath. DIRECTORY_SEPARATOR . $slug;
        }

        $slug .= '.html';

        return new Slug($slug);
    }
}