if (window.jQuery) {
	jQuery(function () {
		var $monthCalendar = jQuery('.fullCalendarIo');
		if ($monthCalendar.length && typeof monthEvents != 'undefined') {

			var language = $monthCalendar.first().attr('data-language');
			if (typeof language == typeof undefined || language == false) {
				language = "en";
			}
			
			jQuery('.regularMonthView').hide();
			$monthCalendar.fullCalendar({
				lang: language, header: {
					left: '', center: '', right: ''
				}, defaultDate: monthEventsCurrentDate, events: monthEvents
			});
		}

	});
} else {
	alert('The Moment.js function of the calendarize need jQuery!');
}
