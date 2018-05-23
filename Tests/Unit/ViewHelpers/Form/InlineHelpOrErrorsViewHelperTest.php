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
use Sto\Tellmatic\ViewHelpers\Form\InlineHelpOrErrorsViewHelper;

class InlineHelpOrErrorsViewHelperTest extends ViewHelperBaseTestcase
{
    /**
     * @var InlineHelpOrErrorsViewHelper
     */
    private $viewHelper;

    protected function setUp()
    {
        parent::setUp();
        $this->viewHelper = new InlineHelpOrErrorsViewHelper();
        $this->injectDependenciesIntoViewHelper($this->viewHelper);
        $this->viewHelper->initializeArguments();
    }

    /**
     * @test
     */
    public function renderReturnsEmptyStringByDefault()
    {
        $this->viewHelper->setRenderChildrenClosure(
            function () {
                return '';
            }
        );
        $this->assertEquals('', $this->viewHelper->render());
    }

    /**
     * @test
     */
    public function renderWrapsChildrenInHelpBlock()
    {
        $this->viewHelper->setRenderChildrenClosure(
            function () {
                return 'children';
            }
        );
        $this->assertEquals('<span class="help-block">children</span>', $this->viewHelper->render());
    }
}
