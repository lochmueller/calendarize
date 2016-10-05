<?php
/**
 * RealUrl
 */

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
 * RealUrl
 */
class RealUrl extends AbstractUrl
{

    /**
     * Convert the given information
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
     * Build the realurl alias
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
        return $this->id2alias($params['value']);
    }

    /**
     * Handle the alias to index ID convert
     *
     * @param $value
     *
     * @return null
     */
    protected function alias2id($value)
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
            return (int)$row['value_id'];
        }

        $matches = [];
        if (preg_match('/^idx-([0-9]+)$/', $value, $matches)) {
            return $matches[1];
        }
        return null;
    }

    /**
     * Handle the index ID to alias convert
     *
     * @param $value
     *
     * @return string
     */
    protected function id2alias($value)
    {
        $databaseConnection = HelperUtility::getDatabaseConnection();
        $row = $databaseConnection->exec_SELECTgetSingleRow(
            'value_alias',
            'tx_realurl_uniqalias',
            'tablename=' . $databaseConnection->fullQuoteStr(
                IndexerService::TABLE_NAME,
                IndexerService::TABLE_NAME
            ) . ' AND value_id=' . (int)$value
        );
        if (isset($row['value_alias'])) {
            return $row['value_alias'];
        }

        $alias = $this->getIndexBase((int)$value);
        $alias = $this->cleanUrl($alias);

        $databaseConnection = HelperUtility::getDatabaseConnection();
        $entry = [
            'tablename'   => IndexerService::TABLE_NAME,
            'field_alias' => 'title',
            'field_id'    => 'uid',
            'value_alias' => $alias,
            'value_id'    => $value,
        ];
        if ($this->isOldRealUrlVersion()) {
            $entry['tstamp'] = time();
        }
        $databaseConnection->exec_INSERTquery('tx_realurl_uniqalias', $entry);

        return $alias;
    }

    /**
     * Generate the realurl part
     *
     * @param string $alias
     *
     * @return string
     */
    protected function cleanUrl($alias)
    {

        if ($this->isOldRealUrlVersion()) {
            /** @var \tx_realurl_advanced $realUrl */
            $realUrl = GeneralUtility::makeInstance('tx_realurl_advanced');
            $configuration = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['realurl']['_DEFAULT']['pagePath'];
            if (is_array($configuration)) {
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

        return $processedTitle;
    }

    /**
     * Check if this is a old version of realurl < 2.0.0
     *
     * @return bool
     */
    protected function isOldRealUrlVersion()
    {
        $extVersion = ExtensionManagementUtility::getExtensionVersion('realurl');
        return VersionNumberUtility::convertVersionNumberToInteger($extVersion) < 2000000;
    }
}
