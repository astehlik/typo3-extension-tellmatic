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


class SubscribeValidator extends \Tx_Formhandler_Finisher_DB {

	/**
	 * Initialize the class variables
	 *
	 * @param array $gp GET and POST variable array
	 * @param array $settings Typoscript configuration for the component (component.1.config.*)
	 *
	 * @return void
	 */
	public function init($gp, $settings) {
		$this->gp = $gp;
		$this->settings = $settings;
	}

	/**
	 * Validates the submitted values using given settings
	 *
	 * @param array $errors Reference to the errors array to store the errors occurred
	 * @return boolean
	 */
	public function validate(&$errors) {

		// if there are already errors we do not proceed
		if (count($errors)) {
			$this->utilityFuncs->debugMessage('Skipping tellmatic subscription since previous errors have been detected.');
			return FALSE;
		}

		$success = FALSE;

		try {
			$response = $this->sendSubscribeRequest();
			if ($response->getSuccess()) {
				$success = TRUE;
			} else {
				$errors['tellmatic'] = $response->getFailureCode();
				$this->utilityFuncs->debugMessage('Exception during tellmatic request: ' . $response->getFailureReason());
			}
		} catch (\Exception $exception) {
			$errors['tellmatic'] = \Sto\Tellmatic\Tellmatic\TellmaticResponse::FAILURE_CODE_UNKNOWN;
			$this->utilityFuncs->debugMessage('Exception during tellmatic request: ' . $exception->getMessage());
			return FALSE;
		}

		return $success;
	}

	/**
	 * @return \Sto\Tellmatic\Tellmatic\TellmaticResponse
	 * @throws \RuntimeException
	 */
	public function sendSubscribeRequest() {

		$queryFields = $this->parseFields();

		if (!isset($queryFields['email'])) {
			throw new \RuntimeException('No email field was configured.');
		}

		$email = $queryFields['email'];
		unset($queryFields['email']);

		/**
		 * @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager
		 * @var \Sto\Tellmatic\Tellmatic\TellmaticClient $tellmaticClient
		 */
		$objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
		$tellmaticClient = $objectManager->get('Sto\\Tellmatic\\Tellmatic\\TellmaticClient');

		if (isset($this->settings['overrideSubscribeUrl'])) {
			$overrideSubscribeUrl = $this->utilityFuncs->getSingle($this->settings, 'overrideSubscribeUrl');
			$tellmaticClient->setCustomUrl($overrideSubscribeUrl);
		}

		return $tellmaticClient->sendSubscribeRequest($email, $queryFields);
	}
}

?>