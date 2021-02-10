.. _removeoutdated:

Remove outdated events
======================

Outdated events can either be hidden or removed with a scheduler task.

The events are determined by looking at the index, where the end date has to be older than the waitingPeriod (relative to today).
This must be true for all indices of a single event.
This task also supports custom events.


1. Add a new Scheduler Task
2. Select the class :guilabel:`Execute console commands`
3. Select :guilabel:`Frequency` for the execution
4. Go to section :guilabel:`Schedulable Command. Save and reopen to define
   command arguments` at the bottom.
5. Select :guilabel:`calendarize:cleanup` (press save)
6. Select the options you want to execute the queue with.
