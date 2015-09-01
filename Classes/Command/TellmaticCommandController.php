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

use Sto\Tellmatic\Tellmatic\Request\SubscribeRequest;
use Sto\Tellmatic\Tellmatic\Request\UnsubscribeRequest;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\CommandController;

/**
 * Tellmatic API commands.
 */
class TellmaticCommandController extends CommandController {

	/**
	 * @inject
	 * @var \Sto\Tellmatic\Tellmatic\TellmaticClient
	 */
	protected $tellmaticClient;

	/**
	 * Sends a subscribe request.
	 *
	 * @param string $email
	 * @param bool $doNotSendEmails
	 * @param bool $validateOnly
	 * @param string $overrideAddressStatus
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
		$doNotSendEmails = FALSE,
		$validateOnly = FALSE,
		$overrideAddressStatus = NULL,
		/** @noinspection PhpUnusedParameterInspection */
		$f0 = NULL,
		/** @noinspection PhpUnusedParameterInspection */
		$f1 = NULL,
		/** @noinspection PhpUnusedParameterInspection */
		$f2 = NULL,
		/** @noinspection PhpUnusedParameterInspection */
		$f3 = NULL,
		/** @noinspection PhpUnusedParameterInspection */
		$f4 = NULL,
		/** @noinspection PhpUnusedParameterInspection */
		$f5 = NULL,
		/** @noinspection PhpUnusedParameterInspection */
		$f6 = NULL,
		/** @noinspection PhpUnusedParameterInspection */
		$f7 = NULL,
		/** @noinspection PhpUnusedParameterInspection */
		$f8 = NULL,
		/** @noinspection PhpUnusedParameterInspection */
		$f9 = NULL
	) {
		$subscribeRequest = GeneralUtility::makeInstance(SubscribeRequest::class, $email);

		if ($doNotSendEmails) {
			$subscribeRequest->setDoNotSendEmails(TRUE);
		}

		if ($validateOnly) {
			$subscribeRequest->setValidateOnly(TRUE);
		}

		if (isset($overrideAddressStatus)) {
			$subscribeRequest->setOverrideAddressStatus($overrideAddressStatus);
		}

		$additionalFields = array();
		for ($i = 0; $i <= 9; $i++) {
			$variable = 'f' . $i;
			if (isset($$variable)) {
				$additionalFields[$variable] = $$variable;
			}
		}
		if (!empty($additionalFields)) {
			$subscribeRequest->setAdditionalFields($additionalFields);
		}

		$result = $this->tellmaticClient->sendSubscribeRequest($subscribeRequest);
		if ($result->getSuccess()) {
			$this->outputLine('Subscription was successful.');
		} else {
			$this->outputLine('An error occured: ' . $result->getFailureReason() . ' (' . $result->getFailureCode() . ')');
		}
	}

	/**
	 * Shows the subscribe status.
	 *
	 * @param string $email
	 */
	public function subsribeStatusCommand($email) {
		$this->outputLine($this->tellmaticClient->getSubscribeState($email)->getSubscribeState());
	}

	/**
	 * Sends an unsubscribe request.
	 *
	 * @param string $email
	 * @param bool $doNotSendEmails
	 */
	public function unsubscribeCommand($email, $doNotSendEmails = FALSE) {

		$unsubscribeRequest = GeneralUtility::makeInstance(UnsubscribeRequest::class, $email);

		if ($doNotSendEmails) {
			$unsubscribeRequest->setDoNotSendEmails(TRUE);
		}

		$result = $this->tellmaticClient->sendUnsubscribeRequest($unsubscribeRequest);
		if ($result->getSuccess()) {
			$this->outputLine('Unsubscription was successful.');
		} else {
			$this->outputLine('An error occured: ' . $result->getFailureReason() . ' (' . $result->getFailureCode() . ')');
		}
	}
}