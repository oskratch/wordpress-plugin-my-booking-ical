<?php
/*
 * Plugin Name: My Booking iCal Form
 * Plugin URI: https://github.com/oskratch/wordpress-my-booking-ical
 * Description: A form that allows you to request apartment or room reservations through contact. A calendar is used to select a date range and connects to an iCal file to turn off reserved dates.
 * Author: Oscar Periche, Metalinked
 * Author URI: https://metalinked.net/
 * Version: 1.1.0
 * Requires at least: 6.3
 * Requires PHP: 8
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 *
 * Copyright (c) 2025 Oscar Periche, Metalinked
 */

defined('ABSPATH') or die("Action not allowed");
define('MBIF_DIR', plugin_dir_path(__FILE__));

// Define Options

function mbif_Options(){
    return array(
        'mbif_emailto_enable' => 0,
        'mbif_emailto' => get_option('admin_email'),
        'mbif_emailto_secondary' => "",
        'mbif_label_shown' => 0,
        'min_days_default' => 3,
        'currency' => '€',
    );
}

/*
** Enable action plugin
*/

function mbif_plugin_enable() {

    // Database: Creating tables

    global $wpdb;
    
    include_once ABSPATH . 'wp-admin/includes/upgrade.php';

    $table_name_1 = $wpdb->prefix . "my_booking_ical_forms";

    $sql_1 = "CREATE TABLE `" . $table_name_1 . "` (";
    $sql_1 .= "`id` int(5) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,";
    $sql_1 .= "`reference` varchar(30) DEFAULT NULL,";
    $sql_1 .= "`title` varchar(100) NOT NULL,";
    $sql_1 .= "`ical_booking_url` varchar(200) DEFAULT NULL,";
    $sql_1 .= "`ical_airbnb_url` varchar(200) DEFAULT NULL,";
    $sql_1 .= "`min_days` int(3) NOT NULL,";
    $sql_1 .= "`price` decimal(5,2) NOT NULL,";
    $sql_1 .= "`max_capacity` int(2) NOT NULL,";
    $sql_1 .= "`parking_option` tinyint(1) NOT NULL DEFAULT '0'";
    $sql_1 .= ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    dbDelta($sql_1);
    
    $table_name_2 = $wpdb->prefix . "my_booking_ical_requests";

    $sql_2 = "CREATE TABLE `" . $table_name_2 . "` (";
    $sql_2 .= "`id` int(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,";
    $sql_2 .= "`form_id` int(5) NOT NULL,";
    $sql_2 .= "`first_name` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,";
    $sql_2 .= "`last_name` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,";
    $sql_2 .= "`email` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,";
    $sql_2 .= "`phone` varchar(30) COLLATE utf8mb4_unicode_ci,";
    $sql_2 .= "`guest_count` int(2) NOT NULL,";
    $sql_2 .= "`entry_date` date NOT NULL,";
    $sql_2 .= "`departure_date` date NOT NULL,";
    $sql_2 .= "`parking` tinyint(1) NOT NULL DEFAULT '0',";
    $sql_2 .= "`comments` text COLLATE utf8mb4_unicode_ci,";
    $sql_2 .= "`summary` text COLLATE utf8mb4_unicode_ci,";
    $sql_2 .= "`status` enum('pending_review','validated','denied') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending_review',";
    $sql_2 .= "`created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP";
    $sql_2 .= ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    dbDelta($sql_2);
    
    $table_name_3 = $wpdb->prefix . "my_booking_ical_prices";

    $sql_3 = "CREATE TABLE `" . $table_name_3 . "` (";
    $sql_3 .= "`id` int(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,";
    $sql_3 .= "`form_id` int(5) NOT NULL,";
    $sql_3 .= "`from_date` date NOT NULL,";
    $sql_3 .= "`to_date` date NOT NULL,";
    $sql_3 .= "`price` decimal(5,2) NOT NULL";
    $sql_3 .= ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    dbDelta($sql_3);

    // Options: Adding

    foreach(mbif_Options() as $key => $value){
        add_option($key, $value);
    }
}

register_activation_hook(__FILE__, 'mbif_plugin_enable');

/*
** Disable action plugin
*/

function mbif_plugin_disable() {} 

register_deactivation_hook(__FILE__, 'mbif_plugin_disable'); 

require_once MBIF_DIR . '/functions.php';

/*
** Delete action plugin
*/

function mbif_plugin_uninstall() {

    // Database: Deleting tables

    global $wpdb;

    $table_name_1 = $wpdb->prefix . "my_booking_ical_forms";
    $table_name_2 = $wpdb->prefix . "my_booking_ical_requests";
    
    $wpdb->query( "DROP TABLE IF EXISTS $table_name_1" );
    $wpdb->query( "DROP TABLE IF EXISTS $table_name_2" );

    // Options: Delete

    foreach(mbif_Options() as $key => $value){
        delete_option($key, $value);
    }
} 

register_uninstall_hook(__FILE__, 'mbif_plugin_uninstall'); 

require_once MBIF_DIR . '/functions.php';