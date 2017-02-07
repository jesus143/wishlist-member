<?php
/*
 * arpReach Autoresponder Interface
 * Original Author : Fel Jun Palawan
 * Version: $Id: integration.autoresponder.arpreach.php 2813 2015-07-29 14:30:25Z mike $
 */

$__index__ = 'arpreach';

$__ar_options__[$__index__] = 'arpReach';

$__ar_affiliates__[$__index__] = 'http://www.arpreach.com/';
//$__ar_videotutorial__[$__index__] = wlm_video_tutorial ( 'integration', 'ar', $__index__ );

if ($data['ARProvider'] == $__index__):
	if ($__INTERFACE__):
		if (function_exists('curl_init')):
			?>
			<form method="post">
				<input type="hidden" name="saveAR" value="saveAR" />
				<h2 class="wlm-integration-steps">Step 1. Copy the Post URL from arpReach List:</h2>
				<p><?php _e('arpReach uses the Subscription Form Post URL for its integrations. Follow the instructions below to get/create one:', 'wishlist-member'); ?></p>
				<ol style="margin-left:3em">
					<li><?php _e('Log into arpReach and navigate to the following section: Autoresponder > Show List. An autoresponder will need to be created if there is not one yet.', 'wishlist-member'); ?></li>
					<li><?php _e('Select Subscription Forms from the Actions column of the autoresponder.', 'wishlist-member'); ?></li>
					<li><?php _e('Create a new Subscription Form. Ensure the Form Type is set to Offer Subscribe/Unsubscribe in the Content > Display Options section.', 'wishlist-member'); ?></li>
					<li><?php _e('Select Get Form Code from the Action column once there is a Subscription Form.', 'wishlist-member'); ?></li>
					<li><?php _e('Copy the Post URL in the third line that looks like the highlighted text below once the Form Code is available:', 'wishlist-member'); ?>
						<br /><strong>...form method='post' action='<span style="background:yellow;">http://yourdomain.com/arpreach_folder/a.php/sub/1/5bylw9</span>'....</strong></li>
				</ol>
				<h2 class="wlm-integration-steps">Step 2. Assign the Membership Levels to the corresponding Post URLs:</h2>
				<p>Membership Levels can be assigned to Post URLs by pasting the URL in the corresponding field below.</p>
				<br>
				<table class="widefat">
					<thead>
						<tr>
							<th scope="col"><?php _e('Membership Level', 'wishlist-member'); ?></th>
							<th scope="col"><?php _e('Autoresponder Subscription Form Post URL', 'wishlist-member'); ?>
								<?php echo $this->Tooltip("integration-autoresponder-arpreach-url"); ?>
							</th>
							<th class="num" style="width:22em"><?php _e('Unsubscribe if Removed from Level', 'wishlist-member'); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ((array) $wpm_levels AS $levelid => $level): ?>
							<tr class="<?php echo ++$autoresponder_row % 2 ? 'alternate' : ''; ?>">
								<th scope="row"><?php echo $level['name']; ?></th>
								<td><input type="text" name="ar[postURL][<?php echo $levelid; ?>]" value="<?php echo $data[$__index__]['postURL'][$levelid]; ?>" style="width:100%" /></td>
								<?php $arUnsub = ($data[$__index__]['arUnsub'][$levelid] == 1 ? true : false); ?>
								<td class="num"><input type="checkbox" name="ar[arUnsub][<?php echo $levelid; ?>]" value="1" <?php echo $arUnsub ? "checked='checked'" : ""; ?> /></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
				<p class="submit">
					<input type="submit" class="button-primary" value="<?php _e('Update AutoResponder Settings', 'wishlist-member'); ?>" />
				</p>
			</form>
			<?php
			include_once($this->pluginDir . '/admin/tooltips/integration.autoresponder.arpreach.tooltips.php');
		else:
			?>
			<p><?php _e('arpReach requires PHP to have the CURL extension enabled.  Please contact your system administrator.', 'wishlist-member'); ?></p>
		<?php
		endif;
	endif;
endif;
?>
