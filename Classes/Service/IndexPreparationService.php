<?php
/**
 * Helper class for the IndexService
 * Prepare the index
 *
 * @author  Tim Lochmüller
 */

namespace HDNET\Calendarize\Service;

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Helper class for the IndexService
 * Prepare the index
 *
 */
class IndexPreparationService
{

    /**
     * Build the index for one element
     *
     * @param string $configurationKey
     * @param string $tableName
     * @param int    $uid
     *
     * @return array
     */
    public function prepareIndex($configurationKey, $tableName, $uid)
    {
        $rawRecord = BackendUtility::getRecord($tableName, $uid);
        if (!$rawRecord) {
            return [];
        }
        $configurations = GeneralUtility::intExplode(',', $rawRecord['calendarize'], true);
        $neededItems = [];
        if ($configurations) {
            $timeTableService = GeneralUtility::makeInstance('HDNET\\Calendarize\\Service\\TimeTableService');
            $neededItems = $timeTableService->getTimeTablesByConfigurationIds($configurations);
            foreach ($neededItems as $key => $record) {

                $record['foreign_table'] = $tableName;
                $record['foreign_uid'] = $uid;
                $record['unique_register_key'] = $configurationKey;

                $this->prepareRecordForDatabase($record);
                $neededItems[$key] = $record;
            }
        }

        $this->addEnableFieldInformation($neededItems, $tableName, $rawRecord);
        $this->addLanguageInformation($neededItems, $tableName, $rawRecord);
        return $neededItems;
    }

    /**
     * Add the language information
     *
     * @param array  $neededItems
     * @param string $tableName
     * @param array  $record
     */
    protected function addLanguageInformation(array &$neededItems, $tableName, array $record)
    {
        $languageField = isset($GLOBALS['TCA'][$tableName]['ctrl']['languageField']) ? $GLOBALS['TCA'][$tableName]['ctrl']['languageField'] : false;
        $transOrigPointerField = isset($GLOBALS['TCA'][$tableName]['ctrl']['transOrigPointerField']) ? $GLOBALS['TCA'][$tableName]['ctrl']['transOrigPointerField'] : false;

        if (!$languageField || !$transOrigPointerField) {
            return;
        }
        if ((int)$record[$transOrigPointerField] > 0) {
            // no Index for language child elements
            return;
        }
        $language = (int)$record[$languageField];

        foreach ($neededItems as $key => $value) {
            $neededItems[$key]['sys_language_uid'] = $language;
        }
    }

    /**
     * Add the enable field information
     *
     * @param array  $neededItems
     * @param string $tableName
     * @param array  $record
     */
    protected function addEnableFieldInformation(array &$neededItems, $tableName, array $record)
    {
        $enableFields = isset($GLOBALS['TCA'][$tableName]['ctrl']['enablecolumns']) ? $GLOBALS['TCA'][$tableName]['ctrl']['enablecolumns'] : [];
        if (!$enableFields) {
            return;
        }

        $addFields = [];
        if (isset($enableFields['disabled'])) {
            $addFields['hidden'] = (int)$record[$enableFields['disabled']];
        }
        if (isset($enableFields['starttime'])) {
            $addFields['starttime'] = (int)$record[$enableFields['starttime']];
        }
        if (isset($enableFields['endtime'])) {
            $addFields['endtime'] = (int)$record[$enableFields['endtime']];
        }
        if (isset($enableFields['fe_group'])) {
            $addFields['fe_group'] = (string)$record[$enableFields['fe_group']];
        }

        foreach ($neededItems as $key => $value) {
            $neededItems[$key] = array_merge($value, $addFields);
        }
    }

    /**
     * Prepare the record for the database insert
     *
     * @param $record
     *
     * @return void
     */
    protected function prepareRecordForDatabase(&$record)
    {
        foreach ($record as $key => $value) {
            if ($value instanceof \DateTime) {
                $record[$key] = $value->getTimestamp();
            } elseif (is_bool($value)) {
                $record[$key] = (int)$value;
            } elseif ($value === null) {
                $record[$key] = '';
            }
        }
    }
}