<?php

namespace HDNET\Calendarize\Domain\Model;

use HDNET\Autoloader\Annotation\DatabaseField;

trait ImportTrait
{
    /**
     * Import ID if the item is based on an ICS structure.
     *
     * @var string|null
     *
     * @DatabaseField(sql="varchar(150) DEFAULT NULL")
     */
    protected $importId;

    public function getImportId(): ?string
    {
        return $this->importId;
    }

    public function setImportId(?string $importId): void
    {
        $this->importId = $importId;
    }
}
