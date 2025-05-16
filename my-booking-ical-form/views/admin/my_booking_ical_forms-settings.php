<div class="wrap">
  <h1><?php echo __('My Booking iCal Forms Settings', 'my_booking_ical_form')?></h1>
  <form method="post" action="">
    <table class="form-table">
        <tr valign="top">
          <th scope="row"><?php echo __('Send an email notifying of a new request', 'my_booking_ical_form')?></th>
          <td>
            <fieldset>
              <p>
                <label><input name="mbif_emailto_enable" type="radio" value="0"<?php echo !get_option('mbif_emailto_enable') ? " checked='checked'" : ""; ?> /> <?php echo __('No', 'my_booking_ical_form')?></label><br />
                <label><input name="mbif_emailto_enable" type="radio" value="1"<?php echo get_option('mbif_emailto_enable') ? " checked='checked'" : ""; ?> /> <?php echo __('Yes', 'my_booking_ical_form')?></label>
              </p>
            </fieldset>
          </td>
        </tr>
        <tr id="z_emailto" valign="top"<?php echo !get_option('mbif_emailto_enable') ? " class='hidden'" : ""; ?>>
          <th scope="row"><?php echo __('Email where to send received requests', 'my_booking_ical_form')?></th>
          <td><input type="email" name="mbif_emailto" class="regular-text ltr" value="<?php echo esc_attr( get_option('mbif_emailto') ); ?>" /></td>
        </tr>
        <tr id="z_emailto_secondary" valign="top"<?php echo !get_option('mbif_emailto_enable') ? " class='hidden'" : ""; ?>>
          <th scope="row"><?php echo __('Secondary email where to send received requests (optional)', 'my_booking_ical_form')?></th>
          <td><input type="email" name="mbif_emailto_secondary" class="regular-text ltr" value="<?php echo esc_attr( get_option('mbif_emailto_secondary') ); ?>" /></td>
        </tr>
    </table>   
    <h2 class="title"><?php echo __('Forms', 'my_booking_ical_form')?></h2>
    <p><?php echo __('Below, you can make adjustments related to the reservation request form that will be visible to the user on the website.', 'my_booking_ical_form')?></p>
    <table class="form-table">
        <tr valign="top">
          <th scope="row"><?php echo __('Show form label tags', 'my_booking_ical_form')?></th>
          <td>
            <fieldset>
              <p>
                <label><input name="mbif_label_shown" type="radio" value="0"<?php echo !get_option('mbif_label_shown') ? " checked='checked'" : ""; ?> /> <?php echo __('No', 'my_booking_ical_form')?></label><br />
                <label><input name="mbif_label_shown" type="radio" value="1"<?php echo get_option('mbif_label_shown') ? " checked='checked'" : ""; ?> /> <?php echo __('Yes', 'my_booking_ical_form')?></label>
              </p>
              <p class="description">
                <?php echo __('The placeholder attribute will be used to add field information when the form label is not visible.', 'my_booking_ical_form')?>
              </p>
            </fieldset>
          </td>
        </tr>
        <tr valign="top">
          <th scope="row"><label for="min_days_default"><?php echo __('Minimum number of days that can be reserved.', 'my_booking_ical_form')?></label></th>
          <td>
            <input type="number" min="1" id="min_days_default" name="min_days_default" class="regular-text ltr" value="<?php echo esc_attr( get_option('min_days_default') ); ?>" />
            <p class="description">
              <?php echo __('Each apartment has the option to customize this value.', 'my_booking_ical_form')?>
            </p>
          </td>
        </tr>
        <tr valign="top">
          <th scope="row"><label for="currency"><?php echo __('Currency', 'my_booking_ical_form')?></label></th>
          <td>
            <select name="currency" id="currency">
              <option<?php echo get_option('currency') == '€' ? " selected='selected'" : ""; ?> value='€'>Euro €</option>
              <option<?php echo get_option('currency') == '$' ? " selected='selected'" : ""; ?> value='$'>Dollar $</option>
            </select>
          </td>   
        </tr>
    </table>   
    <input type="hidden" name="settings" value="1">
    <?php submit_button(); ?>
  </form>
</div>