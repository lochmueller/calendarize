<?php

declare(strict_types=1);

namespace HDNET\Calendarize\Tests\Functional;

use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

abstract class AbstractFunctionalTest extends FunctionalTestCase
{
    protected $coreExtensionsToLoad = ['workspaces'];

    protected $testExtensionsToLoad = ['typo3conf/ext/autoloader', 'typo3conf/ext/calendarize'];

    /**
     * Sets up this test case.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpBackendUserFromFixture(1);
        $GLOBALS['LANG'] = GeneralUtility::makeInstance(LanguageServiceFactory::class)->create('default');

        $this->importDataSet(ORIGINAL_ROOT . 'typo3conf/ext/calendarize/Tests/Functional/Fixtures/pages.xml');
        $this->importDataSet(ORIGINAL_ROOT . 'typo3conf/ext/calendarize/Tests/Functional/Fixtures/sys_workspace.xml');
    }
}
