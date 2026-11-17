<?php

declare(strict_types=1);

namespace HDNET\Calendarize\Service;

use HDNET\Calendarize\Register;
use HDNET\Calendarize\Utility\HelperUtility;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Resolve the records (e.g. events), that are affected by a change of a
 * configuration or a configuration group.
 *
 * A configuration group is used by one or more configurations (type "group"),
 * which are used by one or more "calendarized" records. If the group (or one of
 * its configurations) is changed, all these records have to be reindexed.
 */
class ConfigurationRelationService extends AbstractService
{
    public const CONFIGURATION_TABLE = 'tx_calendarize_domain_model_configuration';

    public const GROUP_TABLE = 'tx_calendarize_domain_model_configurationgroup';

    /**
     * Find all registered records, that are affected by the given configurations and/or configuration groups.
     *
     * @param array $configurationUids UIDs of tx_calendarize_domain_model_configuration
     * @param array $groupUids         UIDs of tx_calendarize_domain_model_configurationgroup
     *
     * @return array<int, array{uniqueRegisterKey: string, tableName: string, uid: int}>
     */
    public function findAffectedRecords(array $configurationUids = [], array $groupUids = []): array
    {
        $records = [];
        $processedConfigurations = [];
        $processedGroups = [];

        $this->collectByGroups($groupUids, $records, $processedConfigurations, $processedGroups);
        $this->collectByConfigurations($configurationUids, $records, $processedConfigurations, $processedGroups);

        return array_values($records);
    }

    /**
     * Collect all records that use one of the given configuration groups.
     */
    protected function collectByGroups(
        array $groupUids,
        array &$records,
        array &$processedConfigurations,
        array &$processedGroups,
    ): void {
        $groupUids = array_diff($this->cleanUpUids($groupUids), $processedGroups);
        if (!$groupUids) {
            return;
        }

        $processedGroups = array_merge($processedGroups, $groupUids);

        // Groups are assigned to configurations of the type "group" via the field "groups"
        $configurationUids = $this->findRecordUidsByRelation(self::CONFIGURATION_TABLE, 'groups', $groupUids);

        $this->collectByConfigurations($configurationUids, $records, $processedConfigurations, $processedGroups);
    }

    /**
     * Collect all records that use one of the given configurations.
     */
    protected function collectByConfigurations(
        array $configurationUids,
        array &$records,
        array &$processedConfigurations,
        array &$processedGroups,
    ): void {
        $configurationUids = array_diff($this->cleanUpUids($configurationUids), $processedConfigurations);
        if (!$configurationUids) {
            return;
        }

        $processedConfigurations = array_merge($processedConfigurations, $configurationUids);

        foreach (Register::getRegister() as $uniqueRegisterKey => $registerConfiguration) {
            $tableName = $registerConfiguration['tableName'] ?? '';
            if (!$tableName) {
                continue;
            }
            $fieldName = $registerConfiguration['fieldName'] ?? 'calendarize';

            $uids = $this->findRecordUidsByRelation($tableName, $fieldName, $configurationUids);
            if (!$uids) {
                continue;
            }

            $uids = array_merge($uids, $this->findTranslations($tableName, $uids));

            foreach ($uids as $uid) {
                $records[$tableName . ':' . $uid] = [
                    'uniqueRegisterKey' => (string)$uniqueRegisterKey,
                    'tableName' => $tableName,
                    'uid' => $uid,
                ];
            }

            // A group is a "calendarized" record itself, so the usages of the group have to be handled too
            if (self::GROUP_TABLE === $tableName) {
                $this->collectByGroups($uids, $records, $processedConfigurations, $processedGroups);
            }
        }
    }

    /**
     * Find all records of the given table, that reference one of the given UIDs in the given (comma separated) field.
     *
     * @return int[]
     */
    protected function findRecordUidsByRelation(string $tableName, string $fieldName, array $uids): array
    {
        if (!$uids || !isset($GLOBALS['TCA'][$tableName]['columns'][$fieldName])) {
            return [];
        }

        $queryBuilder = HelperUtility::getQueryBuilder($tableName);
        $queryBuilder
            ->getRestrictions()
            ->removeAll()
            ->add(GeneralUtility::makeInstance(DeletedRestriction::class));

        $constraints = [];
        foreach ($uids as $uid) {
            $constraints[] = $queryBuilder->expr()->inSet($fieldName, $queryBuilder->quote((string)$uid));
        }

        $rows = $queryBuilder
            ->select('uid')
            ->from($tableName)
            ->where($queryBuilder->expr()->or(...$constraints))
            ->executeQuery()
            ->fetchAllAssociative();

        return array_map('intval', array_column($rows, 'uid'));
    }

    /**
     * Find the translations of the given records. Translations use the configurations of
     * the default language record, so they have to be reindexed as well.
     *
     * @return int[]
     */
    protected function findTranslations(string $tableName, array $uids): array
    {
        $transPointer = $GLOBALS['TCA'][$tableName]['ctrl']['transOrigPointerField'] ?? '';
        if (!$transPointer || !$uids) {
            return [];
        }

        $queryBuilder = HelperUtility::getQueryBuilder($tableName);
        $queryBuilder
            ->getRestrictions()
            ->removeAll()
            ->add(GeneralUtility::makeInstance(DeletedRestriction::class));

        $rows = $queryBuilder
            ->select('uid')
            ->from($tableName)
            ->where(
                $queryBuilder->expr()->in(
                    $transPointer,
                    $queryBuilder->createNamedParameter($uids, Connection::PARAM_INT_ARRAY),
                ),
            )
            ->executeQuery()
            ->fetchAllAssociative();

        return array_map('intval', array_column($rows, 'uid'));
    }

    /**
     * @return int[]
     */
    protected function cleanUpUids(array $uids): array
    {
        return array_values(array_unique(array_filter(array_map('intval', $uids))));
    }
}
