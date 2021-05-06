.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../Includes.txt


.. _urlrewrite:

URL Rewrite
===========

Speaking URLs for events are not as simple, because the parameter is the Index ID and the related table is configured in the index table.
This is the reason why there are services to generate speaking URLs of index IDs. If you are using an own event structure, please implement the feature interface::

  \HDNET\Calendarize\Features\SpeakingUrlInterface

You can just load the RouteEnhancers of the extension.

.. code-block:: yaml

  imports:
    - { resource: "EXT:calendarize/Configuration/Yaml/RouteEnhancers.yaml" }
