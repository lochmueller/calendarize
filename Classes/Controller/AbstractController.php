<?php
/**
 * Abstract controller
 *
 * @author  Tim Lochmüller
 */

namespace HDNET\Calendarize\Controller;

use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

/**
 * Abstract controller
 */
abstract class AbstractController extends ActionController
{

    /**
     * Init all actions
     */
    public function initializeAction()
    {
        $this->checkStaticTemplateIsIncluded();
        parent::initializeAction();
    }

    /**
     * Check if the static template is included
     */
    protected function checkStaticTemplateIsIncluded()
    {
        if (!isset($this->settings['dateFormat'])) {
            $this->addFlashMessage('Basic configuration settings are missing. It seems, that the Static Extension TypoScript is not loaded to your TypoScript configuration. Please add the calendarize TS to your TS settings.',
                'Configuration Error', FlashMessage::ERROR);
        }
    }
}