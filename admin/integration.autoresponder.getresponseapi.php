<?php
/*
 * GetResponse (API) Autoresponder Interface
 * Original Author : Mike Lopez
 * Version: $Id: integration.autoresponder.getresponseapi.php 2813 2015-07-29 14:30:25Z mike $
 */

$__index__ = 'getresponseAPI';
$__ar_options__[$__index__] = 'GetResponse API';
$__ar_affiliates__[$__index__] = 'http://wlplink.com/go/getresponse';
$__ar_videotutorial__[$__index__] = wlm_video_tutorial ( 'integration', 'ar', $__index__ );

if ($data['ARProvider'] == $__index__):
	if ($__INTERFACE__):
		?>
		<form method="post">
			<input type="hidden" name="saveAR" value="saveAR" />
			<h2 class="wlm-integration-steps">Step 1. Configure GetResponse API Settings:</h2>
			<p>API Credentials are located in the GetResponse account in the following section: <br> My Account > Account Details > Use GetResponse API</p>
			<table class="form-table">
				<tr valign="top">
					<th scope="row"><?php _e('GetResponse API URL', 'wishlist-member'); ?></th>
					<td nowrap>
						<?php $api_url = !empty($data['getresponseAPI']['api_url'])? $data['getresponseAPI']['api_url']: 'http://api2.getresponse.com';?>
						<input type="text" name="ar[api_url]" value="<?php echo $api_url ?>" size="60" />
						<?php echo $this->Tooltip("integration-autoresponder-getresponseapi-tooltips-GetResponse-API-URL"); ?>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><?php _e('GetResponse API Key', 'wishlist-member'); ?></th>
					<td nowrap>
						<input type="text" name="ar[apikey]" value="<?php echo $data['getresponseAPI']['apikey']; ?>" size="60" />
						<?php echo $this->Tooltip("integration-autoresponder-getresponseapi-tooltips-GetResponse-API-Key"); ?>
					</td>
				</tr>
			</table>
			<h2 class="wlm-integration-steps">Step 2. Assign the Membership Levels to the corresponding Campaign Name:</h2>
			<p>Membership Levels can be assigned to email lists by selecting a Campaign Name from the corresponding column below.</p>
			<table class="widefat">
				<thead>
					<tr>
						<th scope="col" width="250"><?php _e('Membership Level', 'wishlist-member'); ?></th>
						<th scope="col" width="1"><?php _e('Campaign Name', 'wishlist-member'); ?>
							<?php echo $this->Tooltip("integration-autoresponder-getresponseapi-tooltips-Campaign-Name"); ?>
						</th>
						<th class="num" style="width:22em"><?php _e('Unsubscribe if Removed from Level', 'wishlist-member'); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ((array) $wpm_levels AS $levelid => $level): ?>
						<tr class="<?php echo ++$autoresponder_row % 2 ? 'alternate' : ''; ?>">
							<th scope="row"><?php echo $level['name']; ?></th>
							<td><input type="text" name="ar[campaign][<?php echo $levelid; ?>]" value="<?php echo $data['getresponseAPI']['campaign'][$levelid]; ?>" size="40" /></td>
							<?php $grUnsub = ($data[$__index__]['grUnsub'][$levelid] == 1 ? true : false); ?>
							<td class="num"><input type="checkbox" name="ar[grUnsub][<?php echo $levelid; ?>]" value="1" <?php echo $grUnsub ? "checked='checked'" : ""; ?> /></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			<p class="submit">
				<input type="submit" class="button-primary" value="<?php _e('Save Settings', 'wishlist-member'); ?>" />
			</p>
		</form>
		<?php
		include_once($this->pluginDir . '/admin/tooltips/integration.autoresponder.getresponseapi.tooltips.php');
	endif;
endif;
?>