..  include:: /Includes.txt

..  _scheduler:

=========
Scheduler
=========

There is a scheduler command controller for re-indexing the current event structures.
This is only a helper scheduler task, if there are external processes that create new events without triggering the index.
It is recommended to configure the scheduler task (command controller) to run every night.

..  toctree::
    :maxdepth: 1
    :titlesonly:

    ImportICal
    RemoveOutdated
