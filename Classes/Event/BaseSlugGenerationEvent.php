<?php

declare(strict_types=1);

namespace HDNET\Calendarize\Event;

use TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface;

final class BaseSlugGenerationEvent
{
    public function __construct(
        private readonly string $uniqueRegisterKey,
        private readonly DomainObjectInterface $model,
        private readonly array $record,
        private string $baseSlug,
    ) {}

    public function getBaseSlug(): string
    {
        return $this->baseSlug;
    }

    public function setBaseSlug(string $baseSlug): void
    {
        $this->baseSlug = $baseSlug;
    }

    public function getModel(): ?DomainObjectInterface
    {
        return $this->model;
    }

    public function getUniqueRegisterKey(): string
    {
        return $this->uniqueRegisterKey;
    }

    public function getRecord(): array
    {
        return $this->record;
    }
}
