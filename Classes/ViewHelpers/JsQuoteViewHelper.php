<?php
/**
 * Quote JS.
 */

namespace HDNET\Calendarize\ViewHelpers;

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Quote JS.
 */
class JsQuoteViewHelper extends AbstractViewHelper
{
    /**
     * Render the Quote JS information.
     *
     * @param string $content
     *
     * @return string
     */
    public function render($content = null)
    {
        $content = $content === null ? $this->renderChildren() : $content;

        return GeneralUtility::quoteJSvalue($content);
    }
}
