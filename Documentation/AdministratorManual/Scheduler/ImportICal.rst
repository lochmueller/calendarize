..  include:: /Includes.txt

..  _importIcs:

Import ICS / ICal Calendar
==========================

An external ICS calendar can be imported with a scheduler task.
For this the scheduler task creates default events on the given pid.
If an event already exists (determined by UID inside importId) on **ANY pid**, the event only gets updated.
For your own events implement the :file:`ImportSingleIcalEventListener`.

#.  Add a new Scheduler Task
#.  Select the class :guilabel:`Execute console commands`
#.  Select :guilabel:`Frequency` for the execution
#.  Go to section :guilabel:`Schedulable Command. Save and reopen to define
    command arguments` at the bottom.
#.  Select :guilabel:`calendarize:import` (press save)
#.  Select the options you want to execute the queue with, it's important to
    check the checkboxes and not only fill in the values.

Arguments
---------

+----------------+-----------------------------------------------------------+------------------------------------------------------------------------------------------------------------+
| Argument       | Description                                               | Example                                                                                                    |
+================+===========================================================+============================================================================================================+
| icsCalendarUri | The URI of the iCalendar ICS                              | https://calendar.google.com/calendar/ical/de.german%23holiday@group.v.calendar.google.com/public/basic.ics |
+----------------+-----------------------------------------------------------+------------------------------------------------------------------------------------------------------------+
| pid            | The page ID to create new elements                        | 10                                                                                                         |
+----------------+-----------------------------------------------------------+------------------------------------------------------------------------------------------------------------+
| since          | Imports all events since the given date.                  | 2020-07-23                                                                                                 |
| (optional)     | You can use relative and absolute PHP dates.              |                                                                                                            |
|                | Note: For recurring even only the start date is relevant. | -10 days                                                                                                   |
+----------------+-----------------------------------------------------------+------------------------------------------------------------------------------------------------------------+


Supported properties
--------------------

The iCalendar format specification is complex, so only a subset of properties is supported.

The generation of (event) configurations currently support following properties :

*   UID
*   start / end date and time
*   allday events
*   duration (composer only)
*   status (e.g. CANCELLED) (`STATUS`)
*   recurring configuration (`RRULE`)

    *   frequency (daily, weekly, monthly, yearly) (`FREQ`)
    *   interval (`INTERVAL`)
    *   until / last date (`UNTIL`)
    *   the number of occurrences (`COUNT`)
    *   (simple) recurrences with monthly/yearly frequencies (e.g. every first Monday into the month) (`BYDAY`, `BYSETPOS`)

In addition to them the **default event** makes use of:

*   title (`SUMMARY`)
*   description (`DESCRIPTION`)
*   location (`LOCATION`)
*   organizer (`ORGANIZER`)
