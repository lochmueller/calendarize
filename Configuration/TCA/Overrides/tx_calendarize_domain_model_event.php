<?php

declare(strict_types=1);
defined('TYPO3') or exit();

if (!(bool)\HDNET\Calendarize\Utility\ConfigurationUtility::get('disableDefaultEvent')) {
    \HDNET\Calendarize\Register::extTables(
        \HDNET\Calendarize\Register::getDefaultCalendarizeConfiguration()
    );
}
