<?php

namespace Sto\Tellmatic\ViewHelpers\Link;

/*                                                                        *
 * This script belongs to the TYPO3 Extension "tellmatic".                *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License, either version 3 of the   *
 * License, or (at your option) any later version.                        *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * View helper that accepts a TypoLink parameter
 */
class TypolinkViewHelper extends AbstractViewHelper
{
    /**
     * @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface
     * @inject
     */
    protected $configurationManager;

    /**
     * Renders a TypoLink
     *
     * @param string $parameter
     * @param array $aTagParams
     * @return string
     */
    public function render($parameter, array $aTagParams = null)
    {
        $contentObject = $this->configurationManager->getContentObject();

        $config = ['parameter' => $parameter];

        if (isset($aTagParams)) {
            $config['ATagParams'] = \TYPO3\CMS\Core\Utility\GeneralUtility::implodeAttributes($aTagParams);
        }

        return $contentObject->typoLink($this->renderChildren(), $config);
    }
}
