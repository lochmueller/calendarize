<?php

/**
 * IfExtensionLoadedViewHelper.
 */
declare(strict_types=1);

namespace HDNET\Calendarize\ViewHelpers;

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractConditionViewHelper;

/**
 * IfExtensionLoadedViewHelper.
 */
class IfExtensionLoadedViewHelper extends AbstractConditionViewHelper
{
    /**
     * Init arguments.
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('extensionKey', 'string', 'The extension key of the extension', true);
    }

    /**
     * Add the condition.
     *
     * @param array|null $arguments
     *
     * @return bool
     */
    public static function evaluateCondition($arguments = null)
    {
        if (!isset($arguments['extensionKey'])) {
            return false;
        }

        return ExtensionManagementUtility::isLoaded($arguments['extensionKey']);
    }
}
