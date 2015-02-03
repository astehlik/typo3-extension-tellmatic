<?php

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Tellmatic',
	'description' => 'Provides methods to communicate with a tellmatic server.',
	'category' => 'plugin',
	'shy' => 0,
	'version' => '0.1.0',
	'dependencies' => 'formhandler',
	'conflicts' => '',
	'priority' => '',
	'loadOrder' => '',
	'module' => '',
	'state' => 'alpha',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearcacheonload' => 0,
	'lockType' => '',
	'author' => 'Alexander Stehlik',
	'author_email' => 'alexander.stehlik.deleteme@googlemail.com',
	'author_company' => '',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'constraints' => array(
		'depends' => array(
			'php' => '5.3.0-0.0.0',
			'typo3' => '6.0.0-6.2.99',
		),
		'conflicts' => array(
		),
		'suggests' => array(
			'formhandler_subscription' => '0.4.1-0.0.0',
		),
	),
);
