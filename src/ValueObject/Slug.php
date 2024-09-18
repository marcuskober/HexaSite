<?php

namespace App\ValueObject;

final class Slug
{
    public function __construct(private string $slug)
    {
    }

    public function __toString(): string
    {
        return $this->slug;
    }
}