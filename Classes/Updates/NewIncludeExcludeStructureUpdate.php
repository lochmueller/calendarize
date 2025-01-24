<?php

declare(strict_types=1);

namespace HDNET\Calendarize\Updates;

use HDNET\Calendarize\Domain\Model\ConfigurationInterface;
use HDNET\Calendarize\Utility\HelperUtility;
use TYPO3\CMS\Install\Attribute\UpgradeWizard;
use TYPO3\CMS\Install\Updates\DatabaseUpdatedPrerequisite;

#[UpgradeWizard('calendarize_newIncludeExcludeStructureUpdate')]
class NewIncludeExcludeStructureUpdate extends AbstractUpdate
{
    /**
     * The human-readable title of the upgrade wizard.
     */
    public function getTitle(): string
    {
        return 'Migrate the calendarize configurations to the new include/exclude/override/cutout structure';
    }

    /**
     * Performs the accordant updates.
     *
     * @return bool Whether everything went smoothly
     */
    public function executeUpdate(): bool
    {
        $table = 'tx_calendarize_domain_model_configuration';

        $q = HelperUtility::getDatabaseConnection($table)->createQueryBuilder();
        $q->update($table)
            ->where(
                $q->expr()->eq('type', $q->quote('timeExclude')),
            )
            ->set('type', ConfigurationInterface::TYPE_TIME)
            ->set('handling', ConfigurationInterface::HANDLING_INCLUDE);
        $q->executeStatement();

        $q->resetQueryParts();
        $q->update($table)
            ->where(
                $q->expr()->eq('type', $q->quote('include')),
            )
            ->set('type', ConfigurationInterface::TYPE_GROUP)
            ->set('handling', ConfigurationInterface::HANDLING_INCLUDE);
        $q->executeStatement();

        $q->resetQueryParts();
        $q->update($table)
            ->where(
                $q->expr()->eq('type', $q->quote('exclude')),
            )
            ->set('type', ConfigurationInterface::TYPE_GROUP)
            ->set('handling', ConfigurationInterface::HANDLING_EXCLUDE);
        $q->executeStatement();

        $q->resetQueryParts();

        $q->update($table)
            ->where(
                $q->expr()->or(
                    $q->expr()->eq('handling', $q->quote('')),
                    $q->expr()->isNull('handling'),
                ),
            )
            ->set('handling', ConfigurationInterface::HANDLING_INCLUDE);
        $q->executeStatement();

        return true;
    }

    public function updateNecessary(): bool
    {
        $table = 'tx_calendarize_domain_model_configuration';

        $q = HelperUtility::getDatabaseConnection($table)->createQueryBuilder();
        $q->count('handling')
            ->from($table)
            ->where(
                $q->expr()->or(
                    $q->expr()->eq('handling', $q->quote('')),
                    $q->expr()->isNull('handling'),
                ),
            );

        $count = $q->executeQuery()->fetchOne();

        if ($count > 0) {
            $this->description = 'We will update ' . $count . ' calendarize configurations';

            return true;
        }

        return false;
    }

    public function getPrerequisites(): array
    {
        return [
            DatabaseUpdatedPrerequisite::class,
        ];
    }
}
