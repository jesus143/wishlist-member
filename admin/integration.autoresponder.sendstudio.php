<?php
/*
 * Interspire Email Marketer Autoresponder API (formerly SendStudio)
 * Original Author : Fel Jun Palawan
 * Version: $Id: integration.autoresponder.sendstudio.php 2813 2015-07-29 14:30:25Z mike $
 */

/*
  GENERAL PROGRAM NOTES: (This script was based on Mike's Autoresponder integrations.)
  Purpose: This is the UI part of the code. This is displayed as the admin area for Interspire Email Marketer Integration in WishList Member Dashboard.
  Location: admin/
  Calling program : integration.autoresponder.php
  Logic Flow:
  1. integration.autoresponder.php displays this script (integration.autoresponder.sendstudio.php)
  and displays current or default settings
  2. on user update, this script submits value to integration.autoresponder.php, which in turn save the value
  3. after saving the values, integration.autoresponder.php call this script again with $wpm_levels contains the membership levels and $data contains the Interspire Email Marketer Integration settings for each membership level.
 */

$__index__ = 'sendstudio';
$__ar_options__[$__index__] = 'Interspire Email Marketer';
$__ar_affiliates__[$__index__] = 'http://wlplink.com/go/interspire-emkt';
//$__ar_videotutorial__[$__index__] = wlm_video_tutorial ( 'integration', 'ar', $__index__ );

if ($data['ARProvider'] == $__index__):
	if ($__INTERFACE__):
		?>
		<form method="post">
			<input type="hidden" name="saveAR" value="saveAR" />

			<h2 class="wlm-integration-steps">Enable the Interspire Email Marketing XML API:</h2>
			<ol style="margin-left:3em">
				<li>Log in to the Interspire Email Marketer account</li>
				<li>Navigate to the following section: <br>User &amp; Groups &raquo; View User Acounts &raquo; (Edit User) &raquo; Advance User Settings</li>
				<li>Check <u>Enable the XML API</u></li>
			</ol>
			<h2 class="wlm-integration-steps">Step 2. Configure the Interspire Email Marketing API Settings:</h2>
			<table class="form-table">
				<tr valign="top">
					<th scope="row"><?php _e('XML Path', 'wishlist-member'); ?></th>
					<td>
						<input type="text" name="ar[sspath]" value="<?php echo $data[$__index__]['sspath']; ?>" size="60" />
						<?php echo $this->Tooltip("integration-autoresponder-sendstudio-tooltips-XML-Path"); ?>

						<br />
						<strong><?php _e('Example: ', 'wishlist-member'); ?><a href="#" >http://www.yourdomain.com/[<i>path/to/IEM/installation</i>]/xml.php</a></strong>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><?php _e('XML Username', 'wishlist-member'); ?></th>
					<td>
						<input type="text" name="ar[ssuname]" value="<?php echo $data[$__index__]['ssuname']; ?>" size="60" />
						<?php echo $this->Tooltip("integration-autoresponder-sendstudio-tooltips-XML-Username"); ?>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><?php _e('XML Token ', 'wishlist-member'); ?></th>
					<td>
						<input type="text" name="ar[sstoken]" value="<?php echo $data[$__index__]['sstoken']; ?>" size="60" />
						<?php echo $this->Tooltip("integration-autoresponder-sendstudio-tooltips-XML-Token"); ?>
					</td>
				</tr>
			</table>
			<h2 class="wlm-integration-steps">Step 3. Assign Custom Field IDs for the First Name and Last Name:</h2>
			<ol style="margin-left:3em">
				<li>Log in to the Interspire Email Marketer account</li>
				<li>Navigate to the following section: <br>Contact Lists Tab &raquo; View Custom Fields and then click "Edit"</li>
				<li>Copy the value of the "ID" parameter from the browser URL<br>
					Example: <a href="javascript: return false;">http://www.yourdomain.com/[<i>path/to/IEM/installation</i>]/admin/index.php?Page=CustomFields&Action=Edit&id=<u><strong>2</strong></u></a> 
					<br> <i>(The number 2 is the ID in this example)</i>
				</li>
			</ol>
			<table class="form-table">
				<tr valign="top">
					<th scope="row"><?php _e('First Name:', 'wishlist-member'); ?></th>
					<td>
						<input type="text" name="ar[ssfnameid]" value="<?php echo ($data[$__index__]['ssfnameid'] == "" ? "2" : $data[$__index__]['ssfnameid']); ?>" size="10" />
						<?php echo $this->Tooltip("integration-autoresponder-sendstudio-tooltips-FName"); ?>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><?php _e('Last Name:', 'wishlist-member'); ?></th>
					<td>
						<input type="text" name="ar[sslnameid]" value="<?php echo ($data[$__index__]['sslnameid'] == "" ? "3" : $data[$__index__]['sslnameid']); ?>" size="10" />
						<?php echo $this->Tooltip("integration-autoresponder-sendstudio-tooltips-LName"); ?>
					</td>
				</tr>
			</table>
			<h2 class="wlm-integration-steps">Step 4. Assign the Membership Levels to the corresponding Interspire Email Marketing Lists:</h2>
			<p>Enter the List ID for each corresponding Membership Level below.</p>
			<p><?php _e('To get the value for the List ID field:', 'wishlist-member'); ?></p>
			<ol style="margin-left:3em">
				<li>Log in to the Interspire Email Marketer account</li>
				<li> Navigate to the following section: <br> "Contact Lists Tab" &raquo; "View Contact Lists" and edit the contact list</li>
				<li>Copy the value of the "ID" parameter from the browser URL<br>
					Example: <a href="javascript: return false;">http://www.yourdomain.com/[<i>path/to/IEM/installation</i>]admin/index.php?Page=Lists&Action=Edit&id=<u><strong>1</strong></u></a>
					<br> <i>(The number 1 is the ID in this example)</i>
				</li>
			</ol>
			<table class="widefat">
				<thead>
					<tr>
						<th scope="col"><?php _e('Membership Level', 'wishlist-member'); ?></th>
						<th scope="col"><?php _e('List ID', 'wishlist-member'); ?>
							<?php echo $this->Tooltip("integration-autoresponder-sendstudio-tooltips-Lists-Id"); ?>
						</th>
						<th class="num" style="width:22em"><?php _e('Unsubscribe if Removed from Level', 'wishlist-member'); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ((array) $wpm_levels AS $levelid => $level): ?>
						<tr class="<?php echo ++$autoresponder_row % 2 ? 'alternate' : ''; ?>">
							<th scope="row"><?php echo $level['name']; ?></th>
							<td><input type="text" name="ar[ssID][<?php echo $levelid; ?>]" value="<?php echo $data[$__index__]['ssID'][$levelid]; ?>" size="10" /></td>
							<?php $ssUnsub = ($data[$__index__]['ssUnsub'][$levelid] == 1 ? true : false); ?>
							<td class="num"><input type="checkbox" name="ar[ssUnsub][<?php echo $levelid; ?>]" value="1" <?php echo $ssUnsub ? "checked='checked'" : ""; ?> /></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<p class="submit">
			<input type="submit" class="button-primary" value="<?php _e('Save Settings', 'wishlist-member'); ?>" />
		</p>
		</form>
		<?php
		include_once($this->pluginDir . '/admin/tooltips/integration.autoresponder.sendstudio.tooltips.php');
	endif;
endif;
?>
