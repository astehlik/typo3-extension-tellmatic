<?php

namespace Sto\Tellmatic\Tellmatic\Exception;

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
 * An invalid email was detected by the Tellmatic API.
 */
class InvalidEmailException extends TellmaticException
{
    /**
     * @param array $responseData
     * @param int $code
     * @param null $previous
     */
    public function __construct(array $responseData, $code = 1441711256, $previous = null)
    {
        parent::__construct($responseData, $code, $previous);
    }
}
