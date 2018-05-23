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
use HTTP_Request2_Response as HttpResponse;

class Typo3HttpResponse implements HttpResponseInterface
{
    /**
     * @var HttpResponse
     */
    private $response;

    public function __construct(HttpResponse $response)
    {
        $this->response = $response;
    }

    /**
     * @return string
     */
    public function getBodyContents()
    {
        return $this->response->getBody();
    }

    /**
     * @return string
     */
    public function getEffectiveUrl()
    {
        return $this->response->getEffectiveUrl();
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
        return $this->response->getStatus();
    }
}
