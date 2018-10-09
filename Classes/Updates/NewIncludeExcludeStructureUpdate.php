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
        $table = 'tx_calendarize_domain_model_configuration';

        $q = HelperUtility::getDatabaseConnection($table)->createQueryBuilder();

        $q->count('*')
            ->from($table)
            ->where(
                $q->expr()->orX(
                    $q->expr()->eq('handling', ''),
                    $q->expr()->isNull('handling')
                )
            );

        $count = $q->execute();

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
        $table = 'tx_calendarize_domain_model_configuration';

        $q = HelperUtility::getDatabaseConnection($table)->createQueryBuilder();
        $q->update($table)
            ->where(
                $q->expr()->eq('type', 'timeExclude')
            )
            ->values([
                'type' => ConfigurationInterface::TYPE_TIME,
                'handling' => ConfigurationInterface::HANDLING_INCLUDE,
            ]);

        $dbQueries[] = $q->getSQL();
        $q->execute();
        $q->resetQueryParts();

        $q->update('tx_calendarize_domain_model_configuration')
            ->where(
                $q->expr()->eq('type', 'include')
            )
            ->values([
                'type' => ConfigurationInterface::TYPE_GROUP,
                'handling' => ConfigurationInterface::HANDLING_INCLUDE,
            ]);

        $dbQueries[] = $q->getSQL();
        $q->execute();
        $q->resetQueryParts();

        $q->update('tx_calendarize_domain_model_configuration')
            ->where(
                $q->expr()->eq('type', 'exclude')
            )
            ->values([
                'type' => ConfigurationInterface::TYPE_GROUP,
                'handling' => ConfigurationInterface::HANDLING_EXCLUDE,
            ]);

        $dbQueries[] = $q->getSQL();
        $q->execute();
        $q->resetQueryParts();

        $q->update('tx_calendarize_domain_model_configuration')
            ->where(
                $q->expr()->orX(
                    $q->expr()->eq('handling', ''),
                    $q->expr()->isNull('handling')
                )
            )
            ->values([
                'handling' => ConfigurationInterface::HANDLING_INCLUDE,
            ]);

        $dbQueries[] = $q->getSQL();
        $q->execute();

        $customMessages = 'All queries are done! :)';

        return true;
    }
}
