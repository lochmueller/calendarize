<?php

/**
 * BackendController.
 */
declare(strict_types=1);

namespace HDNET\Calendarize\Controller;

use HDNET\Calendarize\Domain\Model\Request\OptionRequest;
use TYPO3\CMS\Core\Messaging\FlashMessage;

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
            'options' => $this->getOptions()
        ]);
    }

    /**
     * Option action
     *
     * @param \HDNET\Calendarize\Domain\Model\Request\OptionRequest $options
     */
    public function optionAction(OptionRequest $options)
    {

        // @todo save options

        $this->addFlashMessage('Options saved', '', FlashMessage::OK, true);
        $this->forward('list');
    }

    /**
     * Get option request
     *
     * @return OptionRequest
     */
    protected function getOptions()
    {
        return new OptionRequest();
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
            $typeLocations[$entry['foreign_table']][$entry['pid']] = $entry['unique_register_key'];
        }

        return $typeLocations;
    }
}
