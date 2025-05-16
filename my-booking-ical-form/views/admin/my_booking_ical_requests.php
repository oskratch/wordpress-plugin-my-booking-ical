<div class="wrap">
  <h1><?php echo $item->title;?></h1>
  <p><?php echo __('List of requests received for this apartment.', 'my_booking_ical_form')?></p>
  <?php $table->display();?>
</div>