<?php
/**
 * Copyright (c) 2025 Oscar Periche, Metalinked
 * Licensed under GPL v2 or later
 * https://www.gnu.org/licenses/gpl-2.0.html
 */

function register_my_booking_ical_form_menu() {
    add_menu_page('My Booking iCal', 'My Booking iCal', 'manage_options', 'my_booking_ical_forms', 'my_booking_ical_forms', 'dashicons-building');
    add_submenu_page('my_booking_ical_forms', 'Forms', 'Forms', 'manage_options', 'my_booking_ical_forms', 'my_booking_ical_forms');
    add_submenu_page('my_booking_ical_forms', 'Settings', 'Settings', 'manage_options', 'my_booking_ical_settings', 'my_booking_ical_settings');

    add_submenu_page(null, 'Form Create', 'Form Create', 'manage_options', 'my_booking_ical_forms_create', 'my_booking_ical_forms_create');
    add_submenu_page(null, 'Form Edit', 'Form Edit', 'manage_options', 'my_booking_ical_forms_edit', 'my_booking_ical_forms_edit');
    add_submenu_page(null, 'Forms', 'Forms', 'manage_options', 'my_booking_ical_forms_delete', 'my_booking_ical_forms_delete');
    add_submenu_page(null, 'Requests', 'Requests', 'manage_options', 'my_booking_ical_requests', 'my_booking_ical_requests');
    add_submenu_page(null, 'Requests', 'Requests', 'manage_options', 'my_booking_ical_requests_show', 'my_booking_ical_requests_show');
    add_submenu_page(null, 'Requests', 'Requests', 'manage_options', 'my_booking_ical_requests_validate', 'my_booking_ical_requests_validate');
    add_submenu_page(null, 'Requests', 'Requests', 'manage_options', 'my_booking_ical_requests_delete', 'my_booking_ical_requests_delete');
    add_submenu_page(null, 'Prices', 'Prices', 'manage_options', 'my_booking_ical_prices_create', 'my_booking_ical_prices_create');
    add_submenu_page(null, 'Prices', 'Prices', 'manage_options', 'my_booking_ical_prices_edit', 'my_booking_ical_prices_edit');
    add_submenu_page(null, 'Prices', 'Prices', 'manage_options', 'my_booking_ical_prices_delete', 'my_booking_ical_prices_delete');
}

add_action( 'admin_menu', 'register_my_booking_ical_form_menu' );

