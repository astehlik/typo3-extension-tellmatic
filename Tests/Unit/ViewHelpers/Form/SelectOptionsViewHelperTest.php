<?php

namespace Sto\Tellmatic\Tests\Unit\ViewHelpers\Form;

use Nimut\TestingFramework\TestCase\ViewHelperBaseTestcase;
use Sto\Tellmatic\ViewHelpers\Form\SelectOptionsViewHelper;

class SelectOptionsViewHelperTest extends ViewHelperBaseTestcase
{
    /**
     * @var SelectOptionsViewHelper
     */
    private $viewHelper;

    protected function setUp()
    {
        parent::setUp();
        $this->viewHelper = new SelectOptionsViewHelper();
        $this->injectDependenciesIntoViewHelper($this->viewHelper);
        $this->viewHelper->initializeArguments();
    }

    /**
     * @test
     */
    public function renderReturnsEmptyArrayByDefault()
    {
        $this->viewHelper->setRenderChildrenClosure(
            function () {
                return '';
            }
        );
        $this->assertEquals([], $this->viewHelper->render('testfield'));
    }
}
