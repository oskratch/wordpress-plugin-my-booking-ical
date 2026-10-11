<?php
/**
 * Copyright (c) 2025 Oscar Periche, Metalinked
 * Licensed under GPL v2 or later
 * https://www.gnu.org/licenses/gpl-2.0.html
 */

function register_my_booking_ical_form_menu() {
    add_menu_page('My Booking iCal', 'My Booking iCal', 'manage_options', 'my_booking_ical_forms', 'my_booking_ical_forms', 'dashicons-building');
    add_submenu_page('my_booking_ical_forms', 'Forms', 'Forms', 'manage_options', 'my_booking_ical_forms', 'my_booking_ical_forms');
    $hook = add_submenu_page('my_booking_ical_forms', 'Settings', 'Settings', 'manage_options', 'my_booking_ical_settings', 'my_booking_ical_settings');
    add_action('load-' . $hook, 'my_booking_ical_settings_save');

    // Hidden pages: slug => [title, render callback, action handler].
    // Handlers run on "load-{hook}", before WordPress prints the admin header,
    // so they can still redirect after saving or deleting.
    $hidden_pages = array(
        'my_booking_ical_forms_create'    => array('Form Create', 'my_booking_ical_forms_create', 'my_booking_ical_forms_create_save'),
        'my_booking_ical_forms_edit'      => array('Form Edit', 'my_booking_ical_forms_edit', 'my_booking_ical_forms_edit_save'),
        'my_booking_ical_forms_delete'    => array('Forms', '', 'my_booking_ical_forms_delete'),
        'my_booking_ical_requests'        => array('Requests', 'my_booking_ical_requests', ''),
        'my_booking_ical_requests_show'   => array('Requests', 'my_booking_ical_requests_show', 'my_booking_ical_requests_show_save'),
        'my_booking_ical_requests_delete' => array('Requests', '', 'my_booking_ical_requests_delete'),
        'my_booking_ical_prices_create'   => array('Prices', 'my_booking_ical_prices_create', 'my_booking_ical_prices_create_save'),
        'my_booking_ical_prices_edit'     => array('Prices', 'my_booking_ical_prices_edit', 'my_booking_ical_prices_edit_save'),
        'my_booking_ical_prices_delete'   => array('Prices', '', 'my_booking_ical_prices_delete'),
    );

    foreach ($hidden_pages as $slug => list($title, $render, $handler)) {
        // A render callback is needed for WordPress to route the page at all.
        $hook = add_submenu_page('', $title, $title, 'manage_options', $slug, $render ?: '__return_null');
        // Hidden pages get no title from the menu, which admin-header.php expects.
        add_action('load-' . $hook, function () use ($title) {
            $GLOBALS['title'] = $title;
        });
        if ($handler) {
            add_action('load-' . $hook, $handler);
        }
    }
}

add_action( 'admin_menu', 'register_my_booking_ical_form_menu' );

