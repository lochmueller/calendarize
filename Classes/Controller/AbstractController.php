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
     * Extend the view by the slot class and name and assign the variable to the view
     *
     * @param array  $variables
     * @param string $slotName
     * @param string $slotClass
     */
    protected function slotExtendedAssignMultiple(array $variables, $slotName, $slotClass)
    {
        // @todo call slot
        $this->view->assignMultiple($variables);
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