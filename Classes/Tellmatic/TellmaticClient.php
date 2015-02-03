<?php
namespace Sto\Tellmatic\Tellmatic;

/*                                                                        *
 * This script belongs to the TYPO3 extension "tellmatic".                *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License, either version 3 of the   *
 * License, or (at your option) any later version.                        *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

class TellmaticClient {

	/**
	 * This URL overrides the default URL from the extension configuration
	 *
	 * @var string
	 */
	protected $customUrl;

	/**
	 * @var \Sto\Tellmatic\Utility\ExtensionConfiguration
	 * @inject
	 */
	protected $extensionConfiguration;

	/**
	 * Default URLs to the different APIs
	 *
	 * @var array
	 */
	protected $defaultUrls = array(
		'subscribeRequest' => 'api_subscribe.php',
		'getSubscribeState' => 'api_subscribe_sate.php',
	);

	/**
	 * @var \TYPO3\CMS\Extbase\Object\ObjectManagerInterface
	 * @inject
	 */
	protected $objectManager;

	/**
	 * This array will be used to check if all provided additional
	 * fields are valid.
	 *
	 * @var array
	 */
	protected $allowedAdditionalFields = array(
		'f0' => '',
		'f1' => '',
		'f2' => '',
		'f3' => '',
		'f4' => '',
		'f5' => '',
		'f6' => '',
		'f7' => '',
		'f8' => '',
		'f9' => '',
	);

	/**
	 * @var \TYPO3\CMS\Core\Http\HttpRequest
	 */
	protected $httpRequest;

	/**
	 * The class that should be used as response
	 *
	 * @var string
	 */
	protected $responseClass = 'Sto\\Tellmatic\\Tellmatic\TellmaticResponse';

	/**
	 * Configuration that should be used for the HTTP request
	 *
	 * @var array
	 */
	protected $httpRequestConfiguration = array(
		'follow_redirects' => TRUE,
	);

	/**
	 * If no HTTP request was set externally it will be created.
	 *
	 * Additionally the configuration of the HTTP request will be
	 * initialized.
	 */
	protected function initializeHttpRequest() {

		if (!isset($this->httpRequest)) {
			$this->httpRequest = $this->objectManager->get('TYPO3\\CMS\\Core\\Http\\HttpRequest');
		}

		$this->httpRequest->setConfiguration($this->httpRequestConfiguration);
	}

	/**
	 * @param string $email
	 * @return \Sto\Tellmatic\Tellmatic\SubscribeStateResponse
	 * @throws \RuntimeException
	 */
	public function getSubscribeState($email) {

		$this->initializeHttpRequest();
		$this->httpRequest->setUrl($this->getUrl('getSubscribeState'));

		if (!\TYPO3\CMS\Core\Utility\GeneralUtility::validEmail($email)) {
			throw new \RuntimeException('The provided email address is invalid');
		}

		$this->responseClass = 'Sto\\Tellmatic\Tellmatic\\SubscribeStateResponse';
		$this->httpRequest->addPostParameter('email', $email);

		return $this->doRequestAndGenerateResponse();
	}

	/**
	 * @param string $email The email address that should be subscribed
	 * @param array $additionalFields Optional array containing additional field data (f0 - f9)
	 * @throws \RuntimeException
	 * @return \Sto\Tellmatic\Tellmatic\TellmaticResponse
	 */
	public function sendSubscribeRequest($email, $additionalFields = array()) {

		$this->initializeHttpRequest();
		$this->httpRequest->setUrl($this->getUrl('subscribeRequest'));

		if (!\TYPO3\CMS\Core\Utility\GeneralUtility::validEmail($email)) {
			throw new \RuntimeException('The provided email address is invalid');
		}

		$this->httpRequest->addPostParameter('email', $email);

		$invalidAdditionalFields = array_diff_key($additionalFields, $this->allowedAdditionalFields);
		if (count($invalidAdditionalFields)) {
			throw new \RuntimeException('You provided invalid additional Fields: ' . implode(', ', array_keys($invalidAdditionalFields)));
		}

		foreach ($additionalFields as $name => $value) {
			$this->httpRequest->addPostParameter($name, $value);
		}

		return $this->doRequestAndGenerateResponse();
	}

	/**
	 * Sets a custom URL that should be used for the next request
	 *
	 * @param string $customUrl
	 */
	public function setCustomUrl($customUrl) {
		if (!empty($customUrl)) {
			$this->customUrl = $customUrl;
		}
	}

	/**
	 * Setter for the HTTP request that should be used.
	 *
	 * @param \TYPO3\CMS\Core\Http\HttpRequest $httpRequest
	 */
	public function setHttpRequest($httpRequest) {
		$this->httpRequest = $httpRequest;
	}

	/**
	 * @return \Sto\Tellmatic\Tellmatic\TellmaticResponse
	 * @throws \RuntimeException
	 */
	protected function doRequestAndGenerateResponse() {

		/** @var \Sto\Tellmatic\Tellmatic\TellmaticResponse $tellmaticResponse */
		$tellmaticResponse = $this->objectManager->get($this->responseClass);

		$this->httpRequest->setMethod('POST');
		$httpResponse = $this->httpRequest->send();

		$responseStatus = $httpResponse->getStatus();
		if ($responseStatus !== 200) {
			throw new \RuntimeException(sprintf('HTTP error code %d: %s (requested URL was: %s', $responseStatus, $httpResponse->getReasonPhrase(), $httpResponse->getEffectiveUrl()));
		}

		$responseBody = $httpResponse->getBody();

		$parsedResponse = json_decode($responseBody, TRUE);
		if (!isset($parsedResponse)) {
			throw new \RuntimeException('JSON parser error: ' . json_last_error() . 'The parsed string was: ' . $responseBody, 1377802585);
		}

		if ($parsedResponse['success']) {
			$tellmaticResponse->setRequestSuccessful();
			$tellmaticResponse->processAdditionalResponseData($parsedResponse);
		} else {
			$tellmaticResponse->setFailureFromJsonResponse($parsedResponse);
		}

		return $tellmaticResponse;
	}

	/**
	 * Generates the URL for the given request type
	 *
	 * @param string $requestType
	 * @return string
	 */
	protected function getUrl($requestType) {

		if (isset($this->customUrl)) {
			$url = $this->customUrl;
		} else {
			$baseUrl = $this->extensionConfiguration->getTellmaticUrl();

			if (empty($baseUrl) || parse_url($baseUrl) === FALSE) {
				throw new \RuntimeException('No valid base URL was configured.');
			}

			$url = $baseUrl . $this->defaultUrls[$requestType];
		}

		return $url;
	}
}