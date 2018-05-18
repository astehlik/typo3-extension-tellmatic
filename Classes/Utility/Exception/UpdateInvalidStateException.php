<?php

namespace Sto\Tellmatic\Utility\Exception;

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
 * This exception is thrown when the user tries to access the update form
 * and the address is not in the database any more.
 */
class UpdateInvalidStateException extends \Exception
{
    /**
     * @param string $subscribeState
     * @param int $code
     * @param \Exception|NULL $previous
     */
    public function __construct($subscribeState, $code = 1441813632, \Exception $previous = null)
    {
        $message = 'The address record is not in a valid state for updating the subcription data: ' . $subscribeState;
        parent::__construct($message, $code, $previous);
    }
}
