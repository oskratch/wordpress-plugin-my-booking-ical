<?php
/*
 * Plugin Name: My Booking iCal Form
 * Plugin URI: https://github.com/oskratch/wordpress-plugin-my-booking-ical
 * Description: A form that allows you to request apartment or room reservations through contact. A calendar is used to select a date range and connects to an iCal file to turn off reserved dates.
 * Author: Oscar Periche, Metalinked
 * Author URI: https://metalinked.net/
 * Version: 1.2.0
 * Requires at least: 6.3
 * Requires PHP: 8
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 *
 * Copyright (c) 2025 Oscar Periche, Metalinked
 */

defined('ABSPATH') or die("Action not allowed");
define('MBIF_DIR', plugin_dir_path(__FILE__));
define('MBIF_VERSION', '1.2.0');
define('MBIF_DB_VERSION', '1.3');

function mbif_Options(){
    return array(
        'mbif_emailto_enable' => 0,
        'mbif_emailto' => get_option('admin_email'),
        'mbif_emailto_secondary' => "",
        'mbif_label_shown' => 0,
        'mbif_min_days_default' => 3,
        'mbif_currency' => '€',
    );
}

function mbif_plugin_enable() {

    global $wpdb;

    include_once ABSPATH . 'wp-admin/includes/upgrade.php';

    // dbDelta() needs one column per line (and two spaces after PRIMARY KEY)
    // to detect column changes on existing installs.
    $table_options = "ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    dbDelta("CREATE TABLE {$wpdb->prefix}my_booking_ical_forms (
  id int(5) UNSIGNED NOT NULL AUTO_INCREMENT,
  reference varchar(30) DEFAULT NULL,
  title varchar(100) NOT NULL,
  ical_booking_url varchar(500) DEFAULT NULL,
  ical_airbnb_url varchar(500) DEFAULT NULL,
  min_days int(3) NOT NULL,
  price decimal(8,2) NOT NULL,
  max_capacity int(2) NOT NULL,
  parking_option tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY  (id)
) $table_options;");

    dbDelta("CREATE TABLE {$wpdb->prefix}my_booking_ical_requests (
  id int(10) NOT NULL AUTO_INCREMENT,
  form_id int(5) NOT NULL,
  first_name varchar(80) NOT NULL,
  last_name varchar(120) NOT NULL,
  email varchar(80) NOT NULL,
  phone varchar(30),
  guest_count int(2) NOT NULL,
  entry_date date NOT NULL,
  departure_date date NOT NULL,
  parking tinyint(1) NOT NULL DEFAULT '0',
  comments text,
  summary text,
  status enum('pending_review','validated','denied') NOT NULL DEFAULT 'pending_review',
  created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY  (id)
) $table_options;");

    dbDelta("CREATE TABLE {$wpdb->prefix}my_booking_ical_prices (
  id int(10) NOT NULL AUTO_INCREMENT,
  form_id int(5) NOT NULL,
  from_date date NOT NULL,
  to_date date NOT NULL,
  price decimal(8,2) NOT NULL,
  PRIMARY KEY  (id)
) $table_options;");

    // Up to 1.1.0 these settings were stored under the unprefixed names
    // 'min_days_default' and 'currency'; carry existing values over.
    $legacy_options = array('min_days_default' => 'mbif_min_days_default', 'currency' => 'mbif_currency');
    foreach ($legacy_options as $old => $new) {
        if (get_option($new) === false && get_option($old) !== false) {
            add_option($new, get_option($old));
        }
    }

    foreach(mbif_Options() as $key => $value){
        add_option($key, $value);
    }

    update_option('mbif_db_version', MBIF_DB_VERSION);
}

register_activation_hook(__FILE__, 'mbif_plugin_enable');

function mbif_plugin_disable() {}

register_deactivation_hook(__FILE__, 'mbif_plugin_disable');

/**
 * Re-runs dbDelta() on already-installed sites when the schema changes
 * (e.g. widening the iCal URL columns), without waiting for a deactivate/reactivate.
 */
function mbif_maybe_upgrade_db() {
    if (get_option('mbif_db_version') !== MBIF_DB_VERSION) {
        mbif_plugin_enable();
    }
}
add_action('plugins_loaded', 'mbif_maybe_upgrade_db');

require_once MBIF_DIR . '/functions.php';
