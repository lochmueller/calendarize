<?php

declare(strict_types=1);

namespace HDNET\Calendarize\EventListener;

use HDNET\Calendarize\Domain\Model\Configuration;
use HDNET\Calendarize\Domain\Model\Event;
use HDNET\Calendarize\Domain\Repository\EventRepository;
use HDNET\Calendarize\Event\ImportSingleIcalEvent;
use HDNET\Calendarize\Ical\ICalEvent;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

final class ImportSingleIcalEventListener
{
    /**
     * Event repository.
     *
     * @var EventRepository
     */
    protected $eventRepository;

    /**
     * @var PersistenceManager
     */
    protected $persistenceManager;

    /**
     * ImportSingleIcalEventListener constructor.
     * @param EventRepository $eventRepository
     */
    public function __construct(
        EventRepository $eventRepository,
        PersistenceManager $persistenceManager
    ) {
        $this->eventRepository = $eventRepository;
        $this->persistenceManager = $persistenceManager;
    }

    public function __invoke(ImportSingleIcalEvent $event)
    {
        // TODO: Workaround to disable default event. Look for better solution!
        if ((bool) \HDNET\Calendarize\Utility\ConfigurationUtility::get('disableDefaultEvent')) {
            return;
        }

        $calEvent = $event->getEvent();
        $pid = $event->getPid();

        $eventObj = $this->initializeEventRecord($calEvent->getUid());
        $this->hydrateEventRecord($eventObj, $calEvent, $pid);

        if (null !== $eventObj->getUid() && (int)$eventObj->getUid() > 0) {
            $this->eventRepository->update($eventObj);
        } else {
            $this->eventRepository->add($eventObj);
        }

        $this->persistenceManager->persistAll();
    }

    /**
     * Initializes or gets a event by import id.
     * @param string $importId
     * @return Event
     */
    protected function initializeEventRecord(string $importId)
    {
        $eventObj = $this->eventRepository->findOneByImportId($importId);

        if (!($eventObj instanceof Event)) {
            $eventObj = new Event();
            $eventObj->setImportId($importId);
        }
        return $eventObj;
    }

    /**
     * Hydrates the event record with the event data.
     * @param Event $eventObj
     * @param ICalEvent $calEvent
     * @param int $pid
     */
    protected function hydrateEventRecord(Event $eventObj, ICalEvent $calEvent, int $pid)
    {
        $eventObj->setPid($pid);
        $eventObj->setTitle($calEvent->getTitle());
        $eventObj->setDescription($calEvent->getDescription());
        $eventObj->setLocation($calEvent->getLocation());
        $eventObj->setOrganizer($calEvent->getOrganizer());

        $configuration = new Configuration();
        $configuration->setPid($pid);
        $configuration->setType(Configuration::TYPE_TIME);
        $configuration->setFrequency(Configuration::FREQUENCY_NONE);
        $configuration->setAllDay($calEvent->isAllDay());

        $configuration->setStartDate($calEvent->getStartDate());
        $configuration->setEndDate($calEvent->getEndDate());
        $configuration->setStartTime($calEvent->getStartTime());
        $configuration->setEndTime($calEvent->getEndTime());

        // If the event existed previously there is already an Configuration.
        // To prevent multiple (also duplicate) Configurations, a new store is created.
        // TODO: Find better way to update, ... the existing configuration,
        //       to prevent recreation.
        if ($eventObj->getCalendarize()->count() !== 0) {
            $eventObj->setCalendarize(new ObjectStorage());
        }
        $eventObj->addCalendarize($configuration);
    }
}
