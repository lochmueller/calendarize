<?php
/**
 * Uri to the index
 *
 */
namespace HDNET\Calendarize\ViewHelpers\Uri;

use HDNET\Calendarize\Domain\Model\Index;

/**
 * Uri to the index
 */
class IndexViewHelper extends \HDNET\Calendarize\ViewHelpers\Link\IndexViewHelper
{

    /**
     * Render the uri to the given index
     *
     * @param Index $index
     * @param int   $pageUid
     * @param bool  $absolute
     *
     * @return string
     */
    public function render(Index $index, $pageUid = null, $absolute = false)
    {
        parent::render($index, $pageUid, (bool) $absolute);
        return $this->lastHref;
    }
}
