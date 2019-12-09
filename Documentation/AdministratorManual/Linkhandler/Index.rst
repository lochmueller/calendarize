.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../Includes.txt


.. _linkhandler:

Linkhandler
===========

With a proper configuration of linkhandler function you can select events in Link wizards and RTE. Detials at https://usetypo3.com/linkhandler.html
This configuration is enabled by default.

.. code-block:: php
	:caption: Page TS Config

	TCEMAIN {
		linkHandler {
			tx_calendarize_domain_model_event {
				handler = TYPO3\CMS\Recordlist\LinkHandler\RecordLinkHandler
				label = Events
				configuration {
					table = tx_calendarize_domain_model_event
					storagePid = xxx
					hidePageTree = 1
				}
				scanAfter = page
			}
		}
	}
