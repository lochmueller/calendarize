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
class IndexViewHelper extends AbstractLinkViewHelper
{
    /**
     * Init arguments.
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('index', Index::class, '', true);
        $this->registerArgument('pageUid', 'int', '', false, 0);
        $this->registerArgument('absolute', 'bool', '', false, false);
    }

    /**
     * Render the link to the given index.
     *
     * @return string
     */
    public function render()
    {
        if (!\is_object($this->arguments['index'])) {
            $this->logger->error('Do not call index viewhelper without index');

            return $this->renderChildren();
        }
        $additionalParams = [
            'tx_calendarize_calendar' => [
                'index' => $this->arguments['index']->getUid(),
            ],
        ];

        return parent::renderLink($this->getPageUid($this->arguments['pageUid'], 'detailPid'), $additionalParams, (bool)$this->arguments['absolute']);
    }
}
