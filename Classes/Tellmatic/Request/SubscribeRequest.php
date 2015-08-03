<?php
namespace Sto\Tellmatic\Tellmatic\Request;

/*                                                                        *
 * This script belongs to the TYPO3 extension "tellmatic".                *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License, either version 3 of the   *
 * License, or (at your option) any later version.                        *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\CMS\Core\Http\HttpRequest;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Request for adding a new subscriber to Tellmatic.
 */
class SubscribeRequest {

	/**
	 * Optional array containing additional field data (f0 - f9).
	 *
	 * @var array
	 */
	protected $additionalFields = array();

	/**
	 * This array will be used to check if all provided additional fields are valid.
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
	 * The email address that should be subscribed.
	 *
	 * @var string
	 */
	protected $email;

	/**
	 * Initializes a new SubscribeRequest for the given email address.
	 *
	 * @param string $email The email address that should be subscribed.
	 */
	public function __construct($email) {

		if (empty($email) || !GeneralUtility::validEmail($email)) {
			throw new \RuntimeException('The provided email address is invalid');
		}

		$this->email = $email;
	}

	/**
	 * Initializes the given HTTP request with the required parameters.
	 *
	 * @param HttpRequest $httpRequest
	 */
	public function initializeHttpRequest(HttpRequest $httpRequest) {

		$httpRequest->addPostParameter('email', $this->email);

		foreach ($this->additionalFields as $name => $value) {
			$httpRequest->addPostParameter($name, $value);
		}
	}

	/**
	 * Sets the array containing the additional fields that should be submitted with the subscribe request.
	 *
	 * @param array $additionalFields Optional array containing additional field data (f0 - f9).
	 */
	public function setAdditionalFields(array $additionalFields) {

		$invalidAdditionalFields = array_diff_key($additionalFields, $this->allowedAdditionalFields);

		if (count($invalidAdditionalFields)) {
			throw new \RuntimeException('You provided invalid additional Fields: ' . implode(', ', array_keys($invalidAdditionalFields)));
		}

		$this->additionalFields = $additionalFields;
	}
}