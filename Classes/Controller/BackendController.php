<?php

/**
 * BackendController.
 */
declare(strict_types=1);

namespace HDNET\Calendarize\Controller;

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
            'indices' => $this->indexRepository->findAll(),
            'settings' => $this->settings,
        ]);
    }
}
