<?php

declare(strict_types=1);

namespace HDNET\Calendarize\Widgets\DataProvider;

use HDNET\Calendarize\Domain\Repository\IndexRepository;
use TYPO3\CMS\Dashboard\Widgets\NumberWithIconDataProviderInterface;

class IndexAmountDataProvider implements NumberWithIconDataProviderInterface
{
    /**
     * The index repository.
     *
     * @var \HDNET\Calendarize\Domain\Repository\IndexRepository
     */
    protected $indexRepository;

    public function injectIndexRepository(IndexRepository $indexRepository)
    {
        $this->indexRepository = $indexRepository;
    }

    public function getNumber(): int
    {
        return $this->indexRepository->findAll()->count();
    }
}
