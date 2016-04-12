<?php
/**
 * TCA information
 *
 * @author  Tim Lochmüller
 */

namespace HDNET\Calendarize\UserFunction;

use HDNET\Calendarize\Service\IndexerService;
use HDNET\Calendarize\Service\TimeTable\AbstractTimeTable;
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
            return $this->wrapContent(TranslateUtility::get('save.first'));
        }
        /** @var IndexerService $indexService */
        $indexService = GeneralUtility::makeInstance(IndexerService::class);
        $previewLimit = 10;
        $count = $indexService->getIndexCount($configuration['table'], $configuration['row']['uid']);
        $next = $indexService->getNextEvents($configuration['table'], $configuration['row']['uid'], $previewLimit);
        $content = sprintf(TranslateUtility::get('previewLabel'), $count, $previewLimit) . $this->getEventList($next);
        return $this->wrapContent($content);
    }

    /**
     * Generate the information field
     *
     * @param array  $configuration
     * @param object $fObj
     *
     * @return string
     */
    public function informationGroupField($configuration, $fObj)
    {
        $ids = GeneralUtility::intExplode(',', $configuration['row']['configurations'], true);
        if (!sizeof($ids)) {
            return $this->wrapContent(TranslateUtility::get('save.first'));
        }
        /** @var TimeTableService $timeTableService */
        $timeTableService = GeneralUtility::makeInstance(TimeTableService::class);
        $items = $timeTableService->getTimeTablesByConfigurationIds($ids);
        return $this->wrapContent($this->getEventList($items));
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
            $startDateStamp = $event['start_date'] instanceof \DateTime ? $event['start_date']->getTimestamp() : $event['start_date'];
            $startDate = strftime('%a %d.%m.%G', $startDateStamp);
            $endDateStamp = $event['end_date'] instanceof \DateTime ? $event['end_date']->getTimestamp() : $event['end_date'];
            $endDate = strftime('%a %d.%m.%G', $endDateStamp);
            $entry = $startDate . ' - ' . $endDate;
            if (!$event['all_day']) {
                $start = BackendUtility::time($event['start_time'], false);
                if ((int)$event['end_time'] === AbstractTimeTable::DAY_END) {
                    $end = '"' . TranslateUtility::get('openEndTime') . '"';
                } else {
                    $end = BackendUtility::time($event['end_time'], false);
                }
                $entry .= ' (' . $start . ' - ' . $end . ')';
            }
            $items[] = $entry;
        }
        if (!sizeof($items)) {
            $items[] = TranslateUtility::get('noEvents');
        }
        return '<ul><li>' . implode('</li><li>', $items) . '</li></ul>';
    }

    /**
     * Wrap the content
     *
     * @param string $content
     *
     * @return string
     */
    protected function wrapContent($content)
    {
        return '<div style="padding: 5px;">' . $content . '</div>';
    }
}
