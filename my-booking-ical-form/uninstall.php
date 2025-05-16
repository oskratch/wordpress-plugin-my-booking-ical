<?php
/*
** Database: Deleting Tables
*/

function db_plugin_tables() {
    global $wpdb;
    
    include_once ABSPATH . 'wp-admin/includes/upgrade.php';

    $table_name_1 = $wpdb->prefix . "my_booking_ical_forms";

    $sql_1 = "DROP TABLE `" . $table_name_1 . "`";

    dbDelta($sql_1);
    
    $table_name_2 = $wpdb->prefix . "my_booking_ical_requests";

    $sql_2 = "DROP TABLE `" . $table_name_2 . "`";

    dbDelta($sql_2);
} 