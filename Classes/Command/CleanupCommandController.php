<?php

/**
 * Cleanup the event models.
 */
declare(strict_types=1);

namespace HDNET\Calendarize\Command;

use HDNET\Calendarize\Domain\Model\Event;
use HDNET\Calendarize\Domain\Repository\EventRepository;
use HDNET\Calendarize\Service\IndexerService;
use HDNET\Calendarize\Utility\DateTimeUtility;
use HDNET\Calendarize\Utility\HelperUtility;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\HiddenRestriction;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\SignalSlot\Dispatcher;

/**
 * Cleanup the event models.
 */
class CleanupCommandController extends AbstractCommandController
{
    const MODUS_HIDDEN = 'hide';
    const MODUS_DELETED = 'delete';
    const DEFAULT_WAIT_PERIOD = 14;
    const DEFAULT_CLEANUP_REPOSITORY = 'HDNET\\Calendarize\\Domain\\Repository\\EventRepository';

    /**
     * Cleanup the event models.
     * Remove outdated events to keep a small footprint. This gain maybe a little more performance.
     *
     * @param string $repositoryName repository of the event to clean up. Default: 'HDNET\Calendarize\Domain\Repository\EventRepository'
     * @param string $modus          What to do with cleaned Events? Set them 'hide' or 'delete'. Default: 'hide'
     * @param int    $waitingPeriod  how many days to wait after ending the Event before 'hide/delete' it
     */
    public function runCommand(
        $repositoryName = self::DEFAULT_CLEANUP_REPOSITORY,
        $modus = self::MODUS_HIDDEN,
        $waitingPeriod = self::DEFAULT_WAIT_PERIOD
    ) {
        /** @var EventRepository $repository */
        $repository = GeneralUtility::makeInstance($repositoryName);

        if (!($repository instanceof EventRepository)) {
            return;
        }

        // Index all events to start on a clean slate
        $this->reIndex();

        // get tablename from repository, works only with the extended EventRepository
        $tableName = $repository->getTableName();

        if (!$tableName) {
            $this->enqueueMessage(
                'No tablename found on your given Repository! [' . $repositoryName . ']',
                'Tablename',
                FlashMessage::ERROR
            );

            return;
        }

        $this->enqueueMessage($tableName, 'Tablename', FlashMessage::INFO);

        // events uid, to be precise
        $events = $this->findOutdatedEvents($tableName, $waitingPeriod);

        // climb thru the events and hide/delete them
        foreach ($events as $event) {
            $uid = (int)$event['foreign_uid'];

            $model = $repository->findByUid($uid);

            if (!($model instanceof Event)) {
                $this->enqueueMessage(
                    'Object with uid [' . $uid . '] is not an instance of the event base model.',
                    'Error',
                    FlashMessage::INFO
                );
                continue;
            }

            $this->processEvent($repository, $model, $modus);
        }

        // persist the modified events
        // HelperUtility::persistAll(); @todo handle via DI

        // after all this deleting ... reindex!
        $this->reIndex();
    }

    /**
     * Process the found Event and delete or hide it.
     *
     * @param EventRepository $repository
     * @param Event           $model
     * @param string          $modus
     */
    protected function processEvent(EventRepository $repository, Event $model, $modus)
    {
        // define the function for the delete-modus.
        $delete = function ($repository, $model) {
            $repository->remove($model);
        };

        // define the function for the hide-modus.
        $hide = function ($repository, $model) {
            $model->setHidden(true);
            $repository->update($model);
        };

        if (self::MODUS_DELETED === $modus) {
            $function = $delete;
        } else {
            $function = $hide;
        }

        // dispatch variables
        // use function to write your own reaction
        $variables = [
            'modus' => $modus,
            'repository' => $repository,
            'model' => $model,
            'function' => $function,
        ];

        $dispatcher = $this->objectManager->get(Dispatcher::class);
        $variables = $dispatcher->dispatch(__CLASS__, __FUNCTION__, $variables);

        $myFunction = $variables['function'];
        $myFunction($repository, $model);
    }

    /**
     * Find outdated events.
     *
     * @param string $tableName
     * @param int    $waitingPeriod
     *
     * @throws \Exception
     *
     * @return array
     */
    protected function findOutdatedEvents($tableName, $waitingPeriod): array
    {
        // calculate the waiting time
        $interval = 'P' . (int)$waitingPeriod . 'D';
        $now = DateTimeUtility::getNow();
        $now->sub(new \DateInterval($interval));

        // search for outdated events
        $table = IndexerService::TABLE_NAME;

        $q = HelperUtility::getDatabaseConnection($table)->createQueryBuilder();
        $q->getRestrictions()
            ->removeAll()
            ->add(GeneralUtility::makeInstance(DeletedRestriction::class))
            ->add(GeneralUtility::makeInstance(HiddenRestriction::class));

        $foreignUids = $q->select('foreign_uid')
            ->from($table)
            ->where($q->expr()
                ->gt('end_date', $q->createNamedParameter($now->getTimestamp())))
            ->andWhere($q->expr()
                ->eq('foreign_table', $q->createNamedParameter($tableName)))
            ->execute()
            ->fetchAll();

        $foreignUids = \array_map(function ($item) {
            return (int)$item['foreign_uid'];
        }, $foreignUids);

        $q->select('foreign_uid')
            ->from($table)
            ->where($q->expr()
                ->andX($q->expr()
                    ->lt('end_date', $q->createNamedParameter($now->getTimestamp())), $q->expr()
                    ->eq('foreign_table', $q->createNamedParameter($tableName)), $q->expr()
                    ->notIn('foreign_uid', $foreignUids)));

        $rows = $q->execute()->fetchAll();

        $this->enqueueMessage('Just found ' . \count($rows) . ' Events ready to process.', 'Events found', FlashMessage::INFO);

        return $rows;
    }

    /**
     * Reindex the Events.
     * This may take some time.
     */
    protected function reIndex()
    {
        $indexer = GeneralUtility::makeInstance(IndexerService::class);
        $indexer->reindexAll();
    }
}
