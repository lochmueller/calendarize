<?php

declare(strict_types=1);

namespace HDNET\Calendarize\Tests\Functional\Tca;

use HDNET\Calendarize\Tests\Functional\AbstractFunctionalTest;

class BackendPreviewTest extends AbstractFunctionalTest
{
    /**
     * Sets up this test case.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // $this->importDataSet(ORIGINAL_ROOT . 'typo3conf/ext/calendarize/Tests/Functional/Tca/Fixtures/tx_calendarize_domain_model_event.xml');
    }

    public function testOutputOfLiveEvents()
    {
        self::markTestSkipped('@todo');
    }
}
