<?php

declare(strict_types=1);

use HDNET\Autoloader\Utility\ModelUtility;

defined('TYPO3') or exit();

$GLOBALS['TCA']['sys_template'] = ModelUtility::getTcaOverrideInformation('calendarize', 'sys_template');
