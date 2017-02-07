<?php
/*
 * Drip AutoResponder Interface
 * Drip Site: http://www.drip.co/
 * Original Integration Author : Mike Lopez
 * Version: $Id$
 */

$__index__ = 'drip';
$__ar_options__[$__index__] = 'Drip';
// $__ar_videotutorial__[$__index__] = wlm_video_tutorial ( 'integration', 'ar', $__index__ );

if ($data['ARProvider'] == $__index__):
	if ($__INTERFACE__):
		$dripdata = &$data[$__index__];
		require_once $this->pluginDir . '/extlib/wlm_drip/Drip_API.class.php';

		$drip_api = false;

		$accounts = array();
		$campaigns_options = array();
		if(!empty($dripdata['apitoken'])) {
			$drip_api = new WLM_Drip_Api($dripdata['apitoken']);
			$x = get_transient( 'wlm_drip_ar_campaigns' );
			if(!$x) {
				$x = $drip_api->get_accounts();
				if($x) {
					set_transient( 'wlm_drip_ar_campaigns', $x, MINUTE_IN_SECONDS * 5 );
				}
			}
			foreach($x AS $account) {
				$account['campaigns'] = $drip_api->get_campaigns(array('account_id' => $account['id']));
				$accounts[$account['id']] = $account;
				foreach($account['campaigns'] AS $campaign) {
					$campaigns_options[] = sprintf('<option value="%s-%s">%s - %s</option>', $account['id'], $campaign['id'], $account['name'], $campaign['name']);
				}
			}
		}
		?>
		<form method="post">
			<h2 class="wlm-integration-steps"><?php _e('Step 1. Configure Drip API Settings:','wishlist-member'); ?></h2>
			<p>
				<?php _e('Your API Token is located in your Drip account in the following section:','wishlist-member'); ?><br>
				<?php _e('Settings > My User Settings > API Token', 'wishlist-member'); ?>
			</p>
			<table class="form-table">
				<tr valign="top">
					<th scope="row">
						<?php _e('Drip API Token', 'wishlist-member'); ?>
						<?php echo $this->Tooltip("integration-autoresponder-drip-tooltips-Drip-API-Token"); ?>
					</th>
					<td nowrap>
						<input type="text" name="ar[apitoken]" value="<?php echo $dripdata['apitoken']; ?>" size="32">
					</td>
				</tr>
			</table>
			<?php if(!empty($accounts)) : ?>
				<h2 class="wlm-integration-steps"><?php _e('Step 2. Assign the Membership Levels to the corresponding Campaign:','wishlist-member'); ?></h2>
				<p><?php _e('Membership Levels can be assigned to email lists by selecting a Campaign from the corresponding column below.','wishlist-member'); ?></p>
				<table class="widefat">
					<thead>
						<tr>
							<th scope="col" width="250"><?php _e('Membership Level', 'wishlist-member'); ?></th>
							<th scope="col" width="1">
								<?php _e('Campaign', 'wishlist-member'); ?>
								<?php echo $this->Tooltip("integration-autoresponder-drip-tooltips-Campaign"); ?>
							</th>
							<th class="num" xstyle="white-space:nowrap">
								<?php _e('Double Opt-in', 'wishlist-member'); ?>
								<?php echo $this->Tooltip("integration-autoresponder-drip-tooltips-Double-Opt-In"); ?>
							</th>
							<th class="num" style="width:22em"><?php _e('Unsubscribe if Removed from Level', 'wishlist-member'); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ((array) $wpm_levels AS $levelid => $level): ?>
							<tr>
								<th scope="row"><?php echo $level['name']; ?></th>
								<td>
									<select name="ar[campaign][<?php echo $levelid; ?>]">
										<option value="0"><?php _e('Select a Campaign', 'wishlist-member'); ?></option>
										<?php 
											echo preg_replace('#value="'.$dripdata['campaign'][$levelid].'"#', 'selected="selected" \0', implode('', $campaigns_options)); 
										?>
									</select>
								</td>
								<?php $double = ($dripdata['double'][$levelid] == 1 ? true : false); ?>
								<td class="num"><input type="checkbox" name="ar[double][<?php echo $levelid; ?>]" value="1" <?php echo $double ? "checked='checked'" : ""; ?> /></td>
								<?php $unsub = ($dripdata['unsub'][$levelid] == 1 ? true : false); ?>
								<td class="num"><input type="checkbox" name="ar[unsub][<?php echo $levelid; ?>]" value="1" <?php echo $unsub ? "checked='checked'" : ""; ?> /></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>
			<p class="submit">
				<input type="submit" class="button-primary" value="<?php _e('Save Settings', 'wishlist-member'); ?>" />
			</p>
			<input type="hidden" name="saveAR" value="saveAR" />
		</form>
		<?php
		include_once($this->pluginDir . '/admin/tooltips/integration.autoresponder.drip.tooltips.php');
	endif;
endif;
?>