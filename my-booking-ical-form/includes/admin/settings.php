<?php

function my_booking_ical_settings() {

    global $wpdb;
    
    if(isset($_POST['settings'])) {

        update_option('mbif_emailto_enable', $_POST['mbif_emailto_enable']);
        update_option('mbif_emailto', $_POST['mbif_emailto']);
        update_option('mbif_emailto_secondary', $_POST['mbif_emailto_secondary']);
        update_option('mbif_label_shown', $_POST['mbif_label_shown']);
        update_option('min_days_default', $_POST['min_days_default']);
        update_option('currency', $_POST['currency']);

        echo '<script>window.location.href = "' . admin_url('admin.php?page=my_booking_ical_settings') . '"</script>';
        exit;

    }else{
        require(MBIF_DIR . '/views/admin/my_booking_ical_forms-settings.php');
    }
}