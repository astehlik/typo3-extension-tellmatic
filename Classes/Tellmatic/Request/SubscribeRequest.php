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

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Request for adding a new subscriber to Tellmatic.
 */
class SubscribeRequest implements TellmaticRequestInterface {

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
	 * @var bool
	 */
	protected $doNotSendEmails = FALSE;

	/**
	 * The email address that should be subscribed.
	 *
	 * @var string
	 */
	protected $email;

	/**
	 * @var Memo
	 */
	protected $memo = '';

	/**
	 * @var string
	 */
	protected $overrideAddressStatus = NULL;

	/**
	 * @var bool|null
	 */
	protected $overrideDoubleOptInSetting = NULL;

	/**
	 * @var bool
	 */
	protected $validateOnly = FALSE;

	/**
	 * Initializes a new SubscribeRequest for the given email address.
	 *
	 * @param string $email The email address that should be subscribed.
	 */
	public function __construct($email) {

		if (empty($email) || !GeneralUtility::validEmail($email)) {
			throw new \RuntimeException('The provided email address is invalid: ' . $email);
		}

		$this->email = $email;
		$this->memo = GeneralUtility::makeInstance(Memo::class);
	}

	/**
	 * Initializes the given HTTP request with the required parameters.
	 *
	 * @param AccessibleHttpRequest $httpRequest
	 */
	public function initializeHttpRequest(AccessibleHttpRequest $httpRequest) {

		$this->appendDefaultMemo();

		$httpRequest->addPostParameter('email', $this->email);

		foreach ($this->additionalFields as $name => $value) {
			$httpRequest->addPostParameter($name, $value);
		}

		$memo = $this->memo->getMemo();
		if (!empty($memo)) {
			$httpRequest->addPostParameter('memo', $memo);
		}

		if (isset($this->overrideAddressStatus)) {
			$httpRequest->addPostParameter('overrideAddressStatus', $this->overrideAddressStatus);
		}

		if ($this->validateOnly) {
			$httpRequest->addPostParameter('validateOnly', TRUE);
		}

		if ($this->doNotSendEmails) {
			$httpRequest->addPostParameter('doNotSendEmails', TRUE);
		}

		if (isset($this->overrideDoubleOptInSetting)) {
			$httpRequest->addPostParameter('overrideDoubleOptInSetting', $this->overrideDoubleOptInSetting);
		}
	}

	/**
	 * @return bool
	 */
	public function getDoNotSendEmails() {
		return $this->doNotSendEmails;
	}

	/**
	 * @return Memo
	 */
	public function getMemo() {
		return $this->memo;
	}

	/**
	 * @return string
	 */
	public function getOverrideAddressStatus() {
		return $this->overrideAddressStatus;
	}

	/**
	 * @return bool|null
	 */
	public function getOverrideDoubleOptInSetting() {
		return $this->overrideDoubleOptInSetting;
	}

	/**
	 * @return bool
	 */
	public function getValidateOnly() {
		return $this->validateOnly;
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

	/**
	 * @param bool $doNotSendEmails
	 */
	public function setDoNotSendEmails($doNotSendEmails) {
		$this->doNotSendEmails = $doNotSendEmails;
	}

	/**
	 * @param string $overrideAddressStatus
	 */
	public function setOverrideAddressStatus($overrideAddressStatus) {
		$this->overrideAddressStatus = $overrideAddressStatus;
	}

	/**
	 * @param bool|null $overrideDoubleOptInSetting
	 */
	public function setOverrideDoubleOptInSetting($overrideDoubleOptInSetting) {
		$this->overrideDoubleOptInSetting = $overrideDoubleOptInSetting;
	}

	/**
	 * @param bool $validateOnly
	 */
	public function setValidateOnly($validateOnly) {
		$this->validateOnly = (bool)$validateOnly;
	}

	/**
	 * Adds some information to the memo that is stored in the Tellmatic address record.
	 */
	protected function appendDefaultMemo() {

		$this->memo->appendDefaultMemo($this);

		if ($this->overrideAddressStatus) {
			$this->memo->addLineToMemo('Address status overwritten: ' . $this->overrideAddressStatus);
		}

		if ($this->overrideDoubleOptInSetting) {
			$this->memo->addLineToMemo('Double opt-in setting overwritten: ' . $this->overrideDoubleOptInSetting);
		}
	}
}