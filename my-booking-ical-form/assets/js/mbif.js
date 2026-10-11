jQuery(document).ready(function() {
    document.querySelectorAll('.mbif-wrap[data-form-id]').forEach(function(wrapEl) {
        const formId = wrapEl.dataset.formId;
        const config = window.mbifForms && window.mbifForms[formId];
        if (config) initBookingForm(wrapEl, config);
    });
});

function initBookingForm(wrapEl, config) {
    const $ = jQuery;
    const $wrap = $(wrapEl);
    const $form = $wrap.find('.booking_ical_form');
    // Booked nights as 'yyyymmdd' strings (a booking blocks every night from DTSTART to the day before DTEND).
    const disabledDates = [];

    const $entryCal      = $wrap.find('.mbif-entry-cal');
    const $departureCal  = $wrap.find('.mbif-departure-cal');
    const $entryVal      = $wrap.find('.mbif-entry-date');
    const $departureVal  = $wrap.find('.mbif-departure-date');
    const $priceContainer = $wrap.find('.mbif-price-container');
    const $errorDates    = $wrap.find('.mbif-error-dates');
    const $popup         = $wrap.find('.my-popup');

    if ($popup.length) {
        $popup.fadeIn();
        $popup.find('.my-popup-close').on('click', () => $popup.fadeOut());
        $(window).on('click', (e) => { if ($(e.target).is($popup)) $popup.fadeOut(); });
    }

    $wrap.find('.mbif-send-btn').on('click', function() {
        const entryDate    = $entryVal.val();
        const departureDate = $departureVal.val();
        const formEl = $form[0];

        if (!formEl.checkValidity()) {
            formEl.reportValidity();
            return;
        }
        if (!entryDate || !departureDate) {
            $errorDates.html(config.i18n.selectDates);
            return;
        }
        if (countNights(entryDate, departureDate) < config.minDays) {
            $errorDates.html(config.i18n.minStay.replace('%d', config.minDays));
            return;
        }

        $errorDates.html('');
        $wrap.find('.mbif-summary').val($priceContainer.html());
        formEl.submit();
    });

    async function loadCalendars() {
        try {
            const types = [
                config.hasBookingIcal ? 'booking' : null,
                config.hasAirbnbIcal ? 'airbnb' : null,
            ].filter(Boolean);
            await Promise.all(types.map(type => fetchIcal(config.formId, type, disabledDates)));
        } catch (e) {
            console.error('iCal fetch error:', e);
        }
        initCalendars();
    }

    function initCalendars() {
        const today = new Date(new Date().getFullYear(), new Date().getMonth(), new Date().getDate());

        const sharedOpts = {
            dateFormat: 'dd-mm-yy',
            minDate: today,
            firstDay: 1,
        };

        // Entry: the first night must be free.
        $entryCal.datepicker(Object.assign({}, sharedOpts, {
            beforeShowDay: function(date) {
                if (isBooked(date)) return [false];
                return [true, '', getPriceForDate(date, config.priceRanges, config.basePrice) + ' ' + config.currency];
            },
            onSelect: function(date) {
                $entryVal.val(date);
                const minDeparture = addDays(date, Math.max(1, config.minDays));
                $departureCal.datepicker('option', 'minDate', minDeparture);
                // Departure on the day the next booking starts is fine; later isn't.
                $departureCal.datepicker('option', 'maxDate', firstBookedNightAfter(date));

                const departure = $departureVal.val();
                if (departure && (countNights(date, departure) < 1 || rangeHasBookedNight(date, departure))) {
                    $departureVal.val('');
                    $priceContainer.html('');
                }
                updatePrice(date, $departureVal.val());
            }
        }));

        // Departure: the guest leaves that morning, so only the night before must be free.
        $departureCal.datepicker(Object.assign({}, sharedOpts, {
            minDate: addDays(formatDateForOutput(today), 1),
            beforeShowDay: function(date) {
                const previousNight = new Date(date);
                previousNight.setDate(previousNight.getDate() - 1);
                return [!isBooked(previousNight)];
            },
            onSelect: function(date) {
                const entry = $entryVal.val();
                if (entry && rangeHasBookedNight(entry, date)) {
                    $departureVal.val('');
                    $priceContainer.html('');
                    $errorDates.html(config.i18n.unavailable);
                    return;
                }
                $departureVal.val(date);
                updatePrice(entry, date);
            }
        }));
    }

    function isBooked(date) {
        return disabledDates.indexOf($.datepicker.formatDate('yymmdd', date)) !== -1;
    }

    function rangeHasBookedNight(startStr, endStr) {
        const cur = parseDate(startStr);
        const end = parseDate(endStr);
        while (cur < end) {
            if (isBooked(cur)) return true;
            cur.setDate(cur.getDate() + 1);
        }
        return false;
    }

    function firstBookedNightAfter(startStr) {
        const start = $.datepicker.formatDate('yymmdd', parseDate(startStr));
        const next = disabledDates.filter(d => d > start).sort()[0];
        if (!next) return null;
        return new Date(+next.substring(0, 4), +next.substring(4, 6) - 1, +next.substring(6, 8));
    }

    function updatePrice(startDateStr, endDateStr) {
        if (!startDateStr || !endDateStr) return;

        const start = parseDate(startDateStr);
        const end   = parseDate(endDateStr);

        if (start >= end) { $priceContainer.html(''); return; }

        const segments = [];
        let cur = new Date(start);

        while (cur < end) {
            const price = parseFloat(getPriceForDate(cur, config.priceRanges, config.basePrice));
            if (segments.length > 0 && segments[segments.length - 1].price === price) {
                segments[segments.length - 1].nights++;
            } else {
                segments.push({ nights: 1, price });
            }
            cur.setDate(cur.getDate() + 1);
        }

        const totalPrice = Math.round(segments.reduce((sum, s) => sum + s.nights * s.price, 0) * 100) / 100;

        let html = '<ul class="priceList">';
        segments.forEach(s => {
            const label = s.nights === 1 ? config.i18n.nightName : config.i18n.nightsName;
            html += `<li>${s.nights} ${label} &times; ${removeDecimalIfZero(s.price)} ${config.currency}</li>`;
        });
        html += `</ul><div>${config.i18n.totalPrice}: ${removeDecimalIfZero(totalPrice)} ${config.currency}</div>`;

        $priceContainer.html(html);
        $errorDates.html('');
    }

    loadCalendars();
}

