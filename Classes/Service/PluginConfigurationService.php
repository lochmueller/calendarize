<?php

namespace HDNET\Calendarize\Service;

use HDNET\Calendarize\Domain\Model\PluginConfiguration;
use HDNET\Calendarize\Register;
use HDNET\Calendarize\Utility\HelperUtility;
use TYPO3\CMS\Extbase\SignalSlot\Dispatcher;

/**
 * PluginConfigurationService.
 */
class PluginConfigurationService
{
    /**
     * @param array $settings
     *
     * @return array
     */
    public function respectPluginConfiguration(array $settings)
    {
        $settings['pluginConfiguration'] = $this->buildPluginConfigurationObject((int) $settings['pluginConfiguration']);
        if ($settings['pluginConfiguration'] instanceof PluginConfiguration) {
            $checkFields = [
                'detailPid',
                'listPid',
                'yearPid',
                'monthPid',
                'weekPid',
                'dayPid',
                'bookingPid',
                'configuration',
            ];

            foreach ($checkFields as $checkField) {
                if (in_array(trim($settings[$checkField]), ['', '0'])) {
                    $function = 'get' . ucfirst($checkField);
                    $settings[$checkField] = $settings['pluginConfiguration']->$function();
                }
            }
        }

        $dispatcher = HelperUtility::create(Dispatcher::class);
        $arguments = [
            'settings' => $settings,
        ];
        $arguments = $dispatcher->dispatch(__CLASS__, __METHOD__, $arguments);

        return $arguments['settings'];
    }

    /**
     * Build the plugin configuration object.
     *
     * @param int $uid
     *
     * @return null|object
     */
    protected function buildPluginConfigurationObject($uid)
    {
        $db = HelperUtility::getDatabaseConnection();
        $row = $db->exec_SELECTgetSingleRow('*', 'tx_calendarize_domain_model_pluginconfiguration', 'uid=' . (int) $uid);
        if (!isset($row['model_name'])) {
            return null;
        }

        $query = HelperUtility::getQuery($row['model_name']);
        $query->getQuerySettings()
            ->setRespectStoragePage(false);
        $query->matching($query->equals('uid', $uid));

        return $query->execute()
            ->getFirst();
    }

    /**
     * Add the configurations to the given Plugin configuration.
     *
     * @param array $config
     *
     * @return array
     */
    public function addConfig($config)
    {
        foreach (Register::getRegister() as $key => $configuration) {
            $config['items'][] = [
                $configuration['title'],
                $key,
            ];
        }

        return $config;
    }
}
