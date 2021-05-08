.. include:: ../../Includes.txt

Speaking URLs - Slugs
=====================

A slug is a unique name for an resource.
The index has an slug field for all indices and can be used to map a "speaking URL" segment to an index.
Since an event can produce multiple indices, we need an extra logic to generate unique slugs for them.

Slug structure
--------------

The slug of an index consists out of multiple parts::

   {1:base-slug}-{2?:slug-suffix}-{3?:regular-core-conflict-counter}

1. `base-slug`

   Speaking part of the slug for each event using the :php:`SpeakingUrlInterface` (or a fallback method).

2. `slug-suffix` (optional)

   Additionally date suffix for events with multiple occurrences.

3. `regular-core-conflict-counter` (optional)

   Counting suffix to prevent duplicates, if the previous slug already exists.

Custom base slug for own events
-------------------------------

The :php:`SpeakingUrlInterface` can be used to generate the base slug for events.
Implement this interface in your event model and return a value e.g. a title or slug.


Extend the slug generation
--------------------------

The slugs are generated inside :php:`SlugService` and can be expanded by using the following PSR-14 events:

- :php:`BaseSlugGenerationEvent`
- :php:`SlugSuffixGenerationEvent`
