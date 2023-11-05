..  include:: /Includes.txt

..  _installation:

Installation
============

This is the installation process of the extension:

#.  Install the extension with the extension manager or composer

    ..  code-block:: bash

        composer req lochmueller/calendarize

#.  Include the static TypoScript configuration `Calendarize (calendarize)` in your TypoScript template

    ..  image:: /Images/Administrator/static-templates.png
        :alt: Static include inside Typoscript template
        :class: with-shadow
        :scale: 65

#.  Create a new folder in your page tree, where you create events and related records like configuration groups.

#.  Depending on your needs, create a TYPO3 page for event listing, event details and registration.

#.  Include the plugin “Calendarize” and configure the plugin settings.

#.  Configure extension TypoScript settings depending in your constant editor on your needs.
