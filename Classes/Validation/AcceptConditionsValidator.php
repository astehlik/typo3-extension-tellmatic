<?php

namespace Sto\Tellmatic\Validation;

/*                                                                        *
 * This script belongs to the TYPO3 extension "tellmatic".                *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License, either version 3 of the   *
 * License, or (at your option) any later version.                        *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Validation\Validator\BooleanValidator;

/**
 * If settings.privacyPolicy.checkRequired is TRUE the given value needs also to be TRUE.
 */
class AcceptConditionsValidator extends BooleanValidator
{
    /**
     * @var array
     */
    protected $settings;

    /**
     * @param \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface $configurationManager
     */
    public function injectConfigurationManager(ConfigurationManagerInterface $configurationManager)
    {
        $this->settings = $configurationManager->getConfiguration(
            ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS
        );
    }

    /**
     * Executes the parent boolean validation if settings.privacyPolicy.checkRequired is enabled.
     *
     * @param mixed $value The value that should be validated
     * @return void
     */
    public function isValid($value)
    {
        if (empty($this->settings['privacyPolicy']['checkRequired'])) {
            return;
        }

        $this->options['is'] = true;

        parent::isValid($value);
    }
}
