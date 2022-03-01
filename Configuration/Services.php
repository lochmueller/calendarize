<?php

declare(strict_types=1);

namespace HDNET\Calendarize;

use HDNET\Calendarize\Service\Ical\DissectICalService;
use HDNET\Calendarize\Service\Ical\ICalServiceInterface;
use HDNET\Calendarize\Service\Ical\VObjectICalService;
use HDNET\Calendarize\Utility\TranslateUtility;
use HDNET\Calendarize\Widgets\DataProvider\IndexAmountDataProvider;
use HDNET\Calendarize\Widgets\DataProvider\NextEventsDataProvider;
use Sabre\VObject\Reader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Reference;
use TYPO3\CMS\Dashboard\Widgets\ListWidget;
use TYPO3\CMS\Dashboard\Widgets\NumberWithIconWidget;

return function (ContainerConfigurator $configurator, ContainerBuilder $containerBuilder) {
    $services = $configurator->services();

    if (class_exists(Reader::class)) {
        $services->alias(ICalServiceInterface::class, VObjectICalService::class);
    } else {
        $services->alias(ICalServiceInterface::class, DissectICalService::class);
    }

    if ($containerBuilder->hasDefinition(ListWidget::class)) {
        $services->set('dashboard.widgets.calendarizeNextEvents')
            ->class(ListWidget::class)
            ->arg('$view', new Reference('dashboard.views.widget'))
            ->arg('$dataProvider', new Reference(NextEventsDataProvider::class))
            ->tag('dashboard.widget', [
                'identifier' => 'calendarizeNextEvents',
                'groupNames' => 'calendarizegroup',
                'title' => TranslateUtility::getLll('calendarizeNextEvents.title'),
                'description' => TranslateUtility::getLll('calendarizeNextEvents.description'),
                'iconIdentifier' => 'calendarize-extension',
                'height' => 'medium',
                'width' => 'medium',
            ]);
    }
    if ($containerBuilder->hasDefinition(NumberWithIconWidget::class)) {
        $services->set('dashboard.widgets.calendarizeIndexAmount')
            ->class(NumberWithIconWidget::class)
            ->arg('$view', new Reference('dashboard.views.widget'))
            ->arg('$dataProvider', new Reference(IndexAmountDataProvider::class))
            ->arg('$options', [
                'title' => TranslateUtility::getLll('calendarizeIndexAmount.description'),
                'icon' => 'calendarize-extension',
            ])->tag('dashboard.widget', [
                'identifier' => 'calendarizeIndexAmount',
                'groupNames' => 'calendarizegroup',
                'title' => TranslateUtility::getLll('calendarizeIndexAmount.title'),
                'description' => TranslateUtility::getLll('calendarizeIndexAmount.description'),
                'iconIdentifier' => 'calendarize-extension',
                'height' => 'small',
                'width' => 'small',
            ]);
    }
};
