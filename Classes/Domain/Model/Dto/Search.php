<?php

declare(strict_types=1);

namespace HDNET\Calendarize\Domain\Model\Dto;

class Search
{
    protected $fullText = '';

    /**
     * @var int[]
     */
    protected $categories = [];

    public function getFullText(): string
    {
        return $this->fullText;
    }

    public function setFullText(string $fullText): void
    {
        $this->fullText = $fullText;
    }

    /**
     * @return int
     *
     * @deprecated Use categories instead!
     */
    public function getCategory(): int
    {
        return $this->categories[0] ?? 0;
    }

    /**
     * @param int $category
     *
     * @deprecated Use categories instead!
     */
    public function setCategory(int $category): void
    {
        $this->categories = [$category];
    }

    /**
     * @return int[]
     */
    public function getCategories(): array
    {
        return $this->categories;
    }

    /**
     * @param int[] $categories
     */
    public function setCategories(array $categories): void
    {
        $this->categories = $categories;
    }

    public function isSearch(): bool
    {
        return 0 !== $this->getCategory() || '' !== $this->getFullText();
    }
}
