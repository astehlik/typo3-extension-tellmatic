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
class SubscribeStateResponse extends TellmaticResponse
{
    /**
     * The subscriber does not exist.
     *
     * @const
     */
    const SUBSCRIBE_STATE_NOT_SUBSCRIBED = 'not_subscribed';

    /**
     * The subscriber exists but is not confirmed.
     *
     * @const
     */
    const SUBSCRIBE_STATE_SUBSCRIBED_CONFIRMED = 'subscribed_confirmed';

    /**
     * The subscriber exists but has not yet confirmed his subscription.
     *
     * @const
     */
    const SUBSCRIBE_STATE_SUBSCRIBED_UNCONFIRMED = 'subscribed_unconfirmed';

    /**
     * @var array
     */
    protected $addressData = [];

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
    protected $validSubscribeStates = [
        self::SUBSCRIBE_STATE_NOT_SUBSCRIBED,
        self::SUBSCRIBE_STATE_SUBSCRIBED_CONFIRMED,
        self::SUBSCRIBE_STATE_SUBSCRIBED_UNCONFIRMED,
    ];

    /**
     * @return array
     */
    public function getAddressData()
    {
        return $this->addressData;
    }

    /**
     * Returns the current subscribe state.
     *
     * @return string
     */
    public function getSubscribeState()
    {
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
    public function processAdditionalResponseData($responseData)
    {
        if (empty($responseData['subscribe_state'])) {
            throw new \RuntimeException('Tellmatic did not provide a subscribe state.');
        }

        $subscribeState = $responseData['subscribe_state'];

        if (!in_array($subscribeState, $this->validSubscribeStates)) {
            throw new \RuntimeException('Tellmatic answered with an invalid subscribe state: ' . $subscribeState);
        }

        if (!empty($responseData['address_data']) && is_array($responseData['address_data'])) {
            $this->addressData = $responseData['address_data'];
        }

        $this->subscribeState = $subscribeState;
    }
}
