<?php

/**
 * Typo3Route.
 */
declare(strict_types=1);

namespace HDNET\Calendarize\Service\Url;

use HDNET\Calendarize\Domain\Model\Index;
use TYPO3\CMS\Core\DataHandling\SlugHelper;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Typo3Route.
 */
class Typo3Route extends AbstractUrl
{
    /**
     * Convert the given information.
     *
     * @param $param1
     * @param $param2
     *
     * @return string
     */
    public function convert($param1, $param2)
    {
        if (isset($param1['generate'])) {
            return $this->id2alias($param1['generate']);
        }

        return $this->alias2id($param1['resolve']);
    }

    /**
     * Handle the alias to index ID convert.
     *
     * @param $value
     *
     * @return int
     */
    protected function alias2id($value): int
    {
        $parts = GeneralUtility::trimExplode('-', $value, true);

        return (int)\array_pop($parts);
    }

    /**
     * Handle the index ID to alias convert.
     *
     * @param $value
     *
     * @return string
     */
    protected function id2alias($value): string
    {
        $alias = $this->getIndexBase((int)$value);

        // Because the Slug helper do not remove "/" chars
        $alias = \str_replace('/', '-', $alias);

        $slugHelper = GeneralUtility::makeInstance(SlugHelper::class, 'pages', 'uid', []);
        $alias = $slugHelper->sanitize($alias);

        return (string)$alias;
    }
}
