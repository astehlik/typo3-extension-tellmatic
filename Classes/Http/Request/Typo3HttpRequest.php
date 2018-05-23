<?php

namespace Sto\Tellmatic\Http\Request;

/*                                                                        *
 * This script belongs to the TYPO3 extension "tellmatic".                *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License, either version 3 of the   *
 * License, or (at your option) any later version.                        *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use Sto\Tellmatic\Http\HttpRequestInterface;
use Sto\Tellmatic\Http\Response\Typo3HttpResponse;
use TYPO3\CMS\Core\Http\HttpRequest;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class Typo3HttpRequest extends HttpRequest implements HttpRequestInterface
{
    /**
     * Adds POST parameter(s) to the request.
     *
     * Makes sure that all values are added as strings to prevent hmac validation
     * errors on the Tellmatic side.
     *
     * @param string|array $name parameter name or array ('name' => 'value')
     * @param mixed $value parameter value (can be an array)
     * @return \HTTP_Request2
     */
    public function addPostParameter($name, $value = null)
    {
        if (!is_array($name)) {
            $value = (string)$value;
        }

        return parent::addPostParameter($name, $value);
    }

    /**
     * Returns the current POST parameters array.
     *
     * @return array
     */
    public function getPostParameters()
    {
        return $this->postParams;
    }

    /**
     * @return Typo3HttpResponse
     */
    public function send()
    {
        $this->setMethod('POST');
        $response = parent::send();
        return GeneralUtility::makeInstance(Typo3HttpResponse::class, $response);
    }
}
