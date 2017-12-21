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
     * Modify the given datetime by the string modification.
     *
     * @param string    $modification
     * @param \DateTime $dateTime
     *
     * @return string
     */
    public function render($modification, \DateTime $dateTime = null)
    {
        if (null === $dateTime) {
            $dateTime = $this->renderChildren();
        }
        if (!($dateTime instanceof \DateTimeInterface)) {
            $dateTime = new \DateTime();
        }
        $clone = clone $dateTime;

        return $clone->modify($modification);
    }
}
