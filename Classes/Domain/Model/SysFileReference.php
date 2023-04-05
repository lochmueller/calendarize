<?php

declare(strict_types=1);

namespace HDNET\Calendarize\Domain\Model;

use TYPO3\CMS\Extbase\Domain\Model\FileReference;

/**
 * Class SysFileReference.
 */
class SysFileReference extends FileReference
{
    use ImportTrait;
}
