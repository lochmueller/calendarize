<?php

/**
 * RealUrl.
 */
declare(strict_types=1);

namespace HDNET\Calendarize\Service\Url;

use DmitryDulepov\Realurl\Configuration\ConfigurationReader;
use DmitryDulepov\Realurl\Utility;
use HDNET\Calendarize\Domain\Model\Index;
use HDNET\Calendarize\Service\IndexerService;
use HDNET\Calendarize\Utility\HelperUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
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
        $q = HelperUtility::getDatabaseConnection(IndexerService::TABLE_NAME)->createQueryBuilder();

        $q->select('u.uid')
            ->from('tx_realurl_uniqalias', 'u')
            ->leftJoin('u', IndexerService::TABLE_NAME, 'i', 'u.value_id = i.uid')
            ->where(
                $q->expr()->andX(
                    $q->expr()->isNull('i.uid'),
                    $q->expr()->eq('u.tablename', $q->expr()->literal(IndexerService::TABLE_NAME))
                )
            );

        foreach ($q->execute()->fetchAll() as $row) {
            $removeIds[] = (int) $row['uid'];
        }

        if (empty($removeIds)) {
            return;
        }

        $q->resetQueryParts();

        $q->delete('tx_realurl_uniqalias')
            ->where(
                $q->expr()->in('uid', $removeIds)
            )
            ->execute();
    }

    /**
     * Handle the alias to index ID convert.
     *
     * @param $value
     *
     * @return int
     */
    protected function alias2id($value): int
    {
        $q = HelperUtility::getDatabaseConnection(IndexerService::TABLE_NAME)->createQueryBuilder();

        $row = $q->select('value_id')
            ->from('tx_realurl_uniqalias')
            ->where(
                $q->expr()->andX(
                    $q->expr()->eq('tablename', $q->expr()->literal(IndexerService::TABLE_NAME)),
                    $q->expr()->eq('value_alias', $q->createNamedParameter($value))
                )
            )
            ->execute()
            ->fetch();

        if (isset($row['value_id'])) {
            return (int) $row['value_id'];
        }

        $matches = [];
        if (\preg_match('/^idx-([0-9]+)$/', $value, $matches)) {
            return (int) $matches[1];
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
    protected function id2alias($value): string
    {
        $q = HelperUtility::getDatabaseConnection('tx_realurl_uniqalias')->createQueryBuilder();

        $row = $q->select('value_id')
            ->from('tx_realurl_uniqalias')
            ->where(
                $q->expr()->andX(
                    $q->expr()->eq('tablename', $q->expr()->literal(IndexerService::TABLE_NAME)),
                    $q->expr()->eq('value_id', $q->createNamedParameter((int) $value, \PDO::PARAM_INT))
                )
            )
            ->execute()
            ->fetch();

        if (isset($row['value_alias'])) {
            return (string) $row['value_alias'];
        }

        $alias = $this->getIndexBase((int) $value);
        $alias = $this->cleanUrl($alias);
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
        for ($i = 0;; ++$i) {
            $alias = $i > 0 ? $aliasBase . '-' . $i : $aliasBase;
            if (!$this->aliasAlreadyExists($alias)) {
                $entry['value_alias'] = $alias;
                break;
            }
        }

        $q->resetQueryParts();
        $q->insert('tx_realurl_uniqalias')->values($entry)->execute();

        return (string) $alias;
    }

    /**
     * Check if alias already exists.
     *
     * @param string $alias
     *
     * @return bool
     */
    protected function aliasAlreadyExists($alias)
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tx_realurl_uniqalias');

        $count = $queryBuilder
        ->count('uid')
        ->from('tx_realurl_uniqalias')
        ->where($queryBuilder->expr()->eq('value_alias', $queryBuilder->createNamedParameter($alias)))
        ->execute()
        ->fetchColumn(0);

        return (bool) $count;
    }

    /**
     * Generate the realurl part.
     *
     * @param string $alias
     *
     * @return string
     */
    protected function cleanUrl(string $alias): string
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

        return (string) $processedTitle;
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
