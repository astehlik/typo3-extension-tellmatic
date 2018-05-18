<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

use Sto\Tellmatic\Scheduler\ExtbaseProgressingTask as TellmaticExtbaseProgressingTask;

if (TYPO3_MODE == 'BE') {
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['extbase']['commandControllers'][] =
        \Sto\Tellmatic\Command\TellmaticCommandController::class;
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['extbase']['commandControllers'][] =
        \Sto\Tellmatic\Command\AuthCodeCommandController::class;
}

// Add file indexing task
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks'][TellmaticExtbaseProgressingTask::class] = [
    'extension' => $_EXTKEY,
    'title' => 'Extbase progressing task',
    'description' => 'An Extbase task that supports the display of the progress of the current command.',
    'additionalFields' => \TYPO3\CMS\Extbase\Scheduler\FieldProvider::class,
];

$controllerActions = 'subscribeRequestForm,subscribeRequest,subscribeConfirm'
    . ',updateRequestForm,updateRequest,updateForm,update,unsubscribeForm,unsubscribe';

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'Sto.Tellmatic',
    'Subscribe',
    ['Subscribe' => $controllerActions],
    ['Subscribe' => $controllerActions]
);

unset($controllerActions);
