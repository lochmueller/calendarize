<?php

/**
 * BookingController.
 */
declare(strict_types=1);

namespace HDNET\Calendarize\Controller;

use HDNET\Calendarize\Domain\Model\Index;
use HDNET\Calendarize\Domain\Model\Request\AbstractBookingRequest;
use TYPO3\CMS\Extbase\Annotation as Extbase;

/**
 * BookingController.
 */
class BookingController extends AbstractCompatibilityController
{
    /**
     * Form action.
     *
     * @param \HDNET\Calendarize\Domain\Model\Index $index
     */
    public function bookingAction(Index $index = null)
    {
        $this->view->assign('index', $index);

        $this->eventExtendedAssignMultiple([
            'index' => $index,
        ], __CLASS__, __FUNCTION__);
    }

    /**
     * Send action.
     *
     * @param Index                                                          $index
     * @param \HDNET\Calendarize\Domain\Model\Request\AbstractBookingRequest $request
     *
     * @Extbase\Validate("\HDNET\Calendarize\Validation\Validator\BookingRequestValidator", param="request")
     */
    public function sendAction(Index $index, AbstractBookingRequest $request)
    {
        $request->setIndex($index);

        // Use the Slot to handle the request

        $this->eventExtendedAssignMultiple([
            'request' => $request,
        ], __CLASS__, __FUNCTION__);
    }
}
