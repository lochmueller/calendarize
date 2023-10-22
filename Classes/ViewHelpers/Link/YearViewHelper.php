<?php

declare(strict_types=1);

namespace HDNET\Calendarize\ViewHelpers\Link;

/**
 * Link to the year.
 */
class YearViewHelper extends AbstractActionViewHelper
{
    protected ?string $actionName = 'year';

    /**
     * Init arguments.
     */
    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('date', \DateTimeInterface::class, '', true);
    }

    /**
     * Render the link to the given year.
     *
     * @return string
     */
    public function render()
    {
        $date = $this->arguments['date'];
        $pluginArgs = [
            'year' => $date->format('Y'),
        ];

        return $this->renderExtbaseLink($pluginArgs);
    }
}