function load_mbif_translations() {
    load_plugin_textdomain( 'my_booking_ical_form', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}

add_action( 'plugins_loaded', 'load_mbif_translations' );

add_action('admin_enqueue_scripts', 'my_enqueue_assets_admin');

function my_enqueue_assets_admin($hook_suffix) {
    if (strpos($hook_suffix, 'my_booking_ical') === false) return;

    wp_enqueue_script('mbif-js-admin', plugins_url('assets/admin/js/mbif.js', __FILE__), array(), MBIF_VERSION);
    wp_enqueue_style('mbif-css-admin', plugins_url('assets/admin/css/styles.css', __FILE__), array(), MBIF_VERSION);
}

function enqueue_resources() {
    wp_register_style('jquery-ui-datepicker-css', plugins_url('assets/libs/jquery-ui/jquery-ui.min.css', __FILE__), array(), '1.12.1');
    wp_enqueue_style('jquery-ui-datepicker-css');
    wp_register_style('mbif-css', plugins_url('assets/css/styles.css', __FILE__), array(), MBIF_VERSION);
    wp_enqueue_style('mbif-css');
    wp_enqueue_script('jquery-ui-datepicker');
    // Only ca/en/es datepicker translations are bundled; other locales fall back to English.
    $lang = substr(get_locale(), 0, 2);
    if (!file_exists(MBIF_DIR . 'assets/libs/jquery-ui/i18n/datepicker-' . $lang . '.js')) {
        $lang = 'en';
    }
    wp_register_script('jquery-ui-datepicker-languages', plugins_url('assets/libs/jquery-ui/i18n/datepicker-' . $lang . '.js', __FILE__), array('jquery-ui-datepicker'), MBIF_VERSION);
    wp_enqueue_script('jquery-ui-datepicker-languages');
    wp_register_script('mbif-js', plugins_url('assets/js/mbif.js', __FILE__), array('jquery', 'jquery-ui-datepicker'), MBIF_VERSION, true);
    wp_enqueue_script('mbif-js');
    wp_localize_script('mbif-js', 'mbifSettings', array(
        'proxyUrl' => plugins_url('ical_proxy.php', __FILE__),
    ));
}

add_shortcode('booking_ical_form', 'my_booking_ical_shortcode');

function my_booking_ical_shortcode($atts) {

    global $wpdb;

    $form_id = isset($atts['form_id']) ? intval($atts['form_id']) : 0;
    $item = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM " . $wpdb->prefix . "my_booking_ical_forms WHERE id = %d",
        $form_id
    ));

    if (!$item) return '';

    enqueue_resources();

    $price_ranges = $wpdb->get_results($wpdb->prepare(
        "SELECT from_date, to_date, price FROM {$wpdb->prefix}my_booking_ical_prices WHERE form_id = %d",
        $form_id
    ));

    $form_config = array(
        'formId'         => $form_id,
        'minDays'        => intval($item->min_days),
        'basePrice'      => floatval($item->price),
        'currency'       => get_option('mbif_currency'),
        'priceRanges'    => array_map(function($r) {
            return ['start' => $r->from_date, 'end' => $r->to_date, 'price' => floatval($r->price)];
        }, $price_ranges),
        'hasBookingIcal' => !empty($item->ical_booking_url),
        'hasAirbnbIcal'  => !empty($item->ical_airbnb_url),
        'i18n'           => array(
            'nightName'   => __('Night', 'my_booking_ical_form'),
            'nightsName'  => __('Nights', 'my_booking_ical_form'),
            'totalPrice'  => __('Total price', 'my_booking_ical_form'),
            'selectDates' => __('Please select check-in and check-out dates.', 'my_booking_ical_form'),
            'minStay'     => __('The minimum stay for this apartment is %d days.', 'my_booking_ical_form'),
            'unavailable' => __('The selected dates are no longer available. Please choose different dates.', 'my_booking_ical_form'),
        ),
    );

    wp_add_inline_script(
        'mbif-js',
        'window.mbifForms = window.mbifForms || {}; window.mbifForms[' . $form_id . '] = ' . wp_json_encode($form_config) . ';',
        'before'
    );

    $form_sent       = isset($_GET['form_sent']) ? intval($_GET['form_sent']) : 0;
    $form_action_url = esc_url($_SERVER['REQUEST_URI']);

    ob_start();
    require MBIF_DIR . 'views/public/booking-form.php';
    return ob_get_clean();
}

add_action('init', 'my_booking_ical_send');

/**
 * Converts a 'd-m-Y' (or 'd/m/Y') string coming from the datepicker into a
 * validated 'Y-m-d' string, or false if it isn't a real, parseable date.
 */
function mbif_parse_date_input($raw_date) {
    $parts = preg_split('/[\/\-]/', trim((string) $raw_date));

    if (count($parts) !== 3) return false;

    list($day, $month, $year) = array_map('intval', $parts);

    if (!checkdate($month, $day, $year)) return false;

    return sprintf('%04d-%02d-%02d', $year, $month, $day);
}

