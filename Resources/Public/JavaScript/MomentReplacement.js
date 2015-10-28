if (window.jQuery) {
    jQuery(function () {
        jQuery('.momentJs').each(function () {
            var m = moment(jQuery(this).html());
            jQuery(this).html(m.format(jQuery(this).attr('data-format')));
        });
    });
} else {
    alert('The Moment.js function of the calendarize need jQuery!');
}
