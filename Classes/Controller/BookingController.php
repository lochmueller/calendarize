<?php
/**
 * BookingController
 *
 * @author  Tim Lochmüller
 */

namespace HDNET\Calendarize\Controller;

use HDNET\Calendarize\Domain\Model\Index;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

/**
 * BookingController
 */
class BookingController extends AbstractController
{

    /**
     * Form action
     *
     * @param \HDNET\Calendarize\Domain\Model\Index $index
     */
    public function formAction(Index $index = null)
    {
        DebuggerUtility::var_dump($index);
    }

    /**
     * Send action
     */
    public function sendAction()
    {

    }
}