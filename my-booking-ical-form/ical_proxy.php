<?php
/**
 * Copyright (c) 2025 Oscar Periche, Metalinked
 * Licensed under GPL v2 or later
 * https://www.gnu.org/licenses/gpl-2.0.html
 */

if (!isset($_GET['ical_url'])) {
    http_response_code(400);
    exit;
}

$ical_url = $_GET['ical_url'];

$parsed = parse_url($ical_url);
if (!$parsed || !isset($parsed['scheme']) || !in_array($parsed['scheme'], ['http', 'https'])) {
    http_response_code(400);
    exit('Invalid URL');
}

$ical_content = @file_get_contents($ical_url);

if ($ical_content === false) {
    http_response_code(502);
    exit('Failed to fetch calendar');
}

header('Content-Type: text/calendar; charset=UTF-8');
echo $ical_content;
