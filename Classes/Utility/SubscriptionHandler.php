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
use Sto\Tellmatic\Tellmatic\Request\UnsubscribeRequest;
use Sto\Tellmatic\Tellmatic\Response\SubscribeStateResponse;
use Sto\Tellmatic\Utility\Exception\SubscribeConfirmInvalidStateException;
use Tx\Authcode\Domain\Model\AuthCode;
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Fluid\View\TemplateView;

/**
 * Handles auth code generation and subscription emails.
 */
class SubscriptionHandler {

	/**
	 * @var string
	 */
	protected $authCodeContext;

	/**
	 * @var \Tx\Authcode\Domain\Repository\AuthCodeRepository
	 * @inject
	 */
	protected $authCodeRepository;

	/**
	 * @var ConfigurationManagerInterface
	 */
	protected $configurationManager;

	/**
	 * @var \TYPO3\CMS\Extbase\Object\ObjectManagerInterface
	 * @inject
	 */
	protected $objectManager;

	/**
	 * @var array
	 */
	protected $settings;

	/**
	 * @var \Sto\Tellmatic\Tellmatic\TellmaticClient
	 * @inject
	 */
	protected $tellmaticClient;

	/**
	 * @var \TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder
	 */
	protected $uriBuilder;

	/**
	 * @var TemplateView
	 */
	protected $view;

