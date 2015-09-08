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

use Sto\Tellmatic\Tellmatic\Request\AccessibleHttpRequest;
use Sto\Tellmatic\Tellmatic\Request\SubscribeRequest;
use Sto\Tellmatic\Tellmatic\Response\TellmaticResponse;
use Sto\Tellmatic\Tellmatic\TellmaticClient;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Test for the Tellmatic client and the API.
 */
class TellmaticClientTest extends \TYPO3\CMS\Core\Tests\UnitTestCase {

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
	public function setUp() {

		$this->responseDataValid = json_encode(array(
			'success' => TRUE,
		));

		$this->tellmaticResponse = GeneralUtility::makeInstance(TellmaticResponse::class);

		$this->tellmaticClient = $this->getMock(TellmaticClient::class, array('dummy', 'createResponse', 'getTellmaticApiKey'));
		$this->tellmaticClient->expects($this->once())->method('createResponse')->will($this->returnValue($this->tellmaticResponse));
		$this->tellmaticClient->setCustomUrl($this->testUrlValid);
	}

	/**
	 * @test
	 */
	public function sendSubscribeRequestReturnsTrueOnSuccess() {

		if (GeneralUtility::compat_version('7.0')) {
			$this->markTestSkipped('Skipped because of problems with class loading in PEAR packages in TYPO3 7, see https://forge.typo3.org/issues/67838');
			return;
		}

		$responseMock = $this->getMock('HTTP_Request2_Response', array('getStatus', 'getBody'), array(), '', FALSE);
		$responseMock->expects($this->once())->method('getStatus')->will($this->returnValue(TellmaticResponse::HTTP_STATUS_CODE_OK));
		$responseMock->expects($this->once())->method('getBody')->will($this->returnValue($this->responseDataValid));

		/** @var \TYPO3\CMS\Core\Http\HttpRequest|\PHPUnit_Framework_MockObject_MockObject $httpRequest */
		$httpRequest = $this->getMock(AccessibleHttpRequest::class, array('send'));
		$httpRequest->expects($this->once())->method('send')->will($this->returnValue($responseMock));

		/** @var SubscribeRequest $subscribeRequest */
		$subscribeRequest = GeneralUtility::makeInstance(SubscribeRequest::class, $this->testEmailValid);

		$this->tellmaticClient->setHttpRequest($httpRequest);
		$tellmaticResponse = $this->tellmaticClient->sendSubscribeRequest($subscribeRequest);

		$this->assertTrue($tellmaticResponse->getSuccess());
	}

	/**
	 * @test
	 * @dataProvider sendSubscribeRequestReturnsValidFailureCodeDataProvider
	 * @param string $responseData
	 * @param $expectedFailureCode
	 */
	public function sendSubscribeRequestReturnsValidFailureCode($responseData, $expectedFailureCode) {

		if (GeneralUtility::compat_version('7.0')) {
			$this->markTestSkipped('Skipped because of problems with class loading in PEAR packages in TYPO3 7, see https://forge.typo3.org/issues/67838');
			return;
		}

		$responseMock = $this->getMock('HTTP_Request2_Response', array('getStatus', 'getBody'), array(), '', FALSE);
		$responseMock->expects($this->once())->method('getStatus')->will($this->returnValue(TellmaticResponse::HTTP_STATUS_CODE_OK));
		$responseMock->expects($this->once())->method('getBody')->will($this->returnValue($responseData));

		/** @var \TYPO3\CMS\Core\Http\HttpRequest|\PHPUnit_Framework_MockObject_MockObject $httpRequest */
		$httpRequest = $this->getMock(AccessibleHttpRequest::class, array('send'));
		$httpRequest->expects($this->once())->method('send')->will($this->returnValue($responseMock));

		/** @var SubscribeRequest $subscribeRequest */
		$subscribeRequest = GeneralUtility::makeInstance(SubscribeRequest::class, $this->testEmailValid);

		$this->tellmaticClient->setHttpRequest($httpRequest);
		$tellmaticResponse = $this->tellmaticClient->sendSubscribeRequest($subscribeRequest);

		$this->assertFalse($tellmaticResponse->getSuccess());
		$this->assertEquals($expectedFailureCode, $tellmaticResponse->getFailureCode());
	}

