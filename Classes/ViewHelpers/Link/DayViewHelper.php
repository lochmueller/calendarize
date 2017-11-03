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
     * Init arguments
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('date', \DateTime::class, '', true);
        $this->registerArgument('pageUid', 'int', '', false, 0);
    }

    /**
     * Render the link to the given day.
     *
     * @return string
     */
    public function render()
    {
        $date = $this->arguments['date'];
        $additionalParams = [
            'tx_calendarize_calendar' => [
                'year' => $date->format('Y'),
                'month' => $date->format('n'),
                'day' => $date->format('j'),
            ],
        ];

        return parent::renderLink($this->getPageUid($this->arguments['pageUid']), $additionalParams);
    }
}
