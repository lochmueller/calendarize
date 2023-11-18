<?php

declare(strict_types=1);

namespace HDNET\Calendarize\ViewHelpers;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContext;

/**
 * LanguageViewHelper.
 */
class LanguageViewHelper extends AbstractViewHelper
{
    /**
     * Get the current language ISO code.
     */
    public function render(): string
    {
        /** @var SiteLanguage $language */
        $language = $this->getRequest()->getAttribute('language');

        return $language->getLocale()->getLanguageCode();
    }

    protected function getRequest(): ServerRequestInterface
    {
        /** @var RenderingContext $renderingContext */
        $renderingContext = $this->renderingContext;

        return $renderingContext->getRequest();
    }
}
