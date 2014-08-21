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
	'uploadfolder'       => 0,
	'createDirs'         => '',
	'modify_tables'      => '',
	'clearcacheonload'   => 0,
	'author'             => 'Tim Lochmüller',
	'author_email'       => '',
	'author_company'     => '',
	'constraints'        => array(
		'depends'   => array(
			'typo3'      => '6.2.0-6.2.99',
			'autoloader' => '1.1.0-0.0.0',
		),
	),
);