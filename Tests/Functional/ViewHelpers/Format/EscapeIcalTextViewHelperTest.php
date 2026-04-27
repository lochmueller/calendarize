<?php

declare(strict_types=1);

namespace HDNET\Calendarize\Tests\Functional\ViewHelpers\Format;

use HDNET\Calendarize\Tests\Functional\ViewHelpers\AbstractViewHelperTestCase;

class EscapeIcalTextViewHelperTest extends AbstractViewHelperTestCase
{
    /**
     * @dataProvider textEscapeDataProvider
     */
    public function testTextEscape(string $value, string $expected): void
    {
        $template = '{namespace c=HDNET\Calendarize\ViewHelpers}' .
            '{value -> c:format.escapeIcalText()}';

        self::assertEquals($expected, $this->renderTemplate($template, ['value' => $value]));
    }

    public static function textEscapeDataProvider(): \Generator
    {
        yield 'Backslash' => ['\\', '\\\\'];
        yield 'Newline' => ["\n", '\n'];
        yield 'CRLF' => ["\r\n", '\n'];
        yield 'Semicolon' => [';', '\;'];
        yield 'Comma' => [',', '\,'];
        yield 'Colon (not)' => [':', ':'];
        yield 'Example text' => [
            "This is a description\nwith a linebreak and a ; , and :",
            'This is a description\\nwith a linebreak and a \\; \\, and :',
        ];
    }
}
