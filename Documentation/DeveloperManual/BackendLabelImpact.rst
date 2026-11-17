..  include:: /Includes.txt

Date options impact
-------------------

Every date option (configuration record) of an event is executed in the backend form to
check, if the configuration has any impact in the past or in the future. The result is
rendered as ``data-impact`` attribute around the label of the inline record:

..  code-block:: html

    <span data-impact="past"><b>Time (Include)</b><br />01.01.2020</span>
    <span data-impact="future"><b>Time (Include)</b><br />01.01.2099</span>

``past``
    All dates of the configuration (incl. recurrence and frequency) are over.

``future``
    The configuration creates at least one date of today or of the future.

The attribute is missing, if the impact can not be calculated: for new (unsaved) records,
for external calendars (the ICS file would have to be downloaded for every rendering of
the form) and for configurations without any valid date.

You can use the attribute in your own backend JavaScript, e.g. to hide the date options
of the past:

..  code-block:: javascript

    document.querySelectorAll('[data-impact="past"]').forEach((label) => {
      label.closest('.form-irre-object')?.classList.add('hidden');
    });
