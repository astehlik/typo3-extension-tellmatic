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

class TellmaticResponse {

	const FAILURE_CODE_INVALID_EMAIL = 'invalid_email';
	const FAILURE_CODE_INVALID_FORM_DATA = 'invalid_form_data';
	const FAILURE_CODE_NO_VALID_NEWSLETTER = 'no_valid_newsletter';
	const FAILURE_CODE_INVALID_RESPONSE = 'invalid_response';
	const FAILURE_CODE_UNKNOWN = 'unknown';

	/**
	 * TRUE on success, FALSE on failure
	 *
	 * @var bool
	 */
	protected $success;

	/**
	 * If the request failed this variable contains the failure code.
	 *
	 * @var string
	 */
	protected $failureCode;

	/**
	 * If the request failed this variable contains a human readable
	 * error message (not localized).
	 *
	 * @var string
	 */
	protected $failureReason;

	/**
	 * Initializes the response. By default we suppose the request failed
	 * for an unknown reason.
	 */
	public function __construct() {
		$this->success = FALSE;
		$this->failureCode = self::FAILURE_CODE_UNKNOWN;
		$this->failureReason = 'An unknown error occured or the response could not be processed.';
	}

	/**
	 * Returns the current failure code
	 *
	 * @return string
	 */
	public function getFailureCode() {
		return $this->failureCode;
	}

	/**
	 * Returns the current failure reason
	 *
	 * @return string
	 */
	public function getFailureReason() {
		return $this->failureReason;
	}

	/**
	 * Returns the success state
	 *
	 * @return boolean
	 */
	public function getSuccess() {
		return $this->success;
	}

	/**
	 * Dummy method that can be used my child classes to get additional
	 * data from the response
	 *
	 * @param array $response
	 * @return void
	 */
	public function processAdditionalResponseData($response) {
	}

	/**
	 * Initializes the failure variables fromt eh given parsed JSON
	 * response.
	 *
	 * @param array $response
	 */
	public function setFailureFromJsonResponse($response) {

		$this->success = FALSE;

		if (isset($response['failure_code']) && isset($response['failure_reason'])) {
			$this->failureCode = $response['failure_code'];
			$this->failureReason = 'Server responded with this failure reason: ' . $response['failure_reason'];
		} else {
			$this->failureCode = self::FAILURE_CODE_INVALID_RESPONSE;
			$this->failureReason = 'The response did not provide a failure_code or a failure_reason';
		}
	}

	/**
	 * Initializes the failure variables from the given exception
	 *
	 * @param \Exception $exception
	 */
	public function setFailureFromException($exception) {
		$this->success = FALSE;
		$this->failureCode = self::FAILURE_CODE_UNKNOWN;
		$this->failureReason = 'Exception ' . $exception->getCode() . ': ' . $exception->getMessage();
	}

	/**
	 * This sets the request success to TRUE and clears all errors
	 */
	public function setRequestSuccessful() {
		$this->success = TRUE;
		$this->failureCode = NULL;
		$this->failureReason = NULL;
	}
}