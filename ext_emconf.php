<?php
/**
 * $EM_CONF
 *
 * @category   Extension
 * @package    Autoloader
 * @author     Tim Lochmüller <tim.lochmueller@hdnet.de>
 */


$EM_CONF[$_EXTKEY] = array(
	'title'              => 'Calendarize',
	'description'        => 'Add calendar options to every custom table',
	'category'           => 'misc',
	'shy'                => 0,
	'version'            => '6.2.0',
	'dependencies'       => 'autoloader,extbase,fluid',
	'conflicts'          => '',
	'loadOrder'          => '',
	'module'             => '',
	'priority'           => '',
	'state'              => 'stable',
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
			'extbase'    => '6.2.0-0.0.0',
			'autoloader' => '0.0.1-0.0.0',
			'fluid'      => '6.2.0-0.0.0',
		),
		'conflicts' => array(),
		'suggests'  => array(),
	),
	'suggests'           => array(),
);

?>