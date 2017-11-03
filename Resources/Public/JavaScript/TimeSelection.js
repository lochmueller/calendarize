define(['jquery'], function ($) {
	$('.calendarize-time-selection').on("change", function () {
		let currentField = $(this);
		let name = currentField.data('related-name');
		let id = currentField.data('related-id');
		let originalField = null;
		if (name.length) {
			originalField = $("input[name='" + name + "']");
		} else if (id.length) {
			originalField = $("#" + id);
		} else {
			return originalField;
		}
		originalField.val(currentField.val());
		originalField.trigger('change');
		currentField.val('');
	});
});
