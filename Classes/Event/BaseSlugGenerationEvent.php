<?php

declare(strict_types=1);

namespace HDNET\Calendarize\Event;

use TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface;

final class BaseSlugGenerationEvent
{
    /**
     * @var string
     */
    private $uniqueRegisterKey;

    /**
     * @var DomainObjectInterface|null
     */
    private $model;

    /**
     * @var array
     */
    private $record;

    /**
     * @var string
     */
    private $baseSlug;

    /**
     * BaseSlugGenerationEvent constructor.
     *
     * @param string                $uniqueRegisterKey
     * @param DomainObjectInterface $model
     * @param array                 $record
     * @param string                $baseSlug
     */
    public function __construct(
        string $uniqueRegisterKey,
        DomainObjectInterface $model,
        array $record,
        string $baseSlug
    ) {
        $this->uniqueRegisterKey = $uniqueRegisterKey;
        $this->model = $model;
        $this->record = $record;
        $this->baseSlug = $baseSlug;
    }

    /**
     * @return string
     */
    public function getBaseSlug(): string
    {
        return $this->baseSlug;
    }

    /**
     * @param string $baseSlug
     */
    public function setBaseSlug(string $baseSlug): void
    {
        $this->baseSlug = $baseSlug;
    }

    /**
     * @return DomainObjectInterface|null
     */
    public function getModel(): ?DomainObjectInterface
    {
        return $this->model;
    }

    /**
     * @return string
     */
    public function getUniqueRegisterKey(): string
    {
        return $this->uniqueRegisterKey;
    }

    /**
     * @return array
     */
    public function getRecord(): array
    {
        return $this->record;
    }
}
