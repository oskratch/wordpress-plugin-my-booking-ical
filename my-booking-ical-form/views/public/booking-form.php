<?php defined('ABSPATH') or die; ?>

<div class="mbif-wrap" data-form-id="<?php echo $form_id; ?>">

<?php if ($form_sent === 1): ?>
<div class="my-popup">
    <div class="my-popup-content">
        <span class="my-popup-close">&times;</span>
        <h3><?php _e('Request sent', 'my_booking_ical_form'); ?></h3>
        <p><?php _e('We have sent an email to the provided address with the summary of your booking request details. You will receive the confirmation within 24 hours.', 'my_booking_ical_form'); ?></p>
    </div>
</div>
<?php endif; ?>

<form method="post" class="booking_ical_form" action="<?php echo $form_action_url; ?>">
    <?php wp_nonce_field('mbif_send_request', 'mbif_nonce'); ?>
    <input type="hidden" name="action" value="my_booking_ical_send">
    <input type="hidden" name="form_id" value="<?php echo $form_id; ?>">
    <p class="mbif-hp-field" aria-hidden="true" style="position:absolute;left:-9999px;top:-9999px;">
        <label>Website<input type="text" name="mbif_website" tabindex="-1" autocomplete="off"></label>
    </p>

    <div class="form-group">
        <div class="calendar-col">
            <label for="mbif-entry_date-<?php echo $form_id; ?>"><?php _e('Entry date', 'my_booking_ical_form'); ?></label>
            <div class="mbif-entry-cal"></div>
            <input type="text" id="mbif-entry_date-<?php echo $form_id; ?>" name="entry_date" class="mbif-entry-date" readonly required>
        </div>
        <div class="calendar-col">
            <label for="mbif-departure_date-<?php echo $form_id; ?>"><?php _e('Last night', 'my_booking_ical_form'); ?>*</label>
            <div class="mbif-departure-cal"></div>
            <input type="text" id="mbif-departure_date-<?php echo $form_id; ?>" name="departure_date" class="mbif-departure-date" readonly required>
            <div class="info-additional">* <?php _e('Departure date is the next day before 11am.', 'my_booking_ical_form'); ?></div>
        </div>
    </div>

    <div class="mbif-price-container"></div>
    <div class="mbif-error-dates"></div>

    <div class="form-group">
        <label for="mbif-first_name-<?php echo $form_id; ?>" class="<?php echo get_option('mbif_label_shown') ? '' : 'mbif-visually-hidden'; ?>"><?php _e('First Name', 'my_booking_ical_form'); ?></label>
        <input type="text" id="mbif-first_name-<?php echo $form_id; ?>" name="first_name" placeholder="<?php echo get_option('mbif_label_shown') ? '' : esc_attr__('First Name', 'my_booking_ical_form'); ?>" required>
    </div>

    <div class="form-group">
        <label for="mbif-last_name-<?php echo $form_id; ?>" class="<?php echo get_option('mbif_label_shown') ? '' : 'mbif-visually-hidden'; ?>"><?php _e('Last Name', 'my_booking_ical_form'); ?></label>
        <input type="text" id="mbif-last_name-<?php echo $form_id; ?>" name="last_name" placeholder="<?php echo get_option('mbif_label_shown') ? '' : esc_attr__('Last Name', 'my_booking_ical_form'); ?>" required>
    </div>

    <div class="form-group">
        <label for="mbif-email-<?php echo $form_id; ?>" class="<?php echo get_option('mbif_label_shown') ? '' : 'mbif-visually-hidden'; ?>"><?php _e('Email', 'my_booking_ical_form'); ?></label>
        <input type="email" id="mbif-email-<?php echo $form_id; ?>" name="email" placeholder="<?php echo get_option('mbif_label_shown') ? '' : esc_attr__('Email', 'my_booking_ical_form'); ?>" required>
    </div>

    <div class="form-group">
        <label for="mbif-phone-<?php echo $form_id; ?>" class="<?php echo get_option('mbif_label_shown') ? '' : 'mbif-visually-hidden'; ?>"><?php _e('Phone', 'my_booking_ical_form'); ?></label>
        <input type="text" id="mbif-phone-<?php echo $form_id; ?>" name="phone" placeholder="<?php echo get_option('mbif_label_shown') ? '' : esc_attr__('Phone', 'my_booking_ical_form'); ?>">
    </div>

    <div class="form-group">
        <label for="mbif-guest_count-<?php echo $form_id; ?>" class="<?php echo get_option('mbif_label_shown') ? '' : 'mbif-visually-hidden'; ?>"><?php _e('Select the number of people', 'my_booking_ical_form'); ?></label>
        <select id="mbif-guest_count-<?php echo $form_id; ?>" name="guest_count" required>
            <?php if (!get_option('mbif_label_shown')): ?>
                <option value=""><?php _e('Select the number of people', 'my_booking_ical_form'); ?></option>
            <?php endif; ?>
            <?php for ($a = 1; $a <= $item->max_capacity; $a++): ?>
                <option value="<?php echo $a; ?>"><?php echo $a; ?></option>
            <?php endfor; ?>
        </select>
    </div>

    <?php if ($item->parking_option): ?>
    <div class="form-group">
        <span class="mbif-fieldset-label"><?php _e('Parking', 'my_booking_ical_form'); ?></span>
        <label class="mbif-radio-label"><input type="radio" name="parking" value="0" checked> <?php _e('No', 'my_booking_ical_form'); ?></label>
        <label class="mbif-radio-label"><input type="radio" name="parking" value="1"> <?php _e('Yes', 'my_booking_ical_form'); ?></label>
    </div>
    <?php endif; ?>

    <div class="form-group">
        <label for="mbif-comments-<?php echo $form_id; ?>" class="<?php echo get_option('mbif_label_shown') ? '' : 'mbif-visually-hidden'; ?>"><?php _e('Comments', 'my_booking_ical_form'); ?></label>
        <textarea id="mbif-comments-<?php echo $form_id; ?>" name="comments" placeholder="<?php echo get_option('mbif_label_shown') ? '' : esc_attr__('Comments', 'my_booking_ical_form'); ?>"></textarea>
    </div>

    <div class="form-group">
        <input type="checkbox" name="acceptance" required>
        <span><?php printf(
            __('I have read and accept the %s', 'my_booking_ical_form'),
            '<a target="_blank" class="accept-link" href="' . get_privacy_policy_url() . '">' . __('Privacy Policy', 'my_booking_ical_form') . '</a>'
        ); ?></span>
    </div>

    <div class="form-group">
        <input type="hidden" name="summary" class="mbif-summary">
        <button type="button" class="btn btn-primary mbif-send-btn"><?php _e('Send', 'my_booking_ical_form'); ?></button>
    </div>
</form>

</div>
