<?php
/**
 * Check if a date is upper
 *
 * @author  Tim Lochmüller
 */
namespace HDNET\Calendarize\Tests\Unit\ViewHelpers;

use HDNET\Calendarize\ViewHelpers\IfDateUpperViewHelper;
use TYPO3\CMS\Core\Tests\UnitTestCase;

/**
 * Check if a date is upper
 *
 * @author Tim Lochmüller
 */
class IfDateUpperViewHelperTest extends UnitTestCase
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
