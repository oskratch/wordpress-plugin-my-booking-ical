<?php
/**
 * Copyright (c) 2025 Oscar Periche, Metalinked
 * Licensed under GPL v2 or later
 * https://www.gnu.org/licenses/gpl-2.0.html
 */

function my_booking_ical_settings() {

    global $wpdb;

    if (isset($_POST['settings'])) {

        check_admin_referer('mbif_settings', 'mbif_nonce');

        update_option('mbif_emailto_enable', intval($_POST['mbif_emailto_enable']));
        update_option('mbif_emailto', sanitize_email($_POST['mbif_emailto']));
        update_option('mbif_emailto_secondary', sanitize_email($_POST['mbif_emailto_secondary']));
        update_option('mbif_label_shown', intval($_POST['mbif_label_shown']));
        update_option('min_days_default', intval($_POST['min_days_default']));
        update_option('currency', sanitize_text_field($_POST['currency']));

        echo '<script>window.location.href = "' . esc_js(admin_url('admin.php?page=my_booking_ical_settings')) . '"</script>';
        exit;

    } else {
        require(MBIF_DIR . '/views/admin/my_booking_ical_forms-settings.php');
    }
}
