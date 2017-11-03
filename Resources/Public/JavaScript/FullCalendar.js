if (window.jQuery) {
	jQuery(function () {
		let $monthCalendar = jQuery('.fullCalendarIo');
		if ($monthCalendar.length && typeof monthEvents != 'undefined') {
			monthFirstDay = parseInt(monthFirstDay, 10);
			if (monthFirstDay == 7) {
				monthFirstDay = 0;
			}

			let language = $monthCalendar.first().attr('data-language');
			if (typeof language == typeof undefined || language == false) {
				language = "en";
			}
			jQuery('.regularMonthView').hide();
			$monthCalendar.fullCalendar({
				locale: language, firstDay: monthFirstDay, header: {
					left: '', center: '', right: ''
				}, defaultDate: monthEventsCurrentDate, events: monthEvents
			});
		}

	});
} else {
	alert('The Moment.js function of the calendarize need jQuery!');
}
