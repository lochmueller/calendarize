..  include:: /Includes.txt

..  _removeoutdated:

Remove outdated events
======================

Outdated events can either be hidden or removed with a scheduler task.

The events are determined by looking at the index, where the end date has to be older than the waitingPeriod (relative to today).
This must be true for all indices of a single event.
This task also supports custom events.


#.  Add a new Scheduler Task
#.  Select the class :guilabel:`Execute console commands`
#.  Select :guilabel:`Frequency` for the execution
#.  Go to section :guilabel:`Schedulable Command. Save and reopen to define
    command arguments` at the bottom.
#.  Select :guilabel:`calendarize:cleanup` (press save)
#.  Select the options you want to execute the queue with.
