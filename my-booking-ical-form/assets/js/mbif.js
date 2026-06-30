jQuery(document).ready(function($) {
    $('#sendForm').click(function(e) {
        const entryDate = $('#entry_date').val();
        const departureDate = $('#departure_date').val();
        const errorContainer = $("#errorDates");

        if ($('#requestForm')[0].checkValidity()) {
            if (!entryDate || !departureDate) {
                errorContainer.html("Selecciona una fecha de entrada y de salida.");
            } else if (calculateDaysDifference(entryDate, departureDate) < min_days) {
                errorContainer.html(`La estancia mínima para este apartamento es de ${min_days} días.`);
            } else {
                errorContainer.html('');
                $("#summary").val($("#priceContainer").html());
                $("#requestForm").submit();
            }
        } else {
            $('#requestForm')[0].reportValidity();
        }
    });

    $('#my-popup').fadeIn();
    $('.my-popup-close').on('click', () => $('#my-popup').fadeOut());
    $(window).on('click', (e) => {
        if ($(e.target).is('#my-popup')) $('#my-popup').fadeOut();
    });
});

const disabledDates = [];

async function fetchData(url, iniCal) {
    return new Promise((resolve, reject) => {
        const request = new XMLHttpRequest();
        const proxyUrl = mbifSettings.proxyUrl + '?ical_url=' + encodeURIComponent(url);
        request.open('GET', proxyUrl, true);
        request.send(null);

        request.onreadystatechange = function() {
            if (request.readyState !== 4) return;

            if (request.status === 200) {
                const type = request.getResponseHeader('Content-Type');
                if (type && type.indexOf('text') !== -1) {
                    const lines = request.responseText.split('\n');
                    let entryDate = null, departureDate = null;

                    lines.forEach((line) => {
                        const trimmed = line.trim();
                        if (trimmed.startsWith('DTSTART')) {
                            entryDate = trimmed.split(':').pop().substring(0, 8);
                        } else if (trimmed.startsWith('DTEND')) {
                            departureDate = trimmed.split(':').pop().substring(0, 8);
                        } else if (trimmed === 'END:VEVENT' && entryDate && departureDate) {
                            addDateRange(entryDate, departureDate);
                            entryDate = null;
                            departureDate = null;
                        }
                    });
                }
            }

            if (iniCal) iniCalendar();
            resolve(true);
        };

        request.onerror = () => {
            if (iniCal) iniCalendar();
            reject(new Error('Network error fetching iCal'));
        };
    });
}

function addDateRange(startStr, endStr) {
    let current = new Date(
        parseInt(startStr.substring(0, 4)),
        parseInt(startStr.substring(4, 6)) - 1,
        parseInt(startStr.substring(6, 8))
    );
    const end = new Date(
        parseInt(endStr.substring(0, 4)),
        parseInt(endStr.substring(4, 6)) - 1,
        parseInt(endStr.substring(6, 8))
    );
    while (current < end) {
        const y = current.getFullYear();
        const m = String(current.getMonth() + 1).padStart(2, '0');
        const d = String(current.getDate()).padStart(2, '0');
        disabledDates.push(`${y}${m}${d}`);
        current.setDate(current.getDate() + 1);
    }
}

function addOneDay(dateString) {
    const [day, month, year] = dateString.split('-').map(Number);
    const date = new Date(year, month - 1, day);
    date.setDate(date.getDate() + 1);
    return formatDateForOutput(date);
}

function calculateDaysDifference(date1, date2) {
    const [day1, month1, year1] = date1.split('-').map(Number);
    const [day2, month2, year2] = date2.split('-').map(Number);
    const startDate = new Date(year1, month1 - 1, day1);
    const endDate = new Date(year2, month2 - 1, day2);
    const difference = endDate - startDate;
    return Math.floor(difference / (1000 * 60 * 60 * 24)) + 1;
}

function getPriceForDate(date, priceRangesJson, defaultPrice) {
    const priceRanges = JSON.parse(priceRangesJson);
    for (const range of priceRanges) {
        const startDate = new Date(range.start);
        const endDate = new Date(range.end);
        if (date >= startDate && date <= endDate) {
            return removeDecimalIfZero(range.price);
        }
    }
    return defaultPrice;
}

function getPriceForDateRange(startDateStr, endDateStr, priceRangesJson, defaultPrice) {
    if (!startDateStr || !endDateStr) return;

    const [sd, sm, sy] = startDateStr.split('-').map(Number);
    const [ed, em, ey] = endDateStr.split('-').map(Number);
    const start = new Date(sy, sm - 1, sd);
    const end = new Date(ey, em - 1, ed);

    if (start >= end) {
        jQuery('#priceContainer').html('');
        return;
    }

    const segments = [];
    let cur = new Date(start);

    while (cur < end) {
        const price = parseFloat(getPriceForDate(cur, priceRangesJson, defaultPrice));
        if (segments.length > 0 && segments[segments.length - 1].price === price) {
            segments[segments.length - 1].nights++;
        } else {
            segments.push({ nights: 1, price });
        }
        cur.setDate(cur.getDate() + 1);
    }

    const totalNights = Math.round((end - start) / (1000 * 60 * 60 * 24));
    const totalPrice = segments.reduce((sum, s) => sum + s.nights * s.price, 0);

    let html = `<ul class="priceList">`;
    segments.forEach(s => {
        html += `<li>${s.nights} ${s.nights === 1 ? nightsName : daysName} &times; ${removeDecimalIfZero(s.price)} ${currency}</li>`;
    });
    html += `</ul><div>${totalPriceName}: ${removeDecimalIfZero(totalPrice)} ${currency}</div>`;

    jQuery('#priceContainer').html(html);
    jQuery('#errorDates').html('');
}

function formatDateForOutput(date) {
    const day = String(date.getDate()).padStart(2, '0');
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const year = date.getFullYear();
    return `${day}-${month}-${year}`;
}

function formatDateOnlyNumbers(date) {
    const [day, month, year] = date.split('-');
    return `${year}${month}${day}`;
}

function removeDecimalIfZero(price) {
    return price % 1 === 0 ? Math.trunc(price) : price;
}
