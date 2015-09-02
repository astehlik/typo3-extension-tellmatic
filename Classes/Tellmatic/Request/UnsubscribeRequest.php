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
class UnsubscribeRequest {

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
	 * @param HttpRequest $httpRequest
	 */
	public function initializeHttpRequest(HttpRequest $httpRequest) {

		$this->memo->appendDefaultMemo($this);

		$memo = $this->memo->getMemo();
		if (!empty($memo)) {
			$httpRequest->addPostParameter('memo', $memo);
		}

		$httpRequest->addPostParameter('email', $this->email);

		if ($this->doNotSendEmails) {
			$httpRequest->addPostParameter('doNotSendEmails', TRUE);
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
	 * @param bool $doNotSendEmails
	 */
	public function setDoNotSendEmails($doNotSendEmails) {
		$this->doNotSendEmails = $doNotSendEmails;
	}
}