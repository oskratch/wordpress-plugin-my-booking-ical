<?php
/**
 * Copyright (c) 2025 Oscar Periche, Metalinked
 * Licensed under GPL v2 or later
 * https://www.gnu.org/licenses/gpl-2.0.html
 */

/*
** Menu: Adding admin elements
*/

function register_my_booking_ical_form_menu() {
    //add_menu_page('My Booking iCal', 'My Booking iCal', 'manage_options', MBIF_DIR . '/admin/my_booking_ical_forms.php');
    add_menu_page('My Booking iCal', 'My Booking iCal', 'manage_options', 'my_booking_ical_forms', 'my_booking_ical_forms', 'dashicons-building'); // Aqui cridem directament a la funció afegint un 5è paràmetre
    add_submenu_page('my_booking_ical_forms', 'Forms', 'Forms', 'manage_options', 'my_booking_ical_forms', 'my_booking_ical_forms');
    add_submenu_page('my_booking_ical_forms', 'Settings', 'Settings', 'manage_options', 'my_booking_ical_settings', 'my_booking_ical_settings');
    
    // Habilitar URL, però sense que apareguin al menú (null)
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

/*
** Translations
*/

function load_mbif_translations() {
    load_plugin_textdomain( 'my_booking_ical_form', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}

add_action( 'plugins_loaded', 'load_mbif_translations' );

/*
** Enqueue: Scripts & Style
*/

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
    wp_register_script('mbif-js', plugins_url('assets/js/mbif.js', __FILE__));
    wp_enqueue_script('mbif-js');
}

/*
** Shortcodes
*/

add_shortcode('booking_ical_form', 'my_booking_ical_shortcode');

function my_booking_ical_shortcode($atts) {

    global $wpdb;

    $form_id = isset($atts['form_id']) ? $atts['form_id'] : 0;
    $item = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "my_booking_ical_forms WHERE id = " . $form_id);

    if(isset($item)){

        enqueue_resources();

        $form_html = "";

        $form_sent = isset($_GET['form_sent']) ? $_GET['form_sent'] : null;
        
        if($form_sent == 1){

            $form_html .= '<div id="my-popup" class="my-popup">
                <div class="my-popup-content">
                    <span class="my-popup-close">&times;</span>
                    <h3>' . __('Request sent', 'my_booking_ical_form') . '</h3>
                    <p>' . __('We have sent an email to the provided address with the summary of your booking request details. You will receive the confirmation within 24 hours.', 'my_booking_ical_form') . '</p>
                </div>
            </div>';
        
        }

        $consulta = "SELECT from_date, to_date, price FROM {$wpdb->prefix}my_booking_ical_prices WHERE form_id = " . $form_id;
    
        $resultados = $wpdb->get_results($consulta);
        $datos_precios_reserva = array();

        foreach ($resultados as $resultado) {
            $datos_precios_reserva[] = array(
                'start' => $resultado->from_date,
                'end' => $resultado->to_date,
                'price' => $resultado->price
            );
        }

        $json_precios_reserva = json_encode($datos_precios_reserva);

        $form_action_url = esc_url( $_SERVER['REQUEST_URI'] );

        $form_html .= '<form method="post" id="requestForm" class="booking_ical_form" action="' . $form_action_url . '">';
        $form_html .= '<input type="hidden" name="action" value="my_booking_ical_send">';
        $form_html .= '<input type="hidden" name="form_id" value="' . $atts['form_id'] . '">';
        $form_html .= '<div class="form-group">';

        /*if(get_option('mbif_label_shown')) {
            $form_html .= '<label for="entry_date">' . __('Entry date', 'my_booking_ical_form') . '</label>';
            $form_html .= '<input type="text" id="entry_date" name="entry_date" data-date-inline-picker="true" required>';
        }else{
            $form_html .= '<input type="text" id="entry_date" name="entry_date" placeholder="' . __('Entry date', 'my_booking_ical_form') . '" required>';
        }*/

        $form_html .= '<div class="calendar-col">';
        $form_html .= '<label for="d_entry_date">' . __('Entry date', 'my_booking_ical_form') . '</label>';
        $form_html .= '<div id="d_entry_date"></div>';
        $form_html .= '<input type="text" id="entry_date" name="entry_date" readonly required>';
        $form_html .='</div>';

        $form_html .= '<div class="calendar-col">';
        $form_html .= '<label for="d_departure_date">' . __('Last night', 'my_booking_ical_form') . '*</label>';
        $form_html .= '<div id="d_departure_date"></div>';
        $form_html .= '<input type="text" id="departure_date" name="departure_date" readonly required>';
        $form_html .= '<div class="info-additional">* ' . __('Departure date is the next day before 11am.', 'my_booking_ical_form') . "</div>";
        $form_html .='</div>';
        
        //$form_html .= '</div>';
        //$form_html .= '<div class="form-group">';

        /*if(get_option('mbif_label_shown')) {
            $form_html .= '<label for="departure_date">' . __('Departure date', 'my_booking_ical_form') . '</label>';
            $form_html .= '<input type="text" id="departure_date" name="departure_date" data-date-inline-picker="true" required>';
        }else{
            $form_html .= '<input type="text" id="departure_date" name="departure_date" placeholder="' . __('Departure date', 'my_booking_ical_form') . '" required>';
        }*/

        $form_html .= '</div>';

        $form_html .= '<div id="priceContainer"></div>';

        $form_html .= '<div id="errorDates"></div>';

        $form_html .= '<div class="form-group">';

        if(get_option('mbif_label_shown')) {
            $form_html .= '<label for="first_name">' . __('First Name', 'my_booking_ical_form') . '</label>';
            $form_html .= '<input type="text" id="first_name" name="first_name" required>';
        }else{
            $form_html .= '<input type="text" id="first_name" name="first_name" placeholder="' . __('First Name', 'my_booking_ical_form') . '" required>';
        }
        
        $form_html .= '</div>';
        $form_html .= '<div class="form-group">';

        if(get_option('mbif_label_shown')) {
            $form_html .= '<label for="last_name">' . __('Last Name', 'my_booking_ical_form') . '</label>';
            $form_html .= '<input type="text" id="last_name" name="last_name" required>';
        }else{
            $form_html .= '<input type="text" id="last_name" name="last_name" placeholder="' . __('Last Name', 'my_booking_ical_form') . '" required>';
        }

        $form_html .= '</div>';
        $form_html .= '<div class="form-group">';

        if(get_option('mbif_label_shown')) {
            $form_html .= '<label for="email">' . __('Email', 'my_booking_ical_form') . '</label>';
            $form_html .= '<input type="email" id="email" name="email" required>';
        }else{
            $form_html .= '<input type="email" id="email" name="email" placeholder="' . __('Email', 'my_booking_ical_form') . '" required>';
        }

        $form_html .= '</div>';
        $form_html .= '<div class="form-group">';

        if(get_option('mbif_label_shown')) {
            $form_html .= '<label for="phone">' . __('Phone', 'my_booking_ical_form') . '</label>';
            $form_html .= '<input type="text" id="phone" name="phone">';
        }else{
            $form_html .= '<input type="text" id="phone" name="phone" placeholder="' . __('Phone', 'my_booking_ical_form') . '">';
        }

        $form_html .= '</div>';
        $form_html .= '<div class="form-group">';

        if(get_option('mbif_label_shown')) {
            $form_html .= '<label for="guest_count">' . __('Select the number of people', 'my_booking_ical_form') . '</label>';
            $form_html .= '<select name="guest_count" required>';
            for($a = 1; $a <= $item->max_capacity; $a++){
                $form_html .= '<option value="' . $a . '">' . $a . '</option>';
            }
            $form_html .= '</select>';
        }else{
            $form_html .= '<select name="guest_count" required>';
            $form_html .= '<option value="">' . __('Select the number of people', 'my_booking_ical_form') . '</option>';
            for($a = 1; $a <= $item->max_capacity; $a++){
                $form_html .= '<option value="' . $a . '">' . $a . '</option>';
            }
            $form_html .= '</select>';
        }

        $form_html .= '</div>';
        $form_html .= '<div class="form-group">';

        if($item->parking_option){

            $form_html .= '<label for="parking">';
            $form_html .= __('Parking', 'my_booking_ical_form');
            $form_html .= '</label>';
            $form_html .= '<input type="radio" name="parking" value="0"> ' . __('No', 'my_booking_ical_form');
            $form_html .= '<input type="radio" name="parking" value="1"> ' . __('Yes', 'my_booking_ical_form');

            $form_html .= '</div>';
            $form_html .= '<div class="form-group">';

        }

        if(get_option('mbif_label_shown')) {
            $form_html .= '<label for="comments">' . __('Comments', 'my_booking_ical_form') . '</label>';
            $form_html .= '<textarea id="comments" name="comments"></textarea>';
        }else{
            $form_html .= '<textarea id="comments" name="comments" placeholder="' . __('Comments', 'my_booking_ical_form') . '"></textarea>';
        }

        $form_html .= '</div>';
        $form_html .= '<div class="form-group">';

        $form_html .= '<input type="checkbox" name="acceptance" required> ';
        $form_html .= '<span>' . sprintf( __('I have read and accept the %s', 'my_booking_ical_form'), '<a target="_blank" class="accept-link" href="' . get_privacy_policy_url() . '">' . __('Privacy Policy', 'my_booking_ical_form') . '</a>') . '</span>';

        $form_html .= '</div>';
        $form_html .= '<div class="form-group">';
        $form_html .= '<input type="hidden" id="summary" name="summary">';
        $form_html .= '<button type="button" id="sendForm" class="btn btn-primary">' . __('Send', 'my_booking_ical_form') . '</button>';
        $form_html .= '</div>';
        $form_html .= '</form>';

        $args = array(); // Define $args with appropriate query parameters if needed
        $query = new WP_Query( $args );
        
        $form_html .= "<script>let min_days = " . $item->min_days . ", currency = '" . get_option('currency') . "', dayName = '" . __('Day', 'my_booking_ical_form') . "', nightsName = '" . __('Nights', 'my_booking_ical_form') . "', priceName = '" . __('Price', 'my_booking_ical_form') . "', daysName = '" . __('Days', 'my_booking_ical_form') . "', totalPriceName = '" . __('Total price', 'my_booking_ical_form') . "'; 
    var priceRangesJson = '" . $json_precios_reserva . "';

        var url1 = '" . $item->ical_booking_url . "';
        var url2 = '" . $item->ical_airbnb_url . "';
        
        document.addEventListener('DOMContentLoaded', function() {
            ";

        if($item->ical_booking_url != "" && $item->ical_airbnb_url == ""){
        
            $form_html .= "async function main() {
                try {
                    var result = await fetchData(url1, true);
                } catch (error) {
                    console.error(error);
                }
                }
                
                main();
            ";

        }elseif($item->ical_booking_url == "" && $item->ical_airbnb_url != ""){
        
            $form_html .= "async function main() {
                try {
                    var result = await fetchData(url2, true);
                } catch (error) {
                    console.error(error);
                }
                }
                
                main();
            ";

        }elseif($item->ical_booking_url != "" && $item->ical_airbnb_url != ""){
        
            $form_html .= "async function main1() {
                try {
                    var result = await fetchData(url1, false);
                } catch (error) {
                    console.error(error);
                }
                }
                
                main1();

                async function main2() {
                try {
                    var result = await fetchData(url2, true);
                } catch (error) {
                    console.error(error);
                }
                }
                
                main2();
            ";

        }else{
            
            $form_html .= "iniCalendar();
            ";

        }

        $form_html .= "});
        ";

        $form_html .= "function iniCalendar() {
            let today = new Date(new Date().getFullYear(), new Date().getMonth(), new Date().getDate());
            jQuery('#d_entry_date').datepicker({
                dateFormat: 'dd-mm-yy',
                minDate: today,
                altField: '#entry_date',
                firstDay: 1,
                beforeShowDay: function(date) {
                    var stringDate = jQuery.datepicker.formatDate('yymmdd', date);
                    if(disabledDates.indexOf(stringDate) === -1){
                        return [true, '', getPriceForDate(date, priceRangesJson, " . $item->price . ") + ' " . get_option('currency') . "'];
                        //return [true, ''];
                    }else{
                        return [false];
                    }
                },
                onSelect: function (date) {
                    jQuery('#d_departure_date').datepicker('option', 'minDate', addOneDay(date));
                    jQuery('#entry_date').val(date);
                    getPriceForDateRange(date, jQuery('#departure_date').val(), priceRangesJson, " . $item->price . ");
                }
            });

            jQuery('#d_departure_date').datepicker({
                dateFormat: 'dd-mm-yy',
                minDate: today,
                firstDay: 1,
                beforeShowDay: function(date) {
                    var stringDate = jQuery.datepicker.formatDate('yymmdd', date);
                    if(disabledDates.indexOf(stringDate) === -1){
                        return [true, '', getPriceForDate(date, priceRangesJson, " . $item->price . ") + ' " . get_option('currency') . "'];
                    }else{
                        return [false];
                    }
                    //return [disabledDates.indexOf(stringDate) === -1];
                },
                onSelect: function (date) {
                    jQuery('#departure_date').val(date);
                    getPriceForDateRange(jQuery('#entry_date').val(), date, priceRangesJson, " . $item->price . ");
                }
            });
        }</script>";

        return $form_html;

    }else{
        //wp_send_json_error("No existe");
    }
}

