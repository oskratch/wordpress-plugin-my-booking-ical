<div class="wrap">
  <h1 class="wp-heading-inline"><?php echo __('Forms list', 'my_booking_ical_form')?></h1>
  <a href="/wp-admin/admin.php?page=my_booking_ical_forms_create" class="page-title-action"><?php echo __('Add New', 'my_booking_ical_form')?></a>
  <p><?php echo __('Below is a list of reservation request forms created for each apartment. Please note that a separate form needs to be created for each apartment.', 'my_booking_ical_form')?></p>
  <?php $table->display();?>
</div>