<?php

namespace Sto\Tellmatic\Http\Response;

/*                                                                        *
 * This script belongs to the TYPO3 extension "tellmatic".                *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License, either version 3 of the   *
 * License, or (at your option) any later version.                        *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use Sto\Tellmatic\Http\HttpResponseInterface;
use Psr\Http\Message\ResponseInterface;

class GuzzleHttpResponse implements HttpResponseInterface
{
    /**
     * @var string
     */
    private $effectiveUrl;

    /**
     * @var \Psr\Http\Message\ResponseInterface
     */
    private $response;

    public function __construct(ResponseInterface $response, $effectiveUrl)
    {
        $this->response = $response;
        $this->effectiveUrl = (string)$effectiveUrl;
    }

    /**
     * @return string
     */
    public function getBodyContents()
    {
        return $this->response->getBody()->getContents();
    }

    /**
     * @return string
     */
    public function getEffectiveUrl()
    {
        return '';
    }

    /**
     * @return string
     */
    public function getReasonPhrase()
    {
        return $this->response->getReasonPhrase();
    }

    /**
     * @return int
     */
    public function getStatusCode()
    {
        return $this->response->getStatusCode();
    }
}
