document.addEventListener('DOMContentLoaded', function () {
  if (typeof FullCalendar === 'undefined') {
    return;
  }

  let calendarElements = document.querySelectorAll('.fullCalendarIo');
  if (!calendarElements.length) {
    return;
  }

  // Hide the regular (server side rendered) month table
  let divsToHide = document.getElementsByClassName('regularMonthView');
  for (let i = 0; i < divsToHide.length; i++) {
    divsToHide[i].style.visibility = 'hidden';
    divsToHide[i].style.display = 'none';
  }

  calendarElements.forEach(function (calendarEl) {
    // Events are attached to the element by the Month partial. The globals are
    // only a fallback for custom templates that still use the old markup.
    let events = calendarEl.calendarizeEvents;
    if (typeof events === 'undefined') {
      events = typeof monthEvents !== 'undefined' ? monthEvents : undefined;
    }
    if (typeof events === 'undefined') {
      return;
    }

    let language = calendarEl.getAttribute('data-language');
    if (!language) {
      language = 'en';
    }

    // calendarize uses ISO-8601 (1 Monday - 7 Sunday), FullCalendar 0 Sunday - 6 Saturday
    let firstDay = calendarEl.getAttribute('data-first-day');
    if (!firstDay && typeof monthFirstDay !== 'undefined') {
      firstDay = monthFirstDay;
    }
    firstDay = parseInt(firstDay, 10);
    if (isNaN(firstDay)) {
      firstDay = 1;
    }
    firstDay = firstDay % 7;

    let initialDate = calendarEl.getAttribute('data-initial-date');
    if (!initialDate && typeof monthEventsCurrentDate !== 'undefined') {
      initialDate = monthEventsCurrentDate;
    }

    let options = {
      initialView: 'dayGridMonth',
      locale: language,
      firstDay: firstDay,
      // The server only delivers the events of the rendered month. Navigation
      // has to happen via the calendarize month links (settings.monthPid), so
      // that the events of the target month are fetched from the server.
      headerToolbar: false,
      events: events
    };

    if (initialDate) {
      options.initialDate = initialDate;
    }

    new FullCalendar.Calendar(calendarEl, options).render();
  });
});
