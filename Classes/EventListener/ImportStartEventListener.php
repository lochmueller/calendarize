<?php

declare(strict_types=1);

namespace HDNET\Calendarize\EventListener;

use HDNET\Calendarize\Event\ImportStartEvent;
use HDNET\Calendarize\Utility\ConfigurationUtility;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;

/**
 * Disables all previously imported default events on the target page.
 * The ImportSingleIcalEventListener enables the events of the current import again,
 * so only the events, that are still part of the ICS calendar, are active afterwards.
 */
class ImportStartEventListener
{
    public const TABLE = 'tx_calendarize_domain_model_event';

    public function __construct(
        private readonly ConnectionPool $connectionPool,
    ) {}

    public function __invoke(ImportStartEvent $event): void
    {
        // @todo: Workaround to disable default event. Look for better solution!
        if ((bool)ConfigurationUtility::get('disableDefaultEvent')) {
            return;
        }

        if (!$event->isDisableNotImported()) {
            return;
        }

        $queryBuilder = $this->connectionPool->getQueryBuilderForTable(self::TABLE);
        $queryBuilder->getRestrictions()->removeAll();
        $queryBuilder->update(self::TABLE)
            ->set('hidden', 1)
            ->where(
                $queryBuilder->expr()->eq('pid', $queryBuilder->createNamedParameter($event->getPid(), Connection::PARAM_INT)),
                $queryBuilder->expr()->neq('import_id', $queryBuilder->createNamedParameter('')),
                $queryBuilder->expr()->isNotNull('import_id'),
                $queryBuilder->expr()->eq('deleted', 0),
            )
            ->executeStatement();
    }
}
