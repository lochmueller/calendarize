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

        $options = $this->getOptions();
        $typeLocations = $this->getDifferentTypesAndLocations();

        $this->view->assignMultiple([
            'indices' => $this->indexRepository->findAllForBackend($options),
            'typeLocations' => $typeLocations,
            'pids' => $this->getPids($typeLocations),
            'settings' => $this->settings,
            'options' => $options,
        ]);
    }

    /**
     * Option action.
     *
     * @param \HDNET\Calendarize\Domain\Model\Request\OptionRequest $options
     */
    public function optionAction(OptionRequest $options)
    {
        $GLOBALS['BE_USER']->setAndSaveSessionData('calendarize_be', \serialize($options));
        $this->addFlashMessage('Options saved', '', FlashMessage::OK, true);
        $this->forward('list');
    }

    protected function getPids(array $typeLocations)
    {
        $pids = [];
        foreach ($typeLocations as $locations) {
            $pids = \array_merge($pids, \array_keys($locations));
        }
        $pids = \array_unique($pids);

        return \array_combine($pids, $pids);
    }

    /**
     * Get option request.
     *
     * @return OptionRequest
     */
    protected function getOptions()
    {
        try {
            $info = $GLOBALS['BE_USER']->getSessionData('calendarize_be');
            $object = @\unserialize((string)$info);
            if ($object instanceof OptionRequest) {
                return $object;
            }

            return new OptionRequest();
        } catch (\Exception $exception) {
            return new OptionRequest();
        }
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
