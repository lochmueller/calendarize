<?php

/**
 * Weeks in month view helper.
 */
declare(strict_types=1);

namespace HDNET\Calendarize\ViewHelpers\Loop;

/**
 * Weeks in month view helper.
 */
class WeeksInMonthViewHelper extends AbstractLoopViewHelper
{
    /**
     * Initialize all arguments. You need to override this method and call
     * $this->registerArgument(...) inside this method, to register all your arguments.
     *
     * @api
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('weekStartsAt', 'int', 'Number between 1 and 7', true, 1);
    }

    /**
     * Get the items.
     *
     * @param \DateTime $date
     *
     * @return array
     */
    protected function getItems(\DateTime $date)
    {
        $weeks = [];

        $dateClone = clone $date;
        $dateClone->modify('first day of this month');

        $monthCheck = $dateClone->format('m');
        while ((int)$monthCheck === (int)$dateClone->format('m')) {
            $week = (int)$dateClone->format('W');
            if (!isset($weeks[$week])) {
                $weeks[$week] = [
                    'week' => $week,
                    'date' => clone $dateClone,
                ];
            }
            $dateClone->modify('+1 day');
        }

        return $weeks;
    }
}
