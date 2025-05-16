<div class="wrap">
  <h1><?php echo __('Add Form', 'my_booking_ical_form')?></h1>
  <p><?php echo __('Below, you can create a form to receive reservation requests. Please note that a separate form needs to be created for each apartment.', 'my_booking_ical_form')?></p>
  <form method="post" action="">
    <table class="form-table">
        <tr valign="top">
            <th scope="row"><?php echo __('Apartment name', 'my_booking_ical_form')?></th>
            <td>
                <input type="text" id="title" name="title" autocomplete="off" class="regular-text ltr" value="" required />
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><?php echo __('Reference', 'my_booking_ical_form')?></th>
            <td>
                <input type="text" id="reference" name="reference" autocomplete="off" class="regular-text ltr" value="" required />
            </td>
        </tr>
        <tr valign="top">
            <th scope="row">Booking iCal URL</th>
            <td>
                <input type="text" id="ical_booking_url" name="ical_booking_url" autocomplete="off" class="regular-text ltr" value="" />
                <p class="description">
                    <?php echo __('Leave blank if not applicable', 'my_booking_ical_form')?>
                </p>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row">Airbnb iCal URL</th>
            <td>
                <input type="text" id="ical_airbnb_url" name="ical_airbnb_url" autocomplete="off" class="regular-text ltr" value="" />
                <p class="description">
                    <?php echo __('Leave blank if not applicable', 'my_booking_ical_form')?>
                </p>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><?php echo __('Minimum number of days that can be reserved', 'my_booking_ical_form')?></th>
            <td>
                <input type="number" id="min_days" name="min_days" min="1" class="regular-text ltr" value="<?php echo esc_attr( get_option('min_days_default') ); ?>" required />
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><?php echo __('General price', 'my_booking_ical_form')?> (<?php echo get_option('currency');?>)</th>
            <td>
            <input type="number" id="price" name="price" step="0.01" class="regular-text ltr" value="" required />
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><?php echo __('Maximum capacity', 'my_booking_ical_form')?></th>
            <td>
                <input type="number" id="max_capacity" name="max_capacity" min="1" value="1" class="regular-text ltr" value="" required />
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><?php echo __('Parking option', 'my_booking_ical_form')?></th>
            <td>
                <fieldset>
                    <p>
                        <label><input name="parking_option" type="radio" value="0" checked="checked" /> <?php echo __('No', 'my_booking_ical_form')?></label><br />
                        <label><input name="parking_option" type="radio" value="1" /> <?php echo __('Yes', 'my_booking_ical_form')?></label>
                    </p>
                </fieldset>
            </td>
        </tr>
        <tr valign="top">
            <td><input type="submit" name="submit_create_form" class="button button-primary" value="Guardar" /></td>
            <td></td>
        </tr>
    </table>
  </form>
</div>