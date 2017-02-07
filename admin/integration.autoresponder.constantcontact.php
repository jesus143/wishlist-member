<?php
/*
 * ConstantContact Autoresponder API
 * Original Author : Fel Jun Palawan
 * Version: $Id: integration.autoresponder.constantcontact.php
 */

/*
  GENERAL PROGRAM NOTES: (This script was based on Mike's Autoresponder integrations.)
  Purpose: This is the UI part of the code. This is displayed as the admin area for ConstantContact Integration in WishList Member Dashboard.
  Location: admin/
  Calling program : integration.autoresponder.php
  Logic Flow:
  1. integration.autoresponder.php displays this script (integration.autoresponder.ConstantContact.php)
  and displays current or default settings
  2. on user update, this script submits value to integration.autoresponder.php, which in turn save the value
  3. after saving the values, integration.autoresponder.php call this script again with $wpm_levels contains the membership levels and $data contains the ConstantContact Integration settings for each membership level.
 */

$__index__ = 'constantcontact';
$__ar_options__[$__index__] = 'Constant Contact';
$__ar_videotutorial__[$__index__] = wlm_video_tutorial ( 'integration', 'ar', $__index__ );
$__ar_affiliates__[$__index__] = 'http://wlplink.com/go/constant-contact';

if ($data['ARProvider'] == $__index__):
	$ccerror = "";
	$ccinfo = "";
	require_once($this->pluginDir . '/extlib/ConstantContact.php');
	if ($data[$__index__]['ccusername'] != "" && $data[$__index__]['ccpassword'] != "") {
		$new_cc = New ConstantContact($data[$__index__]['ccusername'], $data[$__index__]['ccpassword']);


		if (is_object($new_cc) && $new_cc->get_service_description()) {
			if (!is_object($new_cc)) {
				$ccerror = "<p>An unknown error occured. Please contact support.</p>";
			}
			// Otherwise, if there is a response code, deal with the connection error
		} elseif (is_object($new_cc) AND isset($new_cc->http_response_code)) {
			$error = $new_cc->http_get_response_code_error($new_cc->http_response_code);
			$ccerror = $error;
		}

		if ($ccerror == "") { //if no error was found
			$lists = $new_cc->get_all_lists();
		}
	} else {
		$ccinfo = '<p>Note: Ensure that the Constant Contact account Username and Password have been entered in Step 1 above before assigning Membership Levels to Email Lists.</p>';
	}
	if ($__INTERFACE__):
		?>
		<form method="post">
			<input type="hidden" name="saveAR" value="saveAR" />
			<h2 class="wlm-integration-steps">Step 1. Enter the Constant Contact Login Information:</h2>
			<?php if ($ccerror != ""): ?>
				<div id='wlm-constant-contact-warning' class='error fade'>
					<?php echo $ccerror; ?>
				</div>
			<?php endif; ?>
			<table class="form-table">
				<tr valign="top">
					<th scope="row"><?php _e('Username: ', 'wishlist-member'); ?></th>
					<td>
						<input style="float:left;" type="text" name="ar[ccusername]" value="<?php echo $data[$__index__]['ccusername']; ?>" size="30" />
						<?php echo $this->Tooltip("integration-autoresponder-constantcontact-tooltips-username"); ?>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><?php _e('Password: ', 'wishlist-member'); ?></th>
					<td>
						<input style="float:left;" type="password" name="ar[ccpassword]" value="<?php echo $data[$__index__]['ccpassword']; ?>" size="30" />
						<?php echo $this->Tooltip("integration-autoresponder-constantcontact-tooltips-password"); ?>
					</td>
				</tr>
			</table>
			<h2 class="wlm-integration-steps">Step 2. Assign the Membership Levels to the corresponding Constant Contact Lists:</h2>
			<p>Membership Levels can be assigned to Email Lists by selecting a List from the corresponding column below.</p>
			<?php if ($ccinfo != ""): ?>
				<?php echo $ccinfo; ?>
			<?php endif; ?>
			<table class="widefat">
				<thead>
					<tr>
						<th scope="col"><?php _e('Membership Level', 'wishlist-member'); ?></th>
						<th scope="col"><?php _e('List', 'wishlist-member'); ?>
							<?php echo $this->Tooltip("integration-autoresponder-constantcontact-tooltips-list"); ?>
						</th>
						<th class="num" style="width:22em"><?php _e('Unsubscribe if Removed from Level', 'wishlist-member'); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ((array) $wpm_levels AS $levelid => $level): ?>
						<tr class="<?php echo ++$autoresponder_row % 2 ? 'alternate' : ''; ?>">
							<th scope="row"><?php echo $level['name']; ?></th>
							<td>
								<?php if (!empty($lists)): ?>
									<select name="ar[ccID][<?php echo $levelid; ?>]">
										<option value="" <?php echo $data[$__index__]['ccID'][$levelid] == "" ? "selected='selected'" : ""; ?>>- Select a List -</option>
										<?php foreach ((array) $lists AS $listsid => $list): ?>
											<option value="<?php echo $list['id']; ?>" <?php echo $data[$__index__]['ccID'][$levelid] == $list['id'] ? "selected='selected'" : ""; ?>><?php echo $list['Name']; ?></option>
										<?php endforeach; ?>
									</select>
								<?php else: ?>
									<select multiple class="chosen-select" style="width: 150px;" data-placeholder="-- List Empty --">
									</select>
								<?php endif; ?>
							</td>
							<?php $ccUnsub = ($data[$__index__]['ccUnsub'][$levelid] == 1 ? true : false); ?>
							<td class="num"><input type="checkbox" name="ar[ccUnsub][<?php echo $levelid; ?>]" value="1" <?php echo $ccUnsub ? "checked='checked'" : ""; ?> /></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			<p class="submit">
				<input type="submit" class="button-primary" value="<?php _e('Update ConstantContact Settings', 'wishlist-member'); ?>" />
			</p>
		</form>
		<?php
		include_once($this->pluginDir . '/admin/tooltips/integration.autoresponder.constantcontact.tooltips.php');
	endif;
endif;
?>

<script type="text/javascript">
		jQuery(function($) {
			$('.chosen-select').chosen({disable_search: true});
		});
		</script>
		<style type="text/css">
		.chosen-container-multi .chosen-choices {
			background-color: #fff;
			  background-image: -webkit-gradient(linear, 50% 0%, 50% 100%, color-stop(1%, #eeeeee), color-stop(15%, #ffffff));
			  background-image: -webkit-linear-gradient(#eeeeee 1%, #ffffff 15%);
			  background-image: -moz-linear-gradient(#eeeeee 1%, #ffffff 15%);
			  background-image: -o-linear-gradient(#eeeeee 1%, #ffffff 15%);
			  background-image: linear-gradient(#eeeeee 1%, #ffffff 15%);
		}
		</style>
