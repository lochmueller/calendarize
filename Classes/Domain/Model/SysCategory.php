<?php
/**
 * SysFileReference
 *
 * Enhance the core SysFileReference.
 */
namespace HDNET\Calendarize\Domain\Model;

use TYPO3\CMS\Extbase\Domain\Model\Category;

/**
 * Class SysFileReference
 *
 *
 * @db sys_category
 */
class SysCategory extends Category
{

    /**
     * Import ID if the item is based on EXT:cal import or ICS strukture.
     *
     * @var string
     * @db varchar(100) DEFAULT '' NOT NULL
     */
    protected $importId;
}
