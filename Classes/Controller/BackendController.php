<?php

/**
 * BackendController.
 */
declare(strict_types=1);

namespace HDNET\Calendarize\Controller;

use HDNET\Calendarize\Domain\Model\Index;

/**
 * BackendController.
 */
class BackendController extends AbstractController
{
    /**
     * Basic backend list.
     */
    public function listAction()
    {
        $this->settings['timeFormat'] = 'H:i';
        $this->settings['dateFormat'] = 'd.m.Y';

        $this->view->assignMultiple([
            'indices' => $this->indexRepository->findAllForBackend(),
            'typeLocations' => $this->getDifferentTypesAndLocations(),
            'settings' => $this->settings,
        ]);
    }

    /**
     * Get the differnet locations for new entries.
     *
     * @return array
     */
    protected function getDifferentTypesAndLocations()
    {
        $typeLocations = [];
        foreach ($this->indexRepository->findDifferentTypesAndLocations() as $entry) {
            /* @var $entry Index */
            $typeLocations[$entry->getForeignTable()][$entry->getPid()] = $entry->getConfiguration()['uniqueRegisterKey'];
        }

        return $typeLocations;
    }
}
