<?php

namespace Sto\Tellmatic\Tests\Unit\Tellmatic;

/*                                                                        *
 * This script belongs to the TYPO3 extension "tellmatic".                *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License, either version 3 of the   *
 * License, or (at your option) any later version.                        *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use Prophecy\Argument;
use Sto\Tellmatic\Http\HttpRequestFactory;
use Sto\Tellmatic\Http\HttpRequestInterface;
use Sto\Tellmatic\Http\HttpResponseInterface;
use Sto\Tellmatic\Tellmatic\Exception\InvalidEmailException;
use Sto\Tellmatic\Tellmatic\Exception\InvalidResponseException;
use Sto\Tellmatic\Tellmatic\Request\SubscribeRequest;
use Sto\Tellmatic\Tellmatic\TellmaticClient;
use Sto\Tellmatic\Utility\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Test for the Tellmatic client and the API.
 */
class TellmaticClientTest extends \Nimut\TestingFramework\TestCase\UnitTestCase
{
    /**
     * Failure code when a page is not found during a HTTP request.
     *
     * @const
     */
    const HTTP_STATUS_CODE_NOT_FOUND = 404;

    /**
     * HTTP return code for successful requests.
     *
     * @const
     */
    const HTTP_STATUS_CODE_OK = 200;

    /**
     * @var string
     */
    protected $responseDataValid;

    /**
     * @var TellmaticClient|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $tellmaticClient;

    /**
     * @var
     */
    protected $testEmailInvalid = 'no email';

    /**
     * @var
     */
    protected $testEmailValid = 'tellmatic-test@swebhosting.de';

    /**
     * @var string
     */
    protected $testUrlInvalid = 'a nonsense URL';

    /**
     * @var string
     */
    protected $testUrlValid = 'http://tellmatic-test.swebhosting.de/';

    private $requestProphecy;

    private $responseProphecy;

    /**
     * Initializes the Tellmatic client.
     */
    public function setUp()
    {
        $this->responseDataValid = json_encode(
            ['success' => true]
        );

        $this->responseProphecy = $this->prophesize(HttpResponseInterface::class);

        $this->requestProphecy = $this->prophesize(HttpRequestInterface::class);
        $this->requestProphecy->send()->willReturn($this->responseProphecy->reveal());

        $requestFactory = $this->prophesize(HttpRequestFactory::class);
        $requestFactory->createHttpRequest()->willReturn($this->requestProphecy->reveal());

        $extensionConfiguration = $this->prophesize(ExtensionConfiguration::class);
        $extensionConfiguration->getTellmaticUrl()->willReturn($this->testUrlValid);
        $extensionConfiguration->getTellmaticApiKey()->willReturn('testkey');

        $this->tellmaticClient = new TellmaticClient();
        $this->tellmaticClient->injectExtensionConfiguration($extensionConfiguration->reveal());
        $this->tellmaticClient->injectHttpRequestFactory($requestFactory->reveal());
    }

    /**
     * @test
     */
    public function sendSubscribeRequestReturnsTrueOnSuccess()
    {
        $this->initializeRequestProphecy();

        $this->responseProphecy->getStatusCode()->willReturn(static::HTTP_STATUS_CODE_OK);
        $this->responseProphecy->getBodyContents()->willReturn($this->responseDataValid);

        /** @var SubscribeRequest $subscribeRequest */
        $subscribeRequest = GeneralUtility::makeInstance(SubscribeRequest::class, $this->testEmailValid);
        $tellmaticResponse = $this->tellmaticClient->sendSubscribeRequest($subscribeRequest);

        $this->assertNotNull($tellmaticResponse);
    }

