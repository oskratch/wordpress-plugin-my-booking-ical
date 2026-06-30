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
    add_submenu_page(null, 'Requests', 'Requests', 'manage_options', 'my_booking_ical_prices_create', 'my_booking_ical_prices_create');
    add_submenu_page(null, 'Requests', 'Requests', 'manage_options', 'my_booking_ical_prices_edit', 'my_booking_ical_prices_edit');
    add_submenu_page(null, 'Requests', 'Requests', 'manage_options', 'my_booking_ical_prices_delete', 'my_booking_ical_prices_delete');
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
        'icalBookingUrl' => $item->ical_booking_url,
        'icalAirbnbUrl'  => $item->ical_airbnb_url,
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

function my_booking_ical_send() {

    if (isset($_POST['action']) && $_POST['action'] == 'my_booking_ical_send') {

        global $wpdb;

        $item = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM " . $wpdb->prefix . "my_booking_ical_forms WHERE id = %d",
            intval($_POST['form_id'])
        ));

        $first_name = sanitize_text_field($_POST['first_name']);
        $last_name = sanitize_text_field($_POST['last_name']);
        $email = sanitize_email($_POST['email']);
        $phone = sanitize_text_field($_POST['phone']);
        $entry_date = sanitize_text_field($_POST['entry_date']);
        $departure_date = sanitize_text_field($_POST['departure_date']);
        $parking = isset($_POST['parking']) ? intval($_POST['parking']) : 0;
        $guest_count = intval($_POST['guest_count']);
        $comments = sanitize_text_field($_POST['comments']);
        $summary = wp_kses_post($_POST['summary']);

        $entry_date = preg_split("/[\/]|[-]+/", $entry_date);
        $entry_date = $entry_date[2] . "-" . $entry_date[1] . "-" . $entry_date[0];

        $departure_date = preg_split("/[\/]|[-]+/", $departure_date);
        $departure_date = $departure_date[2] . "-" . $departure_date[1] . "-" . $departure_date[0];

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
