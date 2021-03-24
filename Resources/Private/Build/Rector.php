<?php

declare(strict_types=1);

use Rector\Core\Configuration\Option;
use Rector\Core\ValueObject\PhpVersion;
use Rector\PostRector\Rector\NameImportingPostRector;
use Ssch\TYPO3Rector\Configuration\Typo3Option;
use Ssch\TYPO3Rector\Set\Typo3SetList;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();

    $parameters->set(Option::SETS, [
        Typo3SetList::TYPO3_104,
        Typo3SetList::TYPO3_11,
    ]);

    $parameters->set(Option::AUTO_IMPORT_NAMES, true);
    $parameters->set(Option::IMPORT_SHORT_CLASSES, false);
    $parameters->set(Option::IMPORT_DOC_BLOCKS, false);
    $parameters->set(Option::PHP_VERSION_FEATURES, PhpVersion::PHP_73);
    $parameters->set(Typo3Option::OUTPUT_CHANGELOG, true);

    $parameters->set(Option::SKIP, [
        NameImportingPostRector::class => [
            'ClassAliasMap.php',
            'ext_localconf.php',
            'ext_emconf.php',
            'ext_tables.php',
            __DIR__ . '/**/TCA/*',
            __DIR__ . '/**/Configuration/RequestMiddlewares.php',
            __DIR__ . '/**/Configuration/Commands.php',
            __DIR__ . '/**/Configuration/AjaxRoutes.php',
            __DIR__ . '/**/Configuration/Extbase/Persistence/Classes.php',
        ],
    ]);
};
