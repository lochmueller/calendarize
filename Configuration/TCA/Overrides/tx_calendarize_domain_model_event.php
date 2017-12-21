<?php

declare(strict_types=1);

use HDNET\Calendarize\Register;
use HDNET\Calendarize\Utility\ConfigurationUtility;
use TYPO3\CMS\Core\Category\CategoryRegistry;

if (!(bool) ConfigurationUtility::get('disableDefaultEvent')) {
    Register::extTables(Register::getDefaultCalendarizeConfiguration());
    CategoryRegistry::getInstance()
        ->add('calendarize', 'tx_calendarize_domain_model_event');
}
