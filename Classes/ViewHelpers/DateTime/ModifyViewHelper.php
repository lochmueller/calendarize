<?php

/**
 * Modify a DateTime.
 */
declare(strict_types=1);

namespace HDNET\Calendarize\ViewHelpers\DateTime;

use HDNET\Calendarize\ViewHelpers\AbstractViewHelper;

/**
 * Modify a DateTime@.
 */
class ModifyViewHelper extends AbstractViewHelper
{
    /**
     * Init arguments.
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('modification', 'string', 'DateTime Object Modification String', true, '');
        $this->registerArgument('dateTime', \DateTimeInterface::class, 'DateTime to modify', false, null);
    }

    /**
     * Modify the given datetime by the string modification.
     *
     * @return string
     */
    public function render()
    {
        $dateTime = $this->arguments['dateTime'];

        if (null === $dateTime) {
            $dateTime = $this->renderChildren();
        }
        if (!$dateTime instanceof \DateTimeInterface) {
            $dateTime = new \DateTime();
        }

        $clone = clone $dateTime;

        return $clone->modify($this->arguments['modification']);
    }
}
