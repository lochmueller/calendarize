..  include:: /Includes.txt

=============
Configuration
=============

The configuration of the calendarize is very straightforward. Please install the extension and include the Static Extension TypoScript to your TypoScript. After that, the extension is working in the frontend.

Via the constant editor, you can adapt template path and feed options. There are also options for date and time formats and limits for browesable views like the month view. Please check the constant editor for all options.

The last step is, place the plugins on your pages. The view is always related to the configured PIDs. That means, if you create a month view and set a detailPid, the template will render detail links to events.
If you leave the detailPid empty and select the monthPid, no events are in the output and the month view is a "small month view". Just try same combinations :)

..  toctree::
    :titlesonly:

    Sitemap
    Breadcrumb
