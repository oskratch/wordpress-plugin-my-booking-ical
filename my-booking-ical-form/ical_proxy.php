<?php
/**
 * Copyright (c) 2025 Oscar Periche, Metalinked
 * Licensed under GPL v2 or later
 * https://www.gnu.org/licenses/gpl-2.0.html
 */

if(isset($_GET['ical_url'])) {
    
    $ical_url = $_GET['ical_url'];

    $ical_content = file_get_contents($ical_url);

    header('Content-Type: text/calendar');
    echo $ical_content;
}