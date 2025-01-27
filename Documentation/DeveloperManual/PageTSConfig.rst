..  include:: /Includes.txt

PageTSConfig
------------

You can enable the generation of a preview link. Please configure the regular "detail" page of the indeces and link to the page including the "extensionConfiguration" parameter. EXT:calendarize will redirect you to the next index entry (to the last, if there is no future entry).

..  code-block:: typoscript

    TCEMAIN.preview {
      tx_calendarize_domain_model_event {
        # Your ID with the detail PID here
        previewPageId = 22
        useDefaultLanguageRecord = 0
        fieldToParameterMap {
          uid = tx_calendarize_calendar[event]
        }
        additionalGetParameters {
          # Change this to your EXT:calendarize event
          tx_calendarize_calendar.extensionConfiguration = Event
          tx_calendarize_calendar.action = detail
          tx_calendarize_calendar.controller = Calendar
        }
      }
    }
