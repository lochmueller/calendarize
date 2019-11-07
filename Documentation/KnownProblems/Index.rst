.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt

All known problems are listed on GitHub in the issue tracker at https://github.com/lochmueller/calendarize/issues

If you find a bug or have a feature request for this extension, please create a issue in the issue tracker on GitHub.


.. _known-problems:

Known Problems
==============

1: The configuration record has starttime, endtime and hidden attributes. This values control the Index Building process. By concept the index is build just once (on save). So: If you use this field, please add the scheduler task in a short-termn interval to get the right index records.
