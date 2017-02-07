<?php
/*
 * iContact Autoresponder API
 * Original Author : Fel Jun Palawan
 * Version: $Id: integration.autoresponder.icontact.php 3007 2016-04-12 13:36:46Z mike $
 */

/*
  GENERAL PROGRAM NOTES: This script was based on Mike's autoresponder integration.
  Calling program : integration.autoresponder.php
  Logic Flow:
  1. integration.autoresponder.php displays this script (integration.autoresponder.icontact)
  and displays current settings
  2. on user update, this script submits value to integration.autoresponder.php, which in turn save the value
  3. after saving the values, control goes back to this page, and:
  3.1 this script do a curl request to iContact to get the AccountID from iContact then;
  3.2 do a curl request to iContact to get the FolderID from iContact
  3.3 save these two IDs (Account ID & Folder ID) to WL options using the SaveOption() function.

  Account ID & Folder ID are needed to make request to iContact for subscribing & unsubscribing contacts
 */

$__index__ = 'icontact';
$__ar_options__[$__index__] = 'iContact';
//$__ar_videotutorial__[$__index__] = wlm_video_tutorial ( 'integration', 'ar', $__index__ );

if ($data['ARProvider'] == $__index__):
	if ($__INTERFACE__):
		?>
		<?php
//after user saves the autoresponder options, script will get AccountID & FolderID
		//this part will attempt to get the AccountID from iContact
		$icdata = $_POST['ar'];
		$icUserName = $data[$__index__]['icusername']; //$icdata['icusername'];
		$icAppPassword = $data[$__index__]['icapipassword']; //$icdata['icapipassword'];
		$icAppID = $data[$__index__]['icapiid']; //$icdata['icapiid'];

		if (!function_exists("curl_init")) {
			die("cURL extension is not installed");
		}
		$headers = array(
			'Accept: text/xml',
			'Content-Type: text/xml',
			'API-Version: 2.0',
			'API-AppId: ' . $icAppID,
			'API-Username: ' . $icUserName,
			'API-Password: ' . $icAppPassword,
		);
		$url = "https://app.icontact.com/icp/a/";
		$ch1 = curl_init();
		curl_setopt($ch1, CURLOPT_URL, $url);
		curl_setopt($ch1, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch1, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch1, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch1, CURLOPT_SSL_VERIFYPEER, false);
		$icresponse = curl_exec($ch1);
		curl_close($ch1);
		preg_match('/<accountId(.*)?>(.*)?<\/accountId>/', $icresponse, $match);
		$icAcctID = $match[2];
		if (!empty($icAcctID)) { // get  the Account ID
			if (!function_exists("curl_init"))
				die("cURL extension is not installed");
			$url = "https://app.icontact.com/icp/a/{$icAcctID}/c";
			$ch2 = curl_init();
			curl_setopt($ch2, CURLOPT_URL, $url);
			curl_setopt($ch2, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch2, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($ch2, CURLOPT_SSL_VERIFYPEER, false);
			$icresponse = curl_exec($ch2);

			// Don't use simplexml_load_string if allow_url_fopen is disabled on the server. This prevents the page from going blank if it's disabled
			if( ini_get('allow_url_fopen') ) 
				$xml_data = simplexml_load_string($icresponse);

			curl_close($ch2);
			preg_match('/<clientFolderId(.*)?>(.*)?<\/clientFolderId>/', $icresponse, $match);
			$icFolderID = $match[2];
		}

		$iclog = $data[$__index__]['iclog'];
		$icID = $data[$__index__]['icID'];
		foreach ((array) $iclog as $key => $value) {
			if ($value == 1 and $icID[$key] != "") {
				$date = date("F j, Y, h:i:s A");
				$logfile = ABSPATH . $icID[$key] . ".txt";
				if (file_exists($logfile)) {
					$logfilehandler = fopen($logfile, 'a');
				} else {
					$logfilehandler = fopen($logfile, 'w');
				}
				if (!$logfilehandler) {
					echo "<div class='error fade'>" . __('<p>Error Creating Log File. Please check folder permission or manually create the file ' . ABSPATH . $logfile . '</p>', 'wishlist-member') . "</div>";
				} else {
					fclose($logfilehandler);
				}
			}
		}

		if (isset($_GET['action']) == 'clear' && isset($_GET['level']) != "" && !isset($_POST['update_icontact'])) {
			$logfile = ABSPATH . $icID[wlm_arrval($_GET,'level')] . ".txt";
			if (file_exists($logfile)) {
				$logfilehandler = fopen($logfile, 'w');
			}
			if (!$logfilehandler) {
				echo "<div class='error fade'>" . __('<p>Error Clearing Log File. Please check folder permission or manually clear the file ' . ABSPATH . $logfile . '</p>', 'wishlist-member') . "</div>";
			} else {
				echo "<div class='error fade'>" . __('<p>Successfully cleared the file ' . ABSPATH . $logfile . '</p>', 'wishlist-member') . "</div>";
				fclose($logfilehandler);
			}
		}
		?>
		<form method="post">
			<input type="hidden" name="saveAR" value="saveAR" />
			<h2 class="wlm-integration-steps"><?php _e('Step 1. Create iContact Integration Password:', 'wishlist-member'); ?></h2>
			<ol style="margin-left:3em">
					<li><?php _e('Copy and paste the following into a new tab:  ', 'wishlist-member'); ?> <a href="https://app.icontact.com/icp/core/externallogin" target="_blank">https://app.icontact.com/icp/core/externallogin</a></li>
					<li><?php _e('Login with the iContact account Username and Password.', 'wishlist-member'); ?></li>
					<li><?php _e('Enter <strong style="background:yellow;font-size:1.2em">60ZU6Al45lBtMmpi1S8tJqsvXdrNK18H</strong> as the Application ID field.', 'wishlist-member'); ?></li>
					<li><?php _e('Enter the desired Application Password.'); ?><br>
					<?php _e('<span style="font-style:italic;">Note: This password will be used in Step 2 below. It is recommend that this password be different than the iContact password for security reasons.</span>', 'wishlist-member'); ?></li>
					<li><?php _e('Click Save.', 'wishlist-member'); ?></li>
				</ol>
			<h2 class="wlm-integration-steps"><?php _e('Step 2. Generate Account and Folder ID:', 'wishlist-member'); ?></h2>
			<table class="form-table">
				<tr valign="top">
					<th scope="row"><?php _e('iContact Username: ', 'wishlist-member'); ?></th>
					<td>
						<input type="text" name="ar[icusername]" value="<?php echo $data[$__index__]['icusername']; ?>" size="40" />
						<?php echo $this->Tooltip("integration-autoresponder-icontact-tooltips-get-username"); ?>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><?php _e('Application Password', 'wishlist-member'); ?></th>
					<td>
						<input type="text" name="ar[icapipassword]" value="<?php echo $data[$__index__]['icapipassword']; ?>" size="40" />
						<?php echo $this->Tooltip("integration-autoresponder-icontact-tooltips-get-pass"); ?>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><?php _e('Account ID', 'wishlist-member'); ?></th>
					<td>
						<input type="text" name="ar[icaccountid]" value="<?php echo $icAcctID; ?>" size="40"  readonly="readonly"/>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><?php _e('Folder', 'wishlist-member'); ?></th>
					<td>
						<!-- If allow_url_fopen is enabled then we can allow the client to select which folder they
							 want to use as simplexml_load_string will be available. If not then use the previous Input Box for the Folder ID -->
						<?php if( ini_get('allow_url_fopen') ) { ?>
						<select name="ar[icfolderid]" >
							<option> Select Folder </option>
							<?php foreach(array($xml_data->clientfolders) as $elements):
								foreach($elements->clientfolder as $element): ?>
									<option  value="<?php echo $element->clientFolderId; ?>" <?php echo ($data[$__index__]['icfolderid'] == $element->clientFolderId) ? 'selected="selected"': ''; ?> >
										<?php echo $element->name; ?>
									</option>
							<?php endforeach;

							endforeach;  ?>

						</select>
						<?php } else { ?>
							<input type="text" name="ar[icfolderid]" value="<?php echo $icFolderID; ?>" size="40"  readonly="readonly"/>
						<?php } ?>
					</td>
				</tr>
			</table>
			<?php if ($icAcctID == "" || $icFolderID == "") { ?>
				<p class="submit">
					<input type="hidden" name="saveAR" value="saveAR" />
					<input type="hidden" name="ar[icapiid]" value="60ZU6Al45lBtMmpi1S8tJqsvXdrNK18H" />
					<p><?php _e('Click the Save Settings button below to Generate the Account and Folder ID and then proceed to Step 3.', 'wishlist-member'); ?></p>
					<input type="submit" class="button button-primary" value="<?php _e('Save Settings', 'wishlist-member'); ?>" />&nbsp;&nbsp;&nbsp;
				</p>
			<?php } else { ?>
				<br />
				<h2 class="wlm-integration-steps">Step 3: Map your Membership Levels to your Lists</h2>
				<p>Paste the Contact List ID for each corresponding Membership Level below.</p>
				<p><?php _e('To get the value for the AutoResponder ID field:', 'wishlist-member'); ?></p>
				<ol style="margin-left:3em">
					<li><?php _e('Go to "My Contacts" &raquo; "My List" and click on the list name.', 'wishlist-member'); ?></li>
					<li><?php _e('Check the URL in your browser\'s address bar. The URL would look similar to this:', 'wishlist-member'); ?> <br>
					https://app.icontact.com/icp/core/mycontacts/lists/edit/<strong>35079</strong>/?token.....</li>
					<li><?php _e('Your Contact List ID is <strong>35079</strong>', 'wishlist-member'); ?> </li>
				</ol>
				<p style="color:grey;font-style: italic;">Note: Due to API limitations, iContact Integration does not support unsubscribing at the moment. Moving or Deleting from a membership level will not unsubcribe the user from your contact list. </p>
				<table class="widefat">
					<thead>
						<tr>
							<th scope="col"><?php _e('Membership Level', 'wishlist-member'); ?></th>
							<th scope="col"><?php _e('Contact List ID', 'wishlist-member'); ?></th>
							<th class="num" style="width:22em"><?php _e('Create a log file for Unsubscribe Members', 'wishlist-member'); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ((array) $wpm_levels AS $levelid => $level): ?>
							<tr class="<?php echo ++$autoresponder_row % 2 ? 'alternate' : ''; ?>">
								<th scope="row"><?php echo $level['name']; ?></th>
								<td><input type="text" name="ar[icID][<?php echo $levelid; ?>]" value="<?php echo $data[$__index__]['icID'][$levelid]; ?>" size="30" /></td>
								<?php $iclog = (($data[$__index__]['iclog'][$levelid] == 1) ? true : false); ?>
								<td class="num"><input type="checkbox" name="ar[iclog][<?php echo $levelid; ?>]" value="1" <?php echo $iclog ? "checked='checked'" : ""; ?> />
									<?php
									if ($iclog && $icID[$levelid] != "") {
										echo '<br />';
										echo '<a href="' . get_bloginfo('wpurl') . '/' . $data[$__index__]['icID'][$levelid] . '.txt" target="_blank">Download Log File</a>';
										echo '&nbsp;&nbsp;|&nbsp;&nbsp;';
										echo '<a href="?page=WishListMember&wl=integration&mode=ar&action=clear&level=' . $levelid . '">Clear Log File</a>';
									} else {
										if ($icID[$levelid] != "") {
											echo '<span style="color:red">Empty List</span>';
										}
									}
									?>
								</td>
							<?php endforeach; ?>
					</tbody>
				</table>
				<p class="submit">
					<input type="hidden" name="saveAR" value="saveAR" />
					<input type="hidden" name="ar[icapiid]" value="60ZU6Al45lBtMmpi1S8tJqsvXdrNK18H" />
					<input type="submit" class="button-primary" name="update_icontact" value="<?php _e('Update iContact Settings', 'wishlist-member'); ?>" />
				</p>
			<?php } ?>
		</form>
		<?php
		include_once($this->pluginDir . '/admin/tooltips/integration.autoresponder.icontact.tooltips.php');
	endif;
endif;
?>
