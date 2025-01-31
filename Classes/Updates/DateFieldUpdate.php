<?php

declare(strict_types=1);

namespace HDNET\Calendarize\Updates;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Types\DateType;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Install\Attribute\UpgradeWizard;

#[UpgradeWizard('calendarize_dateFieldUpdate')]
class DateFieldUpdate extends AbstractUpdate
{
    protected string $title = 'Calendarize Date Field';

    protected string $description = 'This wizard migrates the existing start and end dates in the ' .
        'database from a timestamp to a real date. This enables dates before 1970 and after 2038.';

    protected array $migrationMap = [
        'tx_calendarize_domain_model_configuration' => [
            'start_date',
            'end_date',
        ],
        'tx_calendarize_domain_model_index' => [
            'start_date',
            'end_date',
        ],
    ];

    public function executeUpdate(): bool
    {
        foreach ($this->migrationMap as $table => $fields) {
            foreach ($fields as $field) {
                if ($this->updateNecessaryForTableFieldDateTimeMigration($table, $field)) {
                    $this->executeUpdateForTableFieldDateTimeMigration($table, $field);
                }
            }
        }

        return true;
    }

    protected function executeUpdateForTableFieldDateTimeMigration(string $tableName, string $fieldName): void
    {
        $tmpField = $fieldName . '_' . substr(md5((string)microtime()), 0, 10);

        $sqlQueries = [
            'ALTER TABLE ' . $tableName . ' ADD COLUMN ' . $tmpField . ' int(11) NOT NULL DEFAULT \'0\'',
            'UPDATE ' . $tableName . ' SET ' . $tmpField . ' = ' . $fieldName,
            'ALTER TABLE ' . $tableName . ' CHANGE ' . $fieldName . ' ' . $fieldName . ' int(11) DEFAULT NULL',
            'UPDATE ' . $tableName . ' SET ' . $fieldName . ' = null',
            'ALTER TABLE ' . $tableName . ' CHANGE ' . $fieldName . ' ' . $fieldName . ' DATE NULL default NULL',
            'UPDATE ' . $tableName . ' SET ' . $fieldName . ' = FROM_UNIXTIME(' . $tmpField . ')',
            'UPDATE ' . $tableName . ' SET ' . $fieldName . ' = NULL WHERE ' . $fieldName . ' = \'1970-01-01\'',
        ];

        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable($tableName);
        foreach ($sqlQueries as $sqlQuery) {
            $connection->executeQuery($sqlQuery);
        }
    }

    public function updateNecessary(): bool
    {
        foreach ($this->migrationMap as $table => $fields) {
            foreach ($fields as $field) {
                if ($this->updateNecessaryForTableFieldDateTimeMigration($table, $field)) {
                    return true;
                }
            }
        }

        return false;
    }

    protected function updateNecessaryForTableFieldDateTimeMigration(string $tableName, string $fieldName): bool
    {
        /** @var Connection $connection */
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable($tableName);
        $schemaManager = $connection->createSchemaManager();
        if (!$schemaManager->tablesExist([$tableName])) {
            return false;
        }

        $columns = $schemaManager->listTableColumns($tableName);
        if (!isset($columns[$fieldName]) || !($columns[$fieldName] instanceof Column)) {
            return false;
        }
        $checkField = $columns[$fieldName];

        return !($checkField->getType() instanceof DateType);
    }

    public function getPrerequisites(): array
    {
        // No DB Update is needed
        return [];
    }
}
