<?php

declare(strict_types=1);

namespace HDNET\Calendarize;

use HDNET\Calendarize\Service\Ical\DissectICalService;
use HDNET\Calendarize\Service\Ical\ICalServiceInterface;
use HDNET\Calendarize\Service\Ical\VObjectICalService;
use Sabre\VObject\Reader;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return function (ContainerConfigurator $configurator) {
    $services = $configurator->services();

    if (class_exists(Reader::class)) {
        $services->alias(ICalServiceInterface::class, VObjectICalService::class);
    } else {
        $services->alias(ICalServiceInterface::class, DissectICalService::class);
    }
};
