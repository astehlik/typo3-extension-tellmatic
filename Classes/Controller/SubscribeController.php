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
use Sto\Tellmatic\Tellmatic\Response\SubscribeStateResponse;
use Sto\Tellmatic\Utility\Exception\InvalidAuthCodeException;
use Sto\Tellmatic\Utility\Exception\UpdateInvalidStateException;
use Sto\Tellmatic\Utility\SubscriptionHandler;
use Sto\Tellmatic\Utility\TellmaticArgument;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Validation\Error as ValidationError;
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
	 * @var \TYPO3\CMS\Core\Log\Logger
	 */
	protected $logger;

	/**
	 * @var \Sto\Tellmatic\Tellmatic\TellmaticClient
	 * @inject
	 */
	protected $tellmaticClient;

	/**
	 * @param \TYPO3\CMS\Core\Log\LogManager $logManager
	 */
	public function injectLogManager(\TYPO3\CMS\Core\Log\LogManager $logManager) {
		$this->logger = $logManager->getLogger(__CLASS__);
	}

	/**
	 * When the submitted auth code is valid the email state will be set to "confirmed"
	 * in the Tellmatic database.
	 *
	 * @param string $authCode
	 */
	public function subscribeConfirmAction($authCode) {

		try {
			$authCode = $this->getValidAuthCode($authCode);

			$subscriptionHandler = $this->getSubscriptionHandler();
			$subscriptionHandler->handleSubscribeConfirmation($authCode->getIdentifier(), 'Subscription confirmed by auth code in ' . __METHOD__);

			$this->authCodeRepository->clearAssociatedAuthCodes($authCode);

		} catch (\Exception $e) {
			$this->handleException($e);
		}
	}

	/**
	 * Adds the email with state "waiting" to the Tellmatic database and sends
	 * an auth code mail to the user for confirming his subscription.
	 *
	 * @param string $email
	 * @param boolean $acceptConditions
	 * @param array $additionalData
	 * @validate $email NotEmpty, EmailAddress
	 * @validate $acceptConditions \Sto\Tellmatic\Validation\AcceptConditionsValidator
	 * @validate $additionalData \Sto\Tellmatic\Validation\AdditionalFieldValidator
	 */
	public function subscribeRequestAction(
		$email,
		/** @noinspection PhpUnusedParameterInspection */
		$acceptConditions = FALSE,
		array $additionalData = array()
	) {

		$subscriptionSuccessful = TRUE;
		$subscriptionError = '';

		try {
			$subscriptionHandler = $this->getSubscriptionHandler();
			$subscriptionHandler->handleSubscribeRequest($email, $additionalData, 'Subscription form in ' . __METHOD__);
		} catch (\Exception $e) {
			$this->handleException($e);
		}

        if($this->settings['mail']['adminNotifications']['onSubscribeRequest']) {

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

		// Handle validation errors.
		if (
			$this->controllerContext->getRequest()->getOriginalRequest() !== NULL
			&& $this->controllerContext->getRequest()->getOriginalRequest()->hasArgument('acceptConditions')
		) {
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

		try {
			$authCode = $this->getValidAuthCode($authCode);

			$subscriptionHandler = $this->getSubscriptionHandler();
			$subscriptionHandler->handleUnsubscribeSubmit($authCode->getIdentifier(), $historyId, $queueId, $newsletterId, 'Unsubscribed by auth code in ' . __METHOD__);

			$this->authCodeRepository->clearAssociatedAuthCodes($authCode);

		} catch (\Exception $e) {
			$this->handleException($e);
		}

		$this->view->assign('confirmRemoval', $this->getSubmittedValueOrDefault('confirmRemoval', TRUE));
	}

	/**
	 * Displays a form where the user can confirm the removal of his email address.
	 *
	 * @param string $authCode
	 */
	public function unsubscribeFormAction($authCode) {

		try {
			$authCode = $this->getValidAuthCode($authCode);
			$this->view->assign('authCode', $authCode);
		} catch (\Exception $e) {
			$this->handleException($e);
		}

		$this->view->assignMultiple(array(
			'confirmRemoval' => $this->getSubmittedValueOrDefault('confirmRemoval', TRUE),
			'historyId' => GeneralUtility::_GET('h_id'),
			'queueId' => GeneralUtility::_GET('q_id'),
			'newsletterId' => GeneralUtility::_GET('nl_id'),
		));
	}

	/**
	 * Updates the personal information in the Tellmatic database.
	 *
	 * @param string $email
	 * @param string $authCode
	 * @param array $additionalData
	 * @validate $email NotEmpty, EmailAddress
	 * @validate $additionalData Sto\Tellmatic\Validation\AdditionalFieldValidator
	 */
	public function updateAction($email, $authCode, array $additionalData = array()) {

		try {
			$authCode = $this->getValidAuthCode($authCode);

			if ($email !== $authCode->getIdentifier()) {
				$subscriptionHandler = $this->getSubscriptionHandler();
				$subscriptionHandler->handleUnsubscribeSubmit($authCode->getIdentifier(), 0, 0, 0, 'Unsubscribed because of email address change in ' . __METHOD__);
				$subscriptionHandler->handleSubscribeRequest($email, $additionalData, 'New subscription because of email address change in ' . __METHOD__);
			} else {
				$subscriptionHandler = $this->getSubscriptionHandler();
				$subscriptionHandler->handleUpdateSubmit($authCode->getIdentifier(), $additionalData, 'Data updated by auth code in ' . __METHOD__);
			}

			$this->authCodeRepository->clearAssociatedAuthCodes($authCode);

		} catch (\Exception $e) {
			$this->handleException($e);
		}
	}

	/**
	 * Displays a form where the user can update his personal information.
	 *
	 * @param string $authCode
	 */
	public function updateFormAction($authCode) {

		try {
			$authCode = $this->getValidAuthCode($authCode);
			$this->view->assign('authCode', $authCode);

			$subscribeStateResponse = $this->tellmaticClient->getSubscribeState($authCode->getIdentifier());

			if ($subscribeStateResponse->getSubscribeState() === SubscribeStateResponse::SUBSCRIBE_STATE_NOT_SUBSCRIBED) {
				throw new UpdateInvalidStateException($subscribeStateResponse->getSubscribeState());
			}

			$this->view->assign('email', $this->getSubmittedValueOrDefault('email', $authCode->getIdentifier()));

			if (!empty($this->settings['additionalFields']) && is_array($this->settings['additionalFields'])) {
				$addressData = $subscribeStateResponse->getAddressData();
				foreach ($this->settings['additionalFields'] as $fieldName => &$fieldSettings) {
					if (!empty($addressData[$fieldName])) {
						$fieldSettings['value'] = $this->getSubmittedValueOrDefault('additionalData.' . $fieldName, $addressData[$fieldName]);
					}
				}
				$this->view->assign('settings', $this->settings);
			}

		} catch (\Exception $e) {
			$this->handleException($e);
		}
	}

	/**
	 * Sends an email with a links for updating / removing a subscription.
	 *
	 * @param string $email
	 * @validate $email NotEmpty, EmailAddress
	 */
	public function updateRequestAction($email) {

		try {
			$subscriptionHandler = $this->getSubscriptionHandler();
			$subscriptionHandler->handleUpdateRequest($email);
		} catch (\Exception $e) {
			$this->handleException($e);
		}
	}

	/**
	 * Displays a form where the user can enter his email address for requesting
	 * links for updating / removing his subscription.
	 */
	public function updateRequestFormAction() {
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
	 * @throws InvalidAuthCodeException
	 */
	protected function getValidAuthCode($authCode) {
		$authCodeObject = $this->authCodeRepository->findOneIndependentByAuthCodeAndContext($authCode, static::AUTH_CODE_CONTEXT);
		if (!isset($authCodeObject)) {
			$authCodeObject = $this->authCodeRepository->findOneIndependentByAuthCodeAndContext($authCode, AuthCodeCommandController::AUTH_CODE_CONTEXT);
		}
		if (!isset($authCodeObject)) {
			throw new InvalidAuthCodeException();
		}
		return $authCodeObject;
	}

	/**
	 * Initializes the tellmatic argument validation error based on the given Exception.
	 *
	 * @param \Exception $exception
	 */
	protected function handleException(\Exception $exception) {

		if (isset($this->logger)) {
			$this->logger->critical('An error has occured during tellmatic subscription: ' . $exception->getMessage() . LF . $exception->getTraceAsString());
		}

		/** @noinspection PhpMethodParametersCountMismatchInspection */
		$tellmaticArgument = $this->objectManager->get(TellmaticArgument::class, 'tellmatic', 'Text');
		$this->arguments->addArgument($tellmaticArgument);

		$argumentValidationResults = $this->objectManager->get(\TYPO3\CMS\Extbase\Error\Result::class);
		$argumentValidationResults->addError(new ValidationError($exception->getMessage(), $exception->getCode()));
		$tellmaticArgument->setValidationResults($argumentValidationResults);

		if (
			$this->request instanceof \TYPO3\CMS\Extbase\Mvc\Web\Request
			&& $this->request->getReferringRequest() !== NULL
		) {
			$referringRequest = $this->request->getReferringRequest();
			if (
				$referringRequest->getControllerActionName() !== $this->request->getControllerActionName()
				|| $referringRequest->getControllerName() !== $this->request->getControllerName()
			) {
				$this->errorAction();
			}
		}

		$this->view->assign('hasErrors', TRUE);
		$this->request->setOriginalRequestMappingResults($this->arguments->getValidationResults());
	}
}