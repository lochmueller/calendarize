..  include:: /Includes.txt

Workspaces
==========

EXT:calendarize support Workspaces since version v11. Because of the "special handling" of the index calculation some words for this...

Handling
--------

The index calculation are based on the configurations. That means, if you change a event configuration in a Workspace, there is no 1:1 relation between a Live and a Draft/Workspace record.
That is the why the extension handle the Index records in a special way. First point is, that the index table is part of the Workspace/Versioning mechanism, but is excluded in the Workspace Backend module.
So it is not required to publish every single Index of an Event. The Index will be recalculated after the publish process so there are always the right one.

Next point are the Index records: If you change a Event in a Workspace, ALL live Index records are marked as deleted in the current Workspace and the Indexer create Live-Placeholder and Workspace Index records.
So there will be no relation between a Live Index and a Workspace Index, because all Indices are created new. That do not mean, that the publishing create new Index records in Live!

Note. Every "version record" trigger the live record index process in front of the own index process.

Selection
---------

The "reindexAll" (via Scheduler) but also the single index process take care, that the Live version is always the first in the index process and after the Live Index the Workspace records are created.


Scheduler
---------

I suggest to enable the "reindex all" command in the scheduler to cleanup the index in the right way.

