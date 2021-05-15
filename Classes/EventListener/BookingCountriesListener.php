<?php

namespace HDNET\Calendarize\EventListener;

use HDNET\Calendarize\Event\GenericActionAssignmentEvent;
use SJBR\StaticInfoTables\Domain\Repository\CountryRepository;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

class BookingCountriesListener
{
    public function __invoke(GenericActionAssignmentEvent $event)
    {
        if (\HDNET\Calendarize\Controller\BookingController::class === $event->getClassName()) {
            $variables = $event->getVariables();
            $variables['extended']['countries'] = $this->getCountrySelection();
            $event->setVariables($variables);
        }
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
