<div class="wrap">
	<h1><?php echo __('Edit Price', 'my_booking_ical_form')?></h1>
	<form method="post" action="">
		<?php wp_nonce_field('mbif_edit_price', 'mbif_nonce'); ?>
		<table class="form-table">
			<tr valign="top">
				<th scope="row"><?php echo __('From', 'my_booking_ical_form')?></th>
				<td><input type="date" name="from_date" class="regular-text ltr" value="<?php echo esc_attr($item->from_date);?>" required /></td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php echo __('To', 'my_booking_ical_form')?></th>
				<td><input type="date" name="to_date" class="regular-text ltr" value="<?php echo esc_attr($item->to_date);?>" required /></td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php echo __('Price', 'my_booking_ical_form')?> (<?php echo esc_html(get_option('currency'));?>)</th>
				<td><input type="number" name="price" step="0.01" min="0" class="regular-text ltr" value="<?php echo esc_attr($item->price);?>" required /></td>
			</tr>
		</table>
		<input type="hidden" name="id" value="<?php echo intval($_GET['id']);?>">
		<?php submit_button(); ?>
	</form>
</div>
