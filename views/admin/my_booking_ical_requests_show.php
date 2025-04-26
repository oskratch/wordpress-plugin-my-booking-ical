<div class="wrap">
	<h1><?php echo $form->title;?></h1>
	<p><?php echo sprintf(__('Request received on %s at %s', 'my_booking_ical_form'), date("d-m-Y", strtotime($item->created_at)), date("H:i", strtotime($item->created_at)));?></p>
	<h2 class="title"><?php echo __('Status', 'my_booking_ical_form')?></h2>
	<form method="post" action="">		
		<select name="status">
			<option value="pending_review"<?php echo $item->status == 'pending_review' ? " selected" : ""; ?>><?php echo __('Pending review', 'my_booking_ical_form');?></option>
			<option value="validated"<?php echo $item->status == 'validated' ? " selected" : ""; ?>><?php echo __('Validated', 'my_booking_ical_form');?></option>
			<option value="denied"<?php echo $item->status == 'denied' ? " selected" : ""; ?>><?php echo __('Denied', 'my_booking_ical_form');?></option>
		</select>
		<input type="hidden" name="id" value="<?php echo $_GET['id'];?>">
		<?php submit_button(); ?>
	</form>
    <h2 class="title"><?php echo __('Data', 'my_booking_ical_form')?></h2>
	<ul class="data-booking">
		<li>
			<strong><?php echo __('Reference', 'my_booking_ical_form');?></strong>: <?php echo createReferenceRequest($form->reference, $item->entry_date, $item->id);?>
		</li>
		<li>
			<strong><?php echo __('Entry date', 'my_booking_ical_form');?></strong>: <?php echo date("d-m-Y", strtotime($item->entry_date));?>
		</li>
		<li>
			<strong><?php echo __('Departure date', 'my_booking_ical_form');?></strong>: <?php echo date("d-m-Y", strtotime($item->departure_date));?>
		</li>
		<li>
			<strong><?php echo __('First Name', 'my_booking_ical_form');?></strong>: <?php echo $item->first_name;?>
		</li>
		<li>
			<strong><?php echo __('Last Name', 'my_booking_ical_form');?></strong>: <?php echo $item->last_name;?>
		</li>
		<li>
			<strong><?php echo __('Email', 'my_booking_ical_form');?></strong>: <a href="mailto:<?php echo $item->email;?>"><?php echo $item->email;?></a>
		</li>
		<li>
			<strong><?php echo __('Parking', 'my_booking_ical_form');?></strong>: <?php echo $item->parking ? __('Yes', 'my_booking_ical_form') : __('No', 'my_booking_ical_form');?>
		</li>
		<li>
			<strong><?php echo __('Guests', 'my_booking_ical_form');?></strong>: <?php echo $item->guest_count;?>
		</li>
		<li>
			<strong><?php echo __('Comments', 'my_booking_ical_form');?></strong>: <?php echo $item->comments;?>
		</li>
	</ul>
	<?php if($item->summary){?>
	<h2 class="title"><?php echo __('Request summary', 'my_booking_ical_form')?></h2>
	<p><?php echo $item->summary;?></p>
	<?php }?>
	<div style="margin-top:20px;">
		<a href="/wp-admin/admin.php?page=my_booking_ical_requests&form_id=<?php echo $item->form_id;?>" class="page-title-action"><?php echo __('Back', 'my_booking_ical_form');?></a>
	</div>
</div>