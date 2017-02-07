<?php
$webinars = $webinar_settings['evergreen'];
?>
<h2 class="wlm-integration-steps"><?php _e('Step 1. Ensure that First Name, Last Name and Email Address are the only required fields in the Evergreen Business System webinar settings.', 'wishlist-member'); ?></h2>
<h2 class="wlm-integration-steps">Step 2.  Assign the Membership Levels to the corresponding Webinars:</h2>
<p>Membership Levels can be assigned to Webinars by entering the Registration URL of the webinar in the corresponding Auto Registration Link field below.</p>
<form method="post">
	<table class="widefat">
		<thead>
			<tr>
				<th scope="col" style="max-width:40%"><?php _e('Membership Level', 'wishlist-member'); ?></th>
				<th scope="col"><?php _e('Auto Registration Link', 'wishlist-member'); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($wpm_levels AS $levelid => $level): ?>
				<tr class="<?php echo ++$webinar_row % 2 ? 'alternate' : ''; ?>">
					<th scope="row"><?php echo $level['name']; ?></th>
					<td><input style="width:100%" type="text" name="webinar[evergreen][<?php echo $levelid; ?>]" value="<?php echo $webinars[$levelid]; ?>" size="70" /></td>
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
	data-affiliate="http://evergreenbusinesssystem.com/">
