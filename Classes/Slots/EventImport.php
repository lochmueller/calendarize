<?php

/**
 * Import default events.
 */
declare(strict_types=1);

namespace HDNET\Calendarize\Slots;

use HDNET\Calendarize\Command\ImportCommandController;
use HDNET\Calendarize\Domain\Model\Configuration;
use HDNET\Calendarize\Domain\Model\Event;
use HDNET\Calendarize\Domain\Repository\EventRepository;
use HDNET\Calendarize\Utility\DateTimeUtility;
use HDNET\Calendarize\Utility\HelperUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;

/**
 * Import default events.
 */
class EventImport
{
    /**
     * Event repository.
     *
     * @var \HDNET\Calendarize\Domain\Repository\EventRepository
     */
    protected $eventRepository;

    /**
     * Inject event repository.
     *
     * @param EventRepository $eventRepository
     */
    public function injectEventRepository(EventRepository $eventRepository)
    {
        $this->eventRepository = $eventRepository;
    }

    /**
     * Run the import.
     *
     * @param array                   $event
     * @param ImportCommandController $commandController
     * @param int                     $pid
     * @param bool                    $handled
     *
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     *
     * @return array
     */
    public function importCommand(array $event, $commandController, $pid, $handled)
    {
        $commandController->enqueueMessage('Handle via Default Event Import Slot');

        $eventObject = $this->getEvent($event['uid']);
        $eventObject->setPid($pid);
        $eventObject->setTitle($event['title']);
        $eventObject->setDescription($this->nl2br($event['description']));
        $eventObject->setLocation($event['location']);

        $configuration = $this->getConfiguration($pid, $event['start'], $event['end']);
        $eventObject->addCalendarize($configuration);

        if (null !== $eventObject->getUid() && (int)$eventObject->getUid() > 0) {
            $this->eventRepository->update($eventObject);
            $commandController->enqueueMessage('Update Event Meta data: ' . $eventObject->getTitle(), 'Update');
        } else {
            $this->eventRepository->add($eventObject);
            $commandController->enqueueMessage('Add Event: ' . $eventObject->getTitle(), 'Add');
        }

        $this->persist();
        $handled = true;

        return [
            'event' => $event,
            'commandController' => $commandController,
            'pid' => $pid,
            'handled' => $handled,
        ];
    }

    /**
     * Get the configuration.
     *
     * @param int       $pid
     * @param \DateTime $startDate
     * @param \DateTime $endDate
     *
     * @return Configuration
     */
    protected function getConfiguration($pid, \DateTime $startDate, \DateTime $endDate)
    {
        $configuration = new Configuration();
        $configuration->setPid($pid);
        $configuration->setType(Configuration::TYPE_TIME);
        $configuration->setFrequency(Configuration::FREQUENCY_NONE);
        $configuration->setAllDay(true);

        $startTime = clone $startDate;
        $configuration->setStartDate(DateTimeUtility::resetTime($startDate));
        $endTime = clone $endDate;
        $configuration->setEndDate(DateTimeUtility::resetTime($endDate));

        $startTime = DateTimeUtility::getDaySecondsOfDateTime($startTime);
        if ($startTime > 0) {
            $configuration->setStartTime($startTime);
            $configuration->setEndTime(DateTimeUtility::getDaySecondsOfDateTime($endTime));
            $configuration->setAllDay(false);
        }

        return $configuration;
    }

    /**
     * Get the right event object (or a new one).
     *
     * @param string $importId
     *
     * @return Event
     */
    protected function getEvent($importId)
    {
        $eventObject = $this->eventRepository->findOneByImportId($importId);

        if (!($eventObject instanceof Event)) {
            $eventObject = new Event();
        }
        $eventObject->setImportId($importId);

        return $eventObject;
    }

    /**
     * Store to the DB.
     */
    protected function persist()
    {
        $persist = HelperUtility::create(PersistenceManager::class);
        $persist->persistAll();
    }

    /**
     * Replace new lines.
     *
     * @param string $string
     *
     * @return string
     */
    protected function nl2br($string)
    {
        $string = \nl2br((string)$string);

        return \str_replace('\\n', '<br />', $string);
    }
}
