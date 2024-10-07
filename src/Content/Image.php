<?php

namespace App\Content;

readonly class Image
{
    public int $width;
    public int $height;

    public function __construct(
        public string $path,
        public string $src,
        public string $alt,
    )
    {
        $imageInfo = getimagesize($this->path);
        $this->width = $imageInfo[0];
        $this->height = $imageInfo[1];
    }
}