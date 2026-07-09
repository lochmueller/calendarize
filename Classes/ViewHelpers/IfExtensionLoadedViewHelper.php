<?php

declare(strict_types=1);

namespace HDNET\Calendarize\ViewHelpers;

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractConditionViewHelper;

/**
 * IfExtensionLoadedViewHelper.
 */
class IfExtensionLoadedViewHelper extends AbstractConditionViewHelper
{
    /**
     * Init arguments.
     */
    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('extensionKey', 'string', 'The extension key of the extension', true);
    }

    /**
     * @param array<string, mixed> $arguments
     */
    public static function verdict(array $arguments, RenderingContextInterface $renderingContext): bool
    {
        if (!isset($arguments['extensionKey'])) {
            return false;
        }
        return ExtensionManagementUtility::isLoaded($arguments['extensionKey']);
    }
}
