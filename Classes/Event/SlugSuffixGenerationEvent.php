<?php

declare(strict_types=1);

namespace HDNET\Calendarize\Event;

final class SlugSuffixGenerationEvent
{
    /**
     * @var string
     */
    private $uniqueRegisterKey;

    /**
     * @var array
     */
    private $record;

    /**
     * @var string
     */
    private $slug;

    /**
     * SlugSuffixGenerationEvent constructor.
     *
     * @param string $uniqueRegisterKey
     * @param array  $record
     * @param string $slug
     */
    public function __construct(string $uniqueRegisterKey, array $record, string $slug)
    {
        $this->uniqueRegisterKey = $uniqueRegisterKey;
        $this->record = $record;
        $this->slug = $slug;
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

    /**
     * @return string
     */
    public function getSlug(): string
    {
        return $this->slug;
    }

    /**
     * @param string $slug
     */
    public function setSlug(string $slug): void
    {
        $this->slug = $slug;
    }
}
