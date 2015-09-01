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

use Sto\Tellmatic\Tellmatic\Response\SubscribeStateResponse;
use Sto\Tellmatic\Tellmatic\TellmaticClient;
use Sto\Tellmatic\Utility\FormhandlerUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * Runs different finishers depending on the current Tellmatic subscription state.
 */
class SubscriptionDataPreProcessor extends \Tx_Formhandler_AbstractPreProcessor {

	/**
	 * Checks, if the subscriber exists and calls the sub-finishers accordingly
	 *
	 * @return string|array The output that should be displayed to the user (if any) or the GET/POST data array
	 */
	public function process() {

		$queryFields = $this->getFormhandlerUtility()->parseFields($this->gp, $this->settings);

		if (empty($queryFields['email'])) {
			throw new \RuntimeException('Required field email is empty.');
		}

		$email = $queryFields['email'];

		if (!GeneralUtility::validEmail($email)) {
			throw new \RuntimeException('An invalid email was submitted. Please configure a validator to prevent this.');
		}

		/**
		 * @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager
		 * @var \Sto\Tellmatic\Tellmatic\TellmaticClient $tellmaticClient
		 */
		$objectManager = GeneralUtility::makeInstance(ObjectManager::class);
		$tellmaticClient = $objectManager->get(TellmaticClient::class);
		$tellmaticResponse = $tellmaticClient->getSubscribeState($email);

		switch ($tellmaticResponse->getSubscribeState()) {
			case SubscribeStateResponse::SUBSCRIBE_STATE_SUBSCRIBED_UNCONFIRMED:
			case SubscribeStateResponse::SUBSCRIBE_STATE_SUBSCRIBED_CONFIRMED:
				$this->mergeAddressDataToGpArray($tellmaticResponse->getAddressData());
				break;
		}

		return $this->gp;
	}

	/**
	 * @return FormhandlerUtility
	 */
	protected function getFormhandlerUtility() {
		$formahandlerUtility = GeneralUtility::makeInstance(FormhandlerUtility::class);
		$formahandlerUtility->initialize($this);
		return $formahandlerUtility;
	}

	/**
	 * @param array $addressData
	 */
	protected function mergeAddressDataToGpArray(array $addressData) {

		if (!is_array($this->settings['mapAddressFields.'])) {
			return;
		}

		foreach ($this->settings['mapAddressFields.'] as $addressField => $gpField) {

			if (!isset($addressData[$addressField])) {
				throw new \InvalidArgumentException('The configured address field ' . $addressField . ' does not exist.');
			}

			$this->gp[$gpField] = $addressData[$addressField];
		}
	}
}