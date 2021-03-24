<?php

declare(strict_types=1);
/**
 * Check if a date is upper.
 */

namespace HDNET\Calendarize\Tests\Unit\ViewHelpers;

use HDNET\Calendarize\ViewHelpers\IfDateUpperViewHelper;
use TYPO3\TestingFramework\Fluid\Unit\ViewHelpers\ViewHelperBaseTestcase;

/**
 * Check if a date is upper.
 */
class IfDateUpperViewHelperTest extends ViewHelperBaseTestcase
{
    public function testValidCheck()
    {
        $viewHelper = new IfDateUpperViewHelper();
        $this->injectDependenciesIntoViewHelper($viewHelper);
        $this->setArgumentsUnderTest(
            $viewHelper,
            [
                'base' => '23.04.2004',
                'check' => new \DateTime('2020-12-14'),
            ]
        );
        self::assertTrue($viewHelper->initializeArgumentsAndRender());
    }
}
