<?php

namespace App\Content;

class Image
{
    public int $width;
    public int $height;
    private array $sizes;

    public function __construct(
        public string $path,
        public string $src,
        public string $alt,
        public string $buildDir,
    )
    {
        $imageInfo = getimagesize($this->path);
        $this->width = $imageInfo[0];
        $this->height = $imageInfo[1];

        $image = match ($imageInfo['mime']) {
            'image/jpeg' => imagecreatefromjpeg($this->path),
            'image/png' => imagecreatefrompng($this->path),
            'image/gif' => imagecreatefromgif($this->path),
            default => null,
        };
        $pattern = '#.*/assets#';
        $newPath = preg_replace($pattern, 'assets', $this->path . '.webp');
        imagewebp($image, $this->buildDir . '/' . $newPath, 80);

        $this->src = $newPath;

        foreach ([500, 800, 1000, 1500, 2000] as $size) {
            $this->sizes[$size] = $this->resize($size);
        }
    }

    private function resize(int $newWidth): array
    {
        $imageInfo = getimagesize($this->path);
        $mimeType = $imageInfo['mime'];
        $thumbDir = $this->buildDir . '/assets/thumbs';
        if (!is_dir($thumbDir)) {
            mkdir($thumbDir, 0777, true);
        }
        $filename = $newWidth . '_' . basename($this->path) . '.webp';
        $outputPath = $thumbDir . '/' . $filename;

        $newHeight = intval($imageInfo[1] * ($newWidth / $imageInfo[0]));

        if (file_exists($outputPath)) {
            return [
                'src' => 'assets/thumbs/' . $filename,
                'width' => $newWidth,
                'height' => $newHeight,
            ];
        }

        $sourceImage = match ($mimeType) {
            'image/jpeg' => imagecreatefromjpeg($this->path),
            'image/png' => imagecreatefrompng($this->path),
            'image/gif' => imagecreatefromgif($this->path),
            default => null,
        };

        if (!$sourceImage) {
            return [];
        }

        $resizedImage = imagecreatetruecolor($newWidth, $newHeight);
        imagecopyresampled($resizedImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, imagesx($sourceImage), imagesy($sourceImage));

        // Das Bild als WebP speichern
        imagewebp($resizedImage, $outputPath);

        // Ressourcen freigeben
        imagedestroy($sourceImage);
        imagedestroy($resizedImage);

        return [
            'src' => 'assets/thumbs/' . $filename,
            'width' => $newWidth,
            'height' => $newHeight,
        ];
    }

    public function getSizes(): array
    {
        return $this->sizes;
    }
}