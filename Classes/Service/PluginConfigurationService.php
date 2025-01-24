<?php

declare(strict_types=1);

namespace HDNET\Calendarize\Service;

use HDNET\Calendarize\Domain\Model\PluginConfiguration;
use HDNET\Calendarize\Event\PluginConfigurationSettingsEvent;
use HDNET\Calendarize\Register;
use HDNET\Calendarize\Utility\HelperUtility;
use Psr\EventDispatcher\EventDispatcherInterface;

class PluginConfigurationService
{
    public function __construct(
        protected EventDispatcherInterface $eventDispatcher,
    ) {}

    /**
     * Respect plugin configuration.
     */
    public function respectPluginConfiguration(array $settings): array
    {
        $settings['pluginConfiguration'] = $this->buildPluginConfigurationObject(
            (int)($settings['pluginConfiguration'] ?? 0),
        );
        if ($settings['pluginConfiguration'] instanceof PluginConfiguration) {
            $checkFields = [
                'detailPid',
                'listPid',
                'yearPid',
                'quarterPid',
                'monthPid',
                'weekPid',
                'dayPid',
                'bookingPid',
                'configuration',
            ];

            foreach ($checkFields as $checkField) {
                if (\in_array(trim($settings[$checkField]), ['', '0'], true)) {
                    $function = 'get' . ucfirst($checkField);
                    $settings[$checkField] = $settings['pluginConfiguration']->$function();
                }
            }
        }

        $event = new PluginConfigurationSettingsEvent($settings);
        $this->eventDispatcher->dispatch($event);

        return $event->getSettings();
    }

    /**
     * Add the configurations to the given Plugin configuration.
     */
    public function addConfig(array $config): array
    {
        foreach (Register::getRegister() as $key => $configuration) {
            $config['items'][] = [
                'label' => $configuration['title'],
                'value' => $key,
                'icon' => $GLOBALS['TCA'][$configuration['tableName']]['ctrl']['typeicon_classes']['default'] ?? 'tcarecords-' . $configuration['tableName'] . '-default',
            ];
        }

        return $config;
    }

    /**
     * Build the plugin configuration object.
     */
    protected function buildPluginConfigurationObject(int $uid): ?object
    {
        $table = 'tx_calendarize_domain_model_pluginconfiguration';

        $db = HelperUtility::getDatabaseConnection($table);
        $row = $db
            ->select(['*'], $table, ['uid' => $uid])
            ->fetchAssociative();

        if (!isset($row['model_name'])) {
            return null;
        }

        $query = HelperUtility::getQuery($row['model_name']);
        $query
            ->getQuerySettings()
            ->setRespectSysLanguage(false)
            ->setRespectStoragePage(false);

        return $query
            ->matching($query->equals('uid', $uid))
            ->execute()
            ->getFirst();
    }
}
