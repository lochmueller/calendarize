<?php

/**
 * RealUrl.
 */
declare(strict_types=1);

namespace HDNET\Calendarize\Service\Url;

use DmitryDulepov\Realurl\Configuration\ConfigurationReader;
use DmitryDulepov\Realurl\Utility;
use HDNET\Calendarize\Service\IndexerService;
use HDNET\Calendarize\Utility\HelperUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

/**
 * RealUrl.
 */
class RealUrl extends AbstractUrl
{
    /**
     * Convert the given information.
     *
     * @param $param1
     * @param $param2
     *
     * @return string
     */
    public function convert($param1, $param2)
    {
        return $this->main($param1, $param2);
    }

    /**
     * Build the realurl alias.
     *
     * @param $params
     * @param $ref
     *
     * @return string
     */
    public function main($params, $ref)
    {
        if ($params['decodeAlias']) {
            return $this->alias2id($params['value']);
        }
        $this->cleanupOldLinks();

        return $this->id2alias($params['value']);
    }

    /**
     * Cleanup old URL segments.
     */
    protected function cleanupOldLinks()
    {
        $removeIds = [];
        $databaseConnection = HelperUtility::getDatabaseConnection();
        $selectInvalidItems = 'SELECT tx_realurl_uniqalias.uid 
FROM tx_realurl_uniqalias LEFT JOIN tx_calendarize_domain_model_index ON tx_realurl_uniqalias.value_id = tx_calendarize_domain_model_index.uid 
WHERE tx_calendarize_domain_model_index.uid IS NULL AND tx_realurl_uniqalias.tablename=\'tx_calendarize_domain_model_index\'';
        $res = $databaseConnection->admin_query($selectInvalidItems);
        while ($row = $databaseConnection->sql_fetch_assoc($res)) {
            $removeIds[] = (int) $row['uid'];
        }
        if (empty($removeIds)) {
            return;
        }
        $databaseConnection->exec_DELETEquery('tx_realurl_uniqalias', 'uid IN (' . \implode(',', $removeIds) . ')');
    }

    /**
     * Handle the alias to index ID convert.
     *
     * @param $value
     *
     * @return int
     */
    protected function alias2id($value):int
    {
        $databaseConnection = HelperUtility::getDatabaseConnection();
        $row = $databaseConnection->exec_SELECTgetSingleRow(
            'value_id',
            'tx_realurl_uniqalias',
            'tablename=' . $databaseConnection->fullQuoteStr(
                IndexerService::TABLE_NAME,
                IndexerService::TABLE_NAME
            ) . ' AND value_alias=' . $databaseConnection->fullQuoteStr(
                $value,
                IndexerService::TABLE_NAME
            )
        );
        if (isset($row['value_id'])) {
            return (int) $row['value_id'];
        }

        $matches = [];
        if (\preg_match('/^idx-([0-9]+)$/', $value, $matches)) {
            return (int)$matches[1];
        }
        return 0;
    }

    /**
     * Handle the index ID to alias convert.
     *
     * @param $value
     *
     * @return string
     */
    protected function id2alias($value):string
    {
        $databaseConnection = HelperUtility::getDatabaseConnection();
        $row = $databaseConnection->exec_SELECTgetSingleRow(
            'value_alias',
            'tx_realurl_uniqalias',
            'tablename=' . $databaseConnection->fullQuoteStr(
                IndexerService::TABLE_NAME,
                IndexerService::TABLE_NAME
            ) . ' AND value_id=' . (int) $value
        );
        if (isset($row['value_alias'])) {
            return (string)$row['value_alias'];
        }

        $alias = $this->getIndexBase((int) $value);
        $alias = $this->cleanUrl($alias);

        $databaseConnection = HelperUtility::getDatabaseConnection();
        $entry = [
            'tablename' => IndexerService::TABLE_NAME,
            'field_alias' => 'title',
            'field_id' => 'uid',
            'value_alias' => $alias,
            'value_id' => $value,
        ];
        if ($this->isOldRealUrlVersion()) {
            $entry['tstamp'] = (new \DateTime())->getTimestamp();
        }

        $aliasBase = $alias;
        for ($i = 0; ; $i++) {
            $alias = $i > 0 ? $aliasBase . '-' . $i : $aliasBase;
            if (!$this->aliasAlreadyExists($alias)) {
                $entry['value_alias'] = $alias;
                break;
            }
        }
        $databaseConnection->exec_INSERTquery('tx_realurl_uniqalias', $entry);

        return (string)$alias;
    }

    /**
     * Check if alias already exists
     *
     * @param string $alias
     * @return bool
     */
    protected function aliasAlreadyExists($alias)
    {
        $databaseConnection = HelperUtility::getDatabaseConnection();
        $count = $databaseConnection->exec_SELECTcountRows('*', 'tx_realurl_uniqalias',
            'value_alias=' . $databaseConnection->fullQuoteStr($alias, 'tx_realurl_uniqalias'));
        return (bool)$count;
    }

    /**
     * Generate the realurl part.
     *
     * @param string $alias
     *
     * @return string
     */
    protected function cleanUrl(string $alias):string
    {
        if ($this->isOldRealUrlVersion()) {
            /** @var \tx_realurl_advanced $realUrl */
            $realUrl = GeneralUtility::makeInstance('tx_realurl_advanced');
            $configuration = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['realurl']['_DEFAULT']['pagePath'];
            if (\is_array($configuration)) {
                ObjectAccess::setProperty($realUrl, 'conf', $configuration, true);
            }
            $processedTitle = $realUrl->encodeTitle($alias);
        } else {
            $configuration = GeneralUtility::makeInstance(ConfigurationReader::class, ConfigurationReader::MODE_ENCODE);
            // Init the internal utility by ObjectAccess because the property is
            // set by a protected method only. :( Perhaps this could be part of the construct (in realurl)
            $utility = GeneralUtility::makeInstance(Utility::class, $configuration);
            $processedTitle = $utility->convertToSafeString($alias);
        }

        return (string)$processedTitle;
    }

    /**
     * Check if this is a old version of realurl < 2.0.0.
     *
     * @return bool
     */
    protected function isOldRealUrlVersion()
    {
        $extVersion = ExtensionManagementUtility::getExtensionVersion('realurl');

        return VersionNumberUtility::convertVersionNumberToInteger($extVersion) < 2000000;
    }
}
