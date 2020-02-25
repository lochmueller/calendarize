.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../Includes.txt


.. _urlrewrite:

URL Rewrite
===========

Speaking URLs for events are not as simple, because the parameter is the Index ID and the related table is configured in the index table.
This is the reason, why there are Services to generate speaking URLs of index IDs. If your are using a own event structure. Please implement the feature interface::

  \HDNET\Calendarize\Features\SpeakingUrlInterface

For RealURL the extension register the extensionConfiguration hook. There is a user function that generated and cache the right segments. If you want to configure realurl manualle, take a look into::

  \HDNET\Calendarize\Hooks\RealurlConfiguration

For cooluri you can use this configuration to get the right title incl. a date::

  <part>
    <parameter>tx_calendarize_calendar[@widget_0][currentPage]</parameter>
    <t3conv>1</t3conv>
  </part>
  <part>
    <parameter>tx_calendarize_calendar[index]</parameter>
    <userfunc>\HDNET\Calendarize\Service\Url\CoolUri->convertStatic</userfunc>
  </part>

For TYPO3 > 9.0: Please use the EventMapper for URL rewrite options of the index.
If you are using TYPO3 >= 9.0 just load the RouteEnhancers of the extension.

.. code-block:: yaml

  imports:
    - { resource: "typo3conf/ext/calendarize/Configuration/Yaml/RouteEnhancers.yaml" }
