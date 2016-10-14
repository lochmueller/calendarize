<?php

use HDNET\Calendarize\Service\CalDav;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/** @var CalDav $calDav */
$calDav = GeneralUtility::makeInstance(CalDav::class);
$calDav->runServer($_GET['calId']);
