<?php

/**
 * Link to the index.
 */
declare(strict_types=1);

namespace HDNET\Calendarize\ViewHelpers\Link;

use HDNET\Calendarize\Domain\Model\Index;

/**
 * Link to the index.
 */
class IndexViewHelper extends AbstractActionViewHelper
{
    protected $actionName = 'detail';

    /**
     * Init arguments.
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('index', Index::class, '', true);
    }

    /**
     * Render the link to the given index.
     *
     * @return string
     */
    public function render()
    {
        $pluginArgs = [
            'index' => $this->arguments['index']->getUid(),
        ];

        return $this->renderExtbaseLink($pluginArgs, $this->getPageUid($this->arguments['pageUid'], 'detailPid'));
    }
}
