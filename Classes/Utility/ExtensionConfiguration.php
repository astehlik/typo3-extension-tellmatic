<?php

namespace Sto\Tellmatic\Utility;

/*                                                                        *
 * This script belongs to the TYPO3 extension "tellmatic".                *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License, either version 3 of the   *
 * License, or (at your option) any later version.                        *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\ArrayUtility;

/**
 * Provides access to the global extension configuration
 */
class ExtensionConfiguration implements SingletonInterface
{
    /**
     * Array containing default settings
     *
     * @var array
     */
    protected $defaultSettings = [
        'tellmaticUrl' => null,
        'tellmaticApiKey' => null,
    ];

    /**
     * Array containing the currently active settings
     *
     * @var array
     */
    protected $settings;

    /**
     * Initializes the extension configuration
     */
    public function __construct()
    {
        $this->settings = $this->defaultSettings;

        if (isset($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['tellmatic'])) {
            $settings = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['tellmatic']);
            ArrayUtility::mergeRecursiveWithOverrule($this->settings, $settings);
        }
    }

    /**
     * Returns the Tellmatic API key.
     *
     * @return string
     */
    public function getTellmaticApiKey()
    {
        if (empty($this->settings['tellmaticApiKey'])) {
            throw new \RuntimeException('The Tellmatic API key is not configured in the Extension configuration.');
        }
        return $this->settings['tellmaticApiKey'];
    }

    /**
     * Returns the URL to the tellmatic server with a trailing slash
     *
     * @return string
     */
    public function getTellmaticUrl()
    {
        $tellmaticUrl = $this->settings['tellmaticUrl'];

        if (empty($tellmaticUrl)) {
            return null;
        }

        $tellmaticUrl = rtrim($tellmaticUrl, '/');
        return $tellmaticUrl . '/';
    }
}
