<?php

declare(strict_types=1);

namespace HDNET\Calendarize\ViewHelpers;

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

class IconForRecordViewHelper extends AbstractViewHelper
{
    use CompileWithRenderStatic;

    /**
     * ViewHelper returns HTML, thus we need to disable output escaping
     *
     * @var bool
     */
    protected $escapeOutput = false;

    public function initializeArguments(): void
    {
        $this->registerArgument('table', 'string', 'the table for the record icon', true);
        $this->registerArgument('row', 'array', 'the record row', true);
        $this->registerArgument('size', 'string', 'the icon size', false, Icon::SIZE_SMALL);
        $this->registerArgument('alternativeMarkupIdentifier', 'string', 'alternative markup identifier');
    }

    /**
     * @return string
     */
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {
        $arguments['row'] = BackendUtility::getRecordWSOL($arguments['table'], $arguments['uid']);
        if (!is_array($arguments['row'])) {
            $arguments['row'] = [];
        }

        return parent::renderStatic($arguments, $renderChildrenClosure, $renderingContext);
    }
}
