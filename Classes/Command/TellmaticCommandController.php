<?php

namespace Sto\Tellmatic\Command;

/*                                                                        *
 * This script belongs to the TYPO3 extension "tellmatic".                *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License, either version 3 of the   *
 * License, or (at your option) any later version.                        *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use Sto\Tellmatic\Tellmatic\Request\AddressCountRequest;
use Sto\Tellmatic\Tellmatic\Request\AddressSearchRequest;
use Sto\Tellmatic\Tellmatic\Request\SetCodeRequest;
use Sto\Tellmatic\Tellmatic\Request\SubscribeRequest;
use Sto\Tellmatic\Tellmatic\Request\UnsubscribeRequest;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\CommandController;

/**
 * Tellmatic API commands.
 */
class TellmaticCommandController extends CommandController
{
    /**
     * @inject
     * @var \Sto\Tellmatic\Tellmatic\TellmaticClient
     */
    protected $tellmaticClient;

    /**
     * Counts the addresses in the DB.
     *
     * @param array $search
     * @param int $groupId
     */
    public function addressCountCommand(array $search = [], $groupId = 0)
    {
        $addressCountRequest = GeneralUtility::makeInstance(AddressCountRequest::class);
        $addressCountRequest->setSearch($search);
        $addressCountRequest->setGroupId($groupId);
        $result = $this->tellmaticClient->sendAddressCountRequest($addressCountRequest);
        $this->outputLine('Address count is: ' . $result->getAddressCount());
    }

    /**
     * Counts the addresses in the DB.
     *
     * @param array $search
     * @param int $groupId
     */
    public function addressSearchCommand(array $search = [], $groupId = 0)
    {
        $addressSearchRequest = GeneralUtility::makeInstance(AddressSearchRequest::class);
        $addressSearchRequest->setSearch($search);
        $addressSearchRequest->setGroupId($groupId);
        $result = $this->tellmaticClient->sendAddressSearchRequest($addressSearchRequest);
        foreach ($result->getAddresses() as $address) {
            $this->outputLine('Found address ' . $address['email'] . ' with ID ' . $address['id']);
        }
    }

    /**
     * Sets the code_external.
     *
     * @param int $addressId
     * @param string $code
     */
    public function setCodeCommand($addressId, $code)
    {
        $setCodeRequest = GeneralUtility::makeInstance(SetCodeRequest::class, $addressId, $code);
        $this->tellmaticClient->sendSetCodeRequest($setCodeRequest);
        $this->outputLine('The code was set successfully.');
    }

    /**
     * Sends a subscribe request.
     *
     * @param string $email
     * @param bool $doNotSendEmails
     * @param bool $validateOnly
     * @param string $overrideAddressStatus
     * @param string $memo
     * @param string $f0
     * @param string $f1
     * @param string $f2
     * @param string $f3
     * @param string $f4
     * @param string $f5
     * @param string $f6
     * @param string $f7
     * @param string $f8
     * @param string $f9
     */
    public function subscribeCommand(
        $email,
        $doNotSendEmails = false,
        $validateOnly = false,
        $overrideAddressStatus = null,
        $memo = '',
        /** @noinspection PhpUnusedParameterInspection */
        $f0 = null,
        /** @noinspection PhpUnusedParameterInspection */
        $f1 = null,
        /** @noinspection PhpUnusedParameterInspection */
        $f2 = null,
        /** @noinspection PhpUnusedParameterInspection */
        $f3 = null,
        /** @noinspection PhpUnusedParameterInspection */
        $f4 = null,
        /** @noinspection PhpUnusedParameterInspection */
        $f5 = null,
        /** @noinspection PhpUnusedParameterInspection */
        $f6 = null,
        /** @noinspection PhpUnusedParameterInspection */
        $f7 = null,
        /** @noinspection PhpUnusedParameterInspection */
        $f8 = null,
        /** @noinspection PhpUnusedParameterInspection */
        $f9 = null
    ) {
        $subscribeRequest = GeneralUtility::makeInstance(SubscribeRequest::class, $email);

        if ($doNotSendEmails) {
            $subscribeRequest->setDoNotSendEmails(true);
        }

        if ($validateOnly) {
            $subscribeRequest->setValidateOnly(true);
        }

        if (isset($overrideAddressStatus)) {
            $subscribeRequest->setOverrideAddressStatus($overrideAddressStatus);
        }

        $additionalFields = [];
        for ($i = 0; $i <= 9; $i++) {
            $variable = 'f' . $i;
            if (isset($$variable)) {
                $additionalFields[$variable] = $$variable;
            }
        }
        if (!empty($additionalFields)) {
            $subscribeRequest->setAdditionalFields($additionalFields);
        }

        if (!empty($memo)) {
            $subscribeRequest->getMemo()->addLineToMemo($memo);
        }

        $subscribeRequest->getMemo()->addLineToMemo('subscribeCommand of TellmaticCommandController');

        $this->tellmaticClient->sendSubscribeRequest($subscribeRequest);
        $this->outputLine('Subscription was successful.');
    }

    /**
     * Shows the subscribe status.
     *
     * @param string $email
     */
    public function subsribeStatusCommand($email)
    {
        $this->outputLine($this->tellmaticClient->getSubscribeState($email)->getSubscribeState());
    }

    /**
     * Sends an unsubscribe request.
     *
     * @param string $email
     * @param bool $doNotSendEmails
     * @param string $memo
     * @param int $newsletterId
     * @param int $historyId
     * @param int $queueId
     */
    public function unsubscribeCommand(
        $email,
        $doNotSendEmails = false,
        $memo = '',
        $newsletterId = 0,
        $historyId = 0,
        $queueId = 0
    ) {
        $unsubscribeRequest = GeneralUtility::makeInstance(UnsubscribeRequest::class, $email);

        if ($doNotSendEmails) {
            $unsubscribeRequest->setDoNotSendEmails(true);
        }

        if (!empty($memo)) {
            $unsubscribeRequest->getMemo()->addLineToMemo($memo);
        }

        if (!empty($newsletterId)) {
            $unsubscribeRequest->setNewsletterId($newsletterId);
        }

        if (!empty($historyId)) {
            $unsubscribeRequest->setHistoryId($historyId);
        }

        if (!empty($queueId)) {
            $unsubscribeRequest->setQueueId($queueId);
        }

        $unsubscribeRequest->getMemo()->addLineToMemo('unsubscribeCommand of TellmaticCommandController');

        $this->tellmaticClient->sendUnsubscribeRequest($unsubscribeRequest);
        $this->outputLine('Unsubscription was successful.');
    }
}
