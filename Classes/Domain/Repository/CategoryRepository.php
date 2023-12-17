<?php

declare(strict_types=1);

namespace HDNET\Calendarize\Domain\Repository;

use TYPO3\CMS\Extbase\Domain\Model\Category;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

class CategoryRepository extends AbstractRepository
{
    public function __construct()
    {
        parent::__construct();
        $this->objectType = Category::class;
    }

    public function findByIds(array $categoryIds, array $orderings = []): array|QueryResultInterface
    {
        $query = $this->createQuery();
        $query->getQuerySettings()->setRespectStoragePage(false);
        $query->getQuerySettings()->setRespectSysLanguage(false);

        $query->matching($query->in('uid', $categoryIds));

        if (!empty($orderings)) {
            $query->setOrderings($orderings);
        }

        return $query->execute();
    }
}
