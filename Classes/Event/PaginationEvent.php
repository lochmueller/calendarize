<?php

namespace HDNET\Calendarize\Event;

use TYPO3\CMS\Core\Pagination\AbstractPaginator;
use TYPO3\CMS\Core\Pagination\PaginationInterface;

class PaginationEvent
{
    public function __construct(
        protected AbstractPaginator $paginator,
        protected PaginationInterface $pagination,
        protected array $paginateConfiguration,
        protected array $currentValues,
    ) {}

    public function setPaginator(AbstractPaginator $paginator): void
    {
        $this->paginator = $paginator;
    }

    public function getPaginator(): AbstractPaginator
    {
        return $this->paginator;
    }

    public function setPagination(PaginationInterface $pagination): void
    {
        $this->pagination = $pagination;
    }

    public function getPagination(): PaginationInterface
    {
        return $this->pagination;
    }

    public function getPaginateConfiguration(): array
    {
        return $this->paginateConfiguration;
    }

    public function getCurrentValues(): array
    {
        return $this->currentValues;
    }
}
