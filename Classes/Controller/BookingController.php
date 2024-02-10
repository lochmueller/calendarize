<?php

declare(strict_types=1);

namespace HDNET\Calendarize\Controller;

use HDNET\Calendarize\Domain\Model\Index;
use HDNET\Calendarize\Domain\Model\Request\AbstractBookingRequest;
use HDNET\Calendarize\Validation\Validator\BookingRequestValidator;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Extbase\Annotation as Extbase;

/**
 * BookingController.
 */
class BookingController extends AbstractController
{
    /**
     * Form action.
     */
    public function bookingAction(?Index $index = null): ResponseInterface
    {
        $this->view->assign('index', $index);

        $this->eventExtendedAssignMultiple([
            'index' => $index,
        ], __CLASS__, __FUNCTION__);

        return $this->htmlResponse($this->view->render());
    }

    /**
     * Send action.
     */
    #[Extbase\Validate(['validator' => BookingRequestValidator::class, 'param' => 'request'])]
    public function sendAction(Index $index, AbstractBookingRequest $request): ResponseInterface
    {
        $request->setIndex($index);

        // Use the Slot to handle the request
        $this->eventExtendedAssignMultiple([
            'request' => $request,
        ], __CLASS__, __FUNCTION__);

        return $this->htmlResponse($this->view->render());
    }
}
