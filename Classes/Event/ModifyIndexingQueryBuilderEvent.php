<?php

namespace HDNET\Calendarize\Event;

use TYPO3\CMS\Core\Database\Query\QueryBuilder;

final class ModifyIndexingQueryBuilderEvent
{
    public function __construct(
        private QueryBuilder $queryBuilder,
        private readonly array $configuration,
    ) {}

    public function setQueryBuilder(QueryBuilder $queryBuilder): void
    {
        $this->queryBuilder = $queryBuilder;
    }

    public function getQueryBuilder(): QueryBuilder
    {
        return $this->queryBuilder;
    }

    public function getConfiguration(): array
    {
        return $this->configuration;
    }
}
