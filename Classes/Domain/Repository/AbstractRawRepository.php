<?php

namespace HDNET\Calendarize\Domain\Repository;

use HDNET\Calendarize\Utility\HelperUtility;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;

abstract class AbstractRawRepository
{
    protected $tableName;

    public function deleteByIdentifier(array $idents): bool
    {
        return (bool)$this->getDatabaseConnection()->delete($this->tableName, $idents);
    }

    public function insert($data): bool
    {
        return (bool)$this->getDatabaseConnection()->insert($this->tableName, $data);
    }

    public function truncate(): bool
    {
        return (bool)$this->getDatabaseConnection()->truncate($this->tableName);
    }

    protected function getDatabaseConnection(): Connection
    {
        return HelperUtility::getDatabaseConnection($this->tableName);
    }

    protected function getQueryBuilder(): QueryBuilder
    {
        return $this->getDatabaseConnection()->createQueryBuilder();
    }
}
