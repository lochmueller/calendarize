<?php

declare(strict_types=1);

namespace HDNET\Calendarize\Service\Ical;

use HDNET\Calendarize\Exception\UnableToGetEventsException;
use HDNET\Calendarize\Ical\VObjectEventAdapter;
use HDNET\Calendarize\Service\AbstractService;
use Sabre\VObject\Component\VEvent;
use Sabre\VObject\ParseException;
use Sabre\VObject\Reader;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class VObjectICalService extends AbstractService implements ICalServiceInterface
{
    /**
     * {@inheritdoc}
     */
    public function getEvents(string $filename): array
    {
        $content = GeneralUtility::getUrl($filename);
        if (false === $content) {
            throw new UnableToGetEventsException('Unable to get "' . $filename . '".', 1603307743);
        }

        try {
            $vcalendar = Reader::read(
                $content,
                Reader::OPTION_FORGIVING,
            );
        } catch (ParseException $e) {
            // Rethrow the exception to abstract the type
            throw new UnableToGetEventsException($e->getMessage(), 1603309056, $e);
        }

        /** @var VEvent[] $events */
        $events = [];

        foreach ($vcalendar->VEVENT as $event) {
            $events[] = new VObjectEventAdapter($event);
        }

        return $events;
    }
}
