<?php

namespace Sto\Tellmatic\Tests\Unit\ViewHelpers\Link;

use Nimut\TestingFramework\TestCase\ViewHelperBaseTestcase;
use Sto\Tellmatic\ViewHelpers\Link\TypolinkViewHelper;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

class TypoLinkViewHelperTestextends extends ViewHelperBaseTestcase
{
    /**
     * @var TypolinkViewHelper
     */
    private $viewHelper;

    protected function setUp()
    {
        parent::setUp();
        $this->viewHelper = new TypolinkViewHelper();
        $this->injectDependenciesIntoViewHelper($this->viewHelper);

        $configurationManagerProphecy = $this->prophesize(ConfigurationManagerInterface::class);
        $configurationManagerProphecy->getContentObject()->willReturn(new ContentObjectRenderer());

        $this->inject($this->viewHelper, 'configurationManager', $configurationManagerProphecy->reveal());
        $this->viewHelper->initializeArguments();
    }

    /**
     * @test
     */
    public function renderLinksChildrenWithTypoLink()
    {
        $this->viewHelper->setRenderChildrenClosure(
            function () {
                return 'linktxt';
            }
        );

        $GLOBALS['TSFE'] = new TypoScriptFrontendController([], 0, 0);

        $this->assertEquals(
            '<a href="http://www.google.de">linktxt</a>',
            $this->viewHelper->render('http://www.google.de')
        );
    }
}
