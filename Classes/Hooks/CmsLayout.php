<?php
/**
 * Render the CMS layout
 *
 * @package Calendarize\Hooks
 * @author  Tim Lochmüller
 */

namespace HDNET\Calendarize\Hooks;

use HDNET\Calendarize\Service\ContentElementLayoutService;
use HDNET\Calendarize\Service\FlexFormService;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Render the CMS layout
 *
 * @author Tim Lochmüller
 * @see    News extension (Thanks Georg)
 */
class CmsLayout {

	/**
	 * Flex form service
	 *
	 * @var FlexFormService
	 */
	protected $flexFormService;

	/**
	 * Content element data
	 *
	 * @var ContentElementLayoutService
	 */
	protected $layoutService;

	/**
	 * Returns information about this extension plugin
	 *
	 * @param array $params Parameters to the hook
	 *
	 * @return string Information about pi1 plugin
	 * @hook TYPO3_CONF_VARS|SC_OPTIONS|cms/layout/class.tx_cms_layout.php|list_type_Info|calendarize_calendar
	 */
	public function getExtensionSummary(array $params) {
		$relIconPath = GeneralUtility::getIndpEnv('TYPO3_SITE_URL') . ExtensionManagementUtility::siteRelPath('calendarize') . 'ext_icon.png';
		$this->flexFormService = new FlexFormService();
		$this->layoutService = new ContentElementLayoutService();
		$this->layoutService->setTitle('<img src="' . $relIconPath . '" /> Calendarize');

		if ($params['row']['list_type'] != 'calendarize_calendar') {
			return '';
		}
		$this->flexFormService->load($params['row']['pi_flexform']);
		if (!$this->flexFormService->isValid()) {
			return '';
		}
		$actions = $this->flexFormService->get('switchableControllerActions', 'main');
		$parts = GeneralUtility::trimExplode(';', $actions, TRUE);
		$parts = array_map(function ($element) {
			$split = explode('->', $element);
			return ucfirst($split[1]);
		}, $parts);
		$actionKey = lcfirst(implode('', $parts));

		$this->layoutService->addRow(LocalizationUtility::translate('mode', 'calendarize'), LocalizationUtility::translate('mode.' . $actionKey, 'calendarize'));
		$this->layoutService->addRow(LocalizationUtility::translate('configuration', 'calendarize'), $this->flexFormService->get('settings.configuration', 'main'));

		if ((bool)$this->flexFormService->get('settings.hidePagination', 'main')) {
			$this->layoutService->addRow(LocalizationUtility::translate('hide.pagination.teaser', 'calendarize'), '!!!');
		}
		$this->addPageIdsToTable();
		return $this->layoutService->render();
	}

	/**
	 * Add page IDs to the preview of the element
	 */
	protected function addPageIdsToTable() {
		$pageIdsNames = array(
			'detailPid',
			'listPid',
			'yearPid',
			'monthPid',
			'weekPid',
			'dayPid',
		);
		foreach ($pageIdsNames as $pageIdName) {
			$pageId = (int)$this->flexFormService->get('settings.' . $pageIdName, 'pages');
			$pageRow = BackendUtility::getRecord('pages', $pageId);
			if ($pageRow) {
				$this->layoutService->addRow(LocalizationUtility::translate($pageIdName, 'calendarize'), $pageRow['title'] . ' (' . $pageId . ')');
			}
		}
	}
}
