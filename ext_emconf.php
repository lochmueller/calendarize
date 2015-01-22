<?php
/**
 * $EM_CONF
 *
 * @category   Extension
 * @package    Calendarize
 * @author     Tim Lochmüller
 */


$EM_CONF['calendarize'] = array(
	'title'            => 'Calendarize',
	'description'      => 'Create a structure for timely controlled tables and plugins for the different output. The extension is shipped with one default event table, but the target table is completely independent and configurable from this extension. Use your own Models as Event items in this calender. Experimental concept at the moment / Dev on https://github.com/lochmueller/calendarize',
	'category'         => 'misc',
	'version'          => '1.0.0',
	'state'            => 'alpha',
	'clearcacheonload' => 1,
	'author'           => 'Tim Lochmüller',
	'author_email'     => 'tim@fruit-lab.de',
	'constraints'      => array(
		'depends' => array(
			'typo3'      => '6.2.0-6.2.99',
			'autoloader' => '1.2.1-0.0.0',
		),
	),
);