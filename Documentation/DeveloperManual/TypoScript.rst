..  include:: /Includes.txt

TypoScript
----------

You can render every EXT:calendarize view also directly with TypoScript. The example below renders a single event into the TS PAGE object. Please replace the markers with the right values:

..  code-block:: typoscript

    page.9999 =< tt_content.list.20.calendarize_calendar
    page.9999 {
      switchableControllerActions {
        Calendar {
          1 = single
        }
      }

      settings < plugin.tx_calendarize.settings
      settings {
        # Base on the event configuration table. CSV
        singleItems = ###EVENT_TABLE_NAME###_###EVENT_UID###
        # Note: perhaps also other Event configurations
        configuration = Event
      }
      persistence.storagePid = ###YOUR_STORAGE_FOLDER###
    }
