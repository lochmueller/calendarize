<?php

/**
 * BookingCountries
 */

namespace HDNET\Calendarize\Slots;

use SJBR\StaticInfoTables\Domain\Repository\CountryRepository;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * BookingCountries
 */
class BookingCountries
{

    /**
     * @param $index
     * @param $extended
     * @param $settings
     *
     * @return array
     */
    public function bookingSlot($index, $extended, $settings)
    {
        $extended['countries'] = $this->getCountrySelection();
        return [
            'index' => $index,
            'extended' => $extended,
            'settings' => $settings,
        ];
    }

    /**
     * @param $request
     * @param $extended
     * @param $settings
     *
     * @return array
     */
    public function sendSlot($request, $extended, $settings)
    {
        $extended['countries'] = $this->getCountrySelection();
        return [
            'request' => $request,
            'extended' => $extended,
            'settings' => $settings,
        ];
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
        $objectManager = new ObjectManager();
        $repository = $objectManager->get(CountryRepository::class);
        return $repository->findAll();
    }
}
