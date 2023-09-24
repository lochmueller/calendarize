<?php

declare(strict_types=1);

namespace HDNET\Calendarize\Updates;

use TYPO3\CMS\Install\Attribute\UpgradeWizard;

#[UpgradeWizard('calendarize_tillDateFieldUpdate')]
class TillDateFieldUpdate extends DateFieldUpdate
{
    protected string $title = 'Migrate tillDate database format to real date';

    protected string $description = 'This wizard migrates the existing tillDate configurations in the ' .
        'database from a timestamp to a real date. This enables dates before 1970 and after 2038.';

    protected array $migrationMap = [
        'tx_calendarize_domain_model_configuration' => [
            'till_date',
        ],
    ];
}
