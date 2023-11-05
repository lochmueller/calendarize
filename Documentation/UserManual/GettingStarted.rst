..  include:: /Includes.txt

..  _gettingStarted:

Getting started
===============

The tutorial will show you use the basic usage of the calendarize extension in TYPO3.
This requires the first steps of the :ref:`installation <installation>` to be completed.

The event record stores information, whereas the plugin is used to render these records in the frontend.

..  rst-class:: bignums-xxl

1.  Create an Event

    #.  Create or select a sysfolder to store the records.

    #.  In the list module choose :guilabel:`Create a new record` in the top bar.
        Click on :guilabel:`Event` under :guilabel:`Calendarize - Event Management`.

        ..  image:: ../Images/User/new-record.png
            :alt: New record dialog
            :class: with-shadow
            :scale: 65

    #.  Fill in the field :guilabel:`Title`.

    #.  Navigate to the tab :guilabel:`Date options` and create a Calendarize configuration by clicking on :guilabel:`Create new`

    #.  Fill in the field :guilabel:`Start date` with a date in the **near future**.

        ..  image:: ../Images/User/event-date-options.png
            :alt: Event record in the Date Options tab
            :class: with-shadow
            :scale: 65

    #.  Save and close the record.

2.  Add a plugin

    #.  Create or select a page for listing and showing the events.

    #.  In the page module add a new content element.
        Under the tab :guilabel:`Plugins` choose :guilabel:`Calendar`.

        ..  image:: ../Images/User/create-content-element.png
            :alt: Create content dialog on the plugins page
            :class: with-shadow
            :scale: 65

    #.  You can find the plugin settings within the tab :guilabel:`Plugin`.

    #.  Select the :guilabel:`mode` :code:`List + Detail`.

        ..  image:: ../Images/User/plugin-list-detail.png
            :alt: Plugin settings with List + Detail selected
            :class: with-shadow
            :scale: 65

    #.  Navigate to the subtab :guilabel:`General Configuration`.
        Fill the field :guilabel:`Startingpoint` by selecting the folder from the beginning.

    #.  Save the record.

3.  Check the result

    Load the page in the frontend.
    You should see your created event.
    A click on the title should show the record on the detail page.
