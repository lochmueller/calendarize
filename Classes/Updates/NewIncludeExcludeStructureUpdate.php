<?php

/**
 * NewIncludeExcludeStructureUpdate.
 */
declare(strict_types=1);

namespace HDNET\Calendarize\Updates;

use HDNET\Calendarize\Domain\Model\ConfigurationInterface;
use HDNET\Calendarize\Utility\HelperUtility;
use TYPO3\CMS\Install\Updates\AbstractUpdate;

/**
 * NewIncludeExcludeStructureUpdate.
 */
class NewIncludeExcludeStructureUpdate extends AbstractUpdate
{
    /**
     * The human-readable title of the upgrade wizard.
     *
     * @var string
     */
    protected $title = 'Migrate the calendarize configurations to the new include/exclude/override/cutout structure';

    /**
     * Checks whether updates are required.
     *
     * @param string &$description The description for the update
     *
     * @return bool Whether an update is required (TRUE) or not (FALSE)
     */
    public function checkForUpdate(&$description)
    {
        $databaseConnection = HelperUtility::getDatabaseConnection();
        $count = $databaseConnection->exec_SELECTcountRows(
            '*',
            'tx_calendarize_domain_model_configuration',
            'handling="" OR handling IS NULL'
        );

        if ($count > 0) {
            $description = 'We will update ' . $count . ' calendarize configurations';

            return true;
        }

        return false;
    }

    /**
     * Performs the accordant updates.
     *
     * @param array &$dbQueries      Queries done in this update
     * @param mixed &$customMessages Custom messages
     *
     * @return bool Whether everything went smoothly or not
     */
    public function performUpdate(array &$dbQueries, &$customMessages)
    {
        $databaseConnection = HelperUtility::getDatabaseConnection();
        $query = $databaseConnection->UPDATEquery(
            'tx_calendarize_domain_model_configuration',
            'type="timeExclude"',
            [
            'type' => ConfigurationInterface::TYPE_TIME,
            'handling' => ConfigurationInterface::HANDLING_INCLUDE,
            ]
        );
        $databaseConnection->admin_query($query);
        $dbQueries[] = $query;

        $query = $databaseConnection->UPDATEquery(
            'tx_calendarize_domain_model_configuration',
            'type="include"',
            [
            'type' => ConfigurationInterface::TYPE_GROUP,
            'handling' => ConfigurationInterface::HANDLING_INCLUDE,
            ]
        );
        $databaseConnection->admin_query($query);
        $dbQueries[] = $query;

        $query = $databaseConnection->UPDATEquery(
            'tx_calendarize_domain_model_configuration',
            'type="exclude"',
            [
            'type' => ConfigurationInterface::TYPE_GROUP,
            'handling' => ConfigurationInterface::HANDLING_EXCLUDE,
            ]
        );
        $databaseConnection->admin_query($query);
        $dbQueries[] = $query;

        $query = $databaseConnection->UPDATEquery(
            'tx_calendarize_domain_model_configuration',
            'handling="" OR handling IS NULL',
            [
            'handling' => ConfigurationInterface::HANDLING_INCLUDE,
            ]
        );
        $databaseConnection->admin_query($query);
        $dbQueries[] = $query;

        $customMessages = 'All queries are done! :)';

        return true;
    }
}
