<?php

namespace HDNET\Calendarize\Tests\ViewHelpers\Format;

use HDNET\Calendarize\ViewHelpers\Format\EscapeIcalTextViewHelper;
use TYPO3\TestingFramework\Fluid\Unit\ViewHelpers\ViewHelperBaseTestcase;

class EscapeIcalTextViewHelperTest extends ViewHelperBaseTestcase
{
    /**
     * @var EscapeIcalTextViewHelper
     */
    protected $viewHelper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->viewHelper = new EscapeIcalTextViewHelper();
    }

    /**
     * @dataProvider textEscapeDataProvider
     *
     * @param string $date
     * @param string $expected
     */
    public function testTextEscape(string $value, string $expected)
    {
        $this->setArgumentsUnderTest(
            $this->viewHelper,
            [
                'value' => $value,
            ]
        );
        $actualResult = $this->viewHelper->initializeArgumentsAndRender();
        self::assertEquals($expected, $actualResult);
    }

    public function textEscapeDataProvider(): \Generator
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