    /**
     * @test
     * @dataProvider sendSubscribeRequestReturnsValidFailureCodeDataProvider
     * @param string $responseData
     * @param $expectedFailureCode
     */
    public function sendSubscribeRequestReturnsValidFailureCode($responseData, $expectedFailureCode)
    {
        $this->initializeRequestProphecy();

        $this->responseProphecy->getStatusCode()->willReturn(static::HTTP_STATUS_CODE_OK);
        $this->responseProphecy->getBodyContents()->willReturn($responseData);

        /** @var SubscribeRequest $subscribeRequest */
        $subscribeRequest = GeneralUtility::makeInstance(SubscribeRequest::class, $this->testEmailValid);

        try {
            $this->tellmaticClient->sendSubscribeRequest($subscribeRequest);
            $this->fail('The request did not throw the expected exception: ' . $expectedFailureCode);
        } catch (\Exception $e) {
            $exceptionType = get_class($e);
            $this->assertEquals($expectedFailureCode, $exceptionType);
        }
    }

    /**
     * @return array
     */
    public function sendSubscribeRequestReturnsValidFailureCodeDataProvider()
    {
        return [
            'validJsonInvalidData' => [
                'responseData' => json_encode(['this', 'json', 'data', 'is', 'nonsense']),
                'expectedException' => InvalidResponseException::class,
            ],
            'validJsonInvalidEmail' => [
                'responseData' => json_encode(
                    [
                        'success' => false,
                        'failure_code' => 'invalid_email',
                        'failure_reason' => 'Invalid mail',
                    ]
                ),
                'expectedException' => InvalidEmailException::class,
            ],
            'validJsonInvalidFormData' => [
                'responseData' => json_encode(
                    [
                        'success' => false,
                        'failure_code' => 'invalid_form_data',
                        'failure_reason' => 'Invalid form data',
                    ]
                ),
                'expectedFailureCode' => \Sto\Tellmatic\Tellmatic\Exception\InvalidFormDataException::class,
            ],
        ];
    }

    /**
     * @test
     * @expectedException \Sto\Tellmatic\Tellmatic\Exception\InvalidResponseException
     */
    public function sendSubscribeRequestThrowsExceptionOnErrorResponseCode()
    {
        $this->initializeRequestProphecy();

        $this->responseProphecy->getStatusCode()->willReturn(static::HTTP_STATUS_CODE_NOT_FOUND);
        $this->responseProphecy->getReasonPhrase()->willReturn('Not found!');
        $this->responseProphecy->getEffectiveUrl()->willReturn('http://redirected.url.tld');

        /** @var SubscribeRequest $subscribeRequest */
        $subscribeRequest = GeneralUtility::makeInstance(SubscribeRequest::class, $this->testEmailValid);

        $this->tellmaticClient->sendSubscribeRequest($subscribeRequest);
    }

    /**
     * @test
     * @expectedException \Sto\Tellmatic\Tellmatic\Exception\InvalidResponseException
     */
    public function sendSubscribeRequestThrowsExceptionOnInvalidResponse()
    {
        $this->initializeRequestProphecy();

        $this->responseProphecy->getStatusCode()->willReturn(static::HTTP_STATUS_CODE_OK);
        $this->responseProphecy->getBodyContents()->willReturn('totally invalid response data');

        /** @var SubscribeRequest $subscribeRequest */
        $subscribeRequest = GeneralUtility::makeInstance(SubscribeRequest::class, $this->testEmailValid);

        $this->tellmaticClient->sendSubscribeRequest($subscribeRequest);
    }

    protected function initializeRequestProphecy()
    {
        $this->requestProphecy->setUrl('http://tellmatic-test.swebhosting.de/api_subscribe.php')->shouldBeCalled();
        $this->requestProphecy->addPostParameter('email', $this->testEmailValid)->shouldBeCalled();
        $this->requestProphecy->addPostParameter('memo', Argument::any())->shouldBeCalled();
        $this->requestProphecy->addPostParameter('apiKey', Argument::any())->shouldBeCalled();
        $this->requestProphecy->getPostParameters()->willReturn(['email', $this->testEmailValid]);
    }
}
