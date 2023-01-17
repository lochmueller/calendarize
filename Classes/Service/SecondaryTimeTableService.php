<?php

declare(strict_types=1);

namespace HDNET\Calendarize\Service;

use HDNET\Calendarize\Service\TimeTable\TimeTableInterface;

class SecondaryTimeTableService extends AbstractService
{
    protected array $timeTableServices;

    public function __construct(iterable $timeTableServices)
    {
        $this->timeTableServices = iterator_to_array($timeTableServices);
    }

    public function getSecondaryTimeTables(): array
    {
        return array_filter($this->timeTableServices, function (TimeTableInterface $timeTable) {
            return $timeTable->enable();
        });
    }
}
