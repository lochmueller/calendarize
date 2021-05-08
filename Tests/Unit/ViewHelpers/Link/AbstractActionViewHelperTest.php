<?php

declare(strict_types=1);

namespace HDNET\Calendarize\Tests\Unit\ViewHelpers\Link;

use HDNET\Calendarize\ViewHelpers\Link\AbstractActionViewHelper;
use TYPO3\CMS\Extbase\Mvc\Controller\ControllerContext;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContext;
use TYPO3\TestingFramework\Fluid\Unit\ViewHelpers\ViewHelperBaseTestcase;
use TYPO3Fluid\Fluid\Core\ViewHelper\TagBuilder;

class AbstractActionViewHelperTest extends ViewHelperBaseTestcase
{
    /**
     * @var AbstractActionViewHelper
     */
    protected $viewHelper;

    /**
     * @var UriBuilder
     */
    protected $uriBuilder;

    protected function setUp(): void
    {
        parent::setUp();
        $GLOBALS['TSFE'] = new \stdClass();
        $GLOBALS['TSFE']->id = 0;

        $this->uriBuilder = $this->createMock(UriBuilder::class);
        $this->uriBuilder->expects(self::any())->method('reset')->willReturn($this->uriBuilder);
        $this->uriBuilder->expects(self::any())->method('setTargetPageUid')->willReturn($this->uriBuilder);
        $this->uriBuilder->expects(self::any())->method('setSection')->willReturn($this->uriBuilder);
        $this->uriBuilder->expects(self::any())->method('setArguments')->willReturn($this->uriBuilder);
        $this->uriBuilder->expects(self::any())->method('setCreateAbsoluteUri')->willReturn($this->uriBuilder);

        $this->controllerContext = $this->createMock(ControllerContext::class);
        $this->controllerContext->expects(self::any())->method('getUriBuilder')->willReturn($this->uriBuilder);
        $this->controllerContext->expects(self::any())->method('getRequest')->willReturn($this->request->reveal());

        $this->renderingContext = $this->getMockBuilder(RenderingContext::class)
            ->onlyMethods(['getControllerContext'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->renderingContext->expects(self::any())->method('getControllerContext')->willReturn($this->controllerContext);
        $this->renderingContext->setControllerContext($this->controllerContext);

        $this->viewHelper = $this->getAccessibleMock(AbstractActionViewHelper::class, ['renderChildren']);
        $this->injectDependenciesIntoViewHelper($this->viewHelper);
        $this->viewHelper->initializeArguments();
        $this->tagBuilder = $this->createMock(TagBuilder::class);
        $this->viewHelper->_set('tag', $this->tagBuilder);
    }

    public function testRenderExtbaseLinkForValidLinkTarget()
    {
        $this->uriBuilder->expects(self::once())->method('uriFor')->willReturn('event/test');
        $this->tagBuilder->expects(self::once())->method('render');
        $this->viewHelper->renderExtbaseLink();
    }

    public function testRenderExtbaseLinkForNonValidLinkTarget()
    {
        $this->uriBuilder->expects(self::once())->method('uriFor')->willReturn('');
        $this->tagBuilder->expects(self::never())->method('render');
        $this->viewHelper->renderExtbaseLink();
    }
}
