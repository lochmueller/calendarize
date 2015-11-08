if (window.jQuery) {
	jQuery(function () {
		var $monthCalendar = jQuery('.fullCalendarIo');
		if ($monthCalendar.length && typeof monthEvents != 'undefined') {
			jQuery('.regularMonthView').hide();
			$monthCalendar.fullCalendar({
				header: {
					left: '', center: '', right: ''
				}, defaultDate: monthEventsCurrentDate, events: monthEvents
			});
		}

	});
} else {
	alert('The Moment.js function of the calendarize need jQuery!');
}