<?php
/**
 * Import default events
 *
 * @author  Tim Lochmüller
 */

namespace HDNET\Calendarize\Slots;

use HDNET\Calendarize\Command\ImportCommandController;
use HDNET\Calendarize\Domain\Model\Configuration;
use HDNET\Calendarize\Domain\Model\Event;
use HDNET\Calendarize\Utility\HelperUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;

/**
 * Import default events
 */
class EventImport
{

    /**
     * Event repository
     *
     * @var \HDNET\Calendarize\Domain\Repository\EventRepository
     * @inject
     */
    protected $eventRepository;

    /**
     * @param array                   $event
     * @param ImportCommandController $commandController
     * @param int                     $pid
     * @param boolean                 $handled
     *
     * @return array
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     */
    public function importCommand(array $event, $commandController, $pid, $handled)
    {
        $commandController->enqueueMessage('Handle via Default Event Import Slot');

        /** @var Event $eventObject */
        $update = true;
        $eventObject = $this->eventRepository->findOneByImportId($event['uid']);
        if (!($eventObject instanceof Event)) {
            $update = false;
            $eventObject = new Event();
        }


        $eventObject->setPid($pid);
        $eventObject->setImportId($event['uid']);
        $eventObject->setTitle($event['title']);
        $eventObject->setDescription($this->nl2br($event['description']));

        $configuration = new Configuration();
        $configuration->setPid($pid);
        $configuration->setType(Configuration::TYPE_TIME);
        $configuration->setFrequency(Configuration::FREQUENCY_NONE);
        /** @var \DateTime $startDate */
        $startDate = clone $event['start'];
        $startDate->setTime(0, 0, 0);
        $configuration->setStartDate($startDate);
        /** @var \DateTime $endDate */
        $endDate = clone $event['end'];
        $endDate->setTime(0, 0, 0);
        $configuration->setEndDate($endDate);

        $startTime = $this->dateTimeToDaySeconds($event['start']);
        if ($startTime > 0) {
            $configuration->setStartTime($startTime);
            $configuration->setEndTime($this->dateTimeToDaySeconds($event['end']));
            $configuration->setAllDay(false);
        } else {
            $configuration->setAllDay(true);
        }

        $eventObject->addCalendarize($configuration);

        if ($update) {
            $this->eventRepository->update($eventObject);
            $commandController->enqueueMessage('Update Event Meta data: ' . $eventObject->getTitle(), 'Update');
        } else {
            $this->eventRepository->add($eventObject);
            $commandController->enqueueMessage('Add Event: ' . $eventObject->getTitle(), 'Add');
        }

        $this->persist();
        $handled = true;

        return [
            'event'             => $event,
            'commandController' => $commandController,
            'pid'               => $pid,
            'handled'           => $handled,
        ];
    }

    /**
     * Store to the DB
     */
    protected function persist()
    {
        /** @var $persist PersistenceManager */
        $persist = HelperUtility::create('TYPO3\\CMS\\Extbase\\Persistence\\Generic\\PersistenceManager');
        $persist->persistAll();
    }

    /**
     * Replace new lines
     *
     * @param string $string
     *
     * @return string
     */
    protected function nl2br($string)
    {
        $string = nl2br((string)$string);
        return str_replace('\\n', '<br />', $string);
    }

    /**
     * DateTime to day seconds
     *
     * @param \DateTime $dateTime
     *
     * @return int
     */
    protected function dateTimeToDaySeconds(\DateTime $dateTime)
    {
        $hours = (int)$dateTime->format('G');
        $minutes = ($hours * 60) + (int)$dateTime->format('i');
        return ($minutes * 60) + (int)$dateTime->format('s');
    }
}