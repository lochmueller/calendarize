<?php

namespace HDNET\Calendarize\EventListener;

use HDNET\Calendarize\Controller\BookingController;
use HDNET\Calendarize\Event\GenericActionAssignmentEvent;
use SJBR\StaticInfoTables\Domain\Repository\CountryRepository;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

class BookingCountriesListener
{
    public function __invoke(GenericActionAssignmentEvent $event): void
    {
        if (BookingController::class === $event->getClassName()) {
            $variables = $event->getVariables();
            $variables['extended']['countries'] = $this->getCountrySelection();
            $event->setVariables($variables);
        }
    }

    /**
     * Get country selection.
     */
    protected function getCountrySelection(): array|QueryResultInterface
    {
        if (!ExtensionManagementUtility::isLoaded('static_info_tables')) {
            return [];
        }
        $repository = GeneralUtility::makeInstance(CountryRepository::class);

        return $repository->findAll();
    }
}
