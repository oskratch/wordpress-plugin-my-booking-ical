jQuery(function() {
    jQuery.datepicker.regional['en'] = {
        closeText: "Done",
        prevText: "Prev",
        nextText: "Next",
        currentText: "Today",
        monthNames: [ "January", "February", "March", "April", "May", "June",
        "July", "August", "September", "October", "November", "December" ],
        monthNamesShort: [ "Jan", "Feb", "Mar", "Apr", "May", "Jun",
        "Jul", "Aug", "Sep", "Oct", "Nov", "Dec" ],
        dayNames: [ "Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday" ],
        dayNamesShort: [ "Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat" ],
        dayNamesMin: [ "Su", "Mo", "Tu", "We", "Th", "Fr", "Sa" ],
        weekHeader: "Wk",
        dateFormat: "dd/mm/yy",
        firstDay: 1,
        isRTL: false,
        showMonthAfterYear: false,
        yearSuffix: ''
    };

    jQuery.datepicker.setDefaults(jQuery.datepicker.regional['en']);
}); 