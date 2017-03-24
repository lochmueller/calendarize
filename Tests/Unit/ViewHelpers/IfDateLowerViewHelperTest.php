<?php
/**
 * Check if a date is lower
 *
 * @author  Tim Lochmüller
 */
namespace HDNET\Calendarize\Tests\Unit\ViewHelpers;

use HDNET\Calendarize\ViewHelpers\IfDateLowerViewHelper;
use TYPO3\CMS\Core\Tests\UnitTestCase;

/**
 * Check if a date is lower
 */
class IfDateLowerViewHelperTest extends UnitTestCase
{

    /**
     * @test
     */
    public function testValidCheck()
    {
        $viewHelper = new IfDateLowerViewHelper();
        $this->assertEquals(true, $viewHelper->render(new \DateTime(), '23.04.2004'));
    }
}
