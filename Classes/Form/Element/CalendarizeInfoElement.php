<?php

declare(strict_types=1);
namespace HDNET\Calendarize\Form\Element;

use HDNET\Calendarize\Service\IndexerService;
use HDNET\Calendarize\Utility\DateTimeUtility;
use HDNET\Calendarize\Utility\TranslateUtility;
use TYPO3\CMS\Backend\Form\Element\AbstractFormElement;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class CalendarizeInfoElement extends AbstractFormElement
{
    public function render()
    {
        $result = $this->initializeResultArray();

        $parameters = $this->data['parameterArray']['fieldConf']['config']['parameters'];

        $previewLimit = 10;
        if (isset($parameters['items'])) {
            $previewLimit = (int)$parameters['items'];
        }

        $indexService = GeneralUtility::makeInstance(IndexerService::class);
        $count = $indexService->getIndexCount($this->data['tableName'], $this->data['vanillaUid']);
        $next = $indexService->getNextEvents($this->data['tableName'], $this->data['vanillaUid'], $previewLimit);
        $content = \sprintf(TranslateUtility::get('previewLabel'), $count, $previewLimit) . $this->getEventList($next);

        $result['html'] = $content;
        return $result;
    }

    /**
     * Get event list.
     *
     * @param $events
     *
     * @return string
     */
    protected function getEventList($events)
    {
        $items = [];
        foreach ($events as $event) {
            if (!($event['start_date'] instanceof \DateTimeInterface)) {
                $event['start_date'] = new \DateTime($event['start_date']);
            }
            $startDate = \strftime(DateTimeUtility::FORMAT_DATE_BACKEND, (int)$event['start_date']->getTimestamp());

            if (!($event['end_date'] instanceof \DateTimeInterface)) {
                $event['end_date'] = new \DateTime($event['end_date']);
            }
            $endDate = \strftime(DateTimeUtility::FORMAT_DATE_BACKEND, (int)$event['end_date']->getTimestamp());
            $entry = $startDate . ' - ' . $endDate;
            if (!$event['all_day']) {
                $start = BackendUtility::time($event['start_time'] % DateTimeUtility::SECONDS_DAY, false);
                if ((bool)$event['open_end_time']) {
                    $end = '"' . TranslateUtility::get('openEndTime') . '"';
                } else {
                    $end = BackendUtility::time($event['end_time'] % DateTimeUtility::SECONDS_DAY, false);
                }
                $entry .= ' (' . $start . ' - ' . $end . ')';
            }
            $items[] = $entry;
        }
        if (!$items) {
            $items[] = TranslateUtility::get('noEvents');
        }

        return '<ul><li>' . \implode('</li><li>', $items) . '</li></ul>';
    }
}
