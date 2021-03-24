<?php

declare(strict_types=1);
/**
 * Check if a date is lower.
 */

namespace HDNET\Calendarize\Tests\Unit\ViewHelpers;

use HDNET\Calendarize\ViewHelpers\IfDateLowerViewHelper;
use TYPO3\TestingFramework\Fluid\Unit\ViewHelpers\ViewHelperBaseTestcase;

/**
 * Check if a date is lower.
 */
class IfDateLowerViewHelperTest extends ViewHelperBaseTestcase
{
    public function testValidCheck()
    {
        $viewHelper = new IfDateLowerViewHelper();
        $this->injectDependenciesIntoViewHelper($viewHelper);
        $this->setArgumentsUnderTest(
            $viewHelper,
            [
                'base' => '23.04.2026',
                'check' => new \DateTime('2020-12-14'),
            ]
        );
        self::assertTrue($viewHelper->initializeArgumentsAndRender());
    }
}
