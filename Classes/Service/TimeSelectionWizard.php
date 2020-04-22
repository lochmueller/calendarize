<?php

/**
 * TimeSelectionWizard.
 */
declare(strict_types=1);

namespace HDNET\Calendarize\Service;

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * TimeSelectionWizard.
 */
class TimeSelectionWizard extends AbstractService
{
    /**
     * Render the time selection wizard.
     *
     * @param array  $params
     * @param object $pObj
     *
     * @return string
     */
    public function renderWizard(array $params, $pObj)
    {
        $name = isset($params['itemName']) ? \trim($params['itemName']) : '';
        $match = \preg_match('/(.*)id="(.*?)"(.*)/', $params['item'], $matches);
        $id = $match && isset($matches[2]) ? \trim($matches[2]) : '';
        if ('' === $id && '' === $name) {
            return '';
        }
        $times = $this->getTimes((int)$params['pid']);
        if (!$times) {
            return '';
        }

        if (TYPO3_MODE !== 'FE') {
            $pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
            $pageRenderer->loadRequireJsModule('TYPO3/CMS/Calendarize/TimeSelection');
        }

        $iconFactory = GeneralUtility::makeInstance(IconFactory::class);
        $icon = $iconFactory->getIcon(
            'actions-document-synchronize',
            Icon::SIZE_SMALL
        )->render();

        return '<div class="form-group">
    <div class="input-group">
      <div class="input-group-addon">' . $icon . '</div>
      <select class="form-control calendarize-time-selection" data-related-id="' . $id . '" data-related-name="' . $name . '">
        <option></option>
        ' . $this->renderOptions($times) . '
  </select>
    </div>
  </div>';
    }

    /**
     * Render the options.
     *
     * @param array $options
     *
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
     * Get the times.
     *
     * @param int $pageUid
     *
     * @return array
     */
    protected function getTimes($pageUid)
    {
        $times = [];
        $pagesTsConfig = BackendUtility::getPagesTSconfig($pageUid);
        if (isset($pagesTsConfig['tx_calendarize.']['timeSelectionWizard.']) &&
            \is_array($pagesTsConfig['tx_calendarize.']['timeSelectionWizard.'])
        ) {
            $times = \array_combine(
                $pagesTsConfig['tx_calendarize.']['timeSelectionWizard.'],
                $pagesTsConfig['tx_calendarize.']['timeSelectionWizard.']
            );
        }

        return $times;
    }
}
