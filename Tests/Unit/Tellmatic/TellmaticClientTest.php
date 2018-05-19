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

use Sto\Tellmatic\Tellmatic\Exception\InvalidEmailException;
use Sto\Tellmatic\Tellmatic\Exception\InvalidResponseException;
use Sto\Tellmatic\Tellmatic\Request\AccessibleHttpRequest;
use Sto\Tellmatic\Tellmatic\Request\SubscribeRequest;
use Sto\Tellmatic\Tellmatic\Response\TellmaticResponse;
use Sto\Tellmatic\Tellmatic\TellmaticClient;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Test for the Tellmatic client and the API.
 */
class TellmaticClientTest extends \TYPO3\CMS\Core\Tests\UnitTestCase
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
     * @var TellmaticResponse
     */
    protected $tellmaticResponse;

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
    protected $testUrlValid = 'http://tellmatic-test.swebhosting.de';

    /**
     * Initializes the Tellmatic client.
     */
    public function setUp()
    {
        $this->responseDataValid = json_encode(
            ['success' => true]
        );

        $this->tellmaticResponse = GeneralUtility::makeInstance(TellmaticResponse::class);

        $this->tellmaticClient = $this->getMock(
            TellmaticClient::class,
            [
                'dummy',
                'createResponse',
                'getTellmaticApiKey',
            ]
        );
        $this->tellmaticClient->expects($this->once())->method('createResponse')->will(
            $this->returnValue($this->tellmaticResponse)
        );
        $this->tellmaticClient->setCustomUrl($this->testUrlValid);
    }

    /**
     * @test
     */
    public function sendSubscribeRequestReturnsTrueOnSuccess()
    {
        $responseMock = $this->getMock('HTTP_Request2_Response', ['getStatus', 'getBody'], [], '', false);
        $responseMock->expects($this->once())->method('getStatus')->will(
            $this->returnValue(static::HTTP_STATUS_CODE_OK)
        );
        $responseMock->expects($this->once())->method('getBody')->will($this->returnValue($this->responseDataValid));

        /** @var \TYPO3\CMS\Core\Http\HttpRequest|\PHPUnit_Framework_MockObject_MockObject $httpRequest */
        $httpRequest = $this->getMock(AccessibleHttpRequest::class, ['send']);
        $httpRequest->expects($this->once())->method('send')->will($this->returnValue($responseMock));

        /** @var SubscribeRequest $subscribeRequest */
        $subscribeRequest = GeneralUtility::makeInstance(SubscribeRequest::class, $this->testEmailValid);

        $this->tellmaticClient->setHttpRequest($httpRequest);
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
        $responseMock = $this->getMock('HTTP_Request2_Response', ['getStatus', 'getBody'], [], '', false);
        $responseMock->expects($this->once())->method('getStatus')->will(
            $this->returnValue(static::HTTP_STATUS_CODE_OK)
        );
        $responseMock->expects($this->once())->method('getBody')->will($this->returnValue($responseData));

        /** @var \TYPO3\CMS\Core\Http\HttpRequest|\PHPUnit_Framework_MockObject_MockObject $httpRequest */
        $httpRequest = $this->getMock(AccessibleHttpRequest::class, ['send']);
        $httpRequest->expects($this->once())->method('send')->will($this->returnValue($responseMock));

        /** @var SubscribeRequest $subscribeRequest */
        $subscribeRequest = GeneralUtility::makeInstance(SubscribeRequest::class, $this->testEmailValid);

        $this->tellmaticClient->setHttpRequest($httpRequest);

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
        $responseMock = $this->getMock(
            'HTTP_Request2_Response',
            [
                'getStatus',
                'getBody',
                'getReasonPhrase',
                'getEffectiveUrl',
            ],
            [],
            '',
            false
        );
        $responseMock->expects($this->once())->method('getStatus')->will(
            $this->returnValue(static::HTTP_STATUS_CODE_NOT_FOUND)
        );

        /** @var \TYPO3\CMS\Core\Http\HttpRequest|\PHPUnit_Framework_MockObject_MockObject $httpRequest */
        $httpRequest = $this->getMock(AccessibleHttpRequest::class, ['send']);
        $httpRequest->expects($this->once())->method('send')->will($this->returnValue($responseMock));

        /** @var SubscribeRequest $subscribeRequest */
        $subscribeRequest = GeneralUtility::makeInstance(SubscribeRequest::class, $this->testEmailValid);

        $this->tellmaticClient->setHttpRequest($httpRequest);
        $this->tellmaticClient->sendSubscribeRequest($subscribeRequest);
    }

    /**
     * @test
     * @expectedException \Sto\Tellmatic\Tellmatic\Exception\InvalidResponseException
     */
    public function sendSubscribeRequestThrowsExceptionOnInvalidResponse()
    {
        $responseMock = $this->getMock('HTTP_Request2_Response', ['getStatus', 'getBody'], [], '', false);
        $responseMock->expects($this->once())->method('getStatus')->will(
            $this->returnValue(static::HTTP_STATUS_CODE_OK)
        );
        $responseMock->expects($this->once())->method('getBody')->will(
            $this->returnValue('totally invalid response data')
        );

        /** @var \TYPO3\CMS\Core\Http\HttpRequest|\PHPUnit_Framework_MockObject_MockObject $httpRequest */
        $httpRequest = $this->getMock(AccessibleHttpRequest::class, ['send']);
        $httpRequest->expects($this->once())->method('send')->will($this->returnValue($responseMock));

        /** @var SubscribeRequest $subscribeRequest */
        $subscribeRequest = GeneralUtility::makeInstance(SubscribeRequest::class, $this->testEmailValid);

        $this->tellmaticClient->setHttpRequest($httpRequest);
        $this->tellmaticClient->sendSubscribeRequest($subscribeRequest);
    }
}
