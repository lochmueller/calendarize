<?php

declare(strict_types=1);

namespace HDNET\Calendarize\ViewHelpers;

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Quote JS.
 */
class JsQuoteViewHelper extends AbstractViewHelper
{
    /**
     * Init arguments.
     */
    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('content', 'string', 'Override content', false);
    }

    /**
     * Render the Quote JS information.
     */
    public function render(): string
    {
        $content = null === $this->arguments['content'] || '' === trim((string)$this->arguments['content'])
            ? $this->renderChildren()
            : $this->arguments['content'];

        return GeneralUtility::quoteJSvalue($content);
    }
}
