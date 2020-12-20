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
        $this->registerArgument('section', 'string', '', false);
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
                'year' => (int)$date->format('o'),
                'week' => (int)$date->format('W'),
            ],
        ];
        $section = isset($this->arguments['section']) ? (string)$this->arguments['section'] : '';

        return parent::renderLink($this->getPageUid($this->arguments['pageUid']), $additionalParams, false, $section);
    }
}
