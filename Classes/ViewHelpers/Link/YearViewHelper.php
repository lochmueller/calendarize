<?php
/**
 * Link to the year
 *
 * @author  Tim Lochmüller
 */

namespace HDNET\Calendarize\ViewHelpers\Link;

/**
 * Link to the year
 *
 * @author Tim Lochmüller
 */
class YearViewHelper extends AbstractLinkViewHelper
{

    /**
     * Render the link to the given day
     *
     * @param \DateTime $date
     * @param int       $pageUid
     *
     * @return string
     */
    public function render(\DateTime $date, $pageUid = null)
    {
        $additionalParams = [
            'tx_calendarize_calendar' => [
                'year' => $date->format('Y'),
            ],
        ];
        return parent::renderLink($this->getPageUid($pageUid), $additionalParams);
    }
}