/**
 * Fetches an external iCal feed with SSRF hardening (scheme + private/reserved
 * IP checks), a timeout, and a short transient cache shared with ical_proxy.php.
 */
function mbif_fetch_ical($ical_url) {
    if (empty($ical_url)) return false;

    $cache_key = 'mbif_ical_' . md5($ical_url);
    $cached = get_transient($cache_key);
    if ($cached !== false) return $cached;

    $parsed = parse_url($ical_url);
    if (!$parsed || empty($parsed['scheme']) || !in_array($parsed['scheme'], ['http', 'https'], true) || empty($parsed['host'])) {
        return false;
    }

    $ip = gethostbyname($parsed['host']);
    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
        return false;
    }

    $response = wp_remote_get($ical_url, array(
        'timeout'     => 10,
        'redirection' => 2,
        'user-agent'  => 'MyBookingIcalForm/' . (defined('MBIF_VERSION') ? MBIF_VERSION : '1'),
    ));

    if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
        return false;
    }

    $body = wp_remote_retrieve_body($response);
    set_transient($cache_key, $body, 15 * MINUTE_IN_SECONDS);

    return $body;
}

/**
 * Un-folds RFC 5545 line folding: continuation lines start with a single
 * space or tab and must be joined back onto the previous logical line
 * before parsing, or a long DTSTART/DTEND/SUMMARY value gets truncated.
 */
function mbif_unfold_ical($ical_text) {
    return preg_replace('/\r\n[ \t]|\r[ \t]|\n[ \t]/', '', $ical_text);
}

/**
 * Parses VEVENT DTSTART/DTEND pairs out of raw iCal text into ['Y-m-d','Y-m-d'] ranges.
 */
function mbif_parse_ical_ranges($ical_text) {
    $ranges = array();
    if (empty($ical_text)) return $ranges;

    $entry = null;
    $departure = null;

    foreach (preg_split('/\r\n|\r|\n/', mbif_unfold_ical($ical_text)) as $line) {
        $line = trim($line);
        if (stripos($line, 'DTSTART') === 0) {
            $value = substr(strrchr($line, ':'), 1);
            $entry = substr($value, 0, 8);
        } elseif (stripos($line, 'DTEND') === 0) {
            $value = substr(strrchr($line, ':'), 1);
            $departure = substr($value, 0, 8);
        } elseif ($line === 'END:VEVENT' && $entry && $departure) {
            $start = DateTime::createFromFormat('Ymd', $entry);
            $end = DateTime::createFromFormat('Ymd', $departure);
            if ($start && $end) {
                $ranges[] = array($start->format('Y-m-d'), $end->format('Y-m-d'));
            }
            $entry = null;
            $departure = null;
        }
    }

    return $ranges;
}

function mbif_range_overlaps($entry_date, $departure_date, $occupied_ranges) {
    foreach ($occupied_ranges as $range) {
        if ($entry_date < $range[1] && $departure_date > $range[0]) {
            return true;
        }
    }
    return false;
}

/**
 * Where to send the visitor back after submitting: the page the form was on.
 */
