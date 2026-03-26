<?php

declare(strict_types=1);

namespace HDNET\Calendarize\ViewHelpers\Be;

use HDNET\Calendarize\ViewHelpers\AbstractViewHelper;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * Gets the record by uid from the table as an array.
 * Useful when you have the record as object and require it as row/array (e.g. core:iconForRecord).
 */
class RecordRowViewHelper extends AbstractViewHelper
{
    public function initializeArguments(): void
    {
        $this->registerArgument('table', 'string', 'the table for the record icon', true);
        $this->registerArgument('uid', 'int', 'UID of record', true);
    }

    public function render(): array
    {
        return BackendUtility::getRecordWSOL($this->arguments['table'], $this->arguments['uid']) ?? [];
    }
}
