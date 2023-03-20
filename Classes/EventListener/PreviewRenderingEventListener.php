<?php

declare(strict_types=1);

namespace HDNET\Calendarize\EventListener;

use HDNET\Autoloader\Utility\IconUtility;
use HDNET\Calendarize\Service\ContentElementLayoutService;
use HDNET\Calendarize\Service\FlexFormService;
use HDNET\Calendarize\Utility\TranslateUtility;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Backend\View\Event\PageContentPreviewRenderingEvent;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;

class PreviewRenderingEventListener
{
    public function __construct(
        protected FlexFormService $flexFormService,
        protected ContentElementLayoutService $layoutService
    ) {
    }

    public function __invoke(PageContentPreviewRenderingEvent $event)
    {
        $record = $event->getRecord();
        if ('calendarize_calendar' !== $record['list_type']) {
            return;
        }

        $this->flexFormService->load($record['pi_flexform']);
        if (!$this->flexFormService->isValid()) {
            return;
        }

        $extensionIcon = IconUtility::getByExtensionKey('calendarize', true);
        $extensionIconUsage = PathUtility::getAbsoluteWebPath(GeneralUtility::getFileAbsFileName($extensionIcon));
        $this->layoutService->setTitle('<img src="' . $extensionIconUsage . '" width="32" height="32" /> Calendarize');

        $actions = $this->flexFormService->get('switchableControllerActions', 'main');
        $parts = GeneralUtility::trimExplode(';', $actions, true);
        $parts = array_map(static function ($element) {
            $split = explode('->', $element);

            return ucfirst($split[1]);
        }, $parts);
        $actionKey = lcfirst(implode('', $parts));

        $this->layoutService->addRow(TranslateUtility::get('mode'), TranslateUtility::get('mode.' . $actionKey));

        $pluginConfiguration = (int)$this->flexFormService->get('settings.pluginConfiguration', 'main');
        if ($pluginConfiguration) {
            $table = 'tx_calendarize_domain_model_pluginconfiguration';
            $row = BackendUtility::getRecord($table, $pluginConfiguration);
            $this->layoutService->addRow(
                TranslateUtility::get('tx_calendarize_domain_model_pluginconfiguration'),
                BackendUtility::getRecordTitle($table, $row)
            );
        }

        if ('' !== trim((string)$this->flexFormService->get('settings.configuration', 'general'))) {
            $this->layoutService->addRow(
                TranslateUtility::get('configuration'),
                $this->flexFormService->get('settings.configuration', 'general')
            );
        }

        if ((bool)$this->flexFormService->get('settings.hidePagination', 'main')) {
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
                $this->layoutService->addRow(TranslateUtility::get('override.startdate'), BackendUtility::datetime($overrideStartDate));
            }
            $overrideEndDate = (int)$this->flexFormService->get('settings.overrideEnddate', 'main');
            if ($overrideEndDate) {
                $this->layoutService->addRow(TranslateUtility::get('override.enddate'), BackendUtility::datetime($overrideEndDate));
            }
        }

        $this->addPageIdsToTable();

        $event->setPreviewContent($this->layoutService->render());
    }

    /**
     * Add page IDs to the preview of the element.
     */
    protected function addPageIdsToTable()
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
                    $pageRow['title'] . ' (' . $pageId . ')'
                );
            }
        }
    }
}
