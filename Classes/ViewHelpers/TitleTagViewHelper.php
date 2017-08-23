<?php

/**
 * TitleTagViewHelper.
 */

namespace HDNET\Calendarize\ViewHelpers;

/**
 * TitleTagViewHelper.
 *
 * @see https://github.com/georgringer/news/blob/master/Classes/ViewHelpers/TitleTagViewHelper.php
 */
class TitleTagViewHelper extends AbstractViewHelper
{
    /**
     * Render.
     */
    public function render()
    {
        $content = trim($this->renderChildren());
        if (!empty($content)) {
            $GLOBALS['TSFE']->altPageTitle = $content;
            $GLOBALS['TSFE']->indexedDocTitle = $content;
        }
    }
}
