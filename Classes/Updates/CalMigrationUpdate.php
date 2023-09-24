<?php

declare(strict_types=1);

namespace HDNET\Calendarize\Updates;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Install\Attribute\UpgradeWizard;
use TYPO3\CMS\Install\Updates\DatabaseUpdatedPrerequisite;

#[UpgradeWizard('calendarize_calMigrationUpdate')]
class CalMigrationUpdate extends AbstractUpdate implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * Import prefix.
     */
    public const IMPORT_PREFIX = 'calMigration:';

    public function getTitle(): string
    {
        return 'Migrate cal event structures';
    }

    public function getDescription(): string
    {
        return 'Migrate cal event structures to the new calendarize event structures.
            Try to migrate all cal information and place the new calendarize event models in the same folder
            as the cal-records. Please note: the migration will be create calendarize default models.';
    }

    public function getPrerequisites(): array
    {
        return [
            DatabaseUpdatedPrerequisite::class,
        ];
    }

    public function updateNecessary(): bool
    {
        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
        $connection = $connectionPool->getConnectionByName(ConnectionPool::DEFAULT_CONNECTION_NAME);
        $dbSchema = $connection->createSchemaManager()->introspectSchema();

        $tableNames = array_map(static function ($table) {
            return $table->getName();
        }, $dbSchema->getTables());

        return \in_array('tx_cal_event', $tableNames);
    }

    /**
     * Performs the accordant updates.
     *
     * @return bool Whether everything went smoothly or not
     */
    public function executeUpdate(): bool
    {
        if (\PHP_VERSION_ID >= 80000 || GeneralUtility::makeInstance(Typo3Version::class)->getMajorVersion() > 10) {
            $this->output->writeln(
                'The Cal migration wizard does not support TYPO3 >= 11 and not PHP 8. Please use TYPO3 v10.4!'
            );

            return false;
        }

        return true;
    }
}
