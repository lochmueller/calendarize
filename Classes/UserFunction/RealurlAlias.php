<?php
/**
 * RealURL alias
 *
 * @author  Tim Lochmüller
 */

namespace HDNET\Calendarize\UserFunction;

use DmitryDulepov\Realurl\Configuration\ConfigurationReader;
use HDNET\Calendarize\Domain\Model\Index;
use HDNET\Calendarize\Domain\Repository\IndexRepository;
use HDNET\Calendarize\Features\RealUrlInterface;
use HDNET\Calendarize\Service\IndexerService;
use HDNET\Calendarize\Utility\HelperUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

/**
 * RealURL alias
 *
 * @author Tim Lochmüller
 */
class RealurlAlias
{

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
        $row = $databaseConnection->exec_SELECTgetSingleRow('value_id', 'tx_realurl_uniqalias',
            'tablename=' . $databaseConnection->fullQuoteStr(IndexerService::TABLE_NAME,
                IndexerService::TABLE_NAME) . ' AND value_alias=' . $databaseConnection->fullQuoteStr($value,
                IndexerService::TABLE_NAME));
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
        $row = $databaseConnection->exec_SELECTgetSingleRow('value_alias', 'tx_realurl_uniqalias',
            'tablename=' . $databaseConnection->fullQuoteStr(IndexerService::TABLE_NAME,
                IndexerService::TABLE_NAME) . ' AND value_id=' . (int)$value);
        if (isset($row['value_alias'])) {
            return $row['value_alias'];
        }

        /** @var IndexRepository $indexRepository */
        $indexRepository = HelperUtility::create('HDNET\\Calendarize\\Domain\\Repository\\IndexRepository');
        $index = $indexRepository->findByUid($value);
        if (!($index instanceof Index)) {
            $alias = 'idx-' . $value;
        } else {
            $originalObject = $index->getOriginalObject();
            if (!($originalObject instanceof RealUrlInterface)) {
                $alias = 'idx-' . $value;
            } else {
                $alias = $this->generateRealUrl($originalObject->getRealUrlAliasBase(), $index);
            }
        }

        $databaseConnection = HelperUtility::getDatabaseConnection();
        $entry = [
            'tstamp'      => time(),
            'tablename'   => IndexerService::TABLE_NAME,
            'field_alias' => 'title',
            'field_id'    => 'uid',
            'value_alias' => $alias,
            'value_id'    => $value,
        ];
        $databaseConnection->exec_INSERTquery('tx_realurl_uniqalias', $entry);

        return $alias;
    }

    /**
     * Generate the realurl part
     *
     * @param string $base
     * @param Index  $index
     *
     * @return string
     */
    protected function generateRealUrl($base, Index $index)
    {
        $datePart = $index->isAllDay() ? 'Y-m-d' : 'Y-m-d-h-i';
        $title = $base . '-' . $index->getStartDateComplete()
                ->format($datePart);

        $realUrlVersion = VersionNumberUtility::convertVersionNumberToInteger(ExtensionManagementUtility::getExtensionVersion('realurl'));
        if ($realUrlVersion >= 2000000) {
            $configuration = GeneralUtility::makeInstance('DmitryDulepov\\Realurl\\Configuration\\ConfigurationReader', ConfigurationReader::MODE_ENCODE);
            // Init the internal utility by ObjectAccess because the property is
            // set by a protected method only. :( Perhaps this could be part of the construct (in realurl)
            $utility = GeneralUtility::makeInstance('DmitryDulepov\\Realurl\\Utility', $configuration);
            $processedTitle = $utility->convertToSafeString($title);
        } else {
            /** @var \tx_realurl_advanced $realUrl */
            $realUrl = GeneralUtility::makeInstance('tx_realurl_advanced');
            $configuration = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['realurl']['_DEFAULT']['pagePath'];
            if (is_array($configuration)) {
                ObjectAccess::setProperty($realUrl, 'conf', $configuration, true);
            }
            $processedTitle = $realUrl->encodeTitle($title);
        }

        return $processedTitle;
    }
}
