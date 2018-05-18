<?php

namespace Sto\Tellmatic\Tests\Unit\ViewHelpers\Form;

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
