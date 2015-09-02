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
 * A tellmatic response that contains the subscribe state of an address.
 */
class AddressSearchResponse extends TellmaticResponse {

	/**
	 * @var array
	 */
	protected $addresses = array();

	/**
	 * @return array
	 */
	public function getAddresses() {
		return $this->addresses;
	}

	/**
	 * Checks the subscribe state that tellmatic should have provided
	 * in its response data
	 *
	 * @param array $responseData
	 * @return void
	 * @throws \RuntimeException
	 */
	public function processAdditionalResponseData($responseData) {

		if (!is_array($responseData['addresses'])) {
			throw new \RuntimeException('Tellmatic did not provide a valid addresses property in its response.');
		}

		$this->addresses = $responseData['addresses'];
	}
}