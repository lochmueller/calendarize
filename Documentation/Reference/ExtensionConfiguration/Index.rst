..  include:: /Includes.txt

..  _extensionConfiguration:

=======================
Extension Configuration
=======================

Some general settings can be configured in the Extension Configuration.

#.  Go to :guilabel:`Admin Tools > Settings > Extension Configuration`
#.  Choose :guilabel:`calendarize`

The settings are described here in detail:

..  only:: html

    ..  contents:: Properties
        :local:
        :depth: 2


Basic
=====

..  _extensionConfigurationDisableDefaultEvent:

Disable default event `disableDefaultEvent`
-------------------------------------------

..  confval:: disableDefaultEvent

    :type: boolean
    :Default: 0

    Disable the default event table in the list view and in the registration.

..  _extensionConfigurationFrequencyLimitPerItem:

Frequency limit per item `frequencyLimitPerItem`
------------------------------------------------

..  confval:: frequencyLimitPerItem

    :type: int+
    :Default: 300

    Set the maximum level of iteration of frequency events to avoid endless indexing.

..  _extensionConfigurationDisableDateInSpeakingUrl:

Disable date in speaking URL `disableDateInSpeakingUrl`
-------------------------------------------------------

..  confval:: disableDateInSpeakingUrl

    :type: boolean
    :Default: 0

    Disable the date in the speaking URL generation.

..  _extensionConfigurationTillDays:

Till Days `tillDays`
--------------------

..  confval:: tillDays

    :type: int+
    :Default:

    Maximum of (future) days for which indices should be created (per default based on start date, if till days is relative is true then based on the current day).
    The frequency limit per item is still active, make sure to set the value high enough.

    It is also possible to leave this blank and set the value per configuration item.

..  _extensionConfigurationTillDaysPast:

Till Days Past `tillDaysPast`
-----------------------------

..  confval:: tillDaysPast

    :type: int+
    :Default:

    Maximum of (past) days for which indices should be created (does only make sense if till days relative is enabled).
    The frequency limit per item is still active, make sure to set the value high enough.

    It is also possible to leave this blank and set the value per configuration item.

..  _extensionConfigurationTillDaysRelative:

Till Days Relative `tillDaysRelative`
-------------------------------------

..  confval:: tillDaysRelative

    :type: boolean
    :Default:

    Defines if till days and till days past are based on the start date or based on the current day.

    It is also possible to leave this blank and set the value per configuration item.

..  _extensionConfigurationRespectTimesInTimeFrameConstraints:

Respect times in time frame constraints `respectTimesInTimeFrameConstraints`
----------------------------------------------------------------------------

..  confval:: respectTimesInTimeFrameConstraints

    :type: boolean
    :Default: 0

    Per default :php:`IndexRepository->addTimeFrameConstraints()` only checks `start_date` and `end_date`.
    If you want the actual times to be respected (e.g. if :typoscript:`settings.overrideStartRelative` is set to `now`) enable this option.
