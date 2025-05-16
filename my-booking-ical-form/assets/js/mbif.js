jQuery(document).ready(function($) {
    // Handle form submission
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
                $("#summary").val($("#priceContainer").html());
                $("#requestForm").submit();
            }
        } else {
            $('#requestForm')[0].reportValidity();
        }
    });

    // Show popup on page load
    $('#my-popup').fadeIn();

    // Close popup when clicking the close button
    $('.my-popup-close').on('click', () => $('#my-popup').fadeOut());

    // Optional: Hide popup when clicking outside the popup content
    $(window).on('click', (e) => {
        if ($(e.target).is('#my-popup')) {
            $('#my-popup').fadeOut();
        }
    });
});

// Encapsulate disabledDates to avoid global scope pollution
const disabledDates = [];

// Fetch data from the server and process iCal events
async function fetchData(url, iniCal) {
    return new Promise((resolve, reject) => {
        const request = new XMLHttpRequest();
        request.open('GET', `/wp-content/plugins/my-booking-ical-form/ical_proxy.php?ical_url=${url}`, true);
        request.send(null);

        request.onreadystatechange = function() {
            if (request.readyState === 4 && request.status === 200) {
                const type = request.getResponseHeader('Content-Type');
                if (type && type.indexOf('text') !== -1) {
                    const lines = request.responseText.split('\n');
                    let entryDate, departureDate;

                    lines.forEach((line) => {
                        if (line.includes('DTSTART')) {
                            entryDate = line.split(':')[1];
                        } else if (line.includes('DTEND')) {
                            departureDate = line.split(':')[1];
                        } else if (line.includes('END:VEVENT') && entryDate && departureDate) {
                            // Add all dates between entryDate and departureDate to disabledDates
                            for (let date = entryDate; date < departureDate; date++) {
                                disabledDates.push(parseInt(date).toString());
                            }
                        }
                    });

                    if (iniCal) {
                        iniCalendar();
                    }
                }
            }
            resolve(true);
        };
    });
}

// Add one day to a given date string
function addOneDay(dateString) {
    const [day, month, year] = dateString.split('-').map(Number);
    const date = new Date(year, month - 1, day);
    date.setDate(date.getDate() + 1);

    return formatDateForOutput(date);
}

// Calculate the difference in days between two dates
function calculateDaysDifference(date1, date2) {
    const [day1, month1, year1] = date1.split('-').map(Number);
    const [day2, month2, year2] = date2.split('-').map(Number);

    const startDate = new Date(year1, month1 - 1, day1);
    const endDate = new Date(year2, month2 - 1, day2);

    const difference = endDate - startDate;
    return Math.floor(difference / (1000 * 60 * 60 * 24)) + 1;
}

// Get the price for a specific date based on price ranges
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

// Format a date object to "DD-MM-YYYY"
function formatDateForOutput(date) {
    const day = String(date.getDate()).padStart(2, '0');
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const year = date.getFullYear();
    return `${day}-${month}-${year}`;
}

// Format a date string to "YYYYMMDD" for comparison
function formatDateOnlyNumbers(date) {
    const [day, month, year] = date.split('-');
    return `${year}${month}${day}`;
}

// Remove decimal if the price is a whole number
function removeDecimalIfZero(price) {
    return price % 1 === 0 ? Math.trunc(price) : price;
}