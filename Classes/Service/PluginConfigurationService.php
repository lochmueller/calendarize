<?php


namespace HDNET\Calendarize\Service;

use HDNET\Calendarize\Domain\Model\PluginConfiguration;
use HDNET\Calendarize\Register;
use HDNET\Calendarize\Utility\HelperUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\SignalSlot\Dispatcher;

/**
 * PluginConfigurationService
 */
class PluginConfigurationService
{

    /**
     * @param ConfigurationManagerInterface $configurationManager
     * @return ConfigurationManagerInterface
     */
    public function respectPluginConfiguration(ConfigurationManagerInterface &$configurationManager)
    {
        $rawConfiguration = $configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);

        $rawConfiguration['settings']['pluginConfiguration'] = $this->buildPluginConfigurationObject((int)$rawConfiguration['settings']['pluginConfiguration']);
        if ($rawConfiguration['settings']['pluginConfiguration'] instanceof PluginConfiguration) {
            $checkFields = [
                'detailPid',
                'listPid',
                'yearPid',
                'monthPid',
                'weekPid',
                'dayPid',
                'bookingPid',
            ];

            foreach ($checkFields as $checkField) {
                if ((int)$rawConfiguration['settings'][$checkField] === 0) {
                    $function = 'get' . ucfirst($checkField);
                    $rawConfiguration['settings'][$checkField] = $rawConfiguration['settings']['pluginConfiguration']->$function();
                }
            }


            $rawConfiguration['persistence']['storagePid'] .= ',' . $rawConfiguration['settings']['pluginConfiguration']->getStoragePid();
        }

        /** @var Dispatcher $dispatcher */
        $dispatcher = HelperUtility::create(Dispatcher::class);
        $arguments = [
            'configurationManager' => $configurationManager,
            'rawConfiguration' => $rawConfiguration,
        ];
        $arguments = $dispatcher->dispatch(__CLASS__, __METHOD__, $arguments);

        $configurationManager->setConfiguration($arguments['rawConfiguration']);
        return $configurationManager;
    }

    /**
     * Build the plugin configuration object
     *
     * @param int $uid
     * @return null|object
     */
    protected function buildPluginConfigurationObject($uid)
    {
        $db = HelperUtility::getDatabaseConnection();
        $row = $db->exec_SELECTgetSingleRow('*', 'tx_calendarize_domain_model_pluginconfiguration', 'uid=' . (int)$uid);
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
     * Add the configurations to the given Plugin configuration
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
