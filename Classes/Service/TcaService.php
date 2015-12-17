<?php
/**
 * TCA service
 *
 * @author  Tim Lochmüller
 */

namespace HDNET\Calendarize\Service;

use HDNET\Calendarize\Domain\Model\Configuration;
use HDNET\Calendarize\Utility\TranslateUtility;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * TCA service
 *
 * @author Tim Lochmüller
 */
class TcaService extends AbstractService
{

    /**
     * Render the configuration title
     *
     * @param array $params
     * @param object $object
     */
    public function configurationTitle(&$params, $object)
    {
        $row = $params['row'];
        $type = is_array($row['type']) ? $row['type'][0] : $row['type']; // The new FormEngine prepare the select as array
        $params['title'] .= '<b>' . TranslateUtility::get('configuration.type.' . $type) . '</b><br />';
        switch ($type) {
            case Configuration::TYPE_TIME:
                $params['title'] .= $this->getConfigurationTitleTime($row);
                break;
            case Configuration::TYPE_INCLUDE_GROUP:
            case Configuration::TYPE_EXCLUDE_GROUP:
                $params['title'] .= $this->getConfigurationGroupTitle($row);
                break;
            case Configuration::TYPE_EXTERNAL:
                $params['title'] .= 'URL: ' . $row['external_ics_url'];
                break;
        }
    }

    /**
     * Get group title
     *
     * @param $row
     *
     * @return string
     */
    protected function getConfigurationGroupTitle($row)
    {
        $title = '';
        $groupData = is_array($row['groups']) ? $row['groups'][0] : $row['groups']; // The new FormEngine prepare the select as array
        $groups = GeneralUtility::trimExplode(',', $groupData, true);
        foreach ($groups as $key => $id) {
            $row = BackendUtility::getRecord('tx_calendarize_domain_model_configurationgroup', $id);
            $groups[$key] = $row['title'] . ' (#' . $id . ')';
        }
        if ($groups) {
            $title .= '<ul><li>' . implode('</li><li>', $groups) . '</li></ul>';
        }
        return $title;
    }

    /**
     * Get the title for a configuration time
     *
     * @param $row
     *
     * @return string
     */
    protected function getConfigurationTitleTime($row)
    {
        $title = '';
        if ($row['start_date']) {
            $dateStart = date('d.m.Y', $row['start_date']);
            $dateEnd = date('d.m.Y', $row['end_date'] ?: $row['start_date']);
            $title .= $dateStart;
            if ($dateStart != $dateEnd) {
                $title .= ' - ' . $dateEnd;
            }
        }
        if ($row['all_day']) {
            $title .= ' ' . TranslateUtility::get('tx_calendarize_domain_model_index.all_day');
        } elseif ($row['start_time']) {
            $title .= '<br />' . BackendUtility::time($row['start_time'], false);
            $title .= ' - ' . BackendUtility::time($row['end_time'], false);
        }

        $frequency = is_array($row['frequency']) ? $row['frequency'][0] : $row['frequency']; // The new FormEngine prepare the select as array
        if ($frequency && $frequency !== Configuration::FREQUENCY_NONE) {
            $title .= '<br /><i>' . TranslateUtility::get('configuration.frequency.' . $row['frequency']) . '</i>';
        }
        return $title;
    }

}