document.addEventListener('DOMContentLoaded', function () {
  let calendarEl = document.getElementById('fullCalendarIo');
  if (calendarEl && typeof monthEvents != 'undefined') {
    let divsToHide = document.getElementsByClassName("regularMonthView");
    for (let i = 0; i < divsToHide.length; i++) {
      divsToHide[i].style.visibility = "hidden"; // or
      divsToHide[i].style.display = "none"; // depending on what you're doing
    }

    let language = calendarEl.getAttribute('data-language');
    if (typeof language === typeof undefined || language === false) {
      let language = "en";
    }
    let calendar = new FullCalendar.Calendar(calendarEl, {
      initialView: 'dayGridMonth',
      locale: language,
      firstDay: monthFirstDay,
      events: monthEvents
    });
    calendar.render();
  }
});
