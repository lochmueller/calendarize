<?php

namespace HDNET\Calendarize\Tests\Functional\ViewHelpers\Format;

use HDNET\Calendarize\Tests\Functional\ViewHelpers\AbstractViewHelperTestCase;
use TYPO3\CMS\Fluid\View\StandaloneView;

class EscapeIcalTextViewHelperTest extends AbstractViewHelperTestCase
{
    /**
     * @dataProvider textEscapeDataProvider
     *
     * @param string $value
     * @param string $expected
     */
    public function testTextEscape(string $value, string $expected): void
    {
        $view = new StandaloneView();
        $template = '{namespace c=HDNET\Calendarize\ViewHelpers}' .
            '{value -> c:format.escapeIcalText()}';

        $view->setTemplateSource($template);
        $view->assign('value', $value);

        self::assertEquals($expected, $view->render());
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
