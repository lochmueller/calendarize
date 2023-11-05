..  include:: /Includes.txt

..  _createRecurringEventsWithExceptions:

Create recurring event with exceptions
======================================

This tutorial will show you how to create a recurring event with exceptions / excluded dates.

Scenario
--------

In this example an event is repeated every friday.
It should be limited to a specific time span and exclude the school holidays.
Instead of excluding every single date in each event, a configuration group is used.
Through this the exceptions can be reused in multiple events and easier maintained.

Realization
-----------

..  rst-class:: bignums-xxl

1.  Create a configuration group

    #.  Create a new configuration group

    #.  Add configurations with time frames which should be excluded (:guilabel:`Handling` should still be :code:`Include`)

        ..  image:: ../Images/User/configuration-group-holidays.png
            :alt: New configuration group with multiple configurations
            :class: with-shadow
            :scale: 65


2.  Create a recurring event

    #.  Create an Event and fill in the mandatory fields.

    #.  Add a configuration with a weekly frequency and a till date.

        ..  image:: ../Images/User/event-recurring-frequency.png
            :alt: Event configuration on the frequency tab
            :class: with-shadow
            :scale: 65

    #.  Add an additional configuration and set :guilabel:`Type` to :code:`Group` and the :guilabel:`Handling` to :code:`Exclude`.

    #.  Fill in the field :guilabel:`Groups` by selecting the previously created group.

        ..  image:: ../Images/User/event-exclude-group.png
            :alt: Event configuration on the frequency tab
            :class: with-shadow
            :scale: 65

    #.  Save and repeat this for other events.

3.  Check the result

    Inspect the dates in :guilabel:`Calendarize (Information - Save to refresh...)` or in the frontend.

..  note::

    Group configurations are valid event models, so they might be displayed by themselves in the frontend.
    Use :guilabel:`Configuration (record type)` in the plugin to prevent this.
