..  include:: /Includes.txt

..  _configuration:

Configuration
=============

A configuration defines when an event takes place. It is either a single date, a
recurring date (:guilabel:`Frequency`) or a reference to a configuration group.

..  _configuration-day:

Day
---

The field :guilabel:`Day` has a different meaning, depending on the selected
:guilabel:`Frequency`:

Minutely, hourly and daily frequency
    The generated dates are limited to the selected weekdays. All other dates are
    dropped. This allows e.g. an event that takes place every day, but only on
    Monday and Wednesday.

    The limitation is applied *after* the :guilabel:`Interval`, so an interval of
    two days combined with a selected Monday results in a date every second week.
    If the start date itself is not on one of the selected weekdays, it is
    dropped as well.

Monthly and yearly frequency
    The day is the anchor of the :guilabel:`Recurrence`, e.g. the *first* *Monday*
    of the month. Without a recurrence the day has no effect.

..  note::

    The :guilabel:`Amount` of repetitions counts the dates that are actually
    created, so a daily frequency limited to Monday with an amount of three
    results in three dates on three consecutive Mondays.
