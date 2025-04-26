<div class="wrap nosubsub">
  <h1 class="wp-heading-inline"><?php echo __('Edit Form', 'my_booking_ical_form')?></h1>
  <div id="col-container" class="wp-clearfix">
    <div id="col-left">
      <div class="col-wrap">
        <div class="form-wrap">
          	<form method="post" action="">            
				<table class="form-table">
					<tr valign="top">
						<th scope="row"><?php echo __('Apartment name', 'my_booking_ical_form')?></th>
						<td>
							<input type="text" id="title" name="title" autocomplete="off" class="regular-text ltr" value="<?php echo $item->title;?>" required />
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php echo __('Reference', 'my_booking_ical_form')?></th>
						<td>
							<input type="text" id="reference" name="reference" autocomplete="off" class="regular-text ltr" value="<?php echo $item->reference;?>" required />
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">Booking iCal URL</th>
						<td>
							<input type="text" id="ical_booking_url" name="ical_booking_url" autocomplete="off" class="regular-text ltr" value="<?php echo $item->ical_booking_url;?>" />
							<p class="description">
								<?php echo __('Leave blank if not applicable', 'my_booking_ical_form')?>
							</p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">Airbnb iCal URL</th>
						<td>
							<input type="text" id="ical_airbnb_url" name="ical_airbnb_url" autocomplete="off" class="regular-text ltr" value="<?php echo $item->ical_airbnb_url;?>" />
							<p class="description">
								<?php echo __('Leave blank if not applicable', 'my_booking_ical_form')?>
							</p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php echo __('Minimum number of days that can be reserved', 'my_booking_ical_form')?></th>
						<td>
							<input type="number" id="min_days" name="min_days" min="1" class="regular-text ltr" value="<?php echo $item->min_days;?>" required />
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php echo __('General price', 'my_booking_ical_form')?> (<?php echo get_option('currency');?>)</th>
						<td>
						<input type="number" id="price" name="price" step="0.01" class="regular-text ltr" value="<?php echo $item->price;?>" required />
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php echo __('Maximum capacity', 'my_booking_ical_form')?></th>
						<td>
							<input type="number" id="max_capacity" name="max_capacity" min="1" class="regular-text ltr" value="<?php echo $item->max_capacity;?>" required />
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php echo __('Parking option', 'my_booking_ical_form')?></th>
						<td>
							<fieldset>
								<p>
									<label><input name="parking_option" type="radio" value="0"<?php echo !$item->parking_option ? " checked='checked'" : ""; ?> /> <?php echo __('No', 'my_booking_ical_form')?></label><br />
									<label><input name="parking_option" type="radio" value="1"<?php echo $item->parking_option ? " checked='checked'" : ""; ?> /> <?php echo __('Yes', 'my_booking_ical_form')?></label>
								</p>
							</fieldset>
						</td>
					</tr>
					<tr valign="top">
						<td>
							<input type="hidden" name="id" value="<?php echo $item->id;?>">
							<input type="submit" name="submit" id="submit" class="button button-primary" value="<?php echo __('Save', 'my_booking_ical_form')?>" />		
							<span class="spinner"></span>
						</td>
						<td></td>
					</tr>
				</table>
			</form>
        </div>
      </div>
    </div>
    <div id="col-right" class="table-nopagination">
      <div class="col-wrap">
        <h2><?php echo __('Special prices between dates', 'my_booking_ical_form')?></h2>
        <?php $table->display();?>
        <div class="alignleft actions">
          <a href="/wp-admin/admin.php?page=my_booking_ical_prices_create&form_id=<?php echo $_GET['id'];?>" class="page-title-action"><?php echo __('Add prices', 'my_booking_ical_form');?></a>
        </div>
      </div>
    </div>
  </div>
</div>