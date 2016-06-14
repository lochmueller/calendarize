<?php
/**
 * Check if a date is upper
 *
 * @author  Tim Lochmüller
 */

namespace HDNET\Calendarize\Tests\Unit\ViewHelpers;

use TYPO3\CMS\Core\Tests\UnitTestCase;

/**
 * Check if a date is upper
 *
 * @author Tim Lochmüller
 */
class IfDateUpperViewHelper extends UnitTestCase
{

    /**
     * @test
     */
    public function testValidCheck()
    {
        $viewHelper = new \HDNET\Calendarize\ViewHelpers\IfDateUpperViewHelper();
        $this->assertEquals(true, $viewHelper->render(new \DateTime(), '23.04.2010'));
    }
}


