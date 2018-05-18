<?php

namespace Sto\Tellmatic\Tests\Unit\ViewHelpers\Form;

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
