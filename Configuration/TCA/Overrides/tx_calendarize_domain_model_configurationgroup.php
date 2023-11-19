<?php

declare(strict_types=1);

use HDNET\Calendarize\Register;

defined('TYPO3') or exit();

Register::createTcaConfiguration(Register::getGroupCalendarizeConfiguration());
