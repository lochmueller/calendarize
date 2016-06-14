<?php
/**
 * Modify a DateTime
 *
 * @author  Tim Lochmüller
 */

namespace HDNET\Calendarize\ViewHelpers\DateTime;

use HDNET\Calendarize\ViewHelpers\AbstractViewHelper;

/**
 * Modify a DateTime@
 *
 * @author Tim Lochmüller
 */
class ModifyViewHelper extends AbstractViewHelper
{

    /**
     * Modify the given datetime by the string modification
     *
     * @param string    $modification
     * @param \DateTime $dateTime
     *
     * @return string
     */
    public function render($modification, \DateTime $dateTime = null)
    {
        if ($dateTime === null) {
            $dateTime = $this->renderChildren();
        }
        if (!$dateTime instanceof \DateTime) {
            $dateTime = new \DateTime();
        }
        $clone = clone $dateTime;
        return $clone->modify($modification);
    }
}
