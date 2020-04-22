<?php

/**
 * TCA service.
 */
declare(strict_types=1);

namespace HDNET\Calendarize\Service;

use HDNET\Calendarize\Domain\Model\Configuration;
use HDNET\Calendarize\Utility\DateTimeUtility;
use HDNET\Calendarize\Utility\HelperUtility;
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
     *
     * @param array  $params
     * @param object $object
     */
    public function configurationTitle(array &$params, $object)
    {
        $row = $params['row'];
        $this->migrateFormEngineRow($row);

        $handling = \is_array($row['handling']) ? \array_shift($row['handling']) : $row['handling'];
        $params['title'] .= '<b>' . TranslateUtility::get('configuration.type.' . $row['type']) . ' (' . TranslateUtility::get('configuration.handling.' . $handling) . ')</b><br /> ';
        switch ($row['type']) {
            case Configuration::TYPE_TIME:
                $params['title'] .= $this->getConfigurationTitleTime($row);
                break;
            case Configuration::TYPE_GROUP:
                $params['title'] .= $this->getConfigurationGroupTitle($row);
                break;
            case Configuration::TYPE_EXTERNAL:
                $params['title'] .= 'URL: ' . $row['external_ics_url'];
                break;
        }
    }

    /**
     * Add configurations to event titles.
     *
     * @param array  $params
     * @param object $object
     */
    public function eventTitle(array &$params, $object)
    {
        // if record has no title
        if (!MathUtility::canBeInterpretedAsInteger($params['row']['uid'])) {
            return;
        }

        // base title
        $table = $params['table'];
        unset($GLOBALS['TCA'][$table]['ctrl']['label_userFunc']);
        $params['title'] = BackendUtility::getRecordTitle($table, $params['row']);
        $GLOBALS['TCA'][$table]['ctrl']['label_userFunc'] = self::class . '->eventTitle';

        // base record
        $databaseConnection = HelperUtility::getDatabaseConnection($table);
        $fullRow = $databaseConnection->select(['*'], $table, ['uid' => $params['row']['uid']])->fetch();

        $transPointer = $GLOBALS['TCA'][$table]['ctrl']['transOrigPointerField'] ?? false; // e.g. l10n_parent
        if ($transPointer && (int)$fullRow[$transPointer] > 0) {
            return;
        }

        $configurations = isset($fullRow['calendarize']) ? GeneralUtility::intExplode(
            ',',
            $fullRow['calendarize'],
            true
        ) : [];

        if (empty($configurations)) {
            return;
        }

        foreach ($configurations as $key => $value) {
            $paramsInternal = [
                'row' => (array)$databaseConnection->select(
                    ['*'],
                    'tx_calendarize_domain_model_configuration',
                    ['uid' => $value]
                )->fetch(),
                'title' => '',
            ];
            $this->configurationTitle($paramsInternal, null);
            $configurations[$key] = \strip_tags($paramsInternal['title']);
        }
        $params['title'] .= ' / ' . \implode(' / ', $configurations);
    }

    /**
     * The new FormEngine prepare the select as array
     * Migrate it to the old behavior.
     *
     * @param array $row
     */
    protected function migrateFormEngineRow(array &$row)
    {
        $migrateFields = ['type', 'frequency', 'groups'];
        foreach ($migrateFields as $field) {
            $row[$field] = \is_array($row[$field]) ? $row[$field][0] : $row[$field];
        }
    }

    /**
     * Get group title.
     *
     * @param $row
     *
     * @return string
     */
    protected function getConfigurationGroupTitle($row)
    {
        $title = '';
        $groups = GeneralUtility::trimExplode(',', $row['groups'], true);
        foreach ($groups as $key => $id) {
            $row = BackendUtility::getRecord('tx_calendarize_domain_model_configurationgroup', $id);
            $groups[$key] = $row['title'] . ' (#' . $id . ')';
        }
        if ($groups) {
            $title .= '<ul><li>' . \implode('</li><li>', $groups) . '</li></ul>';
        }

        return $title;
    }

    /**
     * Get the title for a configuration time.
     *
     * @param $row
     *
     * @return string
     */
    protected function getConfigurationTitleTime($row)
    {
        $title = '';
        if ($row['start_date']) {
            try {
                $dateStart = \strftime(DateTimeUtility::FORMAT_DATE_BACKEND, (new \DateTime($row['start_date']))->getTimestamp());
                $dateEnd = \strftime(DateTimeUtility::FORMAT_DATE_BACKEND, (new \DateTime($row['end_date'] ?: $row['start_date']))->getTimestamp());
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
            $title .= ' - ' . BackendUtility::time($row['end_time'] % DateTimeUtility::SECONDS_DAY, false);
        }
        if ($row['frequency'] && Configuration::FREQUENCY_NONE !== $row['frequency']) {
            $title .= ' <br /><i>' . TranslateUtility::get('configuration.frequency.' . $row['frequency']) . '</i>';
        }

        return $title;
    }
}
