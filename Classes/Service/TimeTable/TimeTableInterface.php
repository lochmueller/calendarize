<?php

declare(strict_types=1);

namespace HDNET\Calendarize\Service\TimeTable;

use HDNET\Calendarize\Domain\Model\Configuration;

interface TimeTableInterface
{
    public function enable(): bool;

    public function getIdentifier(): string;

    public function getLabel(): string;

    public function getTcaServiceTypeFields(): string;

    public function getFlexForm(): string;

    public function handleConfiguration(array &$times, Configuration $configuration);
}
