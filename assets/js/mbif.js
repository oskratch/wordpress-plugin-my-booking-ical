jQuery(document).ready(function($) {
    $('#sendForm').click(function(e){
        if($('#requestForm')[0].checkValidity()){
            if($('#entry_date').val() == '' || $('#departure_date').val() == ''){
                $("#errorDates").html("Selecciona una fecha de entrada y de salida.");
            }else{
                if(calculateDaysDifference($('#entry_date').val(), $('#departure_date').val()) < min_days){
                    $("#errorDates").html("La estancia mínima para este apartamento es de " + min_days + " días.");
                }else{
                    $("#summary").val($("#priceContainer").html());
                    $("#requestForm").submit();
                }
            }
        }else{
            $('#requestForm')[0].reportValidity();
        }                
    });

    $('#my-popup').fadeIn();

    $('.my-popup-close').on('click', function() {
        $('#my-popup').fadeOut();
    });

    // Opcional: Amagar el popup quan es fa clic fora del contingut del popup
    $(window).on('click', function(e) {
        if ($(e.target).is('#my-popup')) {
            $('#my-popup').fadeOut();
        }
    });
});

var disabledDates = new Array;

async function fetchData(url, iniCal) {
    return new Promise((resolve, reject) => {
        var request = new XMLHttpRequest();
        request.open('GET', '/wp-content/plugins/my-booking-ical-form/ical_proxy.php?ical_url=' + url, true);
        request.send(null);

        request.onreadystatechange = function() {
            if (request.readyState === 4 && request.status === 200) {
                var type = request.getResponseHeader('Content-Type');
                if (type.indexOf('text') !== 1) {
                    var lines = request.responseText.split('\n');
                    var events_i = 0;

                    for (i = 0; i < lines.length; i++) {
                        if (lines[i].includes('DTSTART')) {
                            var entry_date = lines[i].split(':');
                        }
                        else if (lines[i].includes('DTEND')) {
                            var departure_date = lines[i].split(':');
                        }
                        else if (lines[i].includes('END:VEVENT')) {

                            for(a=entry_date[1]; a<departure_date[1]; a++){
                                disabledDates.push(parseInt(a).toString());
                            }

                            events_i++;
                        }
                    }

                    if(iniCal){
                        iniCalendar();
                    }
                }
            }
            resolve(true);
        };  
    });
}

function addOneDay(dateString) {
    var dateParts = dateString.split('-');
    var day = parseInt(dateParts[0], 10);
    var month = parseInt(dateParts[1], 10) - 1;
    var year = parseInt(dateParts[2], 10);
    var date = new Date(year, month, day);

    date.setDate(date.getDate() + 1);

    var newDay = date.getDate();
    var newMonth = date.getMonth() + 1;
    var newYear = date.getFullYear();

    var formatDay = newDay < 10 ? '0' + newDay : newDay;
    var formatMonth = newMonth < 10 ? '0' + newMonth : newMonth;

    return formatDay + '-' + formatMonth + '-' + newYear;
}

function calculateDaysDifference(date1, date2) {
    var dateParts1 = date1.split("-");
    var dateParts2 = date2.split("-");
    var startDate = new Date(dateParts1[2], dateParts1[1] - 1, dateParts1[0]);
    var endDate = new Date(dateParts2[2], dateParts2[1] - 1, dateParts2[0]);
    var difference = endDate.getTime() - startDate.getTime();
    var daysDifference = Math.floor(difference / (1000 * 60 * 60 * 24));

    return parseInt(daysDifference) + 1;
}

function getPriceForDate(date, priceRangesJson, defaultPrice) {
    var priceRanges = JSON.parse(priceRangesJson);
    for (var i = 0; i < priceRanges.length; i++) {
        var range = priceRanges[i];
        var startDate = new Date(range.start);
        var endDate = new Date(range.end);
        var price = range.price;

        if (date >= startDate && date <= endDate) {
            return removeDecimalIfZero(price);
        }
    }

    return defaultPrice;
}

function getPriceForDateRange(startDate, endDate, priceRangesJson, defaultPrice) {
    if(startDate != "" && endDate  != ""){

        var container_error = document.getElementById('errorDates');
        var container_prices = document.getElementById('priceContainer');
        
        if(calculateDaysDifference(startDate, endDate) < min_days){
            container_error.innerHTML = "La estancia mínima para este apartamento es de " + min_days + " días.";
            container_prices.innerHTML = "";
        }else{

            var priceRanges = JSON.parse(priceRangesJson);
            var totalPrice = 0;
            var totalDays = 0;
            var html = '<ul class="priceList">';
            
            var formattedStartDate = formatDate(startDate);
            var formattedEndDate = formatDate(endDate);

            var currentDate = new Date(formattedStartDate);
            var endDateObj = new Date(formattedEndDate);

            var dayOrder = 1;
            while (currentDate <= endDateObj) {
                
                var formattedDate = formatDateForOutput(currentDate);

                if(!disabledDates.includes(formatDateOnlyNumbers(formattedDate))){
                    var currentPrice = getPriceForDate(currentDate, priceRangesJson, defaultPrice);
                    html += '<li>' + dayName + ' ' + dayOrder + ': ' + formattedDate + ', ' + priceName + ': ' + currentPrice + ' ' + currency + '</li>';
                    totalPrice += currentPrice;
                    totalDays++;
                    dayOrder++;
                }

                currentDate.setDate(currentDate.getDate() + 1);
            }

            html += '</ul>';
            html += '<div>' + nightsName + ': ' + totalDays + ', ' + totalPriceName + ': ' + totalPrice + ' ' + currency + '</div>';

            container_prices.innerHTML = html;
            container_error.innerHTML = "";
        }
    }

    return true;
}

function getDisabledDates() {
    var disabledDates = [];
    jQuery('#d_entry_date').datepicker('option', 'beforeShowDay', function(date) {
        if (date) {
            var dateString = formatDate(date);
            disabledDates.push(dateString);
        }
    });
    return disabledDates;
}

function formatDate(date) {
    var parts = date.split("-");
    var day = parts[0];
    var month = parts[1];
    var year = parts[2];
    return year + '-' + month + '-' + day;
}

function formatDateForOutput(date) {
    var day = date.getDate();
    var month = date.getMonth() + 1;
    var year = date.getFullYear();
    day = (day < 10) ? '0' + day : day;
    month = (month < 10) ? '0' + month : month;
    return day + '-' + month + '-' + year;
}

function formatDateOnlyNumbers(date) {
    var parts = date.split("-");
    var day = parts[0];
    var month = parts[1];
    var year = parts[2];
    return year + month + day;
}

function removeDecimalIfZero(price) {
    if (price % 1 === 0) {
        return Math.trunc(price);
    }
    return price;
}