	/**
	 * @return array
	 */
	public function sendSubscribeRequestReturnsValidFailureCodeDataProvider() {

		return array(
			'validJsonInvalidData' => array(
				'responseData' => json_encode(array('this', 'json', 'data', 'is', 'nonsense')),
				'expectedFailureCode' => TellmaticResponse::FAILURE_CODE_INVALID_RESPONSE,
			),
			'validJsonInvalidEmail' => array(
				'responseData' => json_encode(array(
					'failure_code' => TellmaticResponse::FAILURE_CODE_INVALID_EMAIL,
					'failure_reason' => 'Invalid mail',
				)),
				'expectedFailureCode' => TellmaticResponse::FAILURE_CODE_INVALID_EMAIL,
			),
			'validJsonInvalidFormData' => array(
				'responseData' => json_encode(array(
					'failure_code' => TellmaticResponse::FAILURE_CODE_INVALID_FORM_DATA,
					'failure_reason' => 'Invalid mail',
				)),
				'expectedFailureCode' => TellmaticResponse::FAILURE_CODE_INVALID_FORM_DATA,
			),
		);
	}

	/**
	 * @test
	 * @expectedException \RuntimeException
	 */
	public function sendSubscribeRequestThrowsExceptionOnErrorResponseCode() {

		if (GeneralUtility::compat_version('7.0')) {
			$this->markTestSkipped('Skipped because of problems with class loading in PEAR packages in TYPO3 7, see https://forge.typo3.org/issues/67838');
			return;
		}

		$responseMock = $this->getMock('HTTP_Request2_Response', array('getStatus', 'getBody', 'getReasonPhrase', 'getEffectiveUrl'), array(), '', FALSE);
		$responseMock->expects($this->once())->method('getStatus')->will($this->returnValue(TellmaticResponse::HTTP_STATUS_CODE_NOT_FOUND));

		/** @var \TYPO3\CMS\Core\Http\HttpRequest|\PHPUnit_Framework_MockObject_MockObject $httpRequest */
		$httpRequest = $this->getMock(AccessibleHttpRequest::class, array('send'));
		$httpRequest->expects($this->once())->method('send')->will($this->returnValue($responseMock));

		/** @var SubscribeRequest $subscribeRequest */
		$subscribeRequest = GeneralUtility::makeInstance(SubscribeRequest::class, $this->testEmailValid);

		$this->tellmaticClient->setHttpRequest($httpRequest);
		$this->tellmaticClient->sendSubscribeRequest($subscribeRequest);
	}

	/**
	 * @test
	 * @expectedException \RuntimeException
	 */
	public function sendSubscribeRequestThrowsExceptionOnInvalidResponse() {

		if (GeneralUtility::compat_version('7.0')) {
			$this->markTestSkipped('Skipped because of problems with class loading in PEAR packages in TYPO3 7, see https://forge.typo3.org/issues/67838');
			return;
		}

		$responseMock = $this->getMock('HTTP_Request2_Response', array('getStatus', 'getBody'), array(), '', FALSE);
		$responseMock->expects($this->once())->method('getStatus')->will($this->returnValue(TellmaticResponse::HTTP_STATUS_CODE_OK));
		$responseMock->expects($this->once())->method('getBody')->will($this->returnValue('totally invalid response data'));

		/** @var \TYPO3\CMS\Core\Http\HttpRequest|\PHPUnit_Framework_MockObject_MockObject $httpRequest */
		$httpRequest = $this->getMock(AccessibleHttpRequest::class, array('send'));
		$httpRequest->expects($this->once())->method('send')->will($this->returnValue($responseMock));

		/** @var SubscribeRequest $subscribeRequest */
		$subscribeRequest = GeneralUtility::makeInstance(SubscribeRequest::class, $this->testEmailValid);

		$this->tellmaticClient->setHttpRequest($httpRequest);
		$this->tellmaticClient->sendSubscribeRequest($subscribeRequest);
	}
}