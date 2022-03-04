<?php

/**
 * Link to the booking page.
 */
declare(strict_types=1);

namespace HDNET\Calendarize\ViewHelpers\Link;

use HDNET\Calendarize\Domain\Model\Index;

/**
 * Link to the booking page.
 */
class BookingViewHelper extends AbstractActionViewHelper
{
    protected $controllerName = 'Booking';
    protected $actionName = 'booking';

    /**
     * Init arguments.
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('index', Index::class, '', true);
    }

    /**
     * Render the link to the given booking page for the index.
     *
     * @return string
     */
    public function render()
    {
        $pluginArgs = [
            'index' => $this->arguments['index']->getUid(),
        ];

        return $this->renderExtbaseLink($pluginArgs, $this->getPageUid($this->arguments['pageUid'], 'bookingPid'));
    }
}
