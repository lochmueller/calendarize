<?php

/**
 * Event search service.
 */
declare(strict_types=1);

namespace HDNET\Calendarize\Slots;

use HDNET\Calendarize\Domain\Model\PluginConfiguration;
use HDNET\Calendarize\Domain\Repository\EventRepository;
use HDNET\Calendarize\Utility\HelperUtility;
use TYPO3\CMS\Core\Utility\MathUtility;

/**
 * Event search service.
 */
class EventSearch
{
    /**
     * Table name.
     *
     * Note: This complete class is for the Event Model of the calendarize extension.
     * If you use a own model with special search criteria you have to register your
     * own custom Slot. If you only want the category logic for your model, you can
     * easily register a own slot that is based on this class. Thean you only have
     * to overwrite the tableName property.
     *
     * @var string
     */
    protected $tableName = 'tx_calendarize_domain_model_event';

    /**
     * Check if we can reduce the amount of results.
     *
     * @signalClass \HDNET\Calendarize\Domain\Repository\IndexRepository
     * @signalName findBySearchPre
     *
     * @param array          $indexIds
     * @param \DateTime|null $startDate
     * @param \DateTime|null $endDate
     * @param array          $customSearch
     * @param bool           $emptyPreResult
     * @param array          $additionalSlotArguments
     * @param array          $indexTypes
     *
     * @return array|void
     */
    public function setIdsByCustomSearch(
        array $indexIds,
        \DateTime $startDate = null,
        \DateTime $endDate = null,
        array $customSearch,
        array $indexTypes,
        bool $emptyPreResult,
        array $additionalSlotArguments
    ) {
        if (!\in_array('Event', $indexTypes, true)) {
            return;
        }

        // @todo Filter here for $customSearch['categories'] and take also care of the fullText
        // ?tx_calendarize_calendar[customSearch][categories]=1
        // https://github.com/lochmueller/calendarize/issues/89

        if (!isset($customSearch['fullText'])) {
            return;
        }

        $eventRepository = HelperUtility::create(EventRepository::class);

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
     * @signalClass \HDNET\Calendarize\Domain\Repository\IndexRepository
     * @signalName getDefaultConstraints
     *
     * @param array $indexIds
     * @param array $indexTypes
     * @param array $additionalSlotArguments
     *
     * @return array|null
     */
    public function setIdsByGeneral(array $indexIds, array $indexTypes, array $additionalSlotArguments)
    {
        if (!\in_array('Event', $indexTypes, true)) {
            return;
        }
        $databaseConnection = HelperUtility::getDatabaseConnection();
        $categoryIds = [];
        if (isset($additionalSlotArguments['contentRecord']['uid']) && MathUtility::canBeInterpretedAsInteger($additionalSlotArguments['contentRecord']['uid'])) {
            $rows = $databaseConnection->exec_SELECTgetRows(
                'uid_local',
                'sys_category_record_mm',
                'tablenames="tt_content" AND uid_foreign=' . $additionalSlotArguments['contentRecord']['uid']
            );
            foreach ($rows as $row) {
                $categoryIds[] = (int) $row['uid_local'];
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

        $rows = $databaseConnection->exec_SELECTgetRows(
            'uid_foreign',
            'sys_category_record_mm',
            'tablenames="' . $this->tableName . '" AND uid_local IN (' . \implode(',', $categoryIds) . ')'
        );
        foreach ($rows as $row) {
            $indexIds[] = (int) $row['uid_foreign'];
        }

        return [
            'indexIds' => $indexIds,
            'indexTypes' => $indexTypes,
            'additionalSlotArguments' => $additionalSlotArguments,
        ];
    }
}
