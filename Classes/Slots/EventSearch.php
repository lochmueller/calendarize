<?php

/**
 * Event search service.
 */
declare(strict_types=1);

namespace HDNET\Calendarize\Slots;

use HDNET\Autoloader\Annotation\SignalClass;
use HDNET\Autoloader\Annotation\SignalName;
use HDNET\Calendarize\Domain\Model\PluginConfiguration;
use HDNET\Calendarize\Domain\Repository\EventRepository;
use HDNET\Calendarize\Register;
use HDNET\Calendarize\Utility\HelperUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * Event search service.
 */
class EventSearch
{
    /**
     * Check if we can reduce the amount of results.
     *
     * @SignalClass \HDNET\Calendarize\Domain\Repository\IndexRepository
     * @SignalName findBySearchPre
     *
     * @param array          $indexIds
     * @param \DateTime|null $startDate
     * @param \DateTime|null $endDate
     * @param array          $customSearch
     * @param array          $indexTypes
     * @param bool           $emptyPreResult
     * @param array          $additionalSlotArguments
     *
     * @return array|void
     */
    public function setIdsByCustomSearch(
        array $indexIds,
        \DateTime $startDate = null,
        \DateTime $endDate = null,
        array $customSearch = [],
        array $indexTypes = [],
        bool $emptyPreResult = false,
        array $additionalSlotArguments = []
    ) {
        if (!\in_array($this->getUniqueRegisterKey(), $indexTypes, true)) {
            return;
        }

        // @todo Filter here for $customSearch['categories'] and take also care of the fullText
        // ?tx_calendarize_calendar[customSearch][categories]=1
        // https://github.com/lochmueller/calendarize/issues/89

        if (!isset($customSearch['fullText'])) {
            return;
        }

        $eventRepository = GeneralUtility::makeInstance(ObjectManager::class)->get(EventRepository::class);

        return [
            'indexIds' => $eventRepository->getIdsBySearchTerm($customSearch['fullText']),
            'startDate' => $startDate,
            'endDate' => $endDate,
            'customSearch' => $customSearch,
            'indexTypes' => $indexTypes,
            'emptyPreResult' => $emptyPreResult,
            'additionalSlotArguments' => $additionalSlotArguments,
        ];
    }

    /**
     * Set ids by general.
     *
     * @SignalClass \HDNET\Calendarize\Domain\Repository\IndexRepository
     * @SignalName getDefaultConstraints
     *
     * @param array $indexIds
     * @param array $indexTypes
     * @param array $additionalSlotArguments
     *
     * @return array|null
     */
    public function setIdsByGeneral(array $indexIds, array $indexTypes, array $additionalSlotArguments)
    {
        if (!\in_array($this->getUniqueRegisterKey(), $indexTypes, true)) {
            return;
        }

        $table = 'sys_category_record_mm';
        $db = HelperUtility::getDatabaseConnection($table);
        $q = $db->createQueryBuilder();

        $categoryIds = [];
        if (isset($additionalSlotArguments['contentRecord']['uid']) && MathUtility::canBeInterpretedAsInteger($additionalSlotArguments['contentRecord']['uid'])) {
            $rows = $q->select('uid_local')
                ->from($table)
                ->where(
                    $q->expr()->andX(
                        $q->expr()->eq('tablenames', $q->quote('tt_content')),
                        $q->expr()->eq('fieldname', $q->quote('categories')),
                        $q->expr()->eq('uid_foreign', $q->createNamedParameter($additionalSlotArguments['contentRecord']['uid']))
                    )
                )
                ->execute()
                ->fetchAll();

            foreach ($rows as $row) {
                $categoryIds[] = (int)$row['uid_local'];
            }
        }

        if (isset($additionalSlotArguments['settings']['pluginConfiguration']) && $additionalSlotArguments['settings']['pluginConfiguration'] instanceof PluginConfiguration) {
            /** @var PluginConfiguration $pluginConfiguration */
            $pluginConfiguration = $additionalSlotArguments['settings']['pluginConfiguration'];
            $categories = $pluginConfiguration->getCategories();
            foreach ($categories as $category) {
                $categoryIds[] = $category->getUid();
            }
        }

        if (empty($categoryIds)) {
            return;
        }

        $q->resetQueryParts();
        $rows = $q->select('uid_foreign')
            ->from('sys_category_record_mm')
            ->where(
                $q->expr()->in('uid_local', $categoryIds),
                $q->expr()->eq('tablenames', $q->quote($this->getTableName())),
                $q->expr()->eq('fieldname', $q->quote('categories'))
            )
            ->execute()
            ->fetchAll();

        foreach ($rows as $row) {
            $indexIds[] = (int)$row['uid_foreign'];
        }

        $indexIds[] = -1;

        return [
            'indexIds' => $indexIds,
            'indexTypes' => $indexTypes,
            'additionalSlotArguments' => $additionalSlotArguments,
        ];
    }

    /**
     * Table name.
     *
     * Note: This complete class is for the Event Model of the calendarize extension.
     * If you use a own model with special search criteria you have to register your
     * own custom Slot. If you only want the category logic for your model, you can
     * easily register a own slot that is based on this class. Thean you only have
     * to overwrite the tableName property.
     *
     * @return string
     */
    protected function getTableName()
    {
        $config = Register::getDefaultCalendarizeConfiguration();

        return $config['tableName'];
    }

    /**
     * Unique register key.
     *
     * @return string
     */
    protected function getUniqueRegisterKey()
    {
        $config = Register::getDefaultCalendarizeConfiguration();

        return $config['uniqueRegisterKey'];
    }
}
