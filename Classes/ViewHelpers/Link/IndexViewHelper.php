<?php
/**
 * Link to the index
 *
 * @author  Tim Lochmüller
 */
namespace HDNET\Calendarize\ViewHelpers\Link;

use HDNET\Calendarize\Domain\Model\Index;

/**
 * Link to the index
 *
 * @author Tim Lochmüller
 */
class IndexViewHelper extends AbstractLinkViewHelper
{

    /**
     * Render the link to the given index
     *
     * @param Index $index
     * @param int   $pageUid
     * @param bool  $absolute
     *
     * @return string
     */
    public function render(Index $index, $pageUid = null, $absolute = false)
    {
        $additionalParams = [
            'tx_calendarize_calendar' => [
                'index' => $index->getUid()
            ],
        ];
        return parent::renderLink($this->getPageUid($pageUid, 'detailPid'), $additionalParams, (bool) $absolute);
    }
}