function mbif_return_url() {
    $referer = wp_get_referer();
    if ($referer) return $referer;

    return (is_ssl() ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
}

/**
 * Stops a public submission with a message and a link back to the form.
 */
function mbif_die($message) {
    wp_die(esc_html($message), '', array('response' => 400, 'back_link' => true));
}

function my_booking_ical_send() {

    if (isset($_POST['action']) && $_POST['action'] == 'my_booking_ical_send') {

        if (!isset($_POST['mbif_nonce']) || !wp_verify_nonce($_POST['mbif_nonce'], 'mbif_send_request')) {
            mbif_die(__('Security check failed. Please reload the page and try again.', 'my_booking_ical_form'));
        }

        // Honeypot: real visitors never fill this hidden field.
        if (!empty($_POST['mbif_website'])) {
            wp_safe_redirect(add_query_arg('form_sent', 1, mbif_return_url()));
            exit;
        }

        $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
        $throttle_key = 'mbif_throttle_' . md5($ip);
        if ($ip && get_transient($throttle_key)) {
            mbif_die(__('You are submitting too fast. Please wait a moment and try again.', 'my_booking_ical_form'));
        }

        global $wpdb;

        $form_id = intval($_POST['form_id'] ?? 0);
        $item = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM " . $wpdb->prefix . "my_booking_ical_forms WHERE id = %d",
            $form_id
        ));

        if (!$item) {
            mbif_die(__('Invalid booking form.', 'my_booking_ical_form'));
        }

        $first_name = sanitize_text_field(wp_unslash($_POST['first_name'] ?? ''));
        $last_name = sanitize_text_field(wp_unslash($_POST['last_name'] ?? ''));
        $email = sanitize_email(wp_unslash($_POST['email'] ?? ''));
        $phone = sanitize_text_field(wp_unslash($_POST['phone'] ?? ''));
        $parking = ($item->parking_option && !empty($_POST['parking'])) ? 1 : 0;
        $guest_count = intval($_POST['guest_count'] ?? 0);
        $comments = sanitize_textarea_field(wp_unslash($_POST['comments'] ?? ''));
        $summary = wp_kses_post(wp_unslash($_POST['summary'] ?? ''));

        if ($first_name === '' || $last_name === '' || !is_email($email) || $guest_count < 1 || $guest_count > intval($item->max_capacity)) {
            mbif_die(__('Please fill in all the required fields.', 'my_booking_ical_form'));
        }

        $entry_date = mbif_parse_date_input($_POST['entry_date'] ?? '');
        $departure_date = mbif_parse_date_input($_POST['departure_date'] ?? '');

        if (!$entry_date || !$departure_date || $departure_date <= $entry_date || $entry_date < current_time('Y-m-d')) {
            mbif_die(__('The selected dates are invalid.', 'my_booking_ical_form'));
        }

        $nights = (strtotime($departure_date) - strtotime($entry_date)) / DAY_IN_SECONDS;
        if ($nights < intval($item->min_days)) {
            mbif_die(sprintf(__('The minimum stay for this apartment is %d days.', 'my_booking_ical_form'), intval($item->min_days)));
        }

        $occupied_ranges = array_merge(
            mbif_parse_ical_ranges(mbif_fetch_ical($item->ical_booking_url)),
            mbif_parse_ical_ranges(mbif_fetch_ical($item->ical_airbnb_url))
        );

        if (mbif_range_overlaps($entry_date, $departure_date, $occupied_ranges)) {
            mbif_die(__('The selected dates are no longer available. Please choose different dates.', 'my_booking_ical_form'));
        }

        $result = $wpdb->insert(
            $wpdb->prefix . 'my_booking_ical_requests',
            array(
                'form_id' => $form_id,
                'first_name' => $first_name,
                'last_name' => $last_name,
                'email' => $email,
                'phone' => $phone,
                'guest_count' => $guest_count,
                'entry_date' => $entry_date,
                'departure_date' => $departure_date,
                'parking' => $parking,
                'comments' => $comments,
                'summary' => $summary
            ),
            array('%d', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%d', '%s', '%s')
        );

        if ($result === false) {
            error_log('My Booking iCal Form: could not save request: ' . $wpdb->last_error);
            mbif_die(__('There was an error saving your request. Please contact the site administrator.', 'my_booking_ical_form'));
        }

        $request_id = $wpdb->insert_id;

        // Throttle only stored requests, so a visitor fixing a validation error isn't blocked.
        if ($ip) set_transient($throttle_key, 1, 20);

        if (get_option('mbif_emailto_enable')) {

            $to = get_option('mbif_emailto') ?: get_option('admin_email');
            $subject = __('Reservation request through the website', 'my_booking_ical_form');
            $message = '<strong>' . esc_html($item->title) . '</strong>';
            $message .= '<ul>';
            $message .= '<li>' . __('Reference', 'my_booking_ical_form') . ': ' . esc_html(createReferenceRequest($item->reference, $entry_date, $request_id)) . '</li>';
            $message .= '<li>' . __('Entry date', 'my_booking_ical_form') . ': ' . esc_html(date('d-m-Y', strtotime($entry_date))) . '</li>';
            $message .= '<li>' . __('Departure date', 'my_booking_ical_form') . ': ' . esc_html(date('d-m-Y', strtotime($departure_date))) . '</li>';
            $message .= '<li>' . __('First Name', 'my_booking_ical_form') . ': ' . esc_html($first_name) . '</li>';
            $message .= '<li>' . __('Last Name', 'my_booking_ical_form') . ': ' . esc_html($last_name) . '</li>';
            $message .= '<li>' . __('Email', 'my_booking_ical_form') . ': ' . esc_html($email) . '</li>';
            $message .= '<li>' . __('Phone', 'my_booking_ical_form') . ': ' . esc_html($phone) . '</li>';
            $message .= '<li>' . __('Guests', 'my_booking_ical_form') . ': ' . intval($guest_count) . '</li>';

            if ($item->parking_option) {
                $message .= '<li>' . __('Parking', 'my_booking_ical_form') . ': ' . ($parking ? __('Yes', 'my_booking_ical_form') : __('No', 'my_booking_ical_form')) . '</li>';
            }

            $message .= '<li>' . __('Comments', 'my_booking_ical_form') . ': ' . nl2br(esc_html($comments)) . '</li>';
            $message .= '</ul>';
            $message .= $summary;
            $message .= '<br><a href="' . esc_url(admin_url('admin.php?page=my_booking_ical_requests_show&id=' . $request_id)) . '">' . __('View Details', 'my_booking_ical_form') . '</a>';

            $headers = array(
                'Content-Type: text/html; charset=UTF-8',
                // Lets the admin reply to the guest straight from the notification.
                'Reply-To: ' . str_replace(array('"', "\r", "\n"), '', $first_name . ' ' . $last_name) . ' <' . $email . '>',
            );

            wp_mail($to, $subject, $message, $headers);

            if (get_option('mbif_emailto_secondary') != "") {
                wp_mail(get_option('mbif_emailto_secondary'), $subject, $message, $headers);
            }
        }

        wp_safe_redirect(add_query_arg('form_sent', 1, mbif_return_url()));
        exit;
    }
}

