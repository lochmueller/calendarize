if (window.jQuery) {
	jQuery(function () {
		jQuery('.momentJs').each(function () {
			var $element = jQuery(this);
			var language = $element.attr('data-language');
			if (typeof language !== typeof undefined && language !== false) {
				moment.locale(language);
			}
			var m = moment.utc($element.html());
			jQuery(this).html(m.format($element.attr('data-format')));
		});
	});
} else {
	alert('The Moment.js function of the calendarize need jQuery!');
}
