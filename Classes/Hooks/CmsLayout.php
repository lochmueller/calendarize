<?php

/**
 * Render the CMS layout.
 */
declare(strict_types=1);

namespace HDNET\Calendarize\Hooks;

use HDNET\Autoloader\Utility\IconUtility;
use HDNET\Calendarize\Service\ContentElementLayoutService;
use HDNET\Calendarize\Service\FlexFormService;
use HDNET\Calendarize\Utility\HelperUtility;
use HDNET\Calendarize\Utility\TranslateUtility;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;

/**
 * Render the CMS layout.
 *
 * @see News extension (Thanks Georg)
 */
class CmsLayout extends AbstractHook
{
    /**
     * Flex form service.
     *
     * @var FlexFormService
     */
    protected $flexFormService;

    /**
     * Content element data.
     *
     * @var ContentElementLayoutService
     */
    protected $layoutService;

    /**
     * Returns information about this extension plugin.
     *
     * @param array $params Parameters to the hook
     *
     * @return string Information about pi1 plugin
     * @hook TYPO3_CONF_VARS|SC_OPTIONS|cms/layout/class.tx_cms_layout.php|list_type_Info|calendarize_calendar
     */
    public function getExtensionSummary(array $params)
    {
        if ($params['row']['list_type'] !== 'calendarize_calendar') {
            return '';
        }

        $this->flexFormService = GeneralUtility::makeInstance(FlexFormService::class);
        $this->flexFormService->load($params['row']['pi_flexform']);
        if (!$this->flexFormService->isValid()) {
            return '';
        }

        $extensionIcon = IconUtility::getByExtensionKey('calendarize', true);
        $extensionIconUsage = PathUtility::getAbsoluteWebPath(GeneralUtility::getFileAbsFileName($extensionIcon));
        $this->layoutService = GeneralUtility::makeInstance(ContentElementLayoutService::class);
        $this->layoutService->setTitle('<img src="' . $extensionIconUsage . '" width="32" height="32" /> Calendarize');

        $actions = $this->flexFormService->get('switchableControllerActions', 'main');
        $parts = GeneralUtility::trimExplode(';', $actions, true);
        $parts = \array_map(function ($element) {
            $split = \explode('->', $element);

            return \ucfirst($split[1]);
        }, $parts);
        $actionKey = \lcfirst(\implode('', $parts));

        $this->layoutService->addRow(TranslateUtility::get('mode'), TranslateUtility::get('mode.' . $actionKey));

        $pluginConfiguration = (int) $this->flexFormService->get('settings.pluginConfiguration', 'main');
        if ($pluginConfiguration) {
            $table = 'tx_calendarize_domain_model_pluginconfiguration';
            $row = HelperUtility::getDatabaseConnection()->exec_SELECTgetSingleRow(
                '*',
                $table,
                'uid=' . $pluginConfiguration
            );
            $this->layoutService->addRow(
                TranslateUtility::get('tx_calendarize_domain_model_pluginconfiguration'),
                BackendUtility::getRecordTitle($table, $row)
            );
        }

        if ('' !== \trim((string) $this->flexFormService->get('settings.configuration', 'general'))) {
            $this->layoutService->addRow(
                TranslateUtility::get('configuration'),
                $this->flexFormService->get('settings.configuration', 'general')
            );
        }

        if ((bool) $this->flexFormService->get('settings.hidePagination', 'main')) {
            $this->layoutService->addRow(TranslateUtility::get('hide.pagination.teaser'), '!!!');
        }
        $overrideStartDate = (int) $this->flexFormService->get('settings.overrideStartdate', 'main');
        if ($overrideStartDate) {
            $this->layoutService->addRow('OverrideStartdate', \date('d.m.y H:i', $overrideStartDate));
        }
        $overrideEndDate = (int) $this->flexFormService->get('settings.overrideEnddate', 'main');
        if ($overrideEndDate) {
            $this->layoutService->addRow('OverrideEndDate', \date('d.m.y H:i', $overrideEndDate));
        }

        $this->addPageIdsToTable();

        return $this->layoutService->render();
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
            $pageId = (int) $this->flexFormService->get('settings.' . $pageIdName, 'pages');
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
