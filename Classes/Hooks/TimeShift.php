<?php

/**
 * Time shift function.
 */
declare(strict_types=1);

namespace HDNET\Calendarize\Hooks;

use HDNET\Calendarize\Utility\DateTimeUtility;

/**
 * Time shift function.
 */
class TimeShift extends AbstractHook
{
    /**
     * Shift the time variables.
     *
     * @hook TYPO3_CONF_VARS|SC_OPTIONS|tslib/index_ts.php|preprocessRequest
     */
    public function shift()
    {
        $defaultTime = $GLOBALS['EXEC_TIME'] - $GLOBALS['EXEC_TIME'] % DateTimeUtility::SECONDS_MINUTE;
        if ($GLOBALS['SIM_ACCESS_TIME'] !== $defaultTime) {
            // another process already change the SIM_ACCESS_TIME
            return;
        }

        $configuration = $this->getConfiguration();
        $timeShift = isset($configuration['timeShift']) ? (int) $configuration['timeShift'] : 0;
        if ($timeShift <= 0) {
            // shift is disabled
            return;
        }

        // set new time
        $GLOBALS['SIM_ACCESS_TIME'] = $GLOBALS['EXEC_TIME'] - $GLOBALS['EXEC_TIME'] % $timeShift;
    }

    /**
     * Get the configuration.
     *
     * @return array
     */
    protected function getConfiguration():array
    {
        $config = \unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['calendarize']);
        if (\is_array($config)) {
            return $config;
        }

        return [];
    }
}
