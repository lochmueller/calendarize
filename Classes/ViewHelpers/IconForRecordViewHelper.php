<?php

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */
declare(strict_types=1);

namespace HDNET\Calendarize\ViewHelpers;

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

class IconForRecordViewHelper extends \TYPO3\CMS\Core\ViewHelpers\IconForRecordViewHelper
{
    /**
     * Initializes the arguments.
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('uid', 'int', '', true);
        $this->overrideArgument('row', 'array', 'the record row', false);
    }

    /**
     * @param array                     $arguments
     * @param \Closure                  $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     *
     * @return string
     */
    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext)
    {
        $arguments['row'] = BackendUtility::getRecordWSOL($arguments['table'], $arguments['uid']);
        if (!\is_array($arguments['row'])) {
            $arguments['row'] = [];
        }

        return parent::renderStatic($arguments, $renderChildrenClosure, $renderingContext);
    }
}
