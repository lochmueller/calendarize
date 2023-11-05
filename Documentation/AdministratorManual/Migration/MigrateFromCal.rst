..  include:: /Includes.txt

Migration from cal to calendarize
=================================

This will help you to migrate data from cal to calendarize.

Requirements
------------

..  attention::
    * Installed extension calendarize (version 12)
    * Tables from cal (e.g. :code:`tx_cal_*`, ...)
    * TYPO3 v10.4 (v11 is NOT supported)

    ..  deprecated:: 13
        Was removed in calendarize 13 and later


Migration
---------

Migration of records
~~~~~~~~~~~~~~~~~~~~

The migration is an upgrade wizard and listed inside the admin tool.
Inside the :guilabel:`Upgrade > Upgrade Wizard` and you should be listed as "Migrate cal event structures to the new calendarize event structures. [...]".


..  important::
    The wizard is only displayed, if the table :code:`tx_cal_event` exists.


It is also possible to execute the wizard from the command line.

..  code-block:: bash

    # Run using the identifier 'calendarize_calMigration'
    ./vendor/bin/typo3 upgrade:run calendarize_calMigration

..  versionchanged:: 8.2.0

    The identifier was formerly called :code:`HDNET\\Calendarize\\Updates\\CalMigrationUpdate`.


Migration of plugins
~~~~~~~~~~~~~~~~~~~~

The plugins of cal can partially be migrated to plugins of calendarize.
For this take a look at `sypets/cal2calendarize <https://github.com/sypets/cal2calendarize>`__.


Migrated tables
~~~~~~~~~~~~~~~

The wizard uses the default event model and places the result in the same folder as the old records.
To keep track of already migrated records, the :code:`import_id` is a combination of :code:`calMigration:` and the old uid.


Events
""""""

The basic / common fields of Calendar Events (:code:`tx_cal_event`) get migrated to the Event model.
For most date options a Configuration record is used.


Event Exception (Groups)
""""""""""""""""""""""""

Event exceptions are used to exclude dates in recurring events and can be grouped.

Event Exception Groups (:code:`tx_cal_exception_event_group`) get migrated to Configuration Groups.
The corresponding Event Exceptions (:code:`tx_cal_exception_event`) become single Configurations and are added to the group.
Finally the group gets added to the Events with excluded :guilabel:`Handling`.


Categories
""""""""""

Older versions of cal (< 2.0) used its own table :code:`cal_category` for categories.
These will be migrated to :code:`sys_categories` of TYPO3.
The newer version using :code:`sys_categories` is also supported.


Calendar, Locations, Attendees, Deviations
""""""""""""""""""""""""""""""""""""""""""

These record types are currently **NOT** migrated.


Not migrated
~~~~~~~~~~~~

Be aware that some things are **not** migrated:

- Templates
- TypoScript configurations
- (Plugins)
- Some fields and tables (e.g. calendar, locations, attendees, deviations, ...)
- Deviations
- Additionally fields by other extensions
- ...
