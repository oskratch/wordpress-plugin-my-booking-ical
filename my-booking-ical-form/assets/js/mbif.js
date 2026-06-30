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
        if (calculateDaysDifference(entryDate, departureDate) < config.minDays) {
            $errorDates.html(config.i18n.minStay.replace('%d', config.minDays));
            return;
        }

        $errorDates.html('');
        $wrap.find('.mbif-summary').val($priceContainer.html());
        formEl.submit();
    });

    async function loadCalendars() {
        try {
            const urls = [config.icalBookingUrl, config.icalAirbnbUrl].filter(Boolean);
            await Promise.all(urls.map(url => fetchIcal(url, disabledDates)));
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
            beforeShowDay: function(date) {
                const stringDate = $.datepicker.formatDate('yymmdd', date);
                if (disabledDates.indexOf(stringDate) !== -1) return [false];
                return [true, '', getPriceForDate(date, config.priceRanges, config.basePrice) + ' ' + config.currency];
            }
        };

        $entryCal.datepicker(Object.assign({}, sharedOpts, {
            onSelect: function(date) {
                $departureCal.datepicker('option', 'minDate', addOneDay(date));
                $entryVal.val(date);
                updatePrice(date, $departureVal.val());
            }
        }));

        $departureCal.datepicker(Object.assign({}, sharedOpts, {
            onSelect: function(date) {
                $departureVal.val(date);
                updatePrice($entryVal.val(), date);
            }
        }));
    }

    function updatePrice(startDateStr, endDateStr) {
        if (!startDateStr || !endDateStr) return;

        const [sd, sm, sy] = startDateStr.split('-').map(Number);
        const [ed, em, ey] = endDateStr.split('-').map(Number);
        const start = new Date(sy, sm - 1, sd);
        const end   = new Date(ey, em - 1, ed);

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

        const totalPrice = segments.reduce((sum, s) => sum + s.nights * s.price, 0);

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

async function fetchIcal(url, disabledDates) {
    const proxyUrl = mbifSettings.proxyUrl + '?ical_url=' + encodeURIComponent(url);
    const response = await fetch(proxyUrl);
    if (!response.ok) throw new Error('Failed to fetch calendar: ' + url);
    const text = await response.text();

    const lines = text.split('\n');
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
    const endDate   = new Date(year2, month2 - 1, day2);
    return Math.floor((endDate - startDate) / (1000 * 60 * 60 * 24)) + 1;
}

function getPriceForDate(date, priceRanges, defaultPrice) {
    for (const range of priceRanges) {
        const startDate = new Date(range.start);
        const endDate   = new Date(range.end);
        if (date >= startDate && date <= endDate) {
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
