<?php
namespace Sto\Tellmatic\Tests\Unit\Tellmatic;

use Sto\Tellmatic\Tellmatic\TellmaticResponse;

class TellmaticClientTest extends \TYPO3\CMS\Extbase\Tests\Unit\BaseTestCase {

	public function tellmaticClientReturnsValidResponseDataProvider() {

		return array(
			'nonexistingUrl' => array(
				'responseStatus' => 400,
				'responseData' => '',
				'callGetBody' => FALSE,
				'success' => FALSE,
				'expectedFailureCode' => TellmaticResponse::FAILURE_CODE_UNKNOWN
			),
			'invalidJson' => array(
				'responseStatus' => 200,
				'responseData' => 'Not a JSON string',
				'callGetBody' => TRUE,
				'success' => FALSE,
				'expectedFailureCode' => TellmaticResponse::FAILURE_CODE_UNKNOWN
			),
			'validJsonInvalidData' => array(
				'responseStatus' => 200,
				'responseData' => json_encode(array('this', 'json', 'data', 'is', 'nonsense')),
				'callGetBody' => TRUE,
				'success' => FALSE,
				'expectedFailureCode' => TellmaticResponse::FAILURE_CODE_INVALID_RESPONSE,
			),
			'validJsonInvalidEmail' => array(
				'responseStatus' => 200,
				'responseData' => json_encode(array(
					'success' => FALSE,
					'failure_code' => TellmaticResponse::FAILURE_CODE_INVALID_EMAIL,
					'failure_reason' => 'Invalid mail',
				)),
				'callGetBody' => TRUE,
				'success' => FALSE,
				'expectedFailureCode' => TellmaticResponse::FAILURE_CODE_INVALID_EMAIL,
			),
			'validJsonInvalidFormData' => array(
				'responseStatus' => 200,
				'responseData' => json_encode(array(
					'success' => FALSE,
					'failure_code' => TellmaticResponse::FAILURE_CODE_INVALID_FORM_DATA,
					'failure_reason' => 'Invalid mail',
				)),
				'callGetBody' => TRUE,
				'success' => FALSE,
				'expectedFailureCode' => TellmaticResponse::FAILURE_CODE_INVALID_FORM_DATA,
			),
			'validJsonValidFormData' => array(
				'responseStatus' => 200,
				'responseData' => json_encode(array(
					'success' => TRUE,
				)),
				'callGetBody' => TRUE,
				'success' => TRUE,
				'expectedFailureCode' => '',
			),
		);
	}

	/**
	 * @param int $responseStatus
	 * @param string $responseData
	 * @param bool $callGetBody
	 * @param bool $success
	 * @param $expectedFailureCode
	 * @internal param string $expectedResponse
	 * @return \Sto\Tellmatic\Tellmatic\TellmaticClient
	 * @dataProvider tellmaticClientReturnsValidResponseDataProvider
	 * @test
	 */
	public function tellmaticClientReturnsValidResponse($responseStatus, $responseData, $callGetBody, $success, $expectedFailureCode) {

		$url = 'dummyurl';

		$responseMock = $this->getMock('HTTP_Request2_Response', array('getStatus', 'getBody', 'getReasonPhrase', 'getEffectiveUrl'));
		$responseMock->expects($this->once())->method('getStatus')->will($this->returnValue($responseStatus));

		if ($callGetBody) {
			$responseMock->expects($this->once())->method('getBody')->will($this->returnValue($responseData));
		}

		$httpRequest = $this->getMock('TYPO3\\CMS\\Core\\Http\\HttpRequest', array('setConfiguration', 'setUrl', 'addPostParameter', 'send', 'getStatus', 'getBody'));
		$httpRequest->expects($this->once())->method('setConfiguration');
		$httpRequest->expects($this->once())->method('setUrl')->with($url);
		$httpRequest->expects($this->never())->method('addPostParameter');
		$httpRequest->expects($this->once())->method('send')->will($this->returnValue($responseMock));

		$tellmaticClient = $this->objectManager->get('Sto\\Tellmatic\\Tellmatic\TellmaticClient');
		$tellmaticClient->setHttpRequest($httpRequest);

		/** @var \Sto\Tellmatic\Tellmatic\TellmaticResponse $tellmaticResponse */
		$tellmaticResponse = $tellmaticClient->sendSubscribeRequest($url, array());

		if ($success) {
			$this->assertTrue($tellmaticResponse->getSuccess());
		} else {
			$this->assertFalse($tellmaticResponse->getSuccess());
			$this->assertEquals($expectedFailureCode, $tellmaticResponse->getFailureCode());
		}
	}
}