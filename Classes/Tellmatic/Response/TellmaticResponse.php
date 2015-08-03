<?php
namespace Sto\Tellmatic\Tellmatic\Response;

/*                                                                        *
 * This script belongs to the TYPO3 extension "tellmatic".                *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License, either version 3 of the   *
 * License, or (at your option) any later version.                        *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

/**
 * A generic response from the Tellmatic server.
 */
class TellmaticResponse {

	/**
	 * Failure code returned by Tellmatic if an invalid email address was submitted.
	 *
	 * @const
	 */
	const FAILURE_CODE_INVALID_EMAIL = 'invalid_email';

	/**
	 * Failure code returned by Tellmatic if invalid form data was submitted.
	 *
	 * @const
	 */
	const FAILURE_CODE_INVALID_FORM_DATA = 'invalid_form_data';

	/**
	 * Failure code that is raised when Tellmatic provided an invalid response.
	 *
	 * @const
	 */
	const FAILURE_CODE_INVALID_RESPONSE = 'invalid_response';

	/**
	 * Failure code returned by Tellmatic when no valid newsletter was submitted in the request.
	 *
	 * @const
	 */
	const FAILURE_CODE_NO_VALID_NEWSLETTER = 'no_valid_newsletter';

	/**
	 * Failure code for unexpected errors.
	 *
	 * @const
	 */
	const FAILURE_CODE_UNKNOWN = 'unknown';

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
	 * If the request failed this variable contains the failure code.
	 *
	 * @var string
	 */
	protected $failureCode;

	/**
	 * If the request failed this variable contains a human readable error message (not localized).
	 *
	 * @var string
	 */
	protected $failureReason;

	/**
	 * TRUE on success, FALSE on failure
	 *
	 * @var bool
	 */
	protected $success;

	/**
	 * Initializes the response. By default we suppose the request failed for an unknown reason.
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
	 * Dummy method that can be used my child classes to get additional data from the response.
	 *
	 * @param array $response
	 * @return void
	 */
	public function processAdditionalResponseData($response) {
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
	 * Initializes the failure variables fromt eh given parsed JSON response.
	 *
	 * @param array $response
	 */
	public function setFailureFromJsonResponse($response) {

		$this->success = FALSE;

		if (!empty($response['failure_code']) && !empty($response['failure_reason'])) {
			$this->failureCode = $response['failure_code'];
			$this->failureReason = 'Server responded with this failure reason: ' . $response['failure_reason'];
		} else {
			$this->failureCode = self::FAILURE_CODE_INVALID_RESPONSE;
			$this->failureReason = 'The response did not provide a failure_code or a failure_reason';
		}
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