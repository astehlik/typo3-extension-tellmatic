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

use TYPO3\CMS\Extbase\Error\Result;

/**
 * This class makes the validation results property of controller arguments accessible.
 * This is required to pass on error messages in the subscribe controller.
 */
class TellmaticArgument extends \TYPO3\CMS\Extbase\Mvc\Controller\Argument
{
    /**
     * @param Result $validationResults
     */
    public function setValidationResults(Result $validationResults)
    {
        $this->validationResults = $validationResults;
    }
}
