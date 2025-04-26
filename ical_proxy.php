<?php

if(isset($_GET['ical_url'])) {
    
    $ical_url = $_GET['ical_url'];

    $ical_content = file_get_contents($ical_url);

    header('Content-Type: text/calendar');
    echo $ical_content;
}