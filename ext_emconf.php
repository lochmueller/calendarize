<?php
/**
 * $EM_CONF
 *
 * @category   Extension
 * @package    Calendarize
 * @author     Tim Lochmüller <tim.lochmueller@hdnet.de>
 */


$EM_CONF['calendarize'] = array(
	'title'              => 'Calendarize',
	'description'        => 'Smart Extbase calendar for one central or your own tables.',
	'category'           => 'misc',
	'shy'                => 0,
	'version'            => '1.0.0',
	'dependencies'       => '',
	'conflicts'          => '',
	'loadOrder'          => '',
	'module'             => '',
	'priority'           => '',
	'state'              => 'beta',
	'uploadfolder'       => 0,
	'createDirs'         => '',
	'modify_tables'      => '',
	'clearcacheonload'   => 0,
	'lockType'           => '',
	'author'             => 'Tim Lochmüller',
	'author_email'       => 'tl@hdnet.de',
	'author_company'     => 'hdnet.de',
	'CGLcompliance'      => '',
	'CGLcompliance_note' => '',
	'constraints'        => array(
		'depends'   => array(
			'typo3'      => '6.2.0-6.2.99',
			'autoloader' => '1.1.0-0.0.0',
		),
		'conflicts' => array(),
		'suggests'  => array(),
	),
	'suggests'           => array(),
);