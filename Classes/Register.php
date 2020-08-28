<?php

/**
 * Register the calendarize objects.
 */
declare(strict_types=1);

namespace HDNET\Calendarize;

use HDNET\Calendarize\Domain\Model\ConfigurationGroup;
use HDNET\Calendarize\Domain\Model\Event;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

/**
 * Register the calendarize objects.
 */
class Register
{
    /**
     * Register in the extTables.
     *
     * @param array $configuration
     */
    public static function extTables(array $configuration)
    {
        self::createTcaConfiguration($configuration);
        self::registerItem($configuration);
    }

    /**
     * Register in the extLocalconf.
     *
     * @param array $configuration
     */
    public static function extLocalconf(array $configuration)
    {
        self::registerItem($configuration);
    }

    /**
     * Get the EXT:autoloader default configuration.
     *
     * @return array
     */
    public static function getDefaultAutoloader(): array
    {
        return [
            'Hooks',
            'Slots',
            'SmartObjects',
            'FlexForms',
            'CommandController',
            'StaticTyposcript',
            'TypeConverter',
        ];
    }

    /**
     * Get the register.
     *
     * @return array
     */
    public static function getRegister(): array
    {
        return \is_array($GLOBALS['TYPO3_CONF_VARS']['EXT']['Calendarize']) ? $GLOBALS['TYPO3_CONF_VARS']['EXT']['Calendarize'] : [];
    }

    /**
     * Default configuration for the current extension.
     * If you want to use the calendarize features in your own extension,
     * you have to implement a own configuration.
     *
     * @return array
     */
    public static function getDefaultCalendarizeConfiguration(): array
    {
        $configuration = [
            'uniqueRegisterKey' => 'Event',
            'title' => 'Calendarize Event',
            'modelName' => Event::class,
            'partialIdentifier' => 'Event',
            'tableName' => 'tx_calendarize_domain_model_event',
            'required' => true,
            // 'tcaTypeList'       => '', // optional - only for special type elements
            // 'overrideBookingRequestModel' => \NAME\SPACE\CLASS\Name::class,
            // 'fieldName' => 'xxxx', // default is "calendarize"
        ];

        return $configuration;
    }

    /**
     * @return array
     */
    public static function getGroupCalendarizeConfiguration(): array
    {
        return [
            'uniqueRegisterKey' => 'ConfigurationGroup',
            'title' => 'Calendarize Configuration Group',
            'modelName' => ConfigurationGroup::class,
            'partialIdentifier' => 'ConfigurationGroup',
            'tableName' => 'tx_calendarize_domain_model_configurationgroup',
            'required' => true,
            'fieldName' => 'configurations',
        ];
    }

    /**
     * Add the calendarize to the given TCA.
     *
     * @param array $configuration
     */
    public static function createTcaConfiguration(array $configuration)
    {
        $fieldName = isset($configuration['fieldName']) ? $configuration['fieldName'] : 'calendarize';
        $tableName = $configuration['tableName'];
        $typeList = isset($configuration['tcaTypeList']) ? \trim($configuration['tcaTypeList']) : '';
        $GLOBALS['TCA'][$tableName]['columns'][$fieldName] = [
            'label' => 'Calendarize',
            'l10n_mode' => 'exclude',
            'config' => [
                'type' => 'inline',
                'foreign_table' => 'tx_calendarize_domain_model_configuration',
                'minitems' => $configuration['required'] ? 1 : 0,
                'maxitems' => 99,
                'behaviour' => [
                    'enableCascadingDelete' => true,
                ],
            ],
        ];

        $GLOBALS['TCA'][$tableName]['columns']['calendarize_info'] = [
            'label' => 'LLL:EXT:calendarize/Resources/Private/Language/locallang.xlf:tca.information',
            'config' => [
                'type' => 'user',
                'renderType' => 'calendarizeInfoElement',
                'parameters' => [
                    'items' => 10,
                ],
            ],
        ];
        ExtensionManagementUtility::addToAllTCAtypes($tableName, $fieldName . ',calendarize_info', $typeList);
    }

    /**
     * Internal register.
     *
     * @param array $configuration
     */
    protected static function registerItem(array $configuration)
    {
        $GLOBALS['TYPO3_CONF_VARS']['EXT']['Calendarize'][$configuration['uniqueRegisterKey']] = $configuration;
    }
}
