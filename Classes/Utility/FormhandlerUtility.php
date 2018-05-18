<?php

namespace Sto\Tellmatic\Utility;

/*                                                                        *
 * This script belongs to the TYPO3 extension "tellmatic".                *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License, either version 3 of the   *
 * License, or (at your option) any later version.                        *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use Sto\Tellmatic\Tellmatic\Request\SubscribeRequest;
use Sto\Tellmatic\Tellmatic\Response\TellmaticResponse;
use Sto\Tellmatic\Tellmatic\TellmaticClient;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * Provides access to the global extension configuration
 */
class FormhandlerUtility implements SingletonInterface
{
    /**
     * @var \Tx_Formhandler_Component_Manager
     */
    protected $componentManager;

    /**
     * @var \Tx_Formhandler_Configuration
     */
    protected $configuration;

    /**
     * @var \Tx_Formhandler_Globals
     */
    protected $globals;

    /**
     * @var \Tx_Formhandler_UtilityFuncs
     */
    protected $utilityFuncs;

    /**
     * @param \Tx_Formhandler_AbstractClass $abstractClass
     */
    public function initialize(\Tx_Formhandler_AbstractClass $abstractClass)
    {
        $this->componentManager = $this->getProtectedProperty($abstractClass, 'componentManager');
        $this->configuration = $this->getProtectedProperty($abstractClass, 'configuration');
        $this->globals = $this->getProtectedProperty($abstractClass, 'globals');
        $this->utilityFuncs = $this->getProtectedProperty($abstractClass, 'utilityFuncs');
    }

    /**
     * @param array $gp
     * @param array $settings
     * @return array
     */
    public function parseFields(array $gp, array $settings)
    {
        if (!isset($settings['table'])) {
            $settings['table'] = 'dummy';
        }

        /** @var \Tx_Formhandler_Finisher_DB $finisherDb */
        $finisherDb = $this->makeFormhandlerInstance(\Tx_Formhandler_Finisher_DB::class);
        $finisherDb->init($gp, $settings);

        $method = new \ReflectionMethod(\Tx_Formhandler_Finisher_DB::class, 'parseFields');
        $method->setAccessible(true);
        return $method->invoke($finisherDb);
    }

    /**
     * Builds and executes the subscribe request.
     *
     * @param array $gp
     * @param array $settings
     * @return TellmaticResponse
     */
    public function sendSubscribeRequest($gp, $settings)
    {
        $queryFields = $this->parseFields($gp, $settings);

        if (!isset($queryFields['email'])) {
            throw new \RuntimeException('No email field was configured.');
        }

        $email = $queryFields['email'];
        unset($queryFields['email']);

        /**
         * @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager
         * @var \Sto\Tellmatic\Tellmatic\TellmaticClient $tellmaticClient
         * @var \Sto\Tellmatic\Tellmatic\Request\SubscribeRequest $subscribeRequest
         */
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $tellmaticClient = $objectManager->get(TellmaticClient::class);
        $subscribeRequest = $objectManager->get(SubscribeRequest::class, $email);
        $subscribeRequest->setAdditionalFields($queryFields);

        $validateOnly = $this->utilityFuncs->getSingle($settings, 'validateOnly');
        if ($validateOnly) {
            $subscribeRequest->setValidateOnly(true);
        }

        $doNotSendEmails = $this->utilityFuncs->getSingle($settings, 'doNotSendEmails');
        if ($doNotSendEmails) {
            $subscribeRequest->setDoNotSendEmails(true);
        }

        $addressState = $this->utilityFuncs->getSingle($settings, 'addressState');
        if (!empty($addressState)) {
            $subscribeRequest->setOverrideAddressStatus($addressState);
        }

        $memo = $this->utilityFuncs->getSingle($settings, 'memo');
        if (!empty($memo)) {
            $subscribeRequest->getMemo()->addLineToMemo($memo);
        }

        $overrideSubscribeUrl = $this->utilityFuncs->getSingle($settings, 'overrideSubscribeUrl');
        if (!empty($overrideSubscribeUrl)) {
            $tellmaticClient->setCustomUrl($overrideSubscribeUrl);
        }

        return $tellmaticClient->sendSubscribeRequest($subscribeRequest);
    }

    /**
     * @param \Tx_Formhandler_AbstractClass $abstractClass
     * @param string $propertyName
     * @return mixed
     */
    protected function getProtectedProperty(\Tx_Formhandler_AbstractClass $abstractClass, $propertyName)
    {
        $class = new \ReflectionClass(get_class($abstractClass));
        $property = $class->getProperty($propertyName);
        $property->setAccessible(true);
        return $property->getValue($abstractClass);
    }

    /**
     * @param string $class
     * @return object
     */
    protected function makeFormhandlerInstance($class)
    {
        return GeneralUtility::makeInstance(
            $class,
            $this->componentManager,
            $this->configuration,
            $this->globals,
            $this->utilityFuncs
        );
    }
}
