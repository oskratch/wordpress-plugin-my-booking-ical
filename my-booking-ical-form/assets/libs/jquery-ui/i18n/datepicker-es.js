jQuery(function() {
    jQuery.datepicker.regional['es'] = {
        closeText: "Cerrar",
		prevText: "Ant",
		nextText: "Sig",
		currentText: "Hoy",
		monthNames: [ "enero", "febrero", "marzo", "abril", "mayo", "junio",
		"julio", "agosto", "septiembre", "octubre", "noviembre", "diciembre" ],
		monthNamesShort: [ "ene", "feb", "mar", "abr", "may", "jun",
		"jul", "ago", "sep", "oct", "nov", "dic" ],
		dayNames: [ "domingo", "lunes", "martes", "miércoles", "jueves", "viernes", "sábado" ],
		dayNamesShort: [ "dom", "lun", "mar", "mié", "jue", "vie", "sáb" ],
		dayNamesMin: [ "D", "L", "M", "X", "J", "V", "S" ],
		weekHeader: "Sm",
		dateFormat: "dd/mm/yy",
		firstDay: 1,
		isRTL: false,
		showMonthAfterYear: false,
        yearSuffix: ''
    };

    jQuery.datepicker.setDefaults(jQuery.datepicker.regional['es']);
}); 