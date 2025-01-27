<?php

declare(strict_types=1);

namespace HDNET\Calendarize\Service;

use HDNET\Calendarize\Domain\Model\ConfigurationInterface;
use HDNET\Calendarize\Utility\DateTimeUtility;
use HDNET\Calendarize\Utility\TranslateUtility;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;

/**
 * TCA service.
 */
class TcaService extends AbstractService
{
    /**
     * Render the configuration title.
     */
    public function configurationTitle(array &$params, ?object $_ = null): void
    {
        $row = $params['row'];
        $this->migrateFormEngineRow($row);

        $handling = \is_array($row['handling']) ? array_shift($row['handling']) : $row['handling'];
        $params['title'] .= '<b>' . TranslateUtility::get('configuration.type.' . $row['type'])
            . ' (' . TranslateUtility::get('configuration.handling.' . $handling) . ')</b><br /> ';
        switch ($row['type']) {
            case ConfigurationInterface::TYPE_TIME:
                $params['title'] .= $this->getConfigurationTitleTime($row);
                break;
            case ConfigurationInterface::TYPE_GROUP:
                $params['title'] .= $this->getConfigurationGroupTitle($row);
                break;
            case ConfigurationInterface::TYPE_EXTERNAL:
                $params['title'] .= 'URL: ' . $row['external_ics_url'];
                break;
        }
    }

    /**
     * Add configurations to event titles.
     */
    public function eventTitle(array &$params, ?object $_): void
    {
        // if record has no title
        if (!MathUtility::canBeInterpretedAsInteger($params['row']['uid'] ?? '')) {
            return;
        }

        // base title
        $table = $params['table'];
        unset($GLOBALS['TCA'][$table]['ctrl']['label_userFunc']);
        $params['title'] = BackendUtility::getRecordTitle($table, $params['row']);
        $GLOBALS['TCA'][$table]['ctrl']['label_userFunc'] = self::class . '->eventTitle';

        // base record
        $fullRow = BackendUtility::getRecordWSOL($table, $params['row']['uid']);

        $transPointer = $GLOBALS['TCA'][$table]['ctrl']['transOrigPointerField'] ?? false; // e.g. l10n_parent
        if ($transPointer && (int)($fullRow[$transPointer] ?? 0) > 0) {
            return;
        }

        $configurations = isset($fullRow['calendarize']) ? GeneralUtility::intExplode(
            ',',
            $fullRow['calendarize'],
            true,
        ) : [];

        if (empty($configurations)) {
            return;
        }

        foreach ($configurations as $key => $value) {
            $paramsInternal = [
                'row' => BackendUtility::getRecordWSOL(
                    'tx_calendarize_domain_model_configuration',
                    $value,
                ) ?? [],
                'title' => '',
            ];
            $this->configurationTitle($paramsInternal);
            $configurations[$key] = strip_tags($paramsInternal['title']);
        }
        $params['title'] .= ' / ' . implode(' / ', $configurations);
    }

    /**
     * The new FormEngine prepare the select as array
     * Migrate it to the old behavior.
     */
    protected function migrateFormEngineRow(array &$row): void
    {
        $migrateFields = ['type', 'frequency', 'groups'];
        foreach ($migrateFields as $field) {
            $row[$field] = \is_array($row[$field]) ? array_shift($row[$field]) : $row[$field];
        }
    }

    /**
     * Get group title.
     */
    protected function getConfigurationGroupTitle(array $row): string
    {
        $title = '';
        $groups = GeneralUtility::trimExplode(',', (string)$row['groups'], true);
        foreach ($groups as $key => $id) {
            $row = BackendUtility::getRecord('tx_calendarize_domain_model_configurationgroup', $id);
            if (!empty($row)) {
                $groups[$key] = $row['title'] . ' (#' . $id . ')';
            }
        }
        if ($groups) {
            $title .= '<ul><li>' . implode('</li><li>', $groups) . '</li></ul>';
        }

        return $title;
    }

    /**
     * Get the title for a configuration time.
     */
    protected function getConfigurationTitleTime(array $row): string
    {
        $title = '';
        if ($row['start_date']) {
            try {
                $dateStart = BackendUtility::date((new \DateTime($row['start_date']))->getTimestamp());
                $dateEnd = BackendUtility::date(
                    (new \DateTime($row['end_date'] ?: $row['start_date']))->getTimestamp(),
                );
                $title .= $dateStart;
                if ($dateStart !== $dateEnd) {
                    $title .= ' - ' . $dateEnd;
                }
            } catch (\Exception $exception) {
            }
        }
        if ($row['all_day']) {
            $title .= ' ' . TranslateUtility::get('tx_calendarize_domain_model_index.all_day');
        } elseif ($row['start_time']) {
            $title .= ' <br />' . BackendUtility::time($row['start_time'] % DateTimeUtility::SECONDS_DAY, false);
            if ($row['open_end_time']) {
                $title .= ' - ' . TranslateUtility::get('open_end');
            } else {
                $title .= ' - ' . BackendUtility::time($row['end_time'] % DateTimeUtility::SECONDS_DAY, false);
            }
        }
        if ($row['frequency'] && ConfigurationInterface::FREQUENCY_NONE !== $row['frequency']) {
            $title .= ' <br /><i>' . TranslateUtility::get('configuration.frequency.' . $row['frequency']) . '</i>';
        }

        return $title;
    }
}