function load_mbif_translations() {
    load_plugin_textdomain( 'my_booking_ical_form', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}

add_action( 'plugins_loaded', 'load_mbif_translations' );

add_action('admin_enqueue_scripts', 'my_enqueue_assets_admin');

function my_enqueue_assets_admin() {
    wp_enqueue_script('mbif-js-admin', plugins_url('assets/admin/js/mbif.js', __FILE__));
    wp_enqueue_style('mbif-css-admin', plugins_url('assets/admin/css/styles.css', __FILE__));
}

function enqueue_resources() {
    wp_register_style('jquery-ui-datepicker-css', 'https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css');
    wp_enqueue_style('jquery-ui-datepicker-css');
    wp_register_style('mbif-css', plugins_url('assets/css/styles.css', __FILE__));
    wp_enqueue_style('mbif-css');
    wp_enqueue_script('jquery-ui-datepicker');
    wp_register_script('jquery-ui-datepicker-languages', plugins_url('assets/libs/jquery-ui/i18n/datepicker-' . substr(get_locale(), 0, 2) . '.js', __FILE__));
    wp_enqueue_script('jquery-ui-datepicker-languages');
    wp_register_script('mbif-js', plugins_url('assets/js/mbif.js', __FILE__), array('jquery'), null, true);
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
        'currency'       => get_option('currency'),
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
 * Parses VEVENT DTSTART/DTEND pairs out of raw iCal text into ['Y-m-d','Y-m-d'] ranges.
 */
function mbif_parse_ical_ranges($ical_text) {
    $ranges = array();
    if (empty($ical_text)) return $ranges;

    $entry = null;
    $departure = null;

    foreach (preg_split('/\r\n|\r|\n/', $ical_text) as $line) {
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

function my_booking_ical_send() {

    if (isset($_POST['action']) && $_POST['action'] == 'my_booking_ical_send') {

        if (!isset($_POST['mbif_nonce']) || !wp_verify_nonce($_POST['mbif_nonce'], 'mbif_send_request')) {
            wp_die(__('Security check failed. Please reload the page and try again.', 'my_booking_ical_form'));
        }

        // Honeypot: real visitors never fill this hidden field.
        if (!empty($_POST['mbif_website'])) {
            wp_redirect(add_query_arg('form_sent', 1, $_SERVER['HTTP_REFERER']));
            exit;
        }

        $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
        $throttle_key = 'mbif_throttle_' . md5($ip);
        if ($ip && get_transient($throttle_key)) {
            wp_die(__('You are submitting too fast. Please wait a moment and try again.', 'my_booking_ical_form'));
        }
        if ($ip) set_transient($throttle_key, 1, 20);

        global $wpdb;

        $item = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM " . $wpdb->prefix . "my_booking_ical_forms WHERE id = %d",
            intval($_POST['form_id'])
        ));

        if (!$item) {
            wp_die(__('Invalid booking form.', 'my_booking_ical_form'));
        }

        $first_name = sanitize_text_field($_POST['first_name']);
        $last_name = sanitize_text_field($_POST['last_name']);
        $email = sanitize_email($_POST['email']);
        $phone = sanitize_text_field($_POST['phone']);
        $parking = isset($_POST['parking']) ? intval($_POST['parking']) : 0;
        $guest_count = intval($_POST['guest_count']);
        $comments = sanitize_text_field($_POST['comments']);
        $summary = wp_kses_post($_POST['summary']);

        $entry_date = mbif_parse_date_input($_POST['entry_date'] ?? '');
        $departure_date = mbif_parse_date_input($_POST['departure_date'] ?? '');

        if (!$entry_date || !$departure_date || $departure_date <= $entry_date) {
            wp_die(__('The selected dates are invalid.', 'my_booking_ical_form'));
        }

        $nights = (strtotime($departure_date) - strtotime($entry_date)) / DAY_IN_SECONDS;
        if ($nights < intval($item->min_days)) {
            wp_die(sprintf(__('The minimum stay for this apartment is %d days.', 'my_booking_ical_form'), intval($item->min_days)));
        }

        $occupied_ranges = array_merge(
            mbif_parse_ical_ranges(mbif_fetch_ical($item->ical_booking_url)),
            mbif_parse_ical_ranges(mbif_fetch_ical($item->ical_airbnb_url))
        );

        if (mbif_range_overlaps($entry_date, $departure_date, $occupied_ranges)) {
            wp_die(__('The selected dates are no longer available. Please choose different dates.', 'my_booking_ical_form'));
        }

        $result = $wpdb->insert(
            $wpdb->prefix . 'my_booking_ical_requests',
            array(
                'form_id' => intval($_POST['form_id']),
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
            error_log('Error en la inserció a la base de dades: ' . $wpdb->last_error);
            wp_die(__('Hi ha hagut un error en guardar les dades. Si us plau, contacta amb l\'administrador.', 'my_booking_ical_form'));
        }

        if (get_option('mbif_emailto_enable')) {

            $to = get_option('mbif_emailto');
            $subject = __('Reservation request through the website', 'my_booking_ical_form');
            $message = '<strong>' . esc_html($item->title) . '</strong>';
            $message .= '<ul>';
            $message .= '<li>' . __('Reference', 'my_booking_ical_form') . ': ' . createReferenceRequest($item->reference, $entry_date, $wpdb->insert_id) . '</li>';
            $message .= '<li>' . __('Entry date', 'my_booking_ical_form') . ': ' . esc_html($entry_date) . '</li>';
            $message .= '<li>' . __('Departure date', 'my_booking_ical_form') . ': ' . esc_html($departure_date) . '</li>';
            $message .= '<li>' . __('First Name', 'my_booking_ical_form') . ': ' . esc_html($first_name) . '</li>';
            $message .= '<li>' . __('Last Name', 'my_booking_ical_form') . ': ' . esc_html($last_name) . '</li>';
            $message .= '<li>' . __('Email', 'my_booking_ical_form') . ': ' . esc_html($email) . '</li>';
            $message .= '<li>' . __('Phone', 'my_booking_ical_form') . ': ' . esc_html($phone) . '</li>';
            $message .= '<li>' . __('Guests', 'my_booking_ical_form') . ': ' . intval($guest_count) . '</li>';

            if ($item->parking_option) {
                $message .= '<li>' . __('Parking', 'my_booking_ical_form') . ': ' . ($parking ? __('Yes', 'my_booking_ical_form') : __('No', 'my_booking_ical_form')) . '</li>';
            }

            $message .= '<li>' . __('Comments', 'my_booking_ical_form') . ': ' . esc_html($comments) . '</li>';
            $message .= '</ul>';
            $message .= $summary;
            $message .= '<br><a href="' . esc_url(get_site_url() . '/wp-admin/admin.php?page=my_booking_ical_requests_show&id=' . $wpdb->insert_id) . '">' . __('View Details', 'my_booking_ical_form') . '</a>';

            $headers = array('Content-Type: text/html; charset=UTF-8');

            wp_mail($to, $subject, $message, $headers);

            if (get_option('mbif_emailto_secondary') != "") {
                wp_mail(get_option('mbif_emailto_secondary'), $subject, $message, $headers);
            }
        }

        $my_url_var = add_query_arg('form_sent', 1, $_SERVER['HTTP_REFERER']);
        wp_redirect($my_url_var);
        exit;
    }
}

function createReferenceRequest($apartament_reference, $request_entry_date, $request_id) {
    return $apartament_reference . '-' . DateTime::createFromFormat('Y-m-d', $request_entry_date)->format('Ymd') . '-' . str_pad($request_id, 5, '0', STR_PAD_LEFT);
}

require_once plugin_dir_path( __FILE__ ) . 'includes/admin/forms.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/admin/requests.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/admin/settings.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/admin/prices.php';
