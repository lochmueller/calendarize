<?php

/**
 * Link to the week.
 */
declare(strict_types=1);

namespace HDNET\Calendarize\ViewHelpers\Link;

/**
 * Link to the week.
 */
class WeekViewHelper extends AbstractActionViewHelper
{
    protected $actionName = 'week';

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
            'year' => (int)$date->format('o'),
            'week' => (int)$date->format('W'),
        ];

        return $this->renderExtbaseLink($pluginArgs);
    }
}
