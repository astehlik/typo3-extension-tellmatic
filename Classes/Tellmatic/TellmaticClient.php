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

use Sto\Tellmatic\Http\HttpRequestFactory;
use Sto\Tellmatic\Http\HttpRequestInterface;
use Sto\Tellmatic\Tellmatic\Exception\InvalidResponseException;
use Sto\Tellmatic\Tellmatic\Exception\TellmaticException;
use Sto\Tellmatic\Tellmatic\Request\AddressCountRequest;
use Sto\Tellmatic\Tellmatic\Request\AddressSearchRequest;
use Sto\Tellmatic\Tellmatic\Request\SetCodeRequest;
use Sto\Tellmatic\Tellmatic\Request\SubscribeRequest;
use Sto\Tellmatic\Tellmatic\Request\TellmaticRequestInterface;
use Sto\Tellmatic\Tellmatic\Request\UnsubscribeRequest;
use Sto\Tellmatic\Tellmatic\Response\AddressCountResponse;
use Sto\Tellmatic\Tellmatic\Response\AddressSearchResponse;
use Sto\Tellmatic\Tellmatic\Response\SubscribeStateResponse;
use Sto\Tellmatic\Tellmatic\Response\TellmaticResponse;
use Sto\Tellmatic\Utility\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Client for executing API request on a Tellmatic server.
 */
class TellmaticClient
{
    /**
     * Default URLs to the different APIs
     *
     * @var array
     */
    protected $defaultUrls = [
        'subscribe' => 'api_subscribe.php',
        'unsubscribe' => 'api_unsubscribe.php',
        'getSubscribeState' => 'api_subscribe_state.php',
        'setCode' => 'api_set_code.php',
        'addressCount' => 'api_address_count.php',
        'addressSearch' => 'api_address_search.php',
    ];

    /**
     * @var ExtensionConfiguration
     */
    protected $extensionConfiguration;

    /**
     * @var HttpRequestFactory
     */
    protected $httpRequestFactory;

    public function injectExtensionConfiguration(ExtensionConfiguration $extensionConfiguration)
    {
        $this->extensionConfiguration = $extensionConfiguration;
    }

    public function injectHttpRequestFactory(HttpRequestFactory $httpRequestFactory)
    {
        $this->httpRequestFactory = $httpRequestFactory;
    }

    /**
     * @param string $email
     * @return \Sto\Tellmatic\Tellmatic\Response\SubscribeStateResponse
     * @throws \InvalidArgumentException
     */
    public function getSubscribeState($email)
    {
        $request = $this->initializeHttpRequest();
        $request->setUrl($this->getUrl('getSubscribeState'));

        if (!GeneralUtility::validEmail($email)) {
            throw new \InvalidArgumentException('The provided email address is invalid');
        }

        $request->addPostParameter('email', $email);

        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->doRequestAndGenerateResponse($request, new SubscribeStateResponse());
    }

    /**
     * @param AddressCountRequest $addressCountRequest
     * @return AddressCountResponse
     */
    public function sendAddressCountRequest(AddressCountRequest $addressCountRequest)
    {
        $request = $this->initializeRequest('addressCount', $addressCountRequest);

        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->doRequestAndGenerateResponse($request, new AddressCountResponse());
    }

    /**
     * @param AddressSearchRequest $addressSearchRequest
     * @return AddressSearchResponse
     */
    public function sendAddressSearchRequest(AddressSearchRequest $addressSearchRequest)
    {
        $request = $this->initializeRequest('addressSearch', $addressSearchRequest);

        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->doRequestAndGenerateResponse($request, new AddressSearchResponse());
    }

    /**
     * @param SetCodeRequest $setCodeRequest
     * @return TellmaticResponse
     */
    public function sendSetCodeRequest(SetCodeRequest $setCodeRequest)
    {
        $request = $this->initializeRequest('setCode', $setCodeRequest);
        return $this->doRequestAndGenerateResponse($request, new TellmaticResponse());
    }

    /**
     * Sends a subscribe request to the Tellmatic server.
     *
     * @param SubscribeRequest $subscribeRequest
     * @return TellmaticResponse
     */
    public function sendSubscribeRequest(SubscribeRequest $subscribeRequest)
    {
        $request = $this->initializeRequest('subscribe', $subscribeRequest);
        return $this->doRequestAndGenerateResponse($request, new TellmaticResponse());
    }

