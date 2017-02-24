define(['jquery'], function ($) {
	$('.calendarize-time-selection').on("change", function () {
		var currentField = $(this);
		var name = currentField.data('related-name');
		var id = currentField.data('related-id');
		var originalField = null;
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
