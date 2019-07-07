if (window.jQuery) {
	jQuery(function () {
		jQuery('.momentJs').each(function () {
			let $element = jQuery(this);
			let language = $element.attr('data-language');
			if (typeof language !== typeof undefined && language !== false) {
				moment.locale(language);
			}
			let m = moment.parseZone($element.html());
			jQuery(this).html(m.format($element.attr('data-format')));
		});
	});
} else {
	alert('The Moment.js function of the calendarize need jQuery!');
}
