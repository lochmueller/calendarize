<?php

/**
 * Days in week view helper.
 */
declare(strict_types=1);

namespace HDNET\Calendarize\ViewHelpers\Loop;

/**
 * Days in week view helper.
 */
class DaysInWeekViewHelper extends AbstractLoopViewHelper
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
     * Get items.
     *
     * @param \DateTime      $date
     * @param \DateTime|null $originalDate
     *
     * @return array
     */
    protected function getItems(\DateTime $date, \DateTime $originalDate = null)
    {
        if (null === $originalDate) {
            $originalDate = clone $date;
        }

        $days = [];
        $move = (int)($date->format('N') - ((int)$this->arguments['weekStartsAt']));
        $date->modify('-' . $move . ' days');
        $inWeek = false;
        for ($i = 0; $i < 7; ++$i) {
            $addDate = clone $date;
            if ($addDate->format('d.m.Y') === $originalDate->format('d.m.Y')) {
                $inWeek = true;
            }
            $days[] = [
                'day' => $i,
                'date' => $addDate,
            ];
            $date->modify('+1 day');
        }

        if (!$inWeek) {
            $date = clone $originalDate;
            $date->modify(($originalDate > $days[0]['date'] ? '+' : '-') . '7 days');

            return $this->getItems($date, $originalDate);
        }

        return $days;
    }
}
