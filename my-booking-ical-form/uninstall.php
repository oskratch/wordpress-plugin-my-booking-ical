<?php
/**
 * Copyright (c) 2025 Oscar Periche, Metalinked
 * Licensed under GPL v2 or later
 * https://www.gnu.org/licenses/gpl-2.0.html
 *
 * Runs when the plugin is deleted from the admin. WordPress loads this file
 * instead of the plugin itself, so nothing from the plugin is available here.
 */

defined('WP_UNINSTALL_PLUGIN') or die;

global $wpdb;

$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}my_booking_ical_forms");
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}my_booking_ical_requests");
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}my_booking_ical_prices");

$options = array(
    'mbif_emailto_enable',
    'mbif_emailto',
    'mbif_emailto_secondary',
    'mbif_label_shown',
    'mbif_min_days_default',
    'mbif_currency',
    'mbif_db_version',
);

foreach ($options as $option) {
    delete_option($option);
}

// Cached iCal feeds and submission throttles.
$wpdb->query(
    "DELETE FROM {$wpdb->options}
     WHERE option_name LIKE '\\_transient\\_mbif\\_%'
        OR option_name LIKE '\\_transient\\_timeout\\_mbif\\_%'"
);
