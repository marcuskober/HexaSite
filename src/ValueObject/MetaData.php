<?php

namespace App\ValueObject;

use Symfony\Contracts\Cache\ItemInterface;

final class MetaData
{
    private string $title;
    private string $metaTitle;
    private string $metaDescription;
    private string $summary;
    private Slug $slug;
    private string $path;

    private string $lang;
    private \DateTimeInterface $date;
    private \DateTimeInterface $changeDate;
    private string $layout;
    private string $description;
    private string $markdownPath;
    private string $contentId;
    private bool $navigation;
    private string $navigationTitle;
    private false|array $archive;
    private ?string $image;
    private array $alternatives = [];

    public function __construct(array $data)
    {
        $this->title = $data['title'] ?? 'Untitled';
        $this->metaTitle = $data['meta_title'] ?? $this->title;
        $this->summary = $data['summary'] ?? '';
        $this->metaDescription = $data['meta_description'] ?? $this->summary;
        $this->path = $data['path'];
        $this->slug = $data['slug'];
        $this->lang = $data['lang'] ?? 'en';
        $this->layout  = $data['layout'] ?? 'article';
        $this->description  = $data['description'] ?? '';
        $this->contentId = $data['content_id'] ?? $this->slug;
        $this->navigation = $data['navigation'] ?? false;
        $this->navigationTitle = $data['navigation_title'] ?? $this->title;
        $this->archive = $data['archive'] ?? false;
        $this->image = $data['image'] ?? null;
        $this->date = isset($data['date']) ? new \DateTime("@".$data['date']) : new \DateTime();
        $this->changeDate = $this->date;
    }

    public function getMetaTitle(): string
    {
        return $this->metaTitle;
    }

    public function getMetaDescription(): string
    {
        return $this->metaDescription;
    }

    public function getSummary(): string
    {
        return $this->summary;
    }

    public function setSlug(Slug $slug): void
    {
        $this->slug = $slug;
    }

    public function getAlternatives(): array
    {
        return $this->alternatives;
    }

    public function setAlternatives(array $alternatives): void
    {
        $this->alternatives = $alternatives;
    }

    public function addAlternative(string $language, MetaData $metaData): void
    {
        $this->alternatives[$language] = $metaData;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function isNavigation(): bool
    {
        return $this->navigation;
    }

    public function getNavigationTitle(): string
    {
        return $this->navigationTitle;
    }

    public function getMarkdownPath(): string
    {
        return $this->markdownPath;
    }

    public function setMarkdownPath(string $markdownPath): void
    {
        $this->markdownPath = $markdownPath;
    }

    public function getContentId(): string
    {
        return $this->contentId;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function getLang(): string
    {
        return $this->lang;
    }

    public function getDate(): \DateTimeInterface
    {
        return $this->date;
    }

    public function getLayout(): string
    {
        return $this->layout;
    }

    public function getArchive(): bool|array
    {
        return $this->archive;
    }

    public function setChangeDate(\DateTimeInterface $date): void
    {
        $this->changeDate = $date;
    }

    public function getChangeDate(): \DateTimeInterface
    {
        return $this->changeDate;
    }

    public function getPath(): string
    {
        return $this->path;
    }
}