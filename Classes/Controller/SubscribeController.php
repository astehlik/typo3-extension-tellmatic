<?php
namespace Sto\Tellmatic\Controller;

/*                                                                        *
 * This script belongs to the TYPO3 extension "tellmatic".                *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License, either version 3 of the   *
 * License, or (at your option) any later version.                        *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use Sto\Tellmatic\Command\AuthCodeCommandController;
use Sto\Tellmatic\Tellmatic\Request\SubscribeRequest;
use Sto\Tellmatic\Utility\SubscriptionHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Fluid\View\TemplateView;

/**
 * Controller for handling subscription, updates and unsubscriptions.
 */
class SubscribeController extends ActionController {

	/**
	 * The auth code context that is used for all auth codes.
	 *
	 * @const
	 */
	const AUTH_CODE_CONTEXT = 'tellmatic_subscribe_controller';

	/**
	 * @var \Tx\Authcode\Domain\Repository\AuthCodeRepository
	 * @inject
	 */
	protected $authCodeRepository;

	/**
	 * @var \Sto\Tellmatic\Tellmatic\TellmaticClient
	 * @inject
	 */
	protected $tellmaticClient;

	/**
	 * When the submitted auth code is valid the email state will be set to "confirmed"
	 * in the Tellmatic database.
	 *
	 * @param string $authCode
	 */
	public function subscribeConfirmAction($authCode) {

		$subscriptionSuccessful = TRUE;
		$subscriptionError = '';

		try {
			$authCode = $this->getValidAuthCode($authCode);

			$subscriptionHandler = $this->getSubscriptionHandler();
			$subscriptionHandler->handleSubscribeConfirmation($authCode->getIdentifier(), 'Subscription confirmed by auth code in ' . __METHOD__);

		} catch (\Exception $e) {
			$subscriptionSuccessful = FALSE;
			$subscriptionError = $e->getMessage();
		}

		$this->view->assignMultiple(array(
			'subscriptionSuccessful' => $subscriptionSuccessful,
			'subscriptionError' => $subscriptionError,
		));
	}

	/**
	 * Adds the email with state "waiting" to the Tellmatic database and sends
	 * an auth code mail to the user for confirming his subscription.
	 *
	 * @param string $email
	 * @param boolean $acceptConditions
	 * @param array $additionalData
	 * @validate $email NotEmpty, EmailAddress
	 * @validate $acceptConditions Boolean(is="true")
	 * @validate $additionalData Sto\Tellmatic\Validation\AdditionalFieldValidator
	 */
	public function subscribeRequestAction(
		$email,
		/** @noinspection PhpUnusedParameterInspection */
		$acceptConditions,
		array $additionalData = array()
	) {

		$subscriptionSuccessful = TRUE;
		$subscriptionError = '';

		try {
			$subscriptionHandler = $this->getSubscriptionHandler();
			$subscriptionHandler->handleSubscribeRequest($email, $additionalData, 'Subscription form in ' . __METHOD__);
		} catch (\Exception $e) {
			$subscriptionSuccessful = FALSE;
			$subscriptionError = $e->getMessage();
		}

		$this->view->assignMultiple(array(
			'subscriptionSuccessful' => $subscriptionSuccessful,
			'subscriptionError' => $subscriptionError,
		));
	}

	/**
	 * Displays a from with which the user can request the subscription to the newsletter.
	 */
	public function subscribeRequestFormAction() {

		$this->assignSalutationOptionsInView();

		// Handle validation errors.
		if ($this->controllerContext->getRequest()->getOriginalRequest() !== NULL) {
			$this->view->assign('acceptConditions', $this->controllerContext->getRequest()->getOriginalRequest()->getArgument('acceptConditions'));
		}
	}

