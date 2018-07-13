Own events
----------

.. _ownevents:

The concept of the calendarize extension is the creation of own models and tables that should be part of the calendar output.

.. code-block:: php

	// in ext_tables.php
	$configuration = [...];
	\HDNET\Calendarize\Register::extTables($configuration);

.. code-block:: php

	// in ext_localconf.php
	$configuration = [...];
	\HDNET\Calendarize\Register::extLocalconf($configuration);


The following code show the configuration that should be the same in ext_tables and ext_localconf:

.. code-block:: php

	$configuration = [
        'uniqueRegisterKey' => 'MyEvent', // A unique Key for the register (e.g. you Extension Key + "Event")
        'title'             => 'My Event', // The title for your events (this is shown in the FlexForm configuration of the Plugins)
        'modelName'         => \HDNET\MyExtension\Domain\Model\MyEvent::class, // the name of your model
        'partialIdentifier' => 'MyEvent', // the identifier of the partials for your event. In most cases this is also unique
        'tableName'         => 'tx_myextension_domain_model_myevent', // the table name of your event table
        'required'          => true, // set to true, than your event need a least one event configuration
        'subClasses'        => array of classnames, // insert here all classNames, which are used for the extended models
    ];

Beginning with Typo3 version 8.5 frontend requests no longer load ext_tables.php in requests.
The only exception is if a backend user is logged in to the backend at the same time to initialize the admin panel or frontend editing.
In order to load the needed column mapping for your Model, you have to override the tca configuration for the corresponding table:

.. code-block:: php

    // in Configuration/TCA/Overrides/<tx_extension_domain_model_event>.php
    \HDNET\Calendarize\Register::createTcaConfiguration($configuration);

To modify the amount of items shown in the preview you can change the amount in the ext_tables.php after calling the Register::extTables method.
The default value is 10 items.

.. code-block:: php
	// in ext_tables.php
	$GLOBALS['TCA']['tx_myextension_domain_model_myevent']['columns']['calendarize_info']['config']['items'] = 25;
