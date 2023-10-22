<?php

declare(strict_types=1);

namespace HDNET\Calendarize\ViewHelpers\Link;

/**
 * Link to the day.
 */
class DayViewHelper extends AbstractActionViewHelper
{
    protected ?string $actionName = 'day';

    /**
     * Init arguments.
     */
    public function initializeArguments(): void
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
            'day' => $date->format('j'),
        ];

        return $this->renderExtbaseLink($pluginArgs);
    }
}
