document.addEventListener('DOMContentLoaded', function () {
  let timeEntries = document.getElementsByClassName("momentJs");
  for (var i = 0; i < timeEntries.length; i++) {

    let language = timeEntries[i].getAttribute('data-language');
    if (typeof language !== typeof undefined && language !== false) {
      moment.locale(language);
    }
    let m = moment.parseZone(timeEntries[i].innerHTML);
    timeEntries[i].innerHTML = m.format(timeEntries[i].getAttribute('data-format'));
  }
});
