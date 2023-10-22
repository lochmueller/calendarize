<?php

declare(strict_types=1);

namespace HDNET\Calendarize\ViewHelpers\Link;

use HDNET\Calendarize\Utility\DateTimeUtility;

/**
 * Link to the quarter.
 */
class QuarterViewHelper extends AbstractActionViewHelper
{
    protected ?string $actionName = 'quarter';

    /**
     * Init arguments.
     */
    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('date', \DateTimeInterface::class, '', true);
    }

    /**
     * Render the link to the given quarter.
     *
     * @return string
     */
    public function render()
    {
        $date = $this->arguments['date'];
        $pluginArgs = [
            'year' => $date->format('Y'),
            'quarter' => DateTimeUtility::getQuarter($date),
        ];

        return $this->renderExtbaseLink($pluginArgs);
    }
}
