<?php

/**
 * BreadcrumbService
 */
declare(strict_types=1);

namespace HDNET\Calendarize\Service;

use GeorgRinger\News\Hooks\BackendUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * BreadcrumbService
 */
class BreadcrumbService extends AbstractService
{

    /**
     * @param string $content
     * @param array $configuration
     */
    public function generate(string $content, array $configuration)
    {
        $arguments = GeneralUtility::_GET('tx_calendarize_calendar');
        $indexUid = isset($arguments['index']) ? (int) $arguments['index'] : 0;
        if ($indexUid === 0) {
            return $content;
        }

        $index = $this->getIndex($indexUid);
        if ($index === null) {
            return $content;
        }

        $event = $this->getEventByIndex($index);
        $contentObjectRenderer = GeneralUtility::makeInstance(ContentObjectRenderer::class);


        if(isset($configuration['doNotLinkIt']) && (bool)$configuration['doNotLinkIt']) {
            $content = $event['title'];
        } else {
            $linkConfiguration = [
                'parameter' => $GLOBALS['TSFE']->id,
                'additionalParams' => '&tx_calendarize_calendar[index]=' . $indexUid,
            ];
            $content = $contentObjectRenderer->typoLink($event['title'], $linkConfiguration);
        }
        return $contentObjectRenderer->stdWrap($content, $configuration);
    }

    /**
     * @param $row
     * @return mixed|null
     */
    protected function getEventByIndex($row)
    {
        return $this->fetchRecordByUid($row['foreign_table'], (int) $row['foreign_uid']);
    }

    /**
     * @param $uid
     * @return mixed|null
     */
    protected function getIndex($uid)
    {
        return $this->fetchRecordByUid('tx_calendarize_domain_model_index', $uid);
    }

    /**
     * @param $uid
     * @return mixed|null
     */
    protected function fetchRecordByUid($table, $uid)
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($table);
        $where = [
            $queryBuilder->expr()->eq('uid', (int) $uid)
        ];
        $rows = $queryBuilder->select('*')
            ->from($table)
            ->where(...$where)
            ->execute()
            ->fetchAll();

        return isset($rows[0]) ? $rows[0] : null;
    }

}
