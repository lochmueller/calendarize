<?php

declare(strict_types=1);
/**
 * Check if a date is upper.
 */

namespace HDNET\Calendarize\Tests\Functional\ViewHelpers;

use TYPO3\CMS\Fluid\View\StandaloneView;

/**
 * Check if a date is upper.
 */
class IfDateUpperViewHelperTest extends AbstractViewHelperTestCase
{
    /**
     * @dataProvider validCheckDataProvider
     */
    public function testValidCheck($base, $check, $expected)
    {
        $view = new StandaloneView();
        $template = '{namespace c=HDNET\Calendarize\ViewHelpers}' .
            '<c:ifDateUpper base="{base}" check="{check}" />';

        $view->setTemplateSource($template);
        $view->assignMultiple([
            'base' => $base,
            'check' => new \DateTime($check),
        ]);

        self::assertEquals($expected, $view->render());
    }

    public static function validCheckDataProvider(): \Generator
    {
        yield 'date is upper' => ['23.04.2004', '2020-12-14', true];
        yield 'date is not upper' => ['23.04.2026', '2020-12-14', false];
    }
}
