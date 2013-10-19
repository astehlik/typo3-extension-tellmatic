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

/**
 * A tellmatic response that contains the subscribe state of an address
 */
class SubscribeStateResponse extends TellmaticResponse {

	const SUBSCRIBE_STATE_NOT_SUBSCRIBED = 'not_subscribed';
	const SUBSCRIBE_STATE_SUBSCRIBED_UNCONFIRMED = 'subscribed_unconfirmed';
	const SUBSCRIBE_STATE_SUBSCRIBED_CONFIRMED = 'subscribed_confirmed';

	/**
	 * The current subscribe state
	 *
	 * @var string
	 */
	protected $subscribeState;

	/**
	 * Allowed subscribe states
	 *
	 * @var array
	 */
	protected $validSubscribeStates = array(
		self::SUBSCRIBE_STATE_NOT_SUBSCRIBED,
		self::SUBSCRIBE_STATE_SUBSCRIBED_CONFIRMED,
		self::SUBSCRIBE_STATE_SUBSCRIBED_UNCONFIRMED,
	);

	public function getSubscribeState() {
		return $this->subscribeState;
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

		if (!isset($responseData['subscribe_state']) || empty($responseData['subscribe_state'])) {
			throw new \RuntimeException('Tellmatic did not provide a subscribe state.');
		}

		$subscribeState = $responseData['subscribe_state'];

		if (!in_array($subscribeState, $this->validSubscribeStates)) {
			throw new \RuntimeException('Tellmatic answered with an invalid subscribe state: ' . $subscribeState);
		}

		$this->subscribeState = $subscribeState;
	}
}

?>