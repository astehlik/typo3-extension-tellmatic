<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
    $_EXTKEY,
    'Configuration/TypoScript/SubscriptionPlugin/',
    'Tellmatic Subscription plugin'
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'Sto.Tellmatic',
    'Subscribe',
    'Tellmatic subscription'
);
