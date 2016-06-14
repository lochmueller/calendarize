<?php

/**
 * CalMigrationUpdate
 */

namespace HDNET\Calendarize\Updates;

use HDNET\Calendarize\Utility\HelperUtility;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Install\Updates\AbstractUpdate;

/**
 * CalMigrationUpdate
 */
class CalMigrationUpdate extends AbstractUpdate
{

    /**
     * The human-readable title of the upgrade wizard
     *
     * @var string
     */
    protected $title = 'Migrate cal event structures to the new calendarize event structures';

    /**
     * Checks whether updates are required.
     *
     * @param string &$description The description for the update
     * @return bool Whether an update is required (TRUE) or not (FALSE)
     */
    public function checkForUpdate(&$description)
    {
        $nonMigratedCalIds = $this->getNonMigratedCalIds();
        $count = count($nonMigratedCalIds);
        if ($count === 0) {
            return false;
        }
        $description = "There " . ($count > 1 ? 'are ' . $count : 'is ' . $count) . " not migrated EXT:cal event" . ($count > 1 ? 's' : '') . ". Run the update process to migrate the events to EXT:calendarize events.";
        return true;
    }

    /**
     * Performs the accordant updates.
     *
     * @param array &$dbQueries Queries done in this update
     * @param mixed &$customMessages Custom messages
     * @return bool Whether everything went smoothly or not
     */
    public function performUpdate(array &$dbQueries, &$customMessages)
    {
        //$calIds = $this->getNonMigratedCalIds();

        $customMessages = 'The Update migration is not implemented yet!';

        return false;
    }

    /**
     * Get the non migrated cal IDs
     *
     * @return array
     */
    protected function getNonMigratedCalIds()
    {
        if (!ExtensionManagementUtility::isLoaded('cal')) {
            return [];
        }

        $database = HelperUtility::getDatabaseConnection();
        $checkImportIds = [];
        $nonMigrated = [];
        $events = $database->exec_SELECTgetRows(
            'uid',
            'tx_cal_event',
            '1=1' . BackendUtility::deleteClause('tx_cal_event')
        );
        foreach ($events as $event) {
            $checkImportIds[] = '"calMigration:' . $event['uid'] . '"';
            $nonMigrated[(int)$event['uid']] = (int)$event['uid'];
        }

        $countOriginal = count($checkImportIds);
        if ($countOriginal === 0) {
            return [];
        }

        $migratedRows = $database->exec_SELECTgetRows(
            'uid,import_id',
            'tx_calendarize_domain_model_event',
            'import_id IN (' . implode(
                ',',
                $checkImportIds
            ) . ')' . BackendUtility::deleteClause('tx_calendarize_domain_model_event')
        );

        foreach ($migratedRows as $migratedRow) {
            $importId = (int)str_replace('calMigration:', '', $migratedRow['import_id']);
            if (isset($nonMigrated[$importId])) {
                unset($nonMigrated[$importId]);
            }
        }
        return $nonMigrated;
    }
}
