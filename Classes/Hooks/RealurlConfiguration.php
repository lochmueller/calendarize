<?php
/**
 * Realurl configuration
 *
 * @author  Tim Lochmüller
 */

namespace HDNET\Calendarize\Hooks;

use HDNET\Calendarize\UserFunction\RealurlAlias;

/**
 * Realurl configuration
 *
 * @author Tim Lochmüller
 */
class RealurlConfiguration extends AbstractHook
{

    /**
     * Add the realurl configuration
     *
     * @param $params
     * @param $pObj
     *
     * @return array
     * @hook TYPO3_CONF_VARS|SC_OPTIONS|ext/realurl/class.tx_realurl_autoconfgen.php|extensionConfiguration
     */
    public function addCalendarizeConfiguration($params, &$pObj)
    {
        return array_merge_recursive($params['config'], [
            'postVarSets' => [
                '_DEFAULT' => [
                    'event'      => [
                        [
                            'GETvar'   => 'tx_calendarize_calendar[index]',
                            'userFunc' => RealurlAlias::class . '->main'
                        ],
                    ],
                    'event-page' => [
                        [
                            'GETvar' => 'tx_calendarize_calendar[@widget_0][currentPage]',
                        ]
                    ]
                ]
            ]
        ]);
    }
}