	/**
	 * When the submitted auth code is valid the email will be unsubscribed.
	 *
	 * @param string $authCode
	 * @param int $historyId
	 * @param int $queueId
	 * @param int $newsletterId
	 * @param boolean $confirmRemoval
	 * @validate $confirmRemoval Boolean(is="true")
	 */
	public function unsubscribeAction(
		$authCode,
		$historyId,
		$queueId,
		$newsletterId,
		/** @noinspection PhpUnusedParameterInspection */
		$confirmRemoval
	) {

		$requestSuccessful = TRUE;
		$errorMessage = '';

		try {
			$authCode = $this->getValidAuthCode($authCode);

			$subscriptionHandler = $this->getSubscriptionHandler();
			$subscriptionHandler->handleUnsubscribeSubmit($authCode->getIdentifier(), $historyId, $queueId, $newsletterId, 'Unsubscribed by auth code in ' . __METHOD__);

			$this->authCodeRepository->clearAssociatedAuthCodes($authCode);

		} catch (\Exception $e) {
			$requestSuccessful = FALSE;
			$errorMessage = $e->getMessage();
		}

		$this->assignSalutationOptionsInView();
		$this->view->assignMultiple(array(
			'requestSuccessful' => $requestSuccessful,
			'errorMessage' => $errorMessage,
			'confirmRemoval' => $this->getSubmittedValueOrDefault('confirmRemoval', TRUE),
		));
	}

	/**
	 * Displays a form where the user can confirm the removal of his email address.
	 *
	 * @param string $authCode
	 */
	public function unsubscribeFormAction($authCode) {

		$requestSuccessful = TRUE;
		$errorMessage = '';

		try {
			$authCode = $this->getValidAuthCode($authCode);
			$this->view->assign('authCode', $authCode);
		} catch (\Exception $e) {
			$requestSuccessful = FALSE;
			$errorMessage = $e->getMessage();
		}

		$this->assignSalutationOptionsInView();
		$this->view->assignMultiple(array(
			'requestSuccessful' => $requestSuccessful,
			'errorMessage' => $errorMessage,
			'confirmRemoval' => $this->getSubmittedValueOrDefault('confirmRemoval', TRUE),
			'historyId' => GeneralUtility::_GET('h_id'),
			'queueId' => GeneralUtility::_GET('q_id'),
			'newsletterId' => GeneralUtility::_GET('nl_id'),
		));
	}

	/**
	 * Updates the personal information in the Tellmatic database.
	 *
	 * @param string $authCode
	 * @param array $additionalData
	 * @validate $additionalData Sto\Tellmatic\Validation\AdditionalFieldValidator
	 */
	public function updateAction($authCode, array $additionalData = array()) {

		$requestSuccessful = TRUE;
		$errorMessage = '';

		try {
			$authCode = $this->getValidAuthCode($authCode);

			$subscriptionHandler = $this->getSubscriptionHandler();
			$subscriptionHandler->handleUpdateSubmit($authCode->getIdentifier(), $additionalData, 'Data updated by auth code in ' . __METHOD__);

			$this->authCodeRepository->clearAssociatedAuthCodes($authCode);

		} catch (\Exception $e) {
			$requestSuccessful = FALSE;
			$errorMessage = $e->getMessage();
		}

		$this->view->assignMultiple(array(
			'requestSuccessful' => $requestSuccessful,
			'errorMessage' => $errorMessage,
		));
	}

	/**
	 * Displays a form where the user can update his personal information.
	 *
	 * @param string $authCode
	 */
	public function updateFormAction($authCode) {

		$requestSuccessful = TRUE;
		$errorMessage = '';

		try {
			$authCode = $this->getValidAuthCode($authCode);
			$this->view->assign('authCode', $authCode);

			$subscribeStateResponse = $this->tellmaticClient->getSubscribeState($authCode->getIdentifier());
			if (!$subscribeStateResponse->getSuccess()) {
				throw new \RuntimeException('Error during Tellmatic request: ' . $subscribeStateResponse->getFailureReason());
			}

			$this->view->assign('email', $authCode->getIdentifier());

			$addressData = $subscribeStateResponse->getAddressData();
			foreach (SubscribeRequest::getAllowedAdditionalFields() as $field => $unused) {
				if (!empty($addressData[$field])) {
					$this->view->assign($field, $this->getSubmittedValueOrDefault('additionalData.' . $field, $addressData[$field]));
				}
			}
		} catch (\Exception $e) {
			$requestSuccessful = FALSE;
			$errorMessage = $e->getMessage();
		}

		$this->assignSalutationOptionsInView();
		$this->view->assignMultiple(array(
			'requestSuccessful' => $requestSuccessful,
			'errorMessage' => $errorMessage,
		));
	}

