<?php
/**
 * BookingController
 *
 * @author  Tim Lochmüller
 */

namespace HDNET\Calendarize\Controller;

use HDNET\Calendarize\Domain\Model\Index;

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
        $this->view->assign('index', $index);
    }

    /**
     * Send action
     */
    public function sendAction()
    {

    }
}