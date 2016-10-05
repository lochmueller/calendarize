<?php
/**
 * TimeSelectionWizard
 */

namespace HDNET\Calendarize\Service;

use TYPO3\CMS\Backend\Form\Element\AbstractFormElement;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * TimeSelectionWizard
 */
class TimeSelectionWizard extends AbstractService
{
    /**
     * Render the time selection wizard
     *
     * @param array $params
     * @param object $pObj
     * @return string
     */
    public function renderWizard(array $params, $pObj)
    {
        $match = preg_match('/(.*)id="(.*?)"(.*)/', $params['item'], $matches);
        if (!$match) {
            return '';
        }
        $id = $matches[2];
        $times = $this->getTimes((int)$params['pid']);
        if (!$times) {
            return '';
        }

        $pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
        $pageRenderer->loadRequireJsModule('TYPO3/CMS/Calendarize/TimeSelection');

        /** @var IconFactory $iconFactory */
        $iconFactory = GeneralUtility::makeInstance(IconFactory::class);

        return '<div class="form-group">
    <div class="input-group">
      <div class="input-group-addon">' . $iconFactory->getIcon(
            'actions-document-synchronize',
            Icon::SIZE_SMALL
        )->render() . '</div>
      <select class="form-control calendarize-time-selection" data-related="' . $id . '">
        <option></option>
        ' . $this->renderOptions($times) . '
  </select>
    </div>
  </div>';
    }

    /**
     * Render the options
     *
     * @param array $options
     * @return string
     */
    protected function renderOptions(array $options)
    {
        $renderedOptions = '';
        foreach ($options as $key => $value) {
            $renderedOptions .= '<option key="' . $key . '">' . $value . '</option>';
        }
        return $renderedOptions;
    }

    /**
     * Get the times
     *
     * @return array
     */
    protected function getTimes($pageUid)
    {

        $times = [];
        $pagesTsConfig = BackendUtility::getPagesTSconfig($pageUid);
        if (isset($pagesTsConfig['tx_calendarize.']['timeSelectionWizard.']) &&
            is_array($pagesTsConfig['tx_calendarize.']['timeSelectionWizard.'])
        ) {
            $times = array_combine(
                $pagesTsConfig['tx_calendarize.']['timeSelectionWizard.'],
                $pagesTsConfig['tx_calendarize.']['timeSelectionWizard.']
            );
        }

        return $times;
    }
}