	/**
	 * Sends an email with a links for updating / removing a subscription.
	 *
	 * @param string $email
	 * @validate $email NotEmpty, EmailAddress
	 */
	public function updateRequestAction($email) {

		$updateRequestSuccessful = TRUE;
		$updateError = '';

		try {
			$subscriptionHandler = $this->getSubscriptionHandler();
			$subscriptionHandler->handleUpdateRequest($email);
		} catch (\Exception $e) {
			$updateRequestSuccessful = FALSE;
			$updateError = $e->getMessage();
		}

		$this->view->assignMultiple(array(
			'requestSuccessful' => $updateRequestSuccessful,
			'errorMessage' => $updateError,
		));
	}

	/**
	 * Displays a form where the user can enter his email address for requesting
	 * links for updating / removing his subscription.
	 */
	public function updateRequestFormAction() {
	}

	/**
	 * Initializes the salutations options in the view.
	 */
	protected function assignSalutationOptionsInView() {

		if (empty($this->settings['salutationOptions'])) {
			return;
		}

		$options = array();
		foreach ($this->settings['salutationOptions'] as $option) {
			$label = $option['label'];
			$value = isset($option['value']) ? $option['value'] : $label;
			$options[$value] = $label;
		}

		$this->view->assign('salutationOptions', $options);
	}

	/**
	 * Checks if a value was submitted in the given property path. If a value is found (not NULL
	 * it will be returned instead of the default value.
	 *
	 * @param string $propertyPath
	 * @param mixed $defaultValue
	 * @return mixed
	 */
	protected function getSubmittedValueOrDefault($propertyPath, $defaultValue) {

		if ($this->controllerContext->getRequest()->getOriginalRequest() === NULL) {
			return $defaultValue;
		}

		$originalArguments = $this->controllerContext->getRequest()->getOriginalRequest()->getArguments();
		$value = \TYPO3\CMS\Extbase\Reflection\ObjectAccess::getPropertyPath($originalArguments, $propertyPath);
		if (is_null($value)) {
			$value = $defaultValue;
		}

		return $value;
	}

	/**
	 * Initializes an instance of the subscription handler.
	 *
	 * @return SubscriptionHandler
	 */
	protected function getSubscriptionHandler() {

		$subscriptionHandler = $this->objectManager->get(SubscriptionHandler::class);
		$subscriptionHandler->setUriBuilder($this->uriBuilder);
		$subscriptionHandler->setAuthCodeContext(static::AUTH_CODE_CONTEXT);

		/** @var TemplateView $view */
		$view = $this->view;
		$subscriptionHandler->setView($view);

		return $subscriptionHandler;
	}

	/**
	 * Fetches the auth code from the database and throws an Exception if none is found.
	 *
	 * @param string $authCode
	 * @return \Tx\Authcode\Domain\Model\AuthCode
	 */
	protected function getValidAuthCode($authCode) {
		$authCodeObject = $this->authCodeRepository->findOneIndependentByAuthCodeAndContext($authCode, static::AUTH_CODE_CONTEXT);
		if (!isset($authCodeObject)) {
			$authCodeObject = $this->authCodeRepository->findOneIndependentByAuthCodeAndContext($authCode, AuthCodeCommandController::AUTH_CODE_CONTEXT);
		}
		if (!isset($authCodeObject)) {
			throw new \InvalidArgumentException('The submitted auth code is invalid.');
		}
		return $authCodeObject;
	}
}