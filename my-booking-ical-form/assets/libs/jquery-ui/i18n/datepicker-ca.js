jQuery(function() {
    jQuery.datepicker.regional['ca'] = {
        closeText: "Tanca",
		prevText: "Anterior",
		nextText: "Següent",
		currentText: "Avui",
		monthNames: [ "gener", "febrer", "març", "abril", "maig", "juny",
		"juliol", "agost", "setembre", "octubre", "novembre", "desembre" ],
		monthNamesShort: [ "gen", "feb", "març", "abr", "maig", "juny",
		"jul", "ag", "set", "oct", "nov", "des" ],
		dayNames: [ "diumenge", "dilluns", "dimarts", "dimecres", "dijous", "divendres", "dissabte" ],
		dayNamesShort: [ "dg", "dl", "dt", "dc", "dj", "dv", "ds" ],
		dayNamesMin: [ "dg", "dl", "dt", "dc", "dj", "dv", "ds" ],
		weekHeader: "Set",
		dateFormat: "dd/mm/yy",
		firstDay: 1,
		isRTL: false,
		showMonthAfterYear: false,
        yearSuffix: ''
    };

    jQuery.datepicker.setDefaults(jQuery.datepicker.regional['ca']);
});