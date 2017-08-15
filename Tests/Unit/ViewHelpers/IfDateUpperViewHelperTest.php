<?php
/**
 * Check if a date is upper
 *
 */
namespace HDNET\Calendarize\Tests\Unit\ViewHelpers;

use HDNET\Calendarize\Tests\Unit\AbstractUnitTest;
use HDNET\Calendarize\ViewHelpers\IfDateUpperViewHelper;

/**
 * Check if a date is upper
 *
 */
class IfDateUpperViewHelperTest extends AbstractUnitTest
{

    /**
     * @test
     */
    public function testValidCheck()
    {
        $viewHelper = new IfDateUpperViewHelper();
        $this->assertEquals(true, $viewHelper->render(new \DateTime(), '23.04.2026'));
    }
}
