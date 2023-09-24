<?php

declare(strict_types=1);

namespace HDNET\Calendarize\Domain\Model;

use TYPO3\CMS\Extbase\Domain\Model\Category;

/**
 * Class SysFileReference.
 */
class SysCategory extends Category
{
    use ImportTrait;
}
