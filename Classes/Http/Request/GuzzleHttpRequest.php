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

use GuzzleHttp\TransferStats;
use Sto\Tellmatic\Http\HttpRequestInterface;
use Sto\Tellmatic\Http\Response\GuzzleHttpResponse;
use TYPO3\CMS\Core\Http\RequestFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class GuzzleHttpRequest implements HttpRequestInterface
{
    /**
     * @var string
     */
    private $method = 'GET';

    private $postParameters = [];

    /**
     * @var \TYPO3\CMS\Core\Http\RequestFactory
     */
    private $requestFactory;

    private $url = '';

    public function __construct(RequestFactory $requestFactory)
    {
        $this->requestFactory = $requestFactory;
    }

    public function addPostParameter($name, $value)
    {
        $this->postParameters[(string)$name] = (string)$value;
    }

    public function getPostParameters()
    {
        return $this->postParameters;
    }

    public function send()
    {
        $options = $this->buildOptions();

        $effectiveUrl = '';
        $options['on_stats'] = function (TransferStats $stats) use (&$effectiveUrl) {
            $effectiveUrl = $stats->getEffectiveUri();
        };

        $response = $this->requestFactory->request($this->url, 'POST', $this->buildOptions());
        return GeneralUtility::makeInstance(GuzzleHttpResponse::class, $response, $effectiveUrl);
    }

    public function setMethod($method)
    {
        $this->method = (string)$method;
    }

    public function setUrl($url)
    {
        $this->url = (string)$url;
    }

    private function buildOptions()
    {
        if ($this->postParameters === []) {
            return [];
        }

        return ['form_params' => $this->postParameters];
    }
}
