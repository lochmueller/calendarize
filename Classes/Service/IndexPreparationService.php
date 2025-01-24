<?php

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
    public function __construct(protected SlugService $slugService) {}

    /**
     * Build the index for one element.
     */
    public function prepareIndex(string $configurationKey, string $tableName, int $uid): array
    {
        $rawRecord = BackendUtility::getRecord($tableName, $uid);
        if (!$rawRecord) {
            return [];
        }

        $register = Register::getRegister();
        $fieldName = $register[$configurationKey]['fieldName'] ?? 'calendarize';
        $configurations = GeneralUtility::intExplode(',', (string)($rawRecord[$fieldName] ?? ''), true);

        $transPointer = $GLOBALS['TCA'][$tableName]['ctrl']['transOrigPointerField'] ?? false; // e.g. l10n_parent
        if ($transPointer && (int)$rawRecord[$transPointer] > 0) {
            $rawOriginalRecord = BackendUtility::getRecord($tableName, (int)$rawRecord[$transPointer]);
            $configurations = GeneralUtility::intExplode(',', $rawOriginalRecord[$fieldName] ?? '', true);
        }

        $neededItems = [];
        if ($configurations) {
            $timeTableService = GeneralUtility::makeInstance(TimeTableService::class);
            $neededItems = $timeTableService->getTimeTablesByConfigurationIds(
                $configurations,
                (int)$rawRecord['t3ver_wsid'],
            );
            foreach ($neededItems as $key => $record) {
                $record['foreign_table'] = $tableName;
                $record['foreign_uid'] = $uid;
                $record['unique_register_key'] = $configurationKey;

                $this->prepareRecordForDatabase($record);
                $neededItems[$key] = $record;
            }
        }

        // Workspace information must be added before language information so we can set the correct l10n_parent
        $this->addWorkspaceInformation($neededItems, $configurationKey, $rawRecord);
        // Language information must be added before ctrl/enable information
        $this->addLanguageInformation($neededItems, $tableName, $rawRecord);
        $this->addEnableFieldInformation($neededItems, $tableName, $rawRecord);
        $this->addCtrlFieldInformation($neededItems, $tableName, $rawRecord);
        $this->addSlugInformation($neededItems, $configurationKey, $rawRecord);

        return $neededItems;
    }

    protected function addWorkspaceInformation(array &$neededItems, string $configurationKey, array $record): void
    {
        $workspace = isset($record['t3ver_wsid']) ? (int)$record['t3ver_wsid'] : 0;
        $origId = isset($record['t3ver_oid']) ? (int)$record['t3ver_oid'] : 0;
        $versionState = isset($record['t3ver_state']) ? (int)$record['t3ver_state'] : 0;
        $versionStage = isset($record['t3ver_stage']) ? (int)$record['t3ver_stage'] : 0;
        $neededItems = array_map(static function ($item) use ($workspace, $origId, $record, $versionState, $versionStage) {
            $item['t3ver_wsid'] = $workspace;
            $item['t3ver_state'] = $versionState;
            $item['t3ver_stage'] = $versionStage;
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
                $searchFor = $value;
                $searchFor['foreign_uid'] = (int)$record[$transPointer];

                $queryBuilder = HelperUtility::getQueryBuilder(IndexerService::TABLE_NAME);
                $where = [];
                foreach ($searchFor as $field => $val) {
                    if (\is_string($val)) {
                        $where[] = $queryBuilder->expr()->eq($field, $queryBuilder->quote($val));
                    } else {
                        $where[] = $queryBuilder->expr()->eq($field, (int)$val);
                    }
                }

                $result = $queryBuilder
                    ->select('uid')
                    ->from(IndexerService::TABLE_NAME)
                    ->andWhere(...$where)
                    ->executeQuery();

                if ($result->rowCount() > 1) {
                    throw new \RuntimeException('Multiple records found for original language record of index ' . $record['uid']);
                }

                $originalLanguageRecord = $result->fetchAssociative();
                if (isset($originalLanguageRecord['uid'])) {
                    $neededItems[$key]['l10n_parent'] = (int)$originalLanguageRecord['uid'];
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
     */
    protected function addEnableFieldInformation(array &$neededItems, string $tableName, array $record): void
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
     */
    protected function addCtrlFieldInformation(array &$neededItems, string $tableName, array $record): void
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
     */
    protected function prepareRecordForDatabase(array &$record): void
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
