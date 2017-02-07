<?php
/*
 * AutoResponse Plus AutoResponder Interface
 * Original Author : Mike Lopez
 * Version: $Id: integration.autoresponder.arp.php 2813 2015-07-29 14:30:25Z mike $
 */

$__index__ = 'arp';
$__ar_options__[$__index__] = 'AutoResponse Plus';
$__ar_affiliates__[$__index__] = 'http://wlplink.com/go/arp';
//$__ar_videotutorial__[$__index__] = wlm_video_tutorial ( 'integration', 'ar', $__index__ );

if ($data['ARProvider'] == $__index__):
	if ($__INTERFACE__):
		if (function_exists('curl_init')):
			?>
			<form method="post">
				<h2 class="wlm-integration-steps">Step 1: Configure AutoResponse Plus Application URL:</h2>
				<br>
				<input type="hidden" name="saveAR" value="saveAR" />
				<table class="form-table">
					<tr valign="top">
						<th scope="row"><?php _e('ARP Application URL', 'wishlist-member'); ?></th>
						<td>
							<input type="text" name="ar[arpurl]" value="<?php echo $data['arp']['arpurl']; ?>" size="60" />
							<?php echo $this->Tooltip("integration-autoresponder-arp-tooltips-ARP-Application-URL"); ?>

							<br />
							<small><?php _e('Example:', 'wishlist-member'); ?> http://www.yourdomain.com/cgi-bin/arp3/arp3-formcapture.pl</small>
						</td>
					</tr>
				</table>
				<h2 class="wlm-integration-steps">Step 2. Assign the Membership Levels to the corresponding AutoResponse Plus Lists:</h2>
				<p>Enter the AutoResponder ID for each corresponding Membership Level below.</p>
				<p><?php _e('Follow the instructions below to locate the value for the AutoResponder ID field:', 'wishlist-member'); ?></p>
				<ol style="margin-left:3em">
					<li><?php _e('Login to the AutoResponse Plus account and view the Autoresponder list.', 'wishlist-member'); ?></li>
					<li><?php _e('Hover the mouse over any of the options in the Actions column and look at the URL in the status bar.', 'wishlist-member'); ?></li>
					<li><?php _e('The ID number will be shown in the URL and will appear as id= ', 'wishlist-member'); ?></li>
					<li><?php _e('Copy the ID number that looks like the highlighted example below:', 'wishlist-member'); ?><br /><strong>http://yourdomain.com/cgi-bin/arp3/arp3.pl?a0=aut&amp;a1=edi&amp;a2=pro&amp;<span style="background:yellow;">id=1</span></strong></li>
				</ol>

				<table class="widefat">
					<thead>
						<tr>
							<th scope="col"><?php _e('Membership Level', 'wishlist-member'); ?></th>
							<th scope="col" style="text-align:center"><?php _e('AutoResponder ID', 'wishlist-member'); ?>
								<?php echo $this->Tooltip("integration-autoresponder-arp-tooltips-Autoresponder-ID"); ?>
							</th>
							<th class="num" style="width:22em"><?php _e('Unsubscribe if Removed from Level', 'wishlist-member'); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ((array) $wpm_levels AS $levelid => $level): ?>
							<tr class="<?php echo ++$autoresponder_row % 2 ? 'alternate' : ''; ?>">
								<th scope="row"><?php echo $level['name']; ?></th>
								<td style="text-align:center"><input type="text" name="ar[arID][<?php echo $levelid; ?>]" value="<?php echo $data['arp']['arID'][$levelid]; ?>" size="10" /></td>
								<?php $arUnsub = ($data[$__index__]['arUnsub'][$levelid] == 1 ? true : false); ?>
								<td class="num"><input type="checkbox" name="ar[arUnsub][<?php echo $levelid; ?>]" value="1" <?php echo $arUnsub ? "checked='checked'" : ""; ?> /></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
				<p class="submit">
					<input type="submit" class="button-primary" value="<?php _e('Save Settings', 'wishlist-member'); ?>" />
				</p>
			</form>
			<?php
			include_once($this->pluginDir . '/admin/tooltips/integration.autoresponder.arp.tooltips.php');
		else:
			?>
			<p><?php _e('AutoResponse Plus requires PHP to have the CURL extension enabled.  Please contact your system administrator.', 'wishlist-member'); ?></p>
		<?php
		endif;
	endif;
endif;
?>
