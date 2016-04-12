<?php
/**
 * BookingController
 *
 * @author  Tim Lochmüller
 */

namespace HDNET\Calendarize\Controller;

use HDNET\Calendarize\Domain\Model\Index;
use HDNET\Calendarize\Domain\Model\Request\AbstractBookingRequest;
use SJBR\StaticInfoTables\Domain\Repository\CountryRepository;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

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
            'countries' => $this->getCountrySelection(),
        ], __CLASS__, __FUNCTION__);
    }

    /**
     * Send action
     *
     * @param Index $index
     * @param \HDNET\Calendarize\Domain\Model\Request\AbstractBookingRequest $request
     */
    public function sendAction(Index $index, AbstractBookingRequest $request)
    {
        $request->setIndex($index);
        
        // Use the Slot to handle the request

        $this->slotExtendedAssignMultiple([
            'countries' => $this->getCountrySelection(),
            'request' => $request,
        ], __CLASS__, __FUNCTION__);
    }

    /**
     * Get country selection
     *
     * @return array
     */
    protected function getCountrySelection()
    {
        if (!ExtensionManagementUtility::isLoaded('static_info_tables')) {
            return [];
        }
        /** @var CountryRepository $repository */
        $repository = $this->objectManager->get('SJBR\\StaticInfoTables\\Domain\\Repository\\CountryRepository');
        return $repository->findAll();
    }
}
