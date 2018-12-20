<?php

/**
 * Link to the week.
 */
declare(strict_types=1);

namespace HDNET\Calendarize\ViewHelpers\Link;

/**
 * Link to the week.
 */
class WeekViewHelper extends AbstractLinkViewHelper
{
    /**
     * Init arguments.
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
        if (!\is_object($this->arguments['date'])) {
            $this->logger->error('Do not call week viewhelper without date');

            return $this->renderChildren();
        }
        $date = $this->arguments['date'];
        $additionalParams = [
            'tx_calendarize_calendar' => [
                'year' => $this->getCwYear($date),
                'week' => $date->format('W'),
            ],
        ];

        return parent::renderLink($this->getPageUid($this->arguments['pageUid']), $additionalParams);
    }

    /**
     * @param \DateTime $date
     * @return int
     */
    protected function getCwYear(\DateTime $date)
    {
        $year = (int)$date->format('Y');
        if ($date->format('m') === '01' && ($date->format('W') === '52' || $date->format('W') === '53')) {
            $year--;
        } else {
            if ($date->format('m') === '12' && $date->format('W') === '01') {
                $year++;
            }
        }
        return $year;
    }
}
