<?php
/**
 * Copyright (c) 2025 Oscar Periche, Metalinked
 * Licensed under GPL v2 or later
 * https://www.gnu.org/licenses/gpl-2.0.html
 *
 * Proxies one of the iCal URLs configured by an admin for a given form, so the
 * browser never sees (or requests) the external URL directly. This intentionally
 * does NOT accept an arbitrary URL from the client: it only ever fetches URLs
 * that an administrator already stored for the requested form_id, which is what
 * keeps this from being an open SSRF relay.
 */

require_once dirname(__FILE__) . '/../../../wp-load.php';

if (!isset($_GET['form_id'], $_GET['type'])) {
    http_response_code(400);
    exit;
}

$form_id = intval($_GET['form_id']);
$type = sanitize_key($_GET['type']);

$column = array(
    'booking' => 'ical_booking_url',
    'airbnb'  => 'ical_airbnb_url',
);

if ($form_id <= 0 || !isset($column[$type])) {
    http_response_code(400);
    exit('Invalid request');
}

global $wpdb;

$ical_url = $wpdb->get_var($wpdb->prepare(
    "SELECT {$column[$type]} FROM " . $wpdb->prefix . "my_booking_ical_forms WHERE id = %d",
    $form_id
));

if (empty($ical_url)) {
    http_response_code(404);
    exit('Calendar not configured');
}

$ical_content = mbif_fetch_ical($ical_url);

if ($ical_content === false) {
    http_response_code(502);
    exit('Failed to fetch calendar');
}

header('Content-Type: text/calendar; charset=UTF-8');
echo $ical_content;
