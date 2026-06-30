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
    <input type="hidden" name="action" value="my_booking_ical_send">
    <input type="hidden" name="form_id" value="<?php echo $form_id; ?>">

    <div class="form-group">
        <div class="calendar-col">
            <label><?php _e('Entry date', 'my_booking_ical_form'); ?></label>
            <div class="mbif-entry-cal"></div>
            <input type="text" name="entry_date" class="mbif-entry-date" readonly required>
        </div>
        <div class="calendar-col">
            <label><?php _e('Last night', 'my_booking_ical_form'); ?>*</label>
            <div class="mbif-departure-cal"></div>
            <input type="text" name="departure_date" class="mbif-departure-date" readonly required>
            <div class="info-additional">* <?php _e('Departure date is the next day before 11am.', 'my_booking_ical_form'); ?></div>
        </div>
    </div>

    <div class="mbif-price-container"></div>
    <div class="mbif-error-dates"></div>

    <div class="form-group">
        <?php if (get_option('mbif_label_shown')): ?>
            <label><?php _e('First Name', 'my_booking_ical_form'); ?></label>
            <input type="text" name="first_name" required>
        <?php else: ?>
            <input type="text" name="first_name" placeholder="<?php esc_attr_e('First Name', 'my_booking_ical_form'); ?>" required>
        <?php endif; ?>
    </div>

    <div class="form-group">
        <?php if (get_option('mbif_label_shown')): ?>
            <label><?php _e('Last Name', 'my_booking_ical_form'); ?></label>
            <input type="text" name="last_name" required>
        <?php else: ?>
            <input type="text" name="last_name" placeholder="<?php esc_attr_e('Last Name', 'my_booking_ical_form'); ?>" required>
        <?php endif; ?>
    </div>

    <div class="form-group">
        <?php if (get_option('mbif_label_shown')): ?>
            <label><?php _e('Email', 'my_booking_ical_form'); ?></label>
            <input type="email" name="email" required>
        <?php else: ?>
            <input type="email" name="email" placeholder="<?php esc_attr_e('Email', 'my_booking_ical_form'); ?>" required>
        <?php endif; ?>
    </div>

    <div class="form-group">
        <?php if (get_option('mbif_label_shown')): ?>
            <label><?php _e('Phone', 'my_booking_ical_form'); ?></label>
            <input type="text" name="phone">
        <?php else: ?>
            <input type="text" name="phone" placeholder="<?php esc_attr_e('Phone', 'my_booking_ical_form'); ?>">
        <?php endif; ?>
    </div>

    <div class="form-group">
        <?php if (get_option('mbif_label_shown')): ?>
            <label><?php _e('Select the number of people', 'my_booking_ical_form'); ?></label>
        <?php endif; ?>
        <select name="guest_count" required>
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
        <label><?php _e('Parking', 'my_booking_ical_form'); ?></label>
        <input type="radio" name="parking" value="0"> <?php _e('No', 'my_booking_ical_form'); ?>
        <input type="radio" name="parking" value="1"> <?php _e('Yes', 'my_booking_ical_form'); ?>
    </div>
    <?php endif; ?>

    <div class="form-group">
        <?php if (get_option('mbif_label_shown')): ?>
            <label><?php _e('Comments', 'my_booking_ical_form'); ?></label>
            <textarea name="comments"></textarea>
        <?php else: ?>
            <textarea name="comments" placeholder="<?php esc_attr_e('Comments', 'my_booking_ical_form'); ?>"></textarea>
        <?php endif; ?>
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
