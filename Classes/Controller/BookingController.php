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
    public function bookingAction(Index $index = null)
    {
        $this->view->assign('index', $index);

        $this->slotExtendedAssignMultiple([
            'index' => $index,
        ], __CLASS__, __FUNCTION__);
    }

    /**
     * Send action
     */
    public function sendAction()
    {


        $this->slotExtendedAssignMultiple([
        ], __CLASS__, __FUNCTION__);
    }
}
