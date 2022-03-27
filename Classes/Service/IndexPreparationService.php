<?php

/**
 * Helper class for the IndexService
 * Prepare the index.
 */
declare(strict_types=1);

namespace HDNET\Calendarize\Service;

use HDNET\Calendarize\Register;
use HDNET\Calendarize\Service\Url\SlugService;
use HDNET\Calendarize\Utility\HelperUtility;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Helper class for the IndexService
 * Prepare the index.
 */
class IndexPreparationService extends AbstractService
{
    /**
     * @var SlugService
     */
    protected $slugService;

    public function __construct(SlugService $slugService)
    {
        $this->slugService = $slugService;
    }

    /**
     * Build the index for one element.
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

        $register = Register::getRegister();
        $fieldName = $register[$configurationKey]['fieldName'] ?? 'calendarize';
        $configurations = GeneralUtility::intExplode(',', $rawRecord[$fieldName] ?? '', true);

        $transPointer = $GLOBALS['TCA'][$tableName]['ctrl']['transOrigPointerField'] ?? false; // e.g. l10n_parent
        if ($transPointer && (int)$rawRecord[$transPointer] > 0) {
            $rawOriginalRecord = BackendUtility::getRecord($tableName, (int)$rawRecord[$transPointer]);
            $configurations = GeneralUtility::intExplode(',', $rawOriginalRecord[$fieldName], true);
        }

        $neededItems = [];
        if ($configurations) {
            $timeTableService = GeneralUtility::makeInstance(TimeTableService::class);
            $neededItems = $timeTableService->getTimeTablesByConfigurationIds($configurations, (int)$rawRecord['t3ver_wsid']);
            foreach ($neededItems as $key => $record) {
                $record['foreign_table'] = $tableName;
                $record['foreign_uid'] = $uid;
                $record['unique_register_key'] = $configurationKey;

                $this->prepareRecordForDatabase($record);
                $neededItems[$key] = $record;
            }
        }

        // Language information must be added before ctrl/enable information
        $this->addLanguageInformation($neededItems, $tableName, $rawRecord);
        $this->addEnableFieldInformation($neededItems, $tableName, $rawRecord);
        $this->addCtrlFieldInformation($neededItems, $tableName, $rawRecord);
        $this->addSlugInformation($neededItems, $configurationKey, $rawRecord);
        $this->addWorkspaceInformation($neededItems, $configurationKey, $rawRecord);

        return $neededItems;
    }

    protected function addWorkspaceInformation(array &$neededItems, string $configurationKey, array $record): void
    {
        $workspace = isset($record['t3ver_wsid']) ? (int)$record['t3ver_wsid'] : 0;
        $origId = isset($record['t3ver_oid']) ? (int)$record['t3ver_oid'] : 0;
        $neededItems = array_map(static function ($item) use ($workspace, $origId, $record) {
            $item['t3ver_wsid'] = $workspace;
            // Set relation to the original record
            if ($workspace) {
                $item['foreign_uid'] = $origId ?: (int)$record['uid'];
            }

            return $item;
        }, $neededItems);
    }

    /**
     * Add the language information.
     *
     * @param array  $neededItems
     * @param string $tableName
     * @param array  $record
     */
    protected function addLanguageInformation(array &$neededItems, string $tableName, array $record): void
    {
        $languageField = $GLOBALS['TCA'][$tableName]['ctrl']['languageField'] ?? false; // e.g. sys_language_uid
        $transPointer = $GLOBALS['TCA'][$tableName]['ctrl']['transOrigPointerField'] ?? false; // e.g. l10n_parent

        if ($transPointer && (int)$record[$transPointer] > 0) {
            foreach ($neededItems as $key => $value) {
                $originalRecord = BackendUtility::getRecord($value['foreign_table'], $value['foreign_uid'], $transPointer);

                $searchFor = $value;
                $searchFor['foreign_uid'] = (int)$originalRecord[$transPointer];

                $db = HelperUtility::getDatabaseConnection(IndexerService::TABLE_NAME);
                $q = $db->createQueryBuilder();
                $where = [];
                foreach ($searchFor as $field => $val) {
                    if (\is_string($val)) {
                        $where[] = $q->expr()->eq($field, $q->quote($val));
                    } else {
                        $where[] = $q->expr()->eq($field, (int)$val);
                    }
                }

                $result = $q->select('uid')->from(IndexerService::TABLE_NAME)->andWhere(...$where)->execute()->fetch();
                if (isset($result['uid'])) {
                    $neededItems[$key]['l10n_parent'] = (int)$result['uid'];
                }
            }
        }

        if ($languageField) {
            $language = (int)$record[$languageField];
            foreach (array_keys($neededItems) as $key) {
                $neededItems[$key]['sys_language_uid'] = $language;
            }
        }
    }

    /**
     * Add the enable field information.
     *
     * @param array  $neededItems
     * @param string $tableName
     * @param array  $record
     */
    protected function addEnableFieldInformation(array &$neededItems, $tableName, array $record)
    {
        $enableFields = $GLOBALS['TCA'][$tableName]['ctrl']['enablecolumns'] ?? [];
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
     * Add the ctrl field information.
     *
     * @param array  $neededItems
     * @param string $tableName
     * @param array  $record
     */
    protected function addCtrlFieldInformation(array &$neededItems, $tableName, array $record)
    {
        $ctrl = $GLOBALS['TCA'][$tableName]['ctrl'] ?? [];
        if (!$ctrl) {
            return;
        }

        $addFields = [];
        if (isset($ctrl['tstamp'])) {
            $addFields['tstamp'] = (int)$record[$ctrl['tstamp']];
        }
        if (isset($ctrl['crdate'])) {
            $addFields['crdate'] = (int)$record[$ctrl['crdate']];
        }

        foreach ($neededItems as $key => $value) {
            $neededItems[$key] = array_merge($value, $addFields);
        }
    }

    /**
     * Add slug to each index.
     *
     * @param array  $neededItems
     * @param string $uniqueRegisterKey
     * @param array  $record
     */
    protected function addSlugInformation(array &$neededItems, string $uniqueRegisterKey, array $record): void
    {
        $slugs = $this->slugService->generateSlugForItems($uniqueRegisterKey, $record, $neededItems);
        foreach ($neededItems as $key => $value) {
            $neededItems[$key] = array_merge($value, $slugs[$key] ?? []);
        }
    }

    /**
     * Prepare the record for the database insert.
     *
     * @param $record
     */
    protected function prepareRecordForDatabase(&$record)
    {
        foreach ($record as $key => $value) {
            if ($value instanceof \DateTimeInterface) {
                $record[$key] = $value->format('Y-m-d');
            } elseif (\is_bool($value) || 'start_time' === $key || 'end_time' === $key) {
                $record[$key] = (int)$value;
            } elseif (null === $value) {
                $record[$key] = '';
            }
        }
    }
}