add_action('init', 'my_booking_ical_send');

function my_booking_ical_send() {

    if (isset($_POST['action']) && $_POST['action'] == 'my_booking_ical_send') {

        global $wpdb;
    
        $item = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "my_booking_ical_forms WHERE id = " . $_POST['form_id']);

        $first_name = sanitize_text_field($_POST['first_name']);
        $last_name = sanitize_text_field($_POST['last_name']);
        $email = sanitize_email($_POST['email']);
        $phone = sanitize_text_field($_POST['phone']);
        $entry_date = sanitize_text_field($_POST['entry_date']);
        $departure_date = sanitize_text_field($_POST['departure_date']);
        $parking = isset($_POST['parking']) ? sanitize_text_field($_POST['parking']) : '0';
        $guest_count = $_POST['guest_count'];
        $comments = sanitize_text_field($_POST['comments']);
        $summary = $_POST['summary'];

        $entry_date = preg_split("/[\/]|[-]+/", $entry_date);
        $entry_date = $entry_date[2] . "-" . $entry_date[1] . "-" . $entry_date[0];

        $departure_date = preg_split("/[\/]|[-]+/", $departure_date);
        $departure_date = $departure_date[2] . "-" . $departure_date[1] . "-" . $departure_date[0];

        // Guarda les dades a la base de dades
        
        $result = $wpdb->insert(
            $wpdb->prefix . 'my_booking_ical_requests',
            array(
                'form_id' => $_POST['form_id'],
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
            array(
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s'
            )
        );

        if ($result === false) {
            error_log('Error en la inserció a la base de dades: ' . $wpdb->last_error);
            wp_die(__('Hi ha hagut un error en guardar les dades. Si us plau, contacta amb l\'administrador.', 'my_booking_ical_form'));
        }

        if(get_option('mbif_emailto_enable')) {
        
            $to = get_option('mbif_emailto');
            $subject = __('Reservation request through the website', 'my_booking_ical_form');
            $message = '<strong>' . $item->title . '</strong>';
            $message .= '<ul>';
            $message .= '<li>' . __('Reference', 'my_booking_ical_form') . ': ' . createReferenceRequest($item->reference, $entry_date, $wpdb->insert_id) . '</li>';
            $message .= '<li>' . __('Entry date', 'my_booking_ical_form') . ': ' . $entry_date . '</li>';
            $message .= '<li>' . __('Departure date', 'my_booking_ical_form') . ': ' . $departure_date . '</li>';
            $message .= '<li>' . __('First Name', 'my_booking_ical_form') . ': ' . $first_name . '</li>';
            $message .= '<li>' . __('Last Name', 'my_booking_ical_form') . ': ' . $last_name . '</li>';
            $message .= '<li>' . __('Email', 'my_booking_ical_form') . ': ' . $email . '</li>';
            $message .= '<li>' . __('Phone', 'my_booking_ical_form') . ': ' . $phone . '</li>';
            $message .= '<li>' . __('Guests', 'my_booking_ical_form') . ': ' . $guest_count . '</li>';

            if($item->parking_option){
                $message .= '<li>' . __('Parking', 'my_booking_ical_form') . ': ' . ($_POST['parking'] ? __('Yes', 'my_booking_ical_form') : __('No', 'my_booking_ical_form')) . '</li>';
            }

            $message .= '<li>' . __('Comments', 'my_booking_ical_form') . ': ' . $comments . '</li>';
            $message .= '</ul>';
            $message .= $summary;
            $message .= '<br><a href="' . get_site_url() . '/wp-admin/admin.php?page=my_booking_ical_requests_show&id=' . $wpdb->insert_id . '">' .  __('View Details', 'my_booking_ical_form') . '</a>';

            $headers = array('Content-Type: text/html; charset=UTF-8');

            $result = wp_mail($to, $subject, $message, $headers);

            if(get_option('mbif_emailto_secondary') != ""){
                
                $result = wp_mail(get_option('mbif_emailto_secondary'), $subject, $message, $headers);
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