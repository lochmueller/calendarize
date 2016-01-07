<?php
/**
 * Index the given events
 *
 * @author  Tim Lochmüller
 */

namespace HDNET\Calendarize\Service;

use HDNET\Calendarize\Register;
use HDNET\Calendarize\Utility\DateTimeUtility;
use HDNET\Calendarize\Utility\HelperUtility;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Index the given events
 *
 * @author Tim Lochmüller
 */
class IndexerService extends AbstractService
{

    /**
     * Index table name
     */
    const TABLE_NAME = 'tx_calendarize_domain_model_index';

    /**
     * Reindex all elements
     *
     * @return void
     */
    public function reindexAll()
    {
        $this->removeInvalidConfigurationIndex();
        $databaseConnection = HelperUtility::getDatabaseConnection();

        foreach (Register::getRegister() as $key => $configuration) {
            $tableName = $configuration['tableName'];
            $this->removeInvalidRecordIndex($tableName);
            $rows = $databaseConnection->exec_SELECTgetRows('uid', $tableName, '1=1' . BackendUtility::deleteClause($tableName));
            foreach ($rows as $row) {
                $this->updateIndex($key, $configuration['tableName'], $row['uid']);
            }
        }
    }

    /**
     * Reindex the given element
     *
     * @param string $configurationKey
     * @param string $tableName
     * @param int    $uid
     *
     * @return void
     */
    public function reindex($configurationKey, $tableName, $uid)
    {
        $this->removeInvalidConfigurationIndex();
        $this->removeInvalidRecordIndex($tableName);
        $this->updateIndex($configurationKey, $tableName, $uid);
    }

    /**
     * Get index count
     *
     * @param $table
     * @param $uid
     *
     * @return mixed
     */
    public function getIndexCount($table, $uid)
    {
        $databaseConnection = HelperUtility::getDatabaseConnection();
        return $databaseConnection->exec_SELECTcountRows('*', self::TABLE_NAME,
            'foreign_table=' . $databaseConnection->fullQuoteStr($table, self::TABLE_NAME) . ' AND foreign_uid=' . (int)$uid);
    }

    /**
     * Get the next events
     *
     * @param string $table
     * @param int    $uid
     * @param int    $limit
     *
     * @return array|NULL
     */
    public function getNextEvents($table, $uid, $limit = 5)
    {
        $databaseConnection = HelperUtility::getDatabaseConnection();
        $now = DateTimeUtility::getNow();
        $now->setTime(0, 0, 0);
        return $databaseConnection->exec_SELECTgetRows('*', self::TABLE_NAME,
            'start_date >= ' . $now->getTimestamp() . ' AND foreign_table=' . $databaseConnection->fullQuoteStr($table,
                self::TABLE_NAME) . ' AND foreign_uid=' . (int)$uid, '', 'start_date ASC, start_time ASC', $limit);
    }

    /**
     * Build the index for one element
     *
     * @param string $configurationKey
     * @param string $tableName
     * @param int    $uid
     *
     * @return void
     */
    protected function updateIndex($configurationKey, $tableName, $uid)
    {
        /** IndexPreparationService */
        static $preparationService = null;
        if ($preparationService === null) {
            $preparationService = GeneralUtility::makeInstance('HDNET\\Calendarize\\Service\\IndexPreparationService');
        }
        $neededItems = $preparationService->prepareIndex($configurationKey, $tableName, $uid);
        $this->insertAndUpdateNeededItems($neededItems, $tableName, $uid);
    }

    /**
     * Insert and/or update the needed index records
     *
     * @param array  $neededItems
     * @param string $tableName
     * @param int    $uid
     */
    protected function insertAndUpdateNeededItems(array $neededItems, $tableName, $uid)
    {
        $databaseConnection = HelperUtility::getDatabaseConnection();
        $checkProperties = [
            'pid',
            'start_date',
            'end_date',
            'start_time',
            'end_time',
            'all_day'
        ];
        $currentItems = $databaseConnection->exec_SELECTgetRows('uid,' . implode(',', $checkProperties), self::TABLE_NAME,
            'foreign_table=' . $databaseConnection->fullQuoteStr($tableName,
                IndexerService::TABLE_NAME) . ' AND foreign_uid=' . $uid);
        foreach ($neededItems as $neededKey => $neededItem) {
            $remove = false;
            foreach ($currentItems as $currentKey => $currentItem) {
                if ($this->isEqualArray($neededItem, $currentItem, $checkProperties)) {
                    $remove = true;
                    unset($neededItems[$neededKey]);
                    unset($currentItems[$currentKey]);
                    break;
                }
            }
            if ($remove) {
                continue;
            }
        }
        foreach ($currentItems as $item) {
            $databaseConnection->exec_DELETEquery(self::TABLE_NAME, 'uid=' . $item['uid']);
        }

        $neededItems = array_values($neededItems);
        if ($neededItems) {
            $databaseConnection->exec_INSERTmultipleRows(self::TABLE_NAME, array_keys($neededItems[0]), $neededItems);
        }
    }

    /**
     * Check if the properties of the given arrays are equals
     *
     * @param array $array1
     * @param array $array2
     * @param array $checkProperties
     *
     * @return bool
     */
    protected function isEqualArray(array $array1, array $array2, array $checkProperties)
    {
        foreach ($checkProperties as $check) {
            if ((int)$array1[$check] !== (int)$array2[$check]) {
                return false;
            }
        }
        return true;
    }

    /**
     * Remove Index items of the given table of records
     * that are deleted or do not exists anymore.
     *
     * @param string $tableName
     */
    protected function removeInvalidRecordIndex($tableName)
    {
        $databaseConnection = HelperUtility::getDatabaseConnection();
        $rows = $databaseConnection->exec_SELECTgetRows('uid', $tableName, '1=1' . BackendUtility::deleteClause($tableName));
        $ids = [];
        foreach ($rows as $row) {
            $ids[] = $row['uid'];
        }
        $where = 'foreign_table=' . $databaseConnection->fullQuoteStr($tableName, IndexerService::TABLE_NAME);
        if ($ids) {
            $where .= ' AND foreign_uid NOT IN (' . implode(',', $ids) . ')';
        }
        $databaseConnection->exec_DELETEquery(self::TABLE_NAME, $where);
    }

    /**
     * Remove index Items of configurations that are not valid anymore
     *
     * @return bool
     */
    protected function removeInvalidConfigurationIndex()
    {
        $validKeys = array_keys(Register::getRegister());
        $databaseConnection = HelperUtility::getDatabaseConnection();
        if ($validKeys) {
            foreach ($validKeys as $key => $value) {
                $validKeys[$key] = $databaseConnection->fullQuoteStr($value, IndexerService::TABLE_NAME);
            }
            return (bool)$databaseConnection->exec_DELETEquery(self::TABLE_NAME,
                'unique_register_key NOT IN (' . implode(',', $validKeys) . ')');
        }
        return (bool)$databaseConnection->exec_TRUNCATEquery(self::TABLE_NAME);
    }

}