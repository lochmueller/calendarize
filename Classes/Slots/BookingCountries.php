<?php

/**
 * BookingCountries.
 */
declare(strict_types=1);

namespace HDNET\Calendarize\Slots;

use SJBR\StaticInfoTables\Domain\Repository\CountryRepository;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * BookingCountries.
 */
class BookingCountries
{
    /**
     * Booking slot.
     *
     * @param $index
     * @param $extended
     * @param $settings
     * @param $contentObject
     *
     * @return array
     */
    public function bookingSlot($index, $extended, $settings, $contentObject)
    {
        $extended['countries'] = $this->getCountrySelection();

        return [
            'index' => $index,
            'extended' => $extended,
            'settings' => $settings,
            'contentObject' => $contentObject,
        ];
    }

    /**
     * Send slot.
     *
     * @param $request
     * @param $extended
     * @param $settings
     * @param $contentObject
     *
     * @return array
     */
    public function sendSlot($request, $extended, $settings, $contentObject)
    {
        $extended['countries'] = $this->getCountrySelection();

        return [
            'request' => $request,
            'extended' => $extended,
            'settings' => $settings,
            'contentObject' => $contentObject,
        ];
    }

    /**
     * Get country selection.
     *
     * @return array
     */
    protected function getCountrySelection()
    {
        if (!ExtensionManagementUtility::isLoaded('static_info_tables')) {
            return [];
        }
        $repository = GeneralUtility::makeInstance(ObjectManager::class)->get(CountryRepository::class);

        return $repository->findAll();
    }
}
