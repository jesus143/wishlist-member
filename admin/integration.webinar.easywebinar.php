<?php
$webinars = $webinar_settings['easywebinar'];
$webinar_list = array();

if(class_exists('webinar_db_interaction')) {
	$wdb = new webinar_db_interaction();
	$webinar_list = $wdb->get_all_webinar();
}
?>
<form method="post">
	<h2 class="wlm-integration-steps">Step 1. Assign the Membership Levels to the corresponding Webinars:</h2>
	<p>Membership Levels can be assigned to Webinars by selecting a webinar from the corresponding column below.</p>
	<table class="widefat">
		<thead>
			<tr>
				<th scope="col" style="max-width:40%"><?php _e('Membership Level', 'wishlist-member'); ?></th>
				<th scope="col"><?php _e('Webinar', 'wishlist-member'); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($wpm_levels AS $levelid => $level): ?>
				<tr class="<?php echo ++$webinar_row % 2 ? 'alternate' : ''; ?>">
					<th scope="row"><?php echo $level['name']; ?></th>
					<td>
						<select name="webinar[easywebinar][<?php echo $levelid; ?>]">
							<option value="">--Select a webinar--</option>
							<?php foreach($webinar_list as $w): ?>
							<?php $selected=$webinars[$levelid]==$w->webinar_id_pk? 'selected="selected"' : null ?>
							<option <?php echo $selected?> value="<?php echo $w->webinar_id_pk?>"><?php echo $w->webinar_event_name?></option>
							<?php endforeach; ?>
						</select>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	<p class="submit">
		<input type="submit" class="button-primary" value="<?php _e('Save Settings', 'wishlist-member'); ?>" />
	</p>
</form>

<div class="integration-links"
	data-video=""
	data-affiliate="http://easywebinar.com/">
