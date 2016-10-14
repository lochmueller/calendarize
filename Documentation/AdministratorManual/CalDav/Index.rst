.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../Includes.txt


.. _caldav:

CalDav
===========

There is a CalDav service (testing only!!!). To active the service run this composer command for your installation:

   composer require sabre/dav ~3.2.0

Furthermore add the following rule to your .htaccess file

   RewriteRule ^CalDav/([a-zA-Z1-9]*)/ /index.php?eID=CalDav&calId=$1 [L]
