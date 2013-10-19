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

class SubscriptionStateSwitch extends \Tx_FormhandlerSubscription_Finisher_Subscribe {

	/**
	 * Inits the finisher mapping settings values to internal attributes.
	 *
	 * @param array $gp
	 * @param array $settings
	 * @return void
	 */
	public function init($gp, $settings) {

		$this->gp = $gp;
		$this->settings = $settings;

		if (array_key_exists('setTemplateSuffix', $this->settings) && (intval($this->settings['setTemplateSuffix']) == 0)) {
			$this->setTemplateSuffix = FALSE;
		}
	}

	/**
	 * Checks, if the subscriber exists and calls the sub-finishers accordingly
	 *
	 * @return string|array The output that should be displayed to the user (if any) or the GET/POST data array
	 */
	public function process() {

		if (!isset($this->settings['email'])) {
			throw new \RuntimeException('Required setting email is missing.');
		}

		$email = $this->settings['email'];

		if (isset($this->settings['email.'])) {
			$email = $this->utilityFuncs->getSingle($this->settings['email'], $this->settings['email.']);
		}

		if (!\TYPO3\CMS\Core\Utility\GeneralUtility::validEmail($email)) {
			throw new \RuntimeException('An invalid email was submitted. Please configure a validator to prevent this.');
		}

		/**
		 * @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager
		 * @var \Sto\Tellmatic\Tellmatic\TellmaticClient $tellmaticClient
		 */
		$objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
		$tellmaticClient = $objectManager->get('Sto\\Tellmatic\\Tellmatic\\TellmaticClient');
		$tellmaticResponse = $tellmaticClient->getSubscribeState($email);

		switch ($tellmaticResponse->getSubscribeState()) {
			case \Sto\Tellmatic\Tellmatic\SubscribeStateResponse::SUBSCRIBE_STATE_SUBSCRIBED_UNCONFIRMED:
				$this->setTemplateSuffix('_UNCONFIRMED_SUBSCRIBER');
				$result = $this->runFinishers($this->settings['existingUnconfirmedSubscriber.']);
				break;
			case \Sto\Tellmatic\Tellmatic\SubscribeStateResponse::SUBSCRIBE_STATE_SUBSCRIBED_CONFIRMED:
				$this->setTemplateSuffix('_CONFIRMED_SUBSCRIBER');
				$result = $this->runFinishers($this->settings['existingConfirmedSubscriber.']);
				break;
			case \Sto\Tellmatic\Tellmatic\SubscribeStateResponse::SUBSCRIBE_STATE_NOT_SUBSCRIBED:
				$this->setTemplateSuffix('_NEW_SUBSCRIBER');
				$result = $this->runFinishers($this->settings['newSubscriber.']);
				break;
		}

		if (strlen($result)) {
			return $result;
		} else {
			return $this->gp;
		}
	}
}
?>