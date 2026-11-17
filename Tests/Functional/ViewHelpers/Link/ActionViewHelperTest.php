<?php

declare(strict_types=1);

namespace HDNET\Calendarize\Tests\Functional\ViewHelpers\Link;

use TYPO3\CMS\Core\Configuration\SiteWriter;
use TYPO3\TestingFramework\Core\Functional\Framework\Frontend\InternalRequest;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Render the link/uri ViewHelpers outside of an Extbase context (e.g. in a FLUIDTEMPLATE).
 *
 * @see https://github.com/lochmueller/calendarize/issues/815
 */
class ActionViewHelperTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = ['typo3conf/ext/calendarize'];

    protected function setUp(): void
    {
        parent::setUp();

        $this->importCSVDataSet(__DIR__ . '/Fixtures/pages.csv');
        $this->get(SiteWriter::class)->write('test', [
            'rootPageId' => 1,
            'base' => 'http://localhost/',
            'languages' => [
                [
                    'title' => 'English',
                    'enabled' => true,
                    'languageId' => 0,
                    'base' => '/',
                    'locale' => 'en_US.UTF-8',
                    'navigationTitle' => 'English',
                    'flag' => 'us',
                ],
            ],
        ]);
        $this->setUpFrontendRootPage(1, [
            'EXT:calendarize/Tests/Functional/ViewHelpers/Link/Fixtures/page.typoscript',
        ]);
    }

    public function testRenderWithoutExtbaseRequest(): void
    {
        $response = $this->executeFrontendSubRequest(new InternalRequest('http://localhost/'));
        $body = (string)$response->getBody();

        self::assertStringContainsString('<div id="uri-list">/detail?tx_calendarize_calendar', $body);
        self::assertStringContainsString('tx_calendarize_calendar%5Baction%5D=list', $body);
        self::assertStringContainsString('tx_calendarize_calendar%5Bcontroller%5D=Calendar', $body);
        self::assertStringContainsString('cHash=', $body);
        self::assertMatchesRegularExpression(
            '#<div id="link-list"><a href="/detail\?[^"]+">List</a></div>#',
            $body,
        );
    }
}
