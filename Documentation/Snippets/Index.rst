..  include:: /Includes.txt

Snippets
========

Get the event title of the current page (e.g. for Breadcrumb navigations):

..  code-block:: typoscript

    lib.eventTitle = CONTENT
    lib.eventTitle {
      table = tx_calendarize_domain_model_event
      select {
        # YOUR Staorge PID here
        pidInList = XXXXXX
        selectFields = tx_calendarize_domain_model_event.*
        join = tx_calendarize_domain_model_index ON tx_calendarize_domain_model_event.uid = tx_calendarize_domain_model_index.foreign_uid AND tx_calendarize_domain_model_index.foreign_table="tx_calendarize_domain_model_event"
        where.data = GP:tx_calendarize_calendar|index
        where.intval = 1
        where.wrap = tx_calendarize_domain_model_index.uid=|
      }
      renderObj = TEXT
      renderObj.field = title
      renderObj.htmlSpecialChars = 1
    }
    page.1000 < lib.eventTitle
    page.1000.wrap = <h3>|</h3>
