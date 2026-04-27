<?php

declare(strict_types=1);
/**
 * Check if a date is lower.
 */

namespace HDNET\Calendarize\Tests\Functional\ViewHelpers;

/**
 * Check if a date is lower.
 */
class IfDateLowerViewHelperTest extends AbstractViewHelperTestCase
{
    /**
     * @dataProvider validCheckDataProvider
     */
    public function testValidCheck($base, $check, $expected): void
    {
        $template = '{namespace c=HDNET\Calendarize\ViewHelpers}' .
            '<c:ifDateLower base="{base}" check="{check}" />';

        self::assertEquals($expected, $this->renderTemplate($template, [
            'base' => $base,
            'check' => new \DateTime($check),
        ]));
    }

    public static function validCheckDataProvider(): \Generator
    {
        yield 'date is lower' => ['23.04.2026', '2020-12-14', true];
        yield 'date is not lower' => ['23.04.2004', '2020-12-14', false];
    }
}
