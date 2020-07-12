<?php

/**
 * NewIncludeExcludeStructureUpdate.
 */
declare(strict_types=1);

namespace HDNET\Calendarize\Updates;

use HDNET\Calendarize\Domain\Model\ConfigurationInterface;
use HDNET\Calendarize\Utility\HelperUtility;
use TYPO3\CMS\Install\Updates\DatabaseUpdatedPrerequisite;
use TYPO3\CMS\Install\Updates\UpgradeWizardInterface;

/**
 * NewIncludeExcludeStructureUpdate.
 */
class NewIncludeExcludeStructureUpdate implements UpgradeWizardInterface
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

        $q->count('handling')
            ->from($table)
            ->where(
                $q->expr()->orX(
                    $q->expr()->eq('handling', $q->quote('')),
                    $q->expr()->isNull('handling')
                )
            );

        $count = $q->execute()->fetchColumn(0);

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
    public function executeUpdate(): bool
    {
        $table = 'tx_calendarize_domain_model_configuration';

        $q = HelperUtility::getDatabaseConnection($table)->createQueryBuilder();
        $q->update($table)
            ->where(
                $q->expr()->eq('type', $q->quote('timeExclude'))
            )
            ->set('type', ConfigurationInterface::TYPE_TIME)
            ->set('handling', ConfigurationInterface::HANDLING_INCLUDE);

        $dbQueries[] = $q->getSQL();
        $q->execute();

        $q->resetQueryParts();

        $q->update($table)
            ->where(
                $q->expr()->eq('type', $q->quote('include'))
            )
            ->set('type', ConfigurationInterface::TYPE_GROUP)
            ->set('handling', ConfigurationInterface::HANDLING_INCLUDE);

        $dbQueries[] = $q->getSQL();
        $q->execute();

        $q->resetQueryParts();

        $q->update($table)
            ->where(
                $q->expr()->eq('type', $q->quote('exclude'))
            )
            ->set('type', ConfigurationInterface::TYPE_GROUP)
            ->set('handling', ConfigurationInterface::HANDLING_EXCLUDE);

        $dbQueries[] = $q->getSQL();
        $q->execute();

        $q->resetQueryParts();

        $q->update($table)
            ->where(
                $q->expr()->orX(
                    $q->expr()->eq('handling', $q->quote('')),
                    $q->expr()->isNull('handling')
                )
            )
            ->set('handling', ConfigurationInterface::HANDLING_INCLUDE);

        $dbQueries[] = $q->getSQL();
        $q->execute();

        $customMessages = 'All queries are done! :)';

        return true;
    }

    public function getIdentifier(): string
    {
        return self::class;
    }

    public function getTitle(): string
    {
        return '';
    }

    public function getDescription(): string
    {
        return '';
    }

    public function updateNecessary(): bool
    {
        return false;
    }

    public function getPrerequisites(): array
    {
        return [
            DatabaseUpdatedPrerequisite::class
        ];
    }
}
