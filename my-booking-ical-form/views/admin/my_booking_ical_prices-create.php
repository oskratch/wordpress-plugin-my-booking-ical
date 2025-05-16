<div class="wrap">
  <h1><?php echo __('Add Price', 'my_booking_ical_form')?></h1>
  <form method="post" action="">
    <table class="form-table">
        <tr valign="top">
          <th scope="row"><?php echo __('From', 'my_booking_ical_form')?></th>
          <td><input type="date" name="from_date" class="regular-text ltr" value="" /></td>
        </tr>
        <tr valign="top">
          <th scope="row"><?php echo __('To', 'my_booking_ical_form')?></th>
          <td><input type="date" name="to_date" class="regular-text ltr" value="" /></td>
        </tr>
        <tr valign="top">
          <th scope="row"><?php echo __('Price', 'my_booking_ical_form')?> (<?php echo get_option('currency');?>)</th>
          <td><input type="number" name="price" step="0.01" class="regular-text ltr" value="" /></td>
        </tr>
    </table> 
    <input type="hidden" name="form_id" value="<?php echo $_GET['form_id'];?>">
    <?php submit_button(); ?>
</div>