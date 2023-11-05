..  include:: /Includes.txt

Speaking URLs - Slugs
=====================

A slug is a unique name for an resource.
The index has an slug field for all indices and can be used to map a "speaking URL" segment to an index.
Since an event can produce multiple indices, we need an extra logic to generate unique slugs for them.

Slug structure
--------------

The slug of an index consists out of multiple parts::

    {1:base-slug}-{2?:slug-suffix}-{3?:regular-core-conflict-counter}

1.  `base-slug`

    Speaking part of the slug for each event using the :php:`SpeakingUrlInterface` (or a fallback method).

2.  `slug-suffix` (optional)

    Additionally date suffix for events with multiple occurrences.

3.  `regular-core-conflict-counter` (optional)

    Counting suffix to prevent duplicates, if the previous slug already exists.

Custom base slug for own events
-------------------------------

The :php:`SpeakingUrlInterface` can be used to generate the base slug for events.
Implement this interface in your event model and return a value e.g. a title or slug.
Alternatively, you could name your slug field `slug` or `path_segment`.


Extend the slug generation
--------------------------

The slugs are generated inside :php:`SlugService` and can be expanded by using the following PSR-14 events:

*  :php:`BaseSlugGenerationEvent`
*  :php:`SlugSuffixGenerationEvent`


SlugSuffixGenerationEvent example
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

This example shows how to add a custom suffix to the slug, in this case the start time of the event.
A resulting slug could look like `test-20201103-1715`.

..  code-block:: php

    <?php

    declare(strict_types=1);

    namespace MyVendor\MyExtension\EventListener;

    use HDNET\Calendarize\Event\SlugSuffixGenerationEvent;

    final class AddEventTimeSlugListener
    {
        public function __invoke(SlugSuffixGenerationEvent $event): void
        {
            // Optional: some additional checks, e.g. based on Model or page id (pid)
            // Get the start_time (seconds since day start) from the record
            $startTime = $event->getRecord()['start_time'];
            // Add to the existing slug (e.g. test-20201103) the current time (17:15)
            // resulting in test-20201103-1715
            $newSlug = $event->getSlug() . '-' . date('Hi', $startTime);
            // Update the slug
            $event->setSlug($newSlug);
        }
    }

Then register the event in your extension's :file:`Configuration/Services.yaml`:

..  code-block:: yaml

    services:
      # ...
      MyVendor\MyExtension\EventListener\AddEventTimeSlugListener:
        tags:
          - name: event.listener
            identifier: 'addEventTimeSlug'

See :ref:`t3coreapi:extension-development-events` for more details on implementing PSR-14 events.
