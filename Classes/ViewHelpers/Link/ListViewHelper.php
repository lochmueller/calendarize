<?php

/**
 * Link to the list.
 */
declare(strict_types=1);

namespace HDNET\Calendarize\ViewHelpers\Link;

/**
 * Link to the list.
 */
class ListViewHelper extends AbstractLinkViewHelper
{
    /**
     * Init arguments.
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('pageUid', 'int', '', false, 0);
    }

    /**
     * Render the link to the given list.
     *
     * @return string
     */
    public function render()
    {
        return parent::renderLink($this->getPageUid($this->arguments['pageUid'], 'listPid'));
    }
}
