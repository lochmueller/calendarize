<?php

declare(strict_types=1);

namespace HDNET\Calendarize\ViewHelpers;

use HDNET\Calendarize\Seo\CalendarizeTitleProvider;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

/**
 * TitleTagViewHelper.
 *
 * @see https://github.com/georgringer/news/blob/master/Classes/ViewHelpers/TitleTagViewHelper.php
 */
class TitleTagViewHelper extends AbstractViewHelper
{
    /**
     * Render the title function.
     *
     * @return string
     */
    public function render(): void
    {
        $content = trim((string)$this->renderChildren());
        if (!empty($content)) {
            GeneralUtility::makeInstance(CalendarizeTitleProvider::class)->setTitle($content);
        }
    }
}