	/**
	 * @param \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface $configurationManager
	 */
	public function injectConfigurationManager(ConfigurationManagerInterface $configurationManager) {
		$this->configurationManager = $configurationManager;
		$this->settings = $configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS);
	}

	/**
	 * Processes the subsription confirmation.
	 *
	 * @param string $email
	 * @param string $memo
	 * @throws SubscribeConfirmInvalidStateException
	 */
	public function handleSubscribeConfirmation($email, $memo) {

		$subscribeStateResponse = $this->tellmaticClient->getSubscribeState($email);

		if ($subscribeStateResponse->getSubscribeState() !== SubscribeStateResponse::SUBSCRIBE_STATE_SUBSCRIBED_UNCONFIRMED) {
			throw new SubscribeConfirmInvalidStateException($subscribeStateResponse->getSubscribeState());
		}

		/** @noinspection PhpMethodParametersCountMismatchInspection */
		$addressData = $subscribeStateResponse->getAddressData();
		$this->sendSubscribeRequest($email, $addressData, $memo, 'confirmed');
	}

	/**
	 * Processes a subscribe request that is generated when the user
	 * fills out the subscription form.
	 *
	 * @param string $email
	 * @param array $additionalData
	 * @param string $memo
	 */
	public function handleSubscribeRequest($email, array $additionalData, $memo) {

		$subscribeStateResponse = $this->getSubscriptionState($email);

		switch ($subscribeStateResponse->getSubscribeState()) {
			case SubscribeStateResponse::SUBSCRIBE_STATE_SUBSCRIBED_CONFIRMED:
				$this->handleSubscribeRequestOfConfirmedSubscriber($email);
				break;
			case SubscribeStateResponse::SUBSCRIBE_STATE_SUBSCRIBED_UNCONFIRMED:
				$this->handleSubscribeRequestOfUnconfirmedSubscriber($email, $additionalData, $memo);
				break;
			case SubscribeStateResponse::SUBSCRIBE_STATE_NOT_SUBSCRIBED:
				$this->handleSubscribeRequestOfNonExistingSubscriber($email, $additionalData, $memo);
				break;
		}
	}

	/**
	 * Processes a submit of the unsubscribe form. Unsubscribes the user from the Tellmatic DB.
	 *
	 * @param string $email
	 * @param int $historyId
	 * @param int $queueId
	 * @param int $newsletterId
	 * @param string $memo
	 */
	public function handleUnsubscribeSubmit($email, $historyId, $queueId, $newsletterId, $memo) {
		/** @noinspection PhpMethodParametersCountMismatchInspection */
		$unsubscribeRequest = $this->objectManager->get(UnsubscribeRequest::class, $email);
		$unsubscribeRequest->setDoNotSendEmails(TRUE);
		$unsubscribeRequest->setHistoryId($historyId);
		$unsubscribeRequest->setQueueId($queueId);
		$unsubscribeRequest->setNewsletterId($newsletterId);
		$unsubscribeRequest->getMemo()->addLineToMemo($memo);
		$this->tellmaticClient->sendUnsubscribeRequest($unsubscribeRequest);
	}

	/**
	 * Processes the update request when the user fills out the update request form.
	 *
	 * @param string $email
	 */
	public function handleUpdateRequest($email) {

		$subscribeStateResponse = $this->getSubscriptionState($email);

		switch ($subscribeStateResponse->getSubscribeState()) {
			case SubscribeStateResponse::SUBSCRIBE_STATE_SUBSCRIBED_CONFIRMED:
				$this->handleUpdateRequestOfConfirmedSubscriber($email);
				break;
			case SubscribeStateResponse::SUBSCRIBE_STATE_SUBSCRIBED_UNCONFIRMED:
				$this->handleUpdateRequestOfUnconfirmedSubscriber($email);
				break;
			case SubscribeStateResponse::SUBSCRIBE_STATE_NOT_SUBSCRIBED:
				$this->handleUpdateRequestOfNonExistingSubscriber($email);
				break;
		}
	}

	/**
	 * Processes the submit of the update form. Updates the data in the Tellmatic database.
	 *
	 * @param string $email
	 * @param array $additionalData
	 * @param string $memo
	 */
	public function handleUpdateSubmit($email, array $additionalData, $memo) {
		$this->sendSubscribeRequest($email, $additionalData, $memo, 'confirmed');
	}

	/**
	 * @param string $authCodeContext
	 */
	public function setAuthCodeContext($authCodeContext) {
		$this->authCodeContext = $authCodeContext;
	}

	/**
	 * @param mixed $uriBuilder
	 */
	public function setUriBuilder($uriBuilder) {
		$this->uriBuilder = $uriBuilder;
	}

	/**
	 * @param TemplateView $view
	 */
	public function setView(TemplateView $view) {
		$this->view = $view;
	}

	/**
	 * Usees the tinyurls extension to generate
	 *
	 * @param string $action
	 * @param string $authCode
	 * @return string
	 */
	protected function generateTinyUrl($action, $authCode) {

		if (empty($this->settings['authCodeUrlSpeaking'])) {
			return NULL;
		}

		if (empty($this->settings['authCodeUrlTempate'][$action])) {
			return NULL;
		}

		$authCodeUrl['value'] = $this->settings['authCodeUrlTempate'][$action];
		$authCodeUrl['insertData'] = 1;
		$authCodeUrl = $this->configurationManager->getContentObject()->cObjGetSingle('TEXT', $authCodeUrl);

		if (empty($authCodeUrl)) {
			return NULL;
		}

		$authCodeUrl = str_replace('###authcode###', $authCode, $authCodeUrl);

		return $authCodeUrl;
	}

	/**
	 * Returns a template view instance that can be used for email generation.
	 *
	 * @param string $mailTemplate
	 * @return TemplateView
	 */
	protected function getMailView($mailTemplate) {
		/** @var TemplateView $view */
		$view = clone($this->view);
		$view->setTemplatePathAndFilename(GeneralUtility::getFileAbsFileName('EXT:tellmatic/Resources/Private/Templates/Subscribe/Mail/' . $mailTemplate . '.txt'));
		return $view;
	}

	/**
	 * Fetches the subscription state / data of an email address.
	 *
	 * @param string $email
	 * @return SubscribeStateResponse
	 */
	protected function getSubscriptionState($email) {
		$subscribeStateResponse = $this->tellmaticClient->getSubscribeState($email);
		return $subscribeStateResponse;
	}

	/**
	 * Subscribe requests of confirmed subscribers are handled as update requests.
	 *
	 * @param string $email
	 */
	protected function handleSubscribeRequestOfConfirmedSubscriber($email) {
		$this->handleUpdateRequestOfConfirmedSubscriber($email);
	}

	/**
	 * Adds the email / subscription data to the tellmatic database with state "waiting".
	 *
	 * Sends an auth code email with which the subscriber can confirm his subscription.
	 *
	 * @param string $email
	 * @param array $additionalData
	 * @param string $memo
	 */
	protected function handleSubscribeRequestOfNonExistingSubscriber($email, array $additionalData, $memo) {

		$subject = 'Ihre Newsletteranmeldung';

		$this->sendSubscribeRequest($email, $additionalData, $memo, 'waiting');

		$authCode = $this->objectManager->get(AuthCode::class);
		$this->authCodeRepository->generateIndependentAuthCode($authCode, $email, $this->authCodeContext);

		$mailView = $this->getMailView('ConfirmSubscription');
		$this->sendAuthCodeMail('subscribeConfirm', $authCode, $subject, $mailView);
	}

	/**
	 * Subscribe requests of unconfirmed subscribers are handled as new subscribers.
	 *
	 * @param string $email
	 * @param array $additionalData
	 * @param string $memo
	 */
	protected function handleSubscribeRequestOfUnconfirmedSubscriber($email, array $additionalData, $memo) {
		$this->handleSubscribeRequestOfNonExistingSubscriber($email, $additionalData, $memo);
	}

	/**
	 * Sends an auth code mail in which the user finds links to update / remove his subscription.
	 *
	 * @param string $email
	 */
	protected function handleUpdateRequestOfConfirmedSubscriber($email) {

		$subject = 'Ihre Newsletteranmeldung';

		$authCode = $this->objectManager->get(AuthCode::class);
		$this->authCodeRepository->generateIndependentAuthCode($authCode, $email, $this->authCodeContext);

		$mailView = $this->getMailView('UpdateSubscription');
		$this->sendAuthCodeMail('updateForm', $authCode, $subject, $mailView, TRUE);
	}

	/**
	 * Sends a mail without any auth code links and a hint that the given email is not subscribed.
	 *
	 * @param string $email
	 */
	protected function handleUpdateRequestOfNonExistingSubscriber($email) {
		$subject = 'Ihre Newsletteranmeldung';
		$view = $this->getMailView('NoSubscription');
		$view->assign('email', $email);
		$this->sendMail($email, $subject, $view->render());
	}

	/**
	 * Generates an auth code mail with which the user can confirm his subscription.
	 *
	 * @param string $email
	 */
	protected function handleUpdateRequestOfUnconfirmedSubscriber($email) {

		$subject = 'Ihre Newsletteranmeldung';

		$authCode = $this->objectManager->get(AuthCode::class);
		$this->authCodeRepository->generateIndependentAuthCode($authCode, $email, $this->authCodeContext);

		$mailView = $this->getMailView('ConfirmSubscription');
		$this->sendAuthCodeMail('subscribeConfirm', $authCode, $subject, $mailView);
	}

	/**
	 * Generates an action URI with the given auth code.
	 *
	 * If the action is "updateForm" an additional unsubscribe URI will be generated.
	 *
	 * These variables are assigned in the view:
	 * actionUrl - contains the URI to the given action.
	 * unsubscribeUrl - only if $buildUnsubscribeUrl is TRUE.
	 *
	 * @param string $action
	 * @param AuthCode $authCode
	 * @param string $subject
	 * @param \TYPO3\CMS\Extbase\Mvc\View\ViewInterface $view
	 * @param bool $buildUnsubscribeUrl
	 */
	protected function sendAuthCodeMail($action, $authCode, $subject, $view, $buildUnsubscribeUrl = FALSE) {

		$email = $authCode->getIdentifier();
		$view->assign('email', $email);

		$actionUrl = $this->uriBuilder
			->reset()
			->setCreateAbsoluteUri(TRUE)
			->setUseCacheHash(FALSE)
			->uriFor($action, array('authCode' => $authCode->getAuthCode()));
		$actionUrlTiny = $this->generateTinyUrl($action, $authCode->getAuthCode());
		$view->assign('actionUrl', $actionUrlTiny ? $actionUrlTiny : $actionUrl);

		if ($buildUnsubscribeUrl) {
			$unsubscribeUrl = $this->uriBuilder
				->reset()
				->setCreateAbsoluteUri(TRUE)
				->setUseCacheHash(FALSE)
				->uriFor('unsubscribeForm', array('authCode' => $authCode->getAuthCode()));
			$unsubscribeUrlTiny = $this->generateTinyUrl('unsubscribeForm', $authCode->getAuthCode());
			$view->assign('unsubscribeUrl', $unsubscribeUrlTiny ? $unsubscribeUrlTiny : $unsubscribeUrl);
		}

		$mailtext = $view->render();

		$this->sendMail($email, $subject, $mailtext);
	}

	/**
	 * Sends an email with the given parameters.
	 *
	 * @param string $email
	 * @param string $subject
	 * @param string $mailtext
	 */
	protected function sendMail($email, $subject, $mailtext) {
		$mail = $this->objectManager->get(MailMessage::class);
		$mail->setFrom($this->settings['mail']['from']);
		$mail->setTo($email);
		$mail->setSubject($subject);
		$mail->addPart($mailtext);
		$mail->send();
	}

	/**
	 * Sens a subscribe request to Tellmatic with the given parameters.
	 *
	 * @param string $email
	 * @param array $additionalData
	 * @param string $memo
	 * @param string $status
	 */
	protected function sendSubscribeRequest($email, $additionalData, $memo, $status) {

		/** @noinspection PhpMethodParametersCountMismatchInspection */
		$subscribeRequest = $this->objectManager->get(SubscribeRequest::class, $email);

		$additionalFields = array();
		foreach (SubscribeRequest::getAllowedAdditionalFields() as $fieldName => $unused) {
			if (!empty($additionalData[$fieldName])) {
				$additionalFields[$fieldName] = $additionalData[$fieldName];
			}
		}
		$subscribeRequest->setAdditionalFields($additionalFields);

		$subscribeRequest->setDoNotSendEmails(TRUE);
		$subscribeRequest->setOverrideAddressStatus($status);
		$subscribeRequest->getMemo()->addLineToMemo($memo);
		$this->tellmaticClient->sendSubscribeRequest($subscribeRequest);
	}
}