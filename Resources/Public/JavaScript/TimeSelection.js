define(['jquery'], function ($) {
    $('.calendarize-time-selection').on("change", function () {
        var currentField = $(this);
        var originalField = $('#' + currentField.data('related'));
        originalField.val(currentField.val());
        originalField.trigger('change');
        currentField.val('');
    });
});
