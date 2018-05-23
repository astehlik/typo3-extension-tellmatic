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

interface HttpRequestInterface
{
    /**
     * @param string $name
     * @param string $value
     * @return void
     */
    public function addPostParameter($name, $value);

    /**
     * @return array
     */
    public function getPostParameters();

    /**
     * @return HttpResponseInterface
     */
    public function send();

    /**
     * @param string $url
     * @return void
     */
    public function setUrl($url);
}
