.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../Includes.txt


.. _changing_templates:

Changing paths of the template
==============================

Please do never change templates directly in the resources folder of the extensions,
since your changes will get overwritten by extension updates.

Configure your TypoScript setup like shown below::

  plugin.tx_calendarize {
    view {
      templateRootPaths {
        150 = your/new/path/
      }
      partialRootPaths {
        150 = your/new/path/
      }
      layoutRootPaths {
        150 = your/new/path/
      }
    }
  }

Doing so, you can just **override single files** from the original templates.
The calendarize templates are always the fallback (position 50). Alternatively you can change the constants::

  plugin.tx_calendarize.view.templateRootPath
  plugin.tx_calendarize.view.partialRootPath
  plugin.tx_calendarize.view.layoutRootPath

Theses constants are used at position 100 in the TS setup of the calendarize extension.
