<?php
namespace Sto\Tellmatic\ViewHelpers\Form;

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
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * View helper for generating the options array for a select view helper.
 */
class SelectOptionsViewHelper extends AbstractViewHelper {

	/**
	 * @var array
	 */
	protected $settings;

	/**
	 * @param \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface $configurationManager
	 */
	public function injectConfigurationManager(ConfigurationManagerInterface $configurationManager) {
		$this->settings = $configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS);
	}

	/**
	 * Generates the options array.
	 *
	 * @param string $fieldName
	 * @return array
	 */
	public function render($fieldName) {

		$options = [];

		if (empty($this->settings['additionalFields'][$fieldName]['options'])) {
			return $options;
		}

		foreach ($this->settings['additionalFields'][$fieldName]['options'] as $option) {
			$label = $option['label'];
			$value = isset($option['value']) ? $option['value'] : $label;
			$options[$value] = $label;
		}

		return $options;
	}
}