async function fetchIcal(formId, type, disabledDates) {
    const proxyUrl = mbifSettings.proxyUrl + '?form_id=' + encodeURIComponent(formId) + '&type=' + encodeURIComponent(type);
    const response = await fetch(proxyUrl);
    if (!response.ok) throw new Error('Failed to fetch calendar: ' + type);
    const text = await response.text();

    // RFC 5545 line folding: a continuation line starts with a single space/tab
    // and must be joined back onto the previous logical line before parsing.
    const unfolded = text.replace(/\r\n[ \t]|\r[ \t]|\n[ \t]/g, '');
    const lines = unfolded.split(/\r\n|\r|\n/);
    let entryDate = null, departureDate = null;

    lines.forEach(line => {
        const trimmed = line.trim();
        if (trimmed.startsWith('DTSTART')) {
            entryDate = trimmed.split(':').pop().substring(0, 8);
        } else if (trimmed.startsWith('DTEND')) {
            departureDate = trimmed.split(':').pop().substring(0, 8);
        } else if (trimmed === 'END:VEVENT' && entryDate && departureDate) {
            addDateRange(entryDate, departureDate, disabledDates);
            entryDate = null;
            departureDate = null;
        }
    });
}

function addDateRange(startStr, endStr, disabledDates) {
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

// Parses a 'dd-mm-yyyy' datepicker value into a local-midnight Date.
function parseDate(dateString) {
    const [day, month, year] = dateString.split('-').map(Number);
    return new Date(year, month - 1, day);
}

function addDays(dateString, days) {
    const date = parseDate(dateString);
    date.setDate(date.getDate() + days);
    return formatDateForOutput(date);
}

// Nights between check-in and check-out (the departure day itself isn't a night).
function countNights(entryDate, departureDate) {
    return Math.round((parseDate(departureDate) - parseDate(entryDate)) / (1000 * 60 * 60 * 24));
}

function getPriceForDate(date, priceRanges, defaultPrice) {
    // Compare as 'YYYY-MM-DD' strings: new Date('YYYY-MM-DD') is UTC midnight,
    // which shifts range boundaries by a day in non-UTC timezones.
    const day = date.getFullYear() + '-' + String(date.getMonth() + 1).padStart(2, '0') + '-' + String(date.getDate()).padStart(2, '0');
    for (const range of priceRanges) {
        if (day >= range.start && day <= range.end) {
            return removeDecimalIfZero(range.price);
        }
    }
    return defaultPrice;
}

function formatDateForOutput(date) {
    const day   = String(date.getDate()).padStart(2, '0');
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const year  = date.getFullYear();
    return `${day}-${month}-${year}`;
}

function removeDecimalIfZero(price) {
    return price % 1 === 0 ? Math.trunc(price) : price;
}
