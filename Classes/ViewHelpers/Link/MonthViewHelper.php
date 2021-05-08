<?php

/**
 * Link to the month.
 */
declare(strict_types=1);

namespace HDNET\Calendarize\ViewHelpers\Link;

/**
 * Link to the month.
 */
class MonthViewHelper extends AbstractActionViewHelper
{
    protected $actionName = 'month';

    /**
     * Init arguments.
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('date', \DateTimeInterface::class, '', true);
    }

    /**
     * Render the link to the given day.
     *
     * @return string
     */
    public function render()
    {
        $date = $this->arguments['date'];
        $pluginArgs = [
            'year' => $date->format('Y'),
            'month' => $date->format('n'),
        ];

        return $this->renderExtbaseLink($pluginArgs);
    }
}
