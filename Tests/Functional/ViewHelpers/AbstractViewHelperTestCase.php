<?php

declare(strict_types=1);

namespace HDNET\Calendarize\Tests\Functional\ViewHelpers;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContextFactory;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;
use TYPO3Fluid\Fluid\View\TemplateView;

abstract class AbstractViewHelperTestCase extends FunctionalTestCase
{
    protected array $coreExtensionsToLoad = ['extbase', 'fluid'];
    protected array $testExtensionsToLoad = ['typo3conf/ext/calendarize'];
    protected bool $initializeDatabase = false;

    protected function renderTemplate(string $template, array $variables = []): mixed
    {
        $renderingContext = GeneralUtility::makeInstance(RenderingContextFactory::class)->create();
        $renderingContext->getTemplatePaths()->setTemplateSource($template);
        $view = new TemplateView($renderingContext);
        $view->assignMultiple($variables);
        return $view->render();
    }
}
