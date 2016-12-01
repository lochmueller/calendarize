<?php
/**
 * Cleanup the event models
 *
 * @author  Carsten Biebricher
 */

namespace HDNET\Calendarize\Command;

use HDNET\Calendarize\Domain\Model\Event;
use HDNET\Calendarize\Domain\Repository\EventRepository;
use HDNET\Calendarize\Service\IndexerService;
use HDNET\Calendarize\Utility\DateTimeUtility;
use HDNET\Calendarize\Utility\HelperUtility;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Extbase\SignalSlot\Dispatcher;

/**
 * Cleanup the event models
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
     * @param int    $waitingPeriod  How many days to wait after ending the Event before 'hide/delete' it.
     *
     * @return void
     */
    public function runCommand(
        $repositoryName = self::DEFAULT_CLEANUP_REPOSITORY,
        $modus = self::MODUS_HIDDEN,
        $waitingPeriod = self::DEFAULT_WAIT_PERIOD
    ) {
        /** @var EventRepository $repository */
        $repository = HelperUtility::create($repositoryName);

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
        HelperUtility::persistAll();

        // after all this deleting ... reindex!
        $this->reIndex();
    }

    /**
     * Process the found Event and delete or hide it.
     *
     * @param EventRepository $repository
     * @param Event $model
     * @param string $modus
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

        //
        if ($modus === self::MODUS_DELETED) {
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
            'function' => $function
        ];

        /** @var Dispatcher $dispatcher */
        $dispatcher = $this->objectManager->get(Dispatcher::class);
        $variables = $dispatcher->dispatch(__CLASS__, __FUNCTION__, $variables);

        $myFunction = $variables['function'];
        $myFunction($repository, $model);
    }

    /**
     * @param string $tableName
     * @param int    $waitingPeriod
     *
     * @return bool|\mysqli_result|object
     */
    protected function findOutdatedEvents($tableName, $waitingPeriod)
    {
        // calculate the waiting time
        $interval = 'P' . (int)$waitingPeriod . 'D';
        $now = DateTimeUtility::getNow();
        $now->sub(new \DateInterval($interval));

        // search for outdated events
        $table = IndexerService::TABLE_NAME;
        $where = 'end_date < ' . $now->getTimestamp() . ' AND foreign_table = \'' . $tableName . '\' ';
        $where .= 'AND foreign_uid NOT IN (';
        $where .= 'SELECT i2.foreign_uid FROM ' . $table . ' i2 WHERE i2.end_date > ' . $now->getTimestamp() . ' AND i2.foreign_table = \'' . $tableName . '\'';
        $where .= ') AND hidden = 0 ' . BackendUtility::deleteClause($table);

        $db = HelperUtility::getDatabaseConnection();
        $rows = $db->exec_SELECTquery('foreign_uid', $table, $where, 'foreign_uid');

        $this->enqueueMessage('Just found ' . $rows->num_rows . ' Events ready to process.', 'Events found', FlashMessage::INFO);

        return $rows;
    }

    /**
     * Reindex the Events.
     * This may take some time.
     */
    protected function reIndex()
    {
        /** @var IndexerService $indexer */
        $indexer = $this->objectManager->get(IndexerService::class);
        $indexer->reindexAll();
    }
}
