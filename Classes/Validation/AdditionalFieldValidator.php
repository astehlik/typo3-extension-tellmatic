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
use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;

/**
 * Validates the additional fields, depending on the configuration.
 */
class AdditionalFieldValidator extends AbstractValidator {

	/**
	 * @var array
	 */
	protected $settings;

	/**
	 * @var \TYPO3\CMS\Extbase\Validation\ValidatorResolver
	 * @inject
	 */
	protected $validatorResolver;

	/**
	 * @param \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface $configurationManager
	 * @return void
	 */
	public function injectConfigurationManager(ConfigurationManagerInterface $configurationManager) {
		$this->settings = $configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS);
	}

	/**
	 * Loops over the configured additionalFieldValidators and merges the validation results
	 * to the result of this validator.
	 *
	 * @param array $value
	 * @return void
	 */
	protected function isValid($value) {

		foreach ($value as $additionalFieldName => $additionalFieldValue) {

			if (empty($this->settings['additionalFieldValidators'][$additionalFieldName])) {
				continue;
			}

			foreach ($this->settings['additionalFieldValidators'][$additionalFieldName] as $validatorSettings) {

				if (empty($validatorSettings['type'])) {
					throw new \InvalidArgumentException('No type was provided in the additional field validator for the field ' . $additionalFieldName);
				}

				$validator = $validatorSettings['type'];
				$options = empty($validatorSettings['options'])
					? array()
					: $validatorSettings['options'];

				/** @var AbstractValidator $validator */
				$validator = $this->validatorResolver->createValidator($validator, $options);
				$result = $validator->validate($additionalFieldValue);
				$this->result->forProperty($additionalFieldName)->merge($result);

			}
		}
	}
}