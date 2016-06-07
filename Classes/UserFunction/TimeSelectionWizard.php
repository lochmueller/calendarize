<?php
/**
 * TimeSelectionWizard
 */

namespace HDNET\Calendarize\UserFunction;

use TYPO3\CMS\Backend\Form\Element\AbstractFormElement;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * TimeSelectionWizard
 */
class TimeSelectionWizard
{
    /**
     * Render the time selection wizard
     *
     * @param array $params
     * @param AbstractFormElement $pObj
     * @return string
     */
    public function renderWizard(array $params, AbstractFormElement $pObj)
    {
        $match = preg_match('/(.*)id="(.*?)"(.*)/', $params['item'], $matches);
        if (!$match) {
            return '';
        }
        $id = $matches[2];

        // @todo update origin field (JS)
        // @todo configuration of field via TsConfig

        return '';

        /** @var IconFactory $iconFactory */
        $iconFactory = GeneralUtility::makeInstance(IconFactory::class);

        return '<div class="form-group">
    <div class="input-group">
      <div class="input-group-addon">' . $iconFactory->getIcon('actions-document-synchronize',
            Icon::SIZE_SMALL)->render() . '</div>
      <select class="form-control" data-related="' . $id . '" onChange="$()alert(\'Works\');">
        <option></option>
        <option value="9:00">9:00 Uhr</option>
        <option value="12:00">12:00 Uhr</option>
        <option value="18:00">18:00 Uhr</option>
        <option value="20:15">20:15 Uhr</option>
  </select>
    </div>
  </div>';
    }
}
