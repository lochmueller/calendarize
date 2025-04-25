<?php

declare(strict_types=1);

namespace HDNET\Calendarize;

use HDNET\Calendarize\EventListener\HideIndexesInWorkspaceModuleEventListener;
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
use TYPO3\CMS\Backend\View\BackendViewFactory;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Dashboard\Widgets\ListWidget;
use TYPO3\CMS\Dashboard\Widgets\NumberWithIconWidget;
use TYPO3\CMS\Workspaces\Event\AfterCompiledCacheableDataForWorkspaceEvent;

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
            ->arg('$dataProvider', new Reference(NextEventsDataProvider::class))
            ->arg('$backendViewFactory', new Reference(BackendViewFactory::class))
            ->tag('dashboard.widget', [
                'identifier' => 'calendarizeNextEvents',
                'groupNames' => 'calendarizegroup',
                'title' => TranslateUtility::getLll('calendarizeNextEvents.title'),
                'description' => TranslateUtility::getLll('calendarizeNextEvents.description'),
                'iconIdentifier' => 'ext-calendarize-wizard-icon',
                'height' => 'medium',
                'width' => 'medium',
            ]);
    }
    if ($containerBuilder->hasDefinition(NumberWithIconWidget::class)) {
        $services->set('dashboard.widgets.calendarizeIndexAmount')
            ->class(NumberWithIconWidget::class)
            ->arg('$dataProvider', new Reference(IndexAmountDataProvider::class))
            ->arg('$backendViewFactory', new Reference(BackendViewFactory::class))
            ->arg('$options', [
                'title' => TranslateUtility::getLll('calendarizeIndexAmount.description'),
                'icon' => 'ext-calendarize-wizard-icon',
            ])->tag('dashboard.widget', [
                'identifier' => 'calendarizeIndexAmount',
                'groupNames' => 'calendarizegroup',
                'title' => TranslateUtility::getLll('calendarizeIndexAmount.title'),
                'description' => TranslateUtility::getLll('calendarizeIndexAmount.description'),
                'iconIdentifier' => 'ext-calendarize-wizard-icon',
                'height' => 'small',
                'width' => 'small',
            ]);
    }

    if (ExtensionManagementUtility::isLoaded('workspaces')) {
        $services->set('calendarize.event_listener.hide_indexes_in_workspace_module')
            ->class(HideIndexesInWorkspaceModuleEventListener::class)
            ->tag('event.listener', [
                'identifier' => 'calendarize.event_listener.hide_indexes_in_workspace_module',
                'event' => AfterCompiledCacheableDataForWorkspaceEvent::class,
            ]);
    }
};
