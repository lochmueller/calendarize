<?php
/**
 * Register the calendarize objects
 *
 * @author  Tim Lochmüller
 */
namespace HDNET\Calendarize;

use HDNET\Calendarize\Domain\Model\Event;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

/**
 * Register the calendarize objects
 */
class Register
{

    /**
     * Register in the extTables
     *
     * @param array $configuration
     *
     * @return void
     */
    public static function extTables(array $configuration)
    {
        self::createTcaConfiguration($configuration);
        self::registerItem($configuration);
    }

    /**
     * Add the calendarize to the given TCA
     *
     * @param $configuration
     */
    protected static function createTcaConfiguration($configuration)
    {
        $tableName = $configuration['tableName'];
        $typeList = isset($configuration['tcaTypeList']) ? trim($configuration['tcaTypeList']) : '';
        $GLOBALS['TCA'][$tableName]['columns']['calendarize'] = [
            'label'     => 'Calendarize',
            'l10n_mode' => 'exclude',
            'config'    => [
                'type'          => 'inline',
                'foreign_table' => 'tx_calendarize_domain_model_configuration',
                'minitems'      => $configuration['required'] ? 1 : 0,
                'maxitems'      => 99,
                'behaviour'     => [
                    'enableCascadingDelete' => true,
                ],
            ],
        ];

        $GLOBALS['TCA'][$tableName]['columns']['calendarize_info'] = [
            'label'  => 'LLL:EXT:calendarize/Resources/Private/Language/locallang.xlf:tca.information',
            'config' => [
                'type'     => 'user',
                'userFunc' => UserFunction\TcaInformation::class . '->informationField',
            ],
        ];
        ExtensionManagementUtility::addToAllTCAtypes($tableName, 'calendarize,calendarize_info', $typeList);
    }

    /**
     * Internal register
     *
     * @param array $configuration
     */
    protected static function registerItem(array $configuration)
    {
        $GLOBALS['TYPO3_CONF_VARS']['EXT']['Calendarize'][$configuration['uniqueRegisterKey']] = $configuration;
    }

    /**
     * Register in the extLocalconf
     *
     * @param array $configuration
     *
     * @return void
     */
    public static function extLocalconf(array $configuration)
    {
        self::registerItem($configuration);
    }

    /**
     * Get the EXT:autoloader default configuration
     *
     * @return array
     */
    public static function getDefaultAutoloader()
    {
        return [
            'Hooks',
            'Slots',
            'SmartObjects',
            'FlexForms',
            'CommandController',
            'StaticTyposcript',
            'ExtensionId',
            'ContextSensitiveHelps',
            'TypeConverter',
        ];
    }

    /**
     * Get the register
     *
     * @return array
     */
    public static function getRegister()
    {
        return is_array($GLOBALS['TYPO3_CONF_VARS']['EXT']['Calendarize']) ? $GLOBALS['TYPO3_CONF_VARS']['EXT']['Calendarize'] : [];
    }

    /**
     * Default configuration for the current extension.
     * If you want to use the calendarize features in your own extension,
     * you have to implement a own configuration.
     *
     * @return array
     */
    public static function getDefaultCalendarizeConfiguration()
    {
        $configuration = [
            'uniqueRegisterKey' => 'Event',
            'title'             => 'Calendarize Event',
            'modelName'         => Event::class,
            'partialIdentifier' => 'Event',
            'tableName'         => 'tx_calendarize_domain_model_event',
            'required'          => true,
            // 'tcaTypeList'       => '', // optional - only for special type elements
            // 'overrideBookingRequestModel' => \NAME\SPACE\CLASS\Name::class,
        ];
        return $configuration;
    }
}
