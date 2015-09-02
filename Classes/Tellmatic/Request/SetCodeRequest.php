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

/**
 * Request for adding a new subscriber to Tellmatic.
 */
class SetCodeRequest implements TellmaticRequestInterface {

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
	 * @param int $addressId
	 * @param string $codeExternal
	 */
	public function __construct($addressId, $codeExternal) {

		if (empty($addressId) || empty($codeExternal)) {
			throw new \RuntimeException('The addressId and the codeExternal must not be empty.');
		}

		$this->addressId = $addressId;
		$this->codeExternal = $codeExternal;
	}

	/**
	 * Initializes the given HTTP request with the required parameters.
	 *
	 * @param AccessibleHttpRequest $httpRequest
	 */
	public function initializeHttpRequest(AccessibleHttpRequest $httpRequest) {
		$httpRequest->addPostParameter('addressId', $this->addressId);
		$httpRequest->addPostParameter('codeExternal', $this->codeExternal);
	}
}