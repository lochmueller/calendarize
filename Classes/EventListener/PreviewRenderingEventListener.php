<?php

declare(strict_types=1);

namespace HDNET\Calendarize\EventListener;

use HDNET\Calendarize\Service\ContentElementLayoutService;
use HDNET\Calendarize\Service\FlexFormService;
use HDNET\Calendarize\Utility\TranslateUtility;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Backend\View\Event\PageContentPreviewRenderingEvent;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Imaging\IconSize;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Provides backend preview for calendarize plugins.
 */
class PreviewRenderingEventListener
{
    public function __construct(
        protected FlexFormService $flexFormService,
        protected ContentElementLayoutService $layoutService,
        protected IconFactory $iconFactory,
    ) {}

    public function __invoke(PageContentPreviewRenderingEvent $event): void
    {
        $record = $event->getRecord();
        if (!str_starts_with($record['list_type'] ?? '', 'calendarize_')) {
            return;
        }

        $this->flexFormService->load($record['pi_flexform']);
        if (!$this->flexFormService->isValid()) {
            return;
        }

        $iconSize = GeneralUtility::makeInstance(Typo3Version::class)->getMajorVersion() <= 12 ? Icon::SIZE_SMALL : IconSize::SMALL;

        $extensionIconUsage = $this->iconFactory->getIcon('ext-calendarize-wizard-icon', $iconSize)->render();
        $this->layoutService->setTitle($extensionIconUsage . ' Calendarize');

        $listType = explode('_', $record['list_type'], 2)[1] ?? '';
        $this->layoutService->addRow(TranslateUtility::get('mode'), TranslateUtility::get('mode.' . $listType));

        $pluginConfiguration = (int)$this->flexFormService->get('settings.pluginConfiguration', 'main');
        if ($pluginConfiguration) {
            $table = 'tx_calendarize_domain_model_pluginconfiguration';
            $row = BackendUtility::getRecord($table, $pluginConfiguration);
            $this->layoutService->addRow(
                TranslateUtility::get('tx_calendarize_domain_model_pluginconfiguration'),
                BackendUtility::getRecordTitle($table, $row),
            );
        }

        if ('' !== trim((string)$this->flexFormService->get('settings.configuration', 'general'))) {
            $this->layoutService->addRow(
                TranslateUtility::get('configuration'),
                $this->flexFormService->get('settings.configuration', 'general'),
            );
        }

        if ($this->flexFormService->get('settings.hidePagination', 'main')) {
            $this->layoutService->addRow(TranslateUtility::get('hide.pagination.teaser'), '!!!');
        }
        $useRelativeDate = (bool)$this->flexFormService->get('settings.useRelativeDate', 'main');
        if ($useRelativeDate) {
            $overrideStartRelative = $this->flexFormService->get('settings.overrideStartRelative', 'main');
            if ($overrideStartRelative) {
                $this->layoutService->addRow(TranslateUtility::get('override.startrelative'), $overrideStartRelative);
            }
            $overrideEndRelative = $this->flexFormService->get('settings.overrideEndRelative', 'main');
            if ($overrideEndRelative) {
                $this->layoutService->addRow(TranslateUtility::get('override.endrelative'), $overrideEndRelative);
            }
        } else {
            $overrideStartDate = (int)$this->flexFormService->get('settings.overrideStartdate', 'main');
            if ($overrideStartDate) {
                $this->layoutService->addRow(
                    TranslateUtility::get('override.startdate'),
                    BackendUtility::datetime($overrideStartDate),
                );
            }
            $overrideEndDate = (int)$this->flexFormService->get('settings.overrideEnddate', 'main');
            if ($overrideEndDate) {
                $this->layoutService->addRow(
                    TranslateUtility::get('override.enddate'),
                    BackendUtility::datetime($overrideEndDate),
                );
            }
        }

        $this->addPageIdsToTable();

        $event->setPreviewContent($this->layoutService->render());
    }

    /**
     * Add page IDs to the preview of the element.
     */
    protected function addPageIdsToTable(): void
    {
        $pageIdsNames = [
            'detailPid',
            'listPid',
            'yearPid',
            'monthPid',
            'weekPid',
            'dayPid',
            'bookingPid',
        ];
        foreach ($pageIdsNames as $pageIdName) {
            $pageId = (int)$this->flexFormService->get('settings.' . $pageIdName, 'pages');
            $pageRow = BackendUtility::getRecord('pages', $pageId);
            if ($pageRow) {
                $this->layoutService->addRow(
                    TranslateUtility::get($pageIdName),
                    $pageRow['title'] . ' (' . $pageId . ')',
                );
            }
        }
    }
}
