<?php

declare(strict_types=1);

namespace HDNET\Calendarize\Service;

use HDNET\Calendarize\Domain\Model\ConfigurationInterface;
use HDNET\Calendarize\Domain\Repository\RawIndexRepository;
use HDNET\Calendarize\Utility\DateTimeUtility;
use HDNET\Calendarize\Utility\TranslateUtility;
use HDNET\Calendarize\Utility\WorkspaceUtility;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * TCA information.
 */
class TcaInformation extends AbstractService
{
    /**
     * Generate the information field.
     */
    public function informationField(array $configuration, ?object $fObj): string
    {
        if (!isset($configuration['row']['uid'])) {
            return $this->wrapContent(TranslateUtility::get('save.first'));
        }

        $previewLimit = 10;
        if (isset($configuration['fieldConf']['config']['items'])) {
            $previewLimit = (int)$configuration['fieldConf']['config']['items'];
        }

        return $this->wrapContent($this->renderPreviewField(
            (string)$configuration['table'],
            (int)$configuration['row']['uid'],
            $previewLimit,
        ));
    }

    public function renderPreviewField(string $tableName, int $uid, int $limit): string
    {
        /** @var RawIndexRepository $rawIndexRepository */
        $rawIndexRepository = GeneralUtility::makeInstance(RawIndexRepository::class);
        $count = $rawIndexRepository->countAllEvents($tableName, $uid, WorkspaceUtility::getCurrentWorkspaceId());
        $next = $rawIndexRepository->findNextEvents(
            $tableName,
            $uid,
            $limit,
            WorkspaceUtility::getCurrentWorkspaceId(),
        );

        return \sprintf(TranslateUtility::get('previewLabel'), $count, $limit) . $this->getEventList($next);
    }

    /**
     * Generate the information field.
     */
    public function informationGroupField(array $configuration, ?object $fObj): string
    {
        $ids = GeneralUtility::intExplode(',', $configuration['row']['configurations'], true);
        if (!$ids) {
            return $this->wrapContent(TranslateUtility::get('save.first'));
        }
        $timeTableService = GeneralUtility::makeInstance(TimeTableService::class);
        $items = $timeTableService->getTimeTablesByConfigurationIds($ids, 0);

        return $this->wrapContent($this->getEventList($items));
    }

    /**
     * Get event list.
     */
    protected function getEventList(array $events): string
    {
        $items = [];
        foreach ($events as $event) {
            if (!($event['start_date'] instanceof \DateTimeInterface)) {
                $event['start_date'] = new \DateTime($event['start_date']);
            }
            $startDate = BackendUtility::date($event['start_date']->getTimestamp());

            if (!($event['end_date'] instanceof \DateTimeInterface)) {
                $event['end_date'] = new \DateTime($event['end_date']);
            }
            $endDate = BackendUtility::date($event['end_date']->getTimestamp());
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
            if (ConfigurationInterface::STATE_DEFAULT !== $event['state']) {
                $entry .= ' / ' . TranslateUtility::get($event['state']);
            }
            $items[] = $entry;
        }
        if (!$items) {
            $items[] = TranslateUtility::get('noEvents');
        }

        return '<ul><li>' . implode('</li><li>', $items) . '</li></ul>';
    }

    /**
     * Wrap the content.
     */
    protected function wrapContent(string $content): string
    {
        return '<div style="padding: 5px;">' . $content . '</div>';
    }
}
