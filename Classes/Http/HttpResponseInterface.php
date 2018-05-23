<?php

namespace Sto\Tellmatic\Http;

/*                                                                        *
 * This script belongs to the TYPO3 extension "tellmatic".                *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License, either version 3 of the   *
 * License, or (at your option) any later version.                        *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

interface HttpResponseInterface
{
    /**
     * @return string
     */
    public function getBodyContents();

    /**
     * @return string
     */
    public function getEffectiveUrl();

    /**
     * @return string
     */
    public function getReasonPhrase();

    /**
     * @return int
     */
    public function getStatusCode();
}
