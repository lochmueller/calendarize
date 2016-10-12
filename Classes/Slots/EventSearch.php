<?php
/**
 * Event search service
 *
 * @author  Tim Lochmüller
 */

namespace HDNET\Calendarize\Slots;

use HDNET\Calendarize\Domain\Repository\EventRepository;
use HDNET\Calendarize\Utility\HelperUtility;
use TYPO3\CMS\Core\Utility\MathUtility;

/**
 * Event search service
 *
 * @author Tim Lochmüller
 */
class EventSearch
{

    /**
     * Table name
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
     * Check if we can reduce the amount of results
     *
     * @signalClass \HDNET\Calendarize\Domain\Repository\IndexRepository
     * @signalName findBySearchPre
     *
     * @param array          $indexIds
     * @param \DateTime|NULL $startDate
     * @param \DateTime|NULL $endDate
     * @param array          $customSearch
     *
     * @return array|void
     */
    public function setIdsByCustomSearch(
        array $indexIds,
        \DateTime $startDate = null,
        \DateTime $endDate = null,
        array $customSearch = []
    ) {

        // Filter here for $customSearch['categories'] and take also care of the fullText
        // ?tx_calendarize_calendar[customSearch][categories]=1
        // https://github.com/lochmueller/calendarize/issues/89

        if (!isset($customSearch['fullText'])) {
            return null;
        }

        /** @var EventRepository $eventRepository */
        $eventRepository = HelperUtility::create(EventRepository::class);
        return [
            'indexIds'     => $eventRepository->getIdsBySearchTerm($customSearch['fullText']),
            'startDate'    => $startDate,
            'endDate'      => $endDate,
            'customSearch' => $customSearch,
        ];
    }

    /**
     * Set ids by general
     *
     * @signalClass \HDNET\Calendarize\Domain\Repository\IndexRepository
     * @signalName getDefaultConstraints
     *
     * @param array $indexIds
     * @param array $indexTypes
     * @param array $contentRecord
     *
     * @return array
     */
    public function setIdsByGeneral(array $indexIds, array $indexTypes, array $contentRecord)
    {
        $databaseConnection = HelperUtility::getDatabaseConnection();
        $categoryIds = [];
        if (isset($contentRecord['uid']) && MathUtility::canBeInterpretedAsInteger($contentRecord['uid'])) {
            $rows = $databaseConnection->exec_SELECTgetRows(
                'uid_local',
                'sys_category_record_mm',
                'tablenames="tt_content" AND uid_foreign=' . $contentRecord['uid']
            );
            foreach ($rows as $row) {
                $categoryIds[] = (int)$row['uid_local'];
            }
        }

        if (empty($categoryIds)) {
            return [
                'indexIds'      => $indexIds,
                'indexTypes'    => $indexTypes,
                'contentRecord' => $contentRecord,
            ];
        }

        $rows = $databaseConnection->exec_SELECTgetRows(
            'uid_foreign',
            'sys_category_record_mm',
            'tablenames="' . $this->tableName . '" AND uid_local IN (' . implode(',', $categoryIds) . ')'
        );
        foreach ($rows as $row) {
            $indexIds[] = (int)$row['uid_foreign'];
        }

        return [
            'indexIds'      => $indexIds,
            'indexTypes'    => $indexTypes,
            'contentRecord' => $contentRecord,
        ];
    }
}
