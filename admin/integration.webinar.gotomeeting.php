<?php
$webinars = $webinar_settings['gotomeeting'];
?>
<h2 class="wlm-integration-steps"><?php _e('Step 1. Ensure that First Name, Last Name and Email Address are the only required fields in the GoToWebinar settings.', 'wishlist-member'); ?></h2>
<form method="post">
	<h2 class="wlm-integration-steps">Step 2.  Assign the Membership Levels to the corresponding Webinars:</h2>
	<p>Membership Levels can be assigned to Webinars by entering the Registration URL of the webinar in the corresponding GoToWebinar Registration URL field below.</p>
	<table class="widefat">
		<thead>
			<tr>
				<th scope="col" style="max-width:40%"><?php _e('Membership Level', 'wishlist-member'); ?></th>
				<th scope="col"><?php _e('GoToWebinar<sup><small>&reg;</small></sup> Registration URL', 'wishlist-member'); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($wpm_levels AS $levelid => $level): ?>
				<tr class="<?php echo ++$webinar_row % 2 ? 'alternate' : ''; ?>">
					<th scope="row"><?php echo $level['name']; ?></th>
					<td><input style="width:100%" type="text" name="webinar[gotomeeting][<?php echo $levelid; ?>]" value="<?php echo $webinars[$levelid]; ?>" size="70" /></td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	<p class="submit">
		<input type="submit" class="button-primary" value="<?php _e('Save Settings', 'wishlist-member'); ?>" />
	</p>
</form>
<div class="integration-links"
	data-video="<?php echo wlm_video_tutorial ( 'integration', 'wb', $webinar_provider ); ?>"
	data-affiliate="http://wlplink.com/go/gotowebinar">
