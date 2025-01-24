<?php

declare(strict_types=1);

namespace HDNET\Calendarize\Event;

final class SlugSuffixGenerationEvent
{
    public function __construct(
        private readonly string $uniqueRegisterKey,
        private readonly array $record,
        private readonly string $baseSlug,
        private string $slug,
    ) {}

    public function getUniqueRegisterKey(): string
    {
        return $this->uniqueRegisterKey;
    }

    public function getRecord(): array
    {
        return $this->record;
    }

    public function getBaseSlug(): string
    {
        return $this->baseSlug;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): void
    {
        $this->slug = $slug;
    }
}
