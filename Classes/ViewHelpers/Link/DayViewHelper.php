<?php
/**
 * Link to the day.
 */

namespace HDNET\Calendarize\ViewHelpers\Link;

/**
 * Link to the day.
 */
class DayViewHelper extends AbstractLinkViewHelper
{
    /**
     * Render the link to the given day.
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
                'month' => $date->format('n'),
                'day' => $date->format('j'),
            ],
        ];

        return parent::renderLink($this->getPageUid($pageUid), $additionalParams);
    }
}
