<?php

declare(strict_types=1);

namespace HDNET\Calendarize\Updates;

class TillDateFieldUpdate extends DateFieldUpdate
{
    protected $title = 'Migrate tillDate database format to real date';

    protected $description = 'This wizard migrates the existing tillDate configurations in the ' .
        'database from a timestamp to a real date. This enables dates before 1970 and after 2038.';

    protected $migrationMap = [
        'tx_calendarize_domain_model_configuration' => [
            'till_date',
        ],
    ];

    public function getIdentifier(): string
    {
        return 'calendarize_tillDateField';
    }
}
