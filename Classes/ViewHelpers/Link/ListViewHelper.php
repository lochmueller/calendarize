<?php

declare(strict_types=1);

namespace HDNET\Calendarize\ViewHelpers\Link;

/**
 * Link to the list.
 */
class ListViewHelper extends AbstractActionViewHelper
{
    protected ?string $actionName = 'list';

    /**
     * Init arguments.
     */
    public function initializeArguments(): void
    {
        parent::initializeArguments();
    }

    /**
     * Render the link to the given list.
     *
     * @return string
     */
    public function render()
    {
        return $this->renderExtbaseLink([], $this->getPageUid('listPid'));
    }
}
