<?php
/*
 * GetResponse Autoresponder API
 * Original Author : Mike Lopez
 * Version: $Id: integration.autoresponder.getresponse.php 2813 2015-07-29 14:30:25Z mike $
 */

$__index__ = 'getresponse';
$__ar_options__[$__index__] = 'GetResponse';
$__ar_affiliates__[$__index__] = 'http://wlplink.com/go/getresponse';
//$__ar_videotutorial__[$__index__] = wlm_video_tutorial ( 'integration', 'ar', $__index__ );

if ($data['ARProvider'] == $__index__):
	if ($__INTERFACE__):
		?>
		<form method="post">
			<input type="hidden" name="saveAR" value="saveAR" />
			<table class="widefat">
				<thead>
					<tr>
						<th scope="col"><?php _e('Membership Level', 'wishlist-member'); ?></th>
						<th scope="col"><?php _e('Autoresponder Email', 'wishlist-member'); ?>
							<?php echo $this->Tooltip("integration-autoresponder-getresponse-tooltips-Autoresponder-Email"); ?>

						</th>
						<th scope="col"><?php _e('Unsubscribe Email', 'wishlist-member'); ?>
							<?php echo $this->Tooltip("integration-autoresponder-getresponse-tooltips-Unsubscribe-Email"); ?>

						</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ((array) $wpm_levels AS $levelid => $level): ?>
						<tr class="<?php echo ++$autoresponder_row % 2 ? 'alternate' : ''; ?>">
							<th scope="row"><?php echo $level['name']; ?></th>
							<td><input type="text" name="ar[email][<?php echo $levelid; ?>]" value="<?php echo $data['getresponse']['email'][$levelid]; ?>" size="40" /></td>
							<td><input type="text" name="ar[remove][<?php echo $levelid; ?>]" value="<?php echo $data['getresponse']['remove'][$levelid]; ?>" size="40" /></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			<p class="submit">
				<input type="submit" class="button-primary" value="<?php _e('Save Settings', 'wishlist-member'); ?>" />
			</p>
		</form>
		<?php
		include_once($this->pluginDir . '/admin/tooltips/integration.autoresponder.getresponse.tooltips.php');
	endif;
endif;
?>