<?php
/*
 * Generic Autoresponder Interface
 * Original Author : Mike Lopez
 * Version: $Id: integration.autoresponder.generic.php 2813 2015-07-29 14:30:25Z mike $
 */

$__index__ = 'generic';
$__ar_options__[$__index__] = 'Generic';
// $__ar_videotutorial__[$__index__] = wlm_video_tutorial ( 'integration', 'ar', $__index__ );

if ($data['ARProvider'] == $__index__):
	if ($__INTERFACE__):
		?>
		<form method="post">
			<h2 class="wlm-integration-steps">Step 1. Set the Subscribe and Unsubscribe Email Addresses:</h2>
			<p>The Generic AutoResponder integration simply sends an email to the Subscribe and Unsubscribe email addresses that are entered below.</p>
			<input type="hidden" name="saveAR" value="saveAR" />
			<table class="widefat">
				<thead>
					<tr>
						<th scope="col"><?php _e('Membership Level', 'wishlist-member'); ?></th>
						<th scope="col" style="width:25em"><?php _e('Subscribe Email', 'wishlist-member'); ?>
							<?php echo $this->Tooltip("integration-autoresponder-generic-tooltips-Subscribe-Email"); ?>

						</th>
						<th scope="col" style="width:25em"><?php _e('Unsubscribe Email', 'wishlist-member'); ?>
							<?php echo $this->Tooltip("integration-autoresponder-generic-tooltips-Unsubscribe-Email"); ?>

						</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ((array) $wpm_levels AS $levelid => $level): ?>
						<tr class="<?php echo ++$autoresponder_row % 2 ? 'alternate' : ''; ?>">
							<th scope="row"><?php echo $level['name']; ?></th>
							<td><input type="text" name="ar[email][<?php echo $levelid; ?>]" value="<?php echo $data['generic']['email'][$levelid]; ?>" style="width:100%" /></td>
							<td><input type="text" name="ar[remove][<?php echo $levelid; ?>]" value="<?php echo $data['generic']['remove'][$levelid]; ?>" style="width:100%" /></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			<p class="submit">
				<input type="submit" class="button-primary" value="<?php _e('Save Settings', 'wishlist-member'); ?>" />
			</p>
		</form>
		<?php
		include_once($this->pluginDir . '/admin/tooltips/integration.autoresponder.generic.tooltips.php');
	endif;
endif;
?>