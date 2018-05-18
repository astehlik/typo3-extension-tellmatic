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
 * This Exception is thrown when an invalid auth code was submitted.
 */
class InvalidAuthCodeException extends \Exception
{
    /**
     * @param string $message
     * @param int $code
     * @param \Exception|NULL $previous
     */
    public function __construct(
        $message = 'The submitted auth code is invalid.',
        $code = 1441802183,
        \Exception $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
