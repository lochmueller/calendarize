<?php

/**
 * SysFileReference.
 *
 * Enhance the core SysFileReference.
 */
declare(strict_types=1);

namespace HDNET\Calendarize\Domain\Model;

use HDNET\Autoloader\Annotation\DatabaseField;
use HDNET\Autoloader\Annotation\DatabaseTable;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;

/**
 * Class SysFileReference.
 *
 * @DatabaseTable(tableName="sys_file_reference")
 */
class SysFileReference extends FileReference
{
    /**
     * Import ID if the item is based on EXT:cal import or ICS strukture.
     *
     * @var string
     *
     * @DatabaseField("string")
     */
    protected $importId;
}
