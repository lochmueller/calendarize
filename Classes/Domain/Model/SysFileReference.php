<?php

/**
 * SysFileReference.
 *
 * Enhance the core SysFileReference.
 */
declare(strict_types=1);

namespace HDNET\Calendarize\Domain\Model;

use HDNET\Autoloader\Annotation\DatabaseTable;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;

/**
 * Class SysFileReference.
 *
 * @DatabaseTable(tableName="sys_file_reference")
 */
class SysFileReference extends FileReference
{
    use ImportTrait;
}
