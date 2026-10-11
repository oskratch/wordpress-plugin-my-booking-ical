<?php
/**
 * Copyright (c) 2025 Oscar Periche, Metalinked
 * Licensed under GPL v2 or later
 * https://www.gnu.org/licenses/gpl-2.0.html
 */

function my_booking_ical_settings_save() {
    if (!isset($_POST['settings'])) return;

    check_admin_referer('mbif_settings', 'mbif_nonce');

    update_option('mbif_emailto_enable', empty($_POST['mbif_emailto_enable']) ? 0 : 1);
    update_option('mbif_emailto', sanitize_email(wp_unslash($_POST['mbif_emailto'] ?? '')));
    update_option('mbif_emailto_secondary', sanitize_email(wp_unslash($_POST['mbif_emailto_secondary'] ?? '')));
    update_option('mbif_label_shown', empty($_POST['mbif_label_shown']) ? 0 : 1);
    update_option('mbif_min_days_default', max(1, intval($_POST['mbif_min_days_default'] ?? 1)));
    update_option('mbif_currency', in_array($_POST['mbif_currency'] ?? '', array('€', '$'), true) ? $_POST['mbif_currency'] : '€');

    wp_safe_redirect(admin_url('admin.php?page=my_booking_ical_settings&settings-updated=1'));
    exit;
}

function my_booking_ical_settings() {
    require(MBIF_DIR . '/views/admin/settings.php');
}
