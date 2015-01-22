<?php

/**
 * TCA Structure for Events
 */

$base = \HDNET\Autoloader\Utility\ModelUtility::getTcaInformation('HDNET\\Calendarize\\Domain\\Model\\Event');

$extensionConfiguration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['calendarize']);

$custom = array(
	'ctrl'    => array(
		'hideTable' => (boolean)$extensionConfiguration['disableDefaultEvent']
	),
	'columns' => array(
		'title'       => array(
			'type' => 'text',
			'eval' => 'required'
		),
		'description' => array(
			'config'        => array(
				'type' => 'text',
			),
			'defaultExtras' => 'richtext:rte_transform[flag=rte_enabled|mode=ts_css]',
		),
	),
);

return \HDNET\Autoloader\Utility\ArrayUtility::mergeRecursiveDistinct($base, $custom);