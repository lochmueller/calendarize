<?php
/**
 * $EM_CONF
 *
 * @category Extension
 * @package  Calendarize
 * @author   Tim Lochmüller
 */


$EM_CONF['calendarize'] = array(
	'title'            => 'Calendarize',
	'description'      => 'Create a structure for timely controlled tables and one plugin for the different output of calendar views. The extension is shipped with one default event table, but the aim of the extension is to "calendarize" every table/model. It is completely independent and configurable! Use your own models as event items in this calender. We need feedback about the concept! Development on https://github.com/lochmueller/calendarize',
	'category'         => 'fe',
	'version'          => '1.3.2',
	'state'            => 'beta',
	'clearcacheonload' => 1,
	'author'           => 'Tim Lochmüller',
	'author_email'     => 'tim@fruit-lab.de',
	'constraints'      => array(
		'depends' => array(
			'typo3'      => '6.2.0-7.2.99',
			'autoloader' => '1.5.5-0.0.0',
		),
	),
);
