<?php

namespace App\Service;

use App\Config\SiteConfig;
use Symfony\Component\Filesystem\Filesystem;

final class ItemWriter
{
    private array $writtenItems = [];

    public function __construct(
        private Filesystem $filesystem,
        private readonly SiteConfig $siteConfig,
    )
    {
    }

    public function writeItem(string $slug, string $content): void
    {
        $itemPath = $this->siteConfig->build_dir . DIRECTORY_SEPARATOR . $slug;
        $this->writtenItems[] = $itemPath;

        if (!is_dir(dirname($itemPath))) {
            mkdir(dirname($itemPath), 0777, true);
        }

        $this->filesystem->dumpFile($itemPath, $content);
    }

    public function cleanUp(): void
    {
//       $finder = new Finder();
//       $finder->files()->in($this->outputPath)->name('*.html');
//
//       foreach ($finder as $file) {
//           $fileName = $file->getRealPath();
//           if (in_array($fileName, $this->writtenItems)) {
//               continue;
//           }
//
//           $this->filesystem->remove($fileName);
//       }
    }
}