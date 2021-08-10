<?php

declare(strict_types=1);

namespace HDNET\Calendarize\Domain\Model\Dto;

class Search
{
    protected $fullText = '';

    protected $category = 0;

    public function getFullText(): string
    {
        return $this->fullText;
    }

    public function setFullText(string $fullText): void
    {
        $this->fullText = $fullText;
    }

    public function getCategory(): int
    {
        return $this->category;
    }

    public function setCategory(int $category): void
    {
        $this->category = $category;
    }

    public function isSearch(): bool
    {
        return 0 !== $this->getCategory() || '' !== $this->getFullText();
    }
}
