.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../Includes.txt


.. _linkhandler:

Linkhandler
===========

With a proper configuration of the EXT:linkhandler you can select in your RTE a event and link to it.

.. code-block:: php
	:caption: linkhandler-setup.ts

	plugin.tx_linkhandler {
		tx_calendarize_domain_model_event {
			parameter={$PID.detailPage}
			additionalParams=&tx_calendarize_calendar[event]={field:uid}
			additionalParams.insertData=1
			useCacheHash=1
		}
	}

Optional you can add a special configuration to the additionalParams

.. code-block:: php
	:caption: linkhandler-setup.ts

	&tx_calendarize_calendar[extensionConfiguration]=Event

where the value is the uniqueRegisterKey you defined in the configuration of your ownevents_.

And to add the Event to your RTE enhance the RTE-Configuration.

.. code-block:: php
	:caption: page-tsconfig.ts

	RTE.default {
		tx_linkhandler {
				tx_calendarize_domain_model_event {
					label=Events
					listTables=tx_calendarize_domain_model_event
					onlyPids=$StorageFolder
					previewPageId=$PreviewPage
				}
		}
	}

