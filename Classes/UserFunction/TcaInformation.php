<?php
/**
 * TCA information
 *
 * @author  Tim Lochmüller
 */

namespace HDNET\Calendarize\UserFunction;

use HDNET\Calendarize\Service\IndexerService;
use HDNET\Calendarize\Service\TimeTableService;
use HDNET\Calendarize\Utility\TranslateUtility;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * TCA information
 *
 * @author Tim Lochmüller
 */
class TcaInformation
{

    /**
     * Generate the information field
     *
     * @param array  $configuration
     * @param object $fObj
     *
     * @return string
     */
    public function informationField($configuration, $fObj)
    {
        if (!isset($configuration['row']['uid'])) {
            $content = TranslateUtility::get('save.first');
        } else {
            /** @var IndexerService $indexService */
            $indexService = GeneralUtility::makeInstance('HDNET\\Calendarize\\Service\\IndexerService');
            $previewLimit = 10;
            $count = $indexService->getIndexCount($configuration['table'], $configuration['row']['uid']);
            $next = $indexService->getNextEvents($configuration['table'], $configuration['row']['uid'], $previewLimit);
            $content = sprintf(TranslateUtility::get('previewLabel'), $count,
                    $previewLimit) . $this->getEventList($next);
        }
        return '<div style="padding: 5px;">' . $content . '</div>';
    }

    /**
     * Generate the information field
     *
     * @param array $configuration
     * @param object $fObj
     *
     * @return string
     */
    public function informationGroupField($configuration, $fObj)
    {
        $ids = GeneralUtility::intExplode(',', $configuration['row']['configurations'], true);

        if (!sizeof($ids)) {
            $content = TranslateUtility::get('save.first');
        } else {
            /** @var TimeTableService $timeTableService */
            $timeTableService = GeneralUtility::makeInstance('HDNET\\Calendarize\\Service\\TimeTableService');
            $items = $timeTableService->getTimeTablesByConfigurationIds($ids);
            $content = $this->getEventList($items);
        }
        return '<div style="padding: 5px;">' . $content . '</div>';
    }

    /**
     * Get event list
     *
     * @param $events
     *
     * @return string
     */
    protected function getEventList($events)
    {
        $items = [];
        foreach ($events as $event) {
            $startDate = ($event['start_date'] instanceof \DateTime) ? $event['start_date']->format("d.m.Y") : date('d.m.Y',
                $event['start_date']);
            $endDate = ($event['end_date'] instanceof \DateTime) ? $event['end_date']->format("d.m.Y") : date('d.m.Y',
                $event['end_date']);
            $entry = $startDate . ' - ' . $endDate;
            if (!$event['all_day']) {
                $start = BackendUtility::time($event['start_time'], false);
                $end = BackendUtility::time($event['end_time'], false);
                $entry .= ' (' . $start . ' - ' . $end . ')';
            }
            $items[] = $entry;
        }
        if (!sizeof($items)) {
            $items[] = TranslateUtility::get('noEvents');
        }
        return '<ul><li>' . implode('</li><li>', $items) . '</li></ul>';
    }
}