<?php

namespace Sto\Tellmatic\Tests\Unit\ViewHelpers\Form;

/*                                                                        *
 * This script belongs to the TYPO3 extension "tellmatic".                *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License, either version 3 of the   *
 * License, or (at your option) any later version.                        *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use Nimut\TestingFramework\TestCase\ViewHelperBaseTestcase;
use Sto\Tellmatic\ViewHelpers\Form\ValidatedControlGroupViewHelper;
use TYPO3\CMS\Fluid\Core\ViewHelper\TagBuilder;

class ValidatedControlGroupViewHelperTest extends ViewHelperBaseTestcase
{
    /**
     * @var ValidatedControlGroupViewHelper
     */
    private $viewHelper;

    protected function setUp()
    {
        parent::setUp();
        $this->viewHelper = new ValidatedControlGroupViewHelper();

        $this->injectDependenciesIntoViewHelper($this->viewHelper);
        $this->inject($this->viewHelper, 'tag', new TagBuilder());

        /** @var \TYPO3\CMS\Fluid\Core\ViewHelper\ArgumentDefinition[] $arguments */
        $arguments = $this->viewHelper->prepareArguments();
        $argumentValues = [];
        foreach ($arguments as $argument) {
            $defaultValue = $argument->getDefaultValue();
            if ($defaultValue) {
                $argumentValues[$argument->getName()] = $defaultValue;
            }
        }

        $this->viewHelper->setArguments($argumentValues);

        $this->viewHelper->validateArguments();
        $this->viewHelper->initialize();
    }

    /**
     * @test
     */
    public function renderWrapsValueInFormGroupDiv()
    {
        $this->viewHelper->setRenderChildrenClosure(
            function () {
                return '';
            }
        );
        $this->assertEquals('<div class="form-group"></div>', $this->viewHelper->render());
    }
}
