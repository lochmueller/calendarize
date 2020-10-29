<?php

declare(strict_types=1);

namespace HDNET\Calendarize\Updates;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Install\Updates\UpgradeWizardInterface;

class DateFieldUpdate implements UpgradeWizardInterface
{
    public function getIdentifier(): string
    {
        return self::class;
    }

    public function getTitle(): string
    {
        return 'Calendarize Date Field';
    }

    public function getDescription(): string
    {
        return '';
    }

    protected $migrationMap = [
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

    protected function executeUpdateForTableFieldDateTimeMigration(string $tableName, string $fieldName)
    {
        $tmpField = $fieldName . '_' . GeneralUtility::shortMD5((string)microtime(), 5);

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

    protected function updateNecessaryForTableFieldDateTimeMigration(string $tableName, string $fieldName)
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($tableName);
        /** @var QueryBuilder $queryBuilder */
        $schemaManager = $queryBuilder->getConnection()->getSchemaManager();
        if (!$schemaManager->tablesExist($tableName)) {
            return false;
        }

        $columns = $schemaManager->listTableColumns($tableName);
        if (!isset($columns[$fieldName]) || !($columns[$fieldName] instanceof \Doctrine\DBAL\Schema\Column)) {
            return false;
        }
        /** @var \Doctrine\DBAL\Schema\Column $checkField */
        $checkField = $columns[$fieldName];

        return !($checkField->getType() instanceof \Doctrine\DBAL\Types\DateType);
    }

    public function getPrerequisites(): array
    {
        // No DB Update is needed
        return [];
    }
}
