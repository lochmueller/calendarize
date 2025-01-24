<?php

namespace HDNET\Calendarize\Event;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\Repository;

final class CleanupEvent
{
    public function __construct(
        private readonly string $modus,
        private readonly Repository $repository,
        private readonly AbstractEntity $model,
        private \Closure $function,
    ) {}

    public function getModus(): string
    {
        return $this->modus;
    }

    public function getRepository(): Repository
    {
        return $this->repository;
    }

    public function getModel(): AbstractEntity
    {
        return $this->model;
    }

    public function getFunction(): \Closure
    {
        return $this->function;
    }

    public function setFunction(\Closure $function): void
    {
        $this->function = $function;
    }
}
