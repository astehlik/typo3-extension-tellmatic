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

interface TellmaticRequestInterface
{
    /**
     * Initializes the given HTTP request with the required parameters.
     *
     * @param AccessibleHttpRequest $httpRequest
     */
    public function initializeHttpRequest(AccessibleHttpRequest $httpRequest);
}
