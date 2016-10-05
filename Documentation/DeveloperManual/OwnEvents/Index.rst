Own events
----------

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
    ];
