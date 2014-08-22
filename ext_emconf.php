<?php
/**
 * $EM_CONF
 *
 * @category   Extension
 * @package    Calendarize
 * @author     Tim Lochmüller
 */


$EM_CONF['calendarize'] = array(
	'title'              => 'Calendarize',
	'description'        => 'Smart Extbase calendar for one central or your own tables.',
	'category'           => 'misc',
	'version'            => '1.0.0',
	'state'              => 'beta',
	'clearcacheonload'   => 1,
	'author'             => 'Tim Lochmüller',
	'author_email'       => 'tim@fruit-lab.de',
	'constraints'        => array(
		'depends'   => array(
			'typo3'      => '6.2.0-6.2.99',
			'autoloader' => '1.2.1-0.0.0',
		),
	),
);