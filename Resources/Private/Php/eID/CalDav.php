<?php

/** @var \HDNET\Calendarize\Service\CalDav $calDav */
$calDav = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('HDNET\\Calendarize\\Service\\CalDav');
$calDav->runServer();