function createReferenceRequest($apartament_reference, $request_entry_date, $request_id) {
    return $apartament_reference . '-' . DateTime::createFromFormat('Y-m-d', $request_entry_date)->format('Ymd') . '-' . str_pad($request_id, 5, '0', STR_PAD_LEFT);
}

/**
 * Returns a 'Y-m-d' string if $raw_date is a real date in that format, or false.
 */
function mbif_sanitize_ymd($raw_date) {
    $date = DateTime::createFromFormat('!Y-m-d', (string) $raw_date);
    return ($date && $date->format('Y-m-d') === $raw_date) ? $raw_date : false;
}

/**
 * usort() comparator shared by the admin list tables. Only sorts by a column the
 * table declared sortable, and compares numeric columns (prices) as numbers.
 */
function mbif_compare_rows($a, $b, $sortable, $default_orderby, $default_order) {
    $orderby = !empty($_GET['orderby']) ? sanitize_key($_GET['orderby']) : '';
    if (!in_array($orderby, $sortable, true)) $orderby = $default_orderby;

    $order = !empty($_GET['order']) ? sanitize_key($_GET['order']) : $default_order;

    $result = (is_numeric($a[$orderby]) && is_numeric($b[$orderby]))
        ? $a[$orderby] <=> $b[$orderby]
        : strcmp((string) $a[$orderby], (string) $b[$orderby]);

    return ($order === 'asc') ? $result : -$result;
}

require_once plugin_dir_path( __FILE__ ) . 'includes/admin/forms.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/admin/requests.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/admin/settings.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/admin/prices.php';
