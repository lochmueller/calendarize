<?php

declare(strict_types=1);

namespace HDNET\Calendarize\Tests\Functional\ViewHelpers;

use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

abstract class AbstractViewHelperTestCase extends FunctionalTestCase
{
    protected array $coreExtensionsToLoad = ['extbase', 'fluid'];
    protected array $testExtensionsToLoad = ['typo3conf/ext/calendarize'];
    protected bool $initializeDatabase = false;
}
