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
use Tx_Formhandler_AbstractFinisher as FormhandlerAbstractFinisher;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * Runs different finishers depending on the current Tellmatic subscription state.
 */
class SubscriptionStateSwitch extends FormhandlerAbstractFinisher {

	/**
	 * If this is true the template suffix will be set according
	 * to the current database query result. These suffixes will
	 * be used: _NEW_SUBSCRIBER, _UNCONFIRMED_SUBSCRIBER and
	 * _CONFIRMED_SUBSCRIBER
	 *
	 * @var bool
	 */
	var $setTemplateSuffix = TRUE;

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
		$objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(ObjectManager::class);
		$tellmaticClient = $objectManager->get(TellmaticClient::class);
		$tellmaticResponse = $tellmaticClient->getSubscribeState($email);
		$result = '';

		switch ($tellmaticResponse->getSubscribeState()) {
			case SubscribeStateResponse::SUBSCRIBE_STATE_SUBSCRIBED_UNCONFIRMED:
				$this->setTemplateSuffix('_UNCONFIRMED_SUBSCRIBER');
				$result = $this->runFinishers($this->settings['existingUnconfirmedSubscriber.']);
				break;
			case SubscribeStateResponse::SUBSCRIBE_STATE_SUBSCRIBED_CONFIRMED:
				$this->setTemplateSuffix('_CONFIRMED_SUBSCRIBER');
				$result = $this->runFinishers($this->settings['existingConfirmedSubscriber.']);
				break;
			case SubscribeStateResponse::SUBSCRIBE_STATE_NOT_SUBSCRIBED:
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

	/**
	 * Adds some default configuration to a compontent.
	 *
	 * @see Tx_Formhandler_Controller_Form::addDefaultComponentConfig()
	 * @param array $conf
	 * @return array
	 */
	protected function addDefaultComponentConfig($conf) {
		$conf['langFiles'] = $this->settings['langFiles'];
		$conf['formValuesPrefix'] = $this->settings['formValuesPrefix'];
		$conf['templateSuffix'] = $this->settings['templateSuffix'];
		return $conf;
	}

	/**
	 * Runs the finishers configured in the given configuration array.
	 *
	 * @see Tx_Formhandler_Controller_Form::processFinished()
	 * @param array $finisherConfig
	 * @return string
	 */
	protected function runFinishers($finisherConfig) {

		$returnValue = NULL;
		ksort($finisherConfig);

		foreach ($finisherConfig as $idx => $tsConfig) {
			if ($idx !== 'disabled') {
				$className = $this->utilityFuncs->getPreparedClassName($tsConfig);
				if (is_array($tsConfig) && strlen($className) > 0) {
					if (intval($this->utilityFuncs->getSingle($tsConfig, 'disable')) !== 1) {

						/** @var \Tx_Formhandler_AbstractComponent $finisher */
						/** @noinspection PhpVoidFunctionResultUsedInspection */
						$finisher = $this->componentManager->getComponent($className);
						$tsConfig['config.'] = $this->addDefaultComponentConfig($tsConfig['config.']);
						$finisher->init($this->gp, $tsConfig['config.']);
						$finisher->validateConfig();

						// if the finisher returns HTML (e.g. Tx_Formhandler_Finisher_SubmittedOK)
						if (intval($this->utilityFuncs->getSingle($tsConfig['config.'], 'returns')) === 1) {
							$returnValue = $finisher->process();
							break;
						} else {
							$this->gp = $finisher->process();
							$this->globals->setGP($this->gp);
						}
					}
				} else {
					$this->utilityFuncs->throwException('classesarray_error');
				}
			}
		}

		return $returnValue;
	}

	/**
	 * Sets the template suffix to the given string if this was not disabled in the settings.
	 *
	 * @param string $templateSuffix
	 */
	protected function setTemplateSuffix($templateSuffix) {
		if ($this->setTemplateSuffix) {
			$this->globals->setTemplateSuffix($templateSuffix);
		}
	}
}