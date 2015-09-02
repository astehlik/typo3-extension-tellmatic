<?php
namespace Sto\Tellmatic\Formhandler;

/*                                                                        *
 * This script belongs to the TYPO3 extension "tellmatic".                *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License, either version 3 of the   *
 * License, or (at your option) any later version.                        *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use Sto\Tellmatic\Tellmatic\Request\UnsubscribeRequest;
use Sto\Tellmatic\Tellmatic\Response\TellmaticResponse;
use Sto\Tellmatic\Tellmatic\TellmaticClient;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * This finisher executes a Tellmatic subscribe request.
 * It uses the same field configuration as the DB finisher.
 */
class UnsubscribeValidator extends \Tx_Formhandler_AbstractValidator {

	/**
	 * Validates the submitted values using given settings
	 *
	 * @param array $errors
	 * @return bool
	 */
	public function validate(&$errors) {

		if (!empty($errors)) {
			return FALSE;
		}

		$result = TRUE;

		try {
			$response = $this->sendUnsubscribeRequest();
			if (!$response->getSuccess()) {
				$errors['tellmatic'] = $response->getFailureCode();
				$this->utilityFuncs->debugMessage('Exception during tellmatic request: ' . $response->getFailureReason());
				$result = FALSE;
			}
		} catch (\Exception $exception) {
			$errors['tellmatic'] = 'unknown';
			$this->utilityFuncs->debugMessage('Exception during tellmatic request: ' . $exception->getMessage());
			$result = FALSE;
		}

		return $result;
	}

	/**
	 * Sends an unsubscribe request to tellmatic.
	 *
	 * @return TellmaticResponse
	 */
	protected function sendUnsubscribeRequest() {

		$email = $this->utilityFuncs->getSingle($this->settings, 'email');
		if (empty($email)) {
			throw new \InvalidArgumentException('The unsubscribe email is not available or not configured correctly.');
		}

		/**
		 * @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager
		 * @var \Sto\Tellmatic\Tellmatic\TellmaticClient $tellmaticClient
		 * @var \Sto\Tellmatic\Tellmatic\Request\SubscribeRequest $subscribeRequest
		 */
		$objectManager = GeneralUtility::makeInstance(ObjectManager::class);
		$tellmaticClient = $objectManager->get(TellmaticClient::class);
		$unsubscribeRequest = $objectManager->get(UnsubscribeRequest::class, $email);

		$memo = $this->utilityFuncs->getSingle($this->settings, 'memo');
		if (!empty($memo)) {
			$subscribeRequest->getMemo()->addLineToMemo($memo);
		}

		return $tellmaticClient->sendUnsubscribeRequest($unsubscribeRequest);

	}
}