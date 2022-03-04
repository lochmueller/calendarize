<?php

declare(strict_types=1);

namespace HDNET\Calendarize\Service\Ical;

use HDNET\Calendarize\Exception\UnableToGetEventsException;
use HDNET\Calendarize\Ical\DissectEventAdapter;
use HDNET\Calendarize\Service\AbstractService;
use JMBTechnologyLimited\ICalDissect\ICalEvent;
use JMBTechnologyLimited\ICalDissect\ICalParser;

class DissectICalService extends AbstractService implements ICalServiceInterface
{
    /**
     * {@inheritdoc}
     */
    public function getEvents(string $filename): array
    {
        $parser = new ICalParser();
        if (!$parser->parseFromFile($filename)) {
            throw new UnableToGetEventsException('Unable to open or parse "' . $filename . '".', 1602345343);
        }
        $events = $parser->getEvents();

        $wrapEvents = static function (ICalEvent $event) {
            return new DissectEventAdapter($event);
        };

        return array_map($wrapEvents, $events);
    }
}
