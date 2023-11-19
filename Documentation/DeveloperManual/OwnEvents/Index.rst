..  include:: /Includes.txt

..  _ownevents:

Own events
==========

The concept of the calendarize extension is the creation of own models and tables that should be part of the calendar output.

Configuration
-------------

Calendarize needs a configuration to handle your own model.
A common practice is to store this in a static function (e.g. :php:`Classes/Register.php`).

..  code-block:: php
    :caption: EXT:some_extension/Classes/Register.php

    $configuration = [
        // Unique Key for the register (e.g. you Extension Key + "Event")
        'uniqueRegisterKey' => 'MyEvent',
        // Title for your events (this is shown in the FlexForm configuration of the Plugins)
        'title'             => 'My Event',
        // Name of your model
        'modelName'         => \HDNET\MyExtension\Domain\Model\MyEvent::class,
        // Partials identifier (HTML) for your event (in most cases unique)
        'partialIdentifier' => 'MyEvent',
        // Table name of your event table
        'tableName'         => 'tx_myextension_domain_model_myevent',
        // If true, your event requires at least one event configuration
        'required'          => true,

        // [OPTIONAL] All classNames used for the extended models
        'subClasses'        => array of classnames,
        // [OPTIONAL] Field name of the configurations, default is 'calendarize' (recommended)
        'fieldName'         => 'calendarize'
    ];

This configuration is then registered to calendarize by adding it to your :php:`ext_localconf.php`:

..  code-block:: php
    :caption: EXT:some_extension/ext_localconf.php

    $configuration = [...];
    \HDNET\Calendarize\Register::extLocalconf($configuration);


Additionally, you need to add the TCA configuration for the event configuration and information field:

..  code-block:: php
    :caption: EXT:some_extension/Configuration/TCA/Overrides/<tx_extension_domain_model_event>.php

    \HDNET\Calendarize\Register::createTcaConfiguration($configuration);


Change preview count
~~~~~~~~~~~~~~~~~~~~

To modify the amount of items shown in the preview you can change the amount in the TCA override.
10 items are displayed by default.

..  code-block:: php
    :caption: EXT:some_extension/Configuration/TCA/Overrides/<tx_extension_domain_model_event>.php

    $GLOBALS['TCA']['tx_myextension_domain_model_myevent']['columns']['calendarize_info']['config']['items'] = 25;
