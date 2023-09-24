<?php

namespace HDNET\Calendarize\Domain\Model;

trait ImportTrait
{
    /**
     * Import ID if the item is based on an ICS structure.
     */
    protected ?string $importId = null;

    public function getImportId(): ?string
    {
        return $this->importId;
    }

    public function setImportId(?string $importId): void
    {
        $this->importId = $importId;
    }
}
