<?php
/**
 * SysFileReference.
 *
 * Enhance the core SysFileReference.
 */

namespace HDNET\Calendarize\Domain\Model;

use TYPO3\CMS\Extbase\Domain\Model\FileReference;

/**
 * Class SysFileReference.
 *
 *
 * @db sys_file_reference
 */
class SysFileReference extends FileReference
{
    /**
     * Import ID if the item is based on EXT:cal import or ICS strukture.
     *
     * @var string
     *
     * @db
     */
    protected $importId;
}
