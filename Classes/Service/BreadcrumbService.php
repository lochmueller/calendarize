<?php

/**
 * BreadcrumbService.
 */
declare(strict_types=1);

namespace HDNET\Calendarize\Service;

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\HttpUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * BreadcrumbService.
 */
class BreadcrumbService extends AbstractService
{
    /**
     * @param string $content
     * @param array  $configuration
     */
    public function generate(string $content, array $configuration)
    {
        $arguments = GeneralUtility::_GET('tx_calendarize_calendar');
        $indexUid = isset($arguments['index']) ? (int)$arguments['index'] : 0;
        if (0 === $indexUid) {
            return $content;
        }

        $index = $this->getIndex($indexUid);
        if (null === $index) {
            return $content;
        }

        $event = $this->getEventByIndex($index);
        $contentObjectRenderer = GeneralUtility::makeInstance(ContentObjectRenderer::class);

        if (isset($configuration['doNotLinkIt']) && (bool)$configuration['doNotLinkIt']) {
            $content = $event['title'];
        } else {
            $linkConfiguration = [
                'parameter' => $GLOBALS['TSFE']->id,
                'additionalParams' => HttpUtility::buildQueryString([
                    'tx_calendarize_calendar' => [
                        'index' => $indexUid,
                        'controller' => 'Calendar',
                        'action' => 'detail',
                    ],
                ], '&'),
            ];
            $content = $contentObjectRenderer->typoLink($event['title'], $linkConfiguration);
        }

        return $contentObjectRenderer->stdWrap($content, $configuration);
    }

    protected function getEventByIndex(array $row)
    {
        return BackendUtility::getRecordWSOL($row['foreign_table'], (int)$row['foreign_uid']);
    }

    protected function getIndex(int $uid)
    {
        return BackendUtility::getRecordWSOL('tx_calendarize_domain_model_index', $uid);
    }
}
