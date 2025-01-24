<?php

declare(strict_types=1);

namespace HDNET\Calendarize\Domain\Model\Dto;

class Search
{
    protected string $fullText = '';

    /**
     * @var int[]
     */
    protected array $categories = [];

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
        @trigger_error(
            'HDNET\Calendarize\Domain\Model\Dto\Search::getCategory',
            \E_USER_DEPRECATED,
        );

        return $this->categories[0] ?? 0;
    }

    /**
     * @param int $category
     *
     * @deprecated Use categories instead!
     */
    public function setCategory(int $category): void
    {
        @trigger_error(
            'HDNET\Calendarize\Domain\Model\Dto\Search::setCategory',
            \E_USER_DEPRECATED,
        );
        $this->categories = [$category];
    }

    public function getCategories(): array
    {
        return $this->categories;
    }

    public function setCategories(array $categories): void
    {
        $this->categories = $categories;
    }

    public function isSearch(): bool
    {
        return !empty($this->categories) || '' !== $this->getFullText();
    }
}
