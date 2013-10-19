<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "tellmatic".
 *
 * Auto generated 11-01-2013 17:23
 *
 * Manual updates:
 * Only the data in the array - everything else is removed by next
 * writing. "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Tellmatic',
	'description' => 'Provides methods to communicate with a tellmatic server.',
	'category' => 'plugin',
	'shy' => 0,
	'version' => '0.0.0',
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
			'formhandler' => '1.6.1-0.0.0',
		),
		'conflicts' => array(
		),
		'suggests' => array(
			'formhandler_subscription' => '0.4.1-0.0.0',
		),
	),
	'_md5_values_when_last_written' => 'a:37:{s:16:"ext_autoload.php";s:4:"0dec";s:12:"ext_icon.gif";s:4:"0d47";s:17:"ext_localconf.php";s:4:"c55e";s:14:"ext_tables.php";s:4:"58b9";s:14:"ext_tables.sql";s:4:"7397";s:62:"Classes/API/Tx_FormhandlerSubscription_API_SubscriptionAPI.php";s:4:"2158";s:81:"Classes/Controller/Tx_FormhandlerSubscription_Controller_AjaxSubmitController.php";s:4:"8e17";s:40:"Classes/Exceptions/AbstractException.php";s:4:"89e4";s:46:"Classes/Exceptions/InvalidSettingException.php";s:4:"61c2";s:46:"Classes/Exceptions/MissingSettingException.php";s:4:"cced";s:75:"Classes/Finisher/Tx_FormhandlerSubscription_Finisher_GenerateAuthCodeDB.php";s:4:"5b5d";s:77:"Classes/Finisher/Tx_FormhandlerSubscription_Finisher_InvalidateAuthCodeDB.php";s:4:"5137";s:77:"Classes/Finisher/Tx_FormhandlerSubscription_Finisher_RemoveAuthCodeRecord.php";s:4:"ff95";s:66:"Classes/Finisher/Tx_FormhandlerSubscription_Finisher_Subscribe.php";s:4:"b471";s:69:"Classes/Finisher/Tx_FormhandlerSubscription_Finisher_UpdateMmData.php";s:4:"536f";s:76:"Classes/Finisher/Tx_FormhandlerSubscription_Finisher_ValidateAuthCodeUID.php";s:4:"bd8e";s:83:"Classes/PreProcessor/Tx_FormhandlerSubscription_PreProcessor_ValidateAuthCodeDB.php";s:4:"23b6";s:59:"Classes/Utils/Tx_FormhandlerSubscription_Utils_AuthCode.php";s:4:"3c4d";s:61:"Classes/View/Tx_FormhandlerSubscription_View_AuthCodeMail.php";s:4:"1ede";s:36:"Configuration/Settings/constants.txt";s:4:"853e";s:32:"Configuration/Settings/setup.txt";s:4:"e4af";s:43:"Resources/Language/DirectMailCategories.xml";s:4:"74c3";s:29:"Resources/Language/Global.xml";s:4:"c4f4";s:34:"Resources/T3D/pagestructure-de.t3d";s:4:"e53b";s:34:"Resources/T3D/pagestructure-en.t3d";s:4:"12f8";s:43:"Resources/Templates/RemoveSubscription.html";s:4:"e47a";s:44:"Resources/Templates/RequestSubscription.html";s:4:"fd81";s:38:"Resources/Templates/RequestUpdate.html";s:4:"d60b";s:43:"Resources/Templates/UpdateSubscription.html";s:4:"be1b";s:65:"Resources/Templates/DirectMailCategories/RequestSubscription.html";s:4:"b080";s:64:"Resources/Templates/DirectMailCategories/UpdateSubscription.html";s:4:"0c10";s:46:"Tests/Unit/Finisher/GenerateAuthCodeDbTest.php";s:4:"30f5";s:44:"Tests/Unit/Fixtures/MockComponentManager.php";s:4:"f405";s:35:"Tests/Unit/Fixtures/MockGlobals.php";s:4:"8efd";s:40:"Tests/Unit/Fixtures/MockUtilityFuncs.php";s:4:"360e";s:14:"doc/manual.pdf";s:4:"6c5b";s:14:"doc/manual.sxw";s:4:"0774";}',
	'suggests' => array(
	),
);

?>