    /**
     * @param UnsubscribeRequest $unsubscribeRequest
     * @return TellmaticResponse
     */
    public function sendUnsubscribeRequest(UnsubscribeRequest $unsubscribeRequest)
    {
        $request = $this->initializeRequest('unsubscribe', $unsubscribeRequest);
        return $this->doRequestAndGenerateResponse($request, new TellmaticResponse());
    }

    /**
     * Adds a hmac POST parameter based on the serialized POST parameters array.
     *
     * @param HttpRequestInterface $request
     */
    protected function addApiKeyToPostParameters(HttpRequestInterface $request)
    {
        $postParameters = $request->getPostParameters();

        $encryptionKeyBackup = $GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey'];
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey'] = $this->getTellmaticApiKey();

        $request->addPostParameter(
            'apiKey',
            GeneralUtility::hmac(serialize($postParameters), 'TellmaticAPI')
        );

        $GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey'] = $encryptionKeyBackup;
    }

    /**
     * @param HttpRequestInterface $httpRequest
     * @param TellmaticResponse $tellmaticResponse
     * @return TellmaticResponse
     */
    protected function doRequestAndGenerateResponse(
        HttpRequestInterface $httpRequest,
        TellmaticResponse $tellmaticResponse
    ) {
        $this->addApiKeyToPostParameters($httpRequest);

        $httpResponse = $httpRequest->send();

        $responseStatus = $httpResponse->getStatusCode();
        if ($responseStatus !== 200) {
            throw new InvalidResponseException(
                sprintf(
                    'HTTP error code %d: %s (requested URL was: %s',
                    $responseStatus,
                    $httpResponse->getReasonPhrase(),
                    $httpResponse->getEffectiveUrl()
                )
            );
        }

        $responseBody = $httpResponse->getBodyContents();

        $parsedResponse = json_decode($responseBody, true);
        if (!isset($parsedResponse)) {
            throw new InvalidResponseException(
                'JSON parser error: ' . json_last_error() . 'The parsed string was: ' . $responseBody
            );
        }

        if (!isset($parsedResponse['success'])) {
            throw new InvalidResponseException('The success property is missing in the response.');
        }

        if ($parsedResponse['success']) {
            $tellmaticResponse->processAdditionalResponseData($parsedResponse);
        } else {
            $this->handleTellmaticError($parsedResponse);
        }

        return $tellmaticResponse;
    }

    /**
     * Reads the API key from the extension configuration.
     *
     * Moved to a seperate method for unit testing.
     *
     * @return string
     */
    protected function getTellmaticApiKey()
    {
        return $this->extensionConfiguration->getTellmaticApiKey();
    }

    /**
     * Generates the URL for the given request type
     *
     * @param string $requestType
     * @return string
     */
    protected function getUrl($requestType)
    {
        $baseUrl = $this->extensionConfiguration->getTellmaticUrl();

        if (empty($baseUrl) || parse_url($baseUrl) === false) {
            throw new \RuntimeException('No valid base URL was configured: ' . $baseUrl);
        }

        $url = $baseUrl . $this->defaultUrls[$requestType];

        return $url;
    }

    /**
     * Throws an exception depending on the error code provided by Tellmatic.
     *
     * @param array $response
     * @throws Exception\TellmaticException
     * @throws InvalidResponseException
     * @throws \Exception
     */
    protected function handleTellmaticError(array $response)
    {
        if (empty($response['failure_code'])) {
            throw new InvalidResponseException('The failure_code property is missing in the response.');
        }

        if (empty($response['failure_reason'])) {
            throw new InvalidResponseException('The failure_reason property is missing in the response.');
        }

        $exceptionClass = 'Sto\\Tellmatic\\Tellmatic\\Exception\\' .
            GeneralUtility::underscoredToUpperCamelCase($response['failure_code']) . 'Exception';

        if (class_exists($exceptionClass)) {
            throw new $exceptionClass($response);
        } else {
            throw new TellmaticException($response);
        }
    }

    /**
     * If no HTTP request was set externally it will be created.
     *
     * Additionally the configuration of the HTTP request will be
     * initialized.
     *
     * @return HttpRequestInterface
     */
    protected function initializeHttpRequest()
    {
        return $this->httpRequestFactory->createHttpRequest();
    }

    /**
     * Initializes the HttpRequest, the URL and the response class.
     *
     * @param string $requestType
     * @param TellmaticRequestInterface $request
     * @return HttpRequestInterface
     */
    protected function initializeRequest($requestType, TellmaticRequestInterface $request)
    {
        $httpRequest = $this->initializeHttpRequest();

        $httpRequest->setUrl($this->getUrl($requestType));
        $request->initializeHttpRequest($httpRequest);

        return $httpRequest;
    }
}
