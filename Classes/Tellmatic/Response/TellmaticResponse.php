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
class TellmaticResponse
{
    /**
     * Dummy method that can be used my child classes to get additional data from the response.
     *
     * @param array $response
     * @return void
     */
    public function processAdditionalResponseData($response)
    {
    }
}
