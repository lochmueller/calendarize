<?php

/**
 * Edit Record ViewHelper, see FormEngine logic.
 */
declare(strict_types=1);

namespace HDNET\Calendarize\ViewHelpers;

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Edit Record ViewHelper, see FormEngine logic.
 *
 * @internal
 */
class EditRecordViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper
{
    /**
     * Init arguments.
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('parameters', 'string', 'A set of GET params to send to FormEngine', true);
    }

    /**
     * Returns a URL to link to FormEngine.
     *
     * @return string URL to FormEngine module + parameters
     *
     * @see \TYPO3\CMS\Backend\Utility\BackendUtility::getModuleUrl()
     */
    public function render()
    {
        $parameters = $this->arguments['parameters'];
        $returnUrl = BackendUtility::getModuleUrl('web_CalendarizeCalendarize');
        $parameters = GeneralUtility::explodeUrl2Array($parameters . '&returnUrl=' . \urlencode($returnUrl));

        return BackendUtility::getModuleUrl('record_edit', $parameters);
    }
}
