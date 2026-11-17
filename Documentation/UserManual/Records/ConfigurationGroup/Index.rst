..  include:: /Includes.txt

..  _configurationgroup:

Configuration Group
===================

A configuration group bundles one or more configurations (dates), that are used by
multiple events. The group is assigned to an event via a configuration of the type
"Group".

Updating the events
-------------------

If a configuration group is changed (e.g. the start/end date of one of its configurations
is changed or a further date is added), all events that use this group are reindexed
automatically. This also works for nested groups (a group that is used by another group)
and for the translations of the events.

..  note::
    If events are created or changed outside of the TYPO3 backend (e.g. by an import or
    a direct database manipulation), the index is not updated automatically. Use the
    :ref:`scheduler <scheduler>` task "calendarize:reindex" in this case.
