<?php

namespace App\ValueObject;

final class MetaData
{
    private string $title;
    private Slug $slug;
    private string $lang;
    private \DateTimeInterface $date;
    private string $layout;
    private string $description;
    private string $markdownPath;

    public function getMarkdownPath(): string
    {
        return $this->markdownPath;
    }

    public function setMarkdownPath(string $markdownPath): void
    {
        $this->markdownPath = $markdownPath;
    }

    public function __construct(array $data)
    {
        $this->title = $data['title'] ?? 'Untitled';
        $this->slug = $data['slug'];
        $this->lang = $data['lang'] ?? 'en';
        $this->layout  = $data['layout'] ?? 'article';
        $this->description  = $data['description'] ?? '';

        $this->date = isset($data['date']) ? new \DateTime("@".$data['date']) : new \DateTime();
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
}