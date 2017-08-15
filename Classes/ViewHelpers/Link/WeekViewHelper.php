<?php
/**
 * Link to the week
 *
 */
namespace HDNET\Calendarize\ViewHelpers\Link;

/**
 * Link to the week
 *
 */
class WeekViewHelper extends AbstractLinkViewHelper
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
                'week' => $date->format('W'),
            ],
        ];
        return parent::renderLink($this->getPageUid($pageUid), $additionalParams);
    }
}
