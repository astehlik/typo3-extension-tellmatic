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

use Sto\Tellmatic\Http\Request\GuzzleHttpRequest;
use Sto\Tellmatic\Http\Request\Typo3HttpRequest;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class HttpRequestFactory
{
    /**
     * Configuration that should be used for the HTTP request
     *
     * @var array
     */
    protected $httpRequestConfiguration = ['follow_redirects' => true];

    /**
     * @return HttpRequestInterface
     */
    public function createHttpRequest()
    {
        if (class_exists('TYPO3\\CMS\\Core\\Http\\RequestFactory')) {
            /** @var \TYPO3\CMS\Core\Http\RequestFactory $requestFactory */
            $requestFactory = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Http\\RequestFactory');
            return GeneralUtility::makeInstance(GuzzleHttpRequest::class, $requestFactory);
        }

        if (class_exists('TYPO3\\CMS\\Core\\Http\\HttpRequest')) {
            return GeneralUtility::makeInstance(Typo3HttpRequest::class);
        }

        throw new \RuntimeException('Can not create HTTP request.');
    }
}
