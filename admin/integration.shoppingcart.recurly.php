<?php
/*
 * Recurly Shopping Cart Integration
 */
require_once $this->pluginDir . '/extlib/WP_RecurlyClient.php';
$__index__ = 'recurly';
$__sc_options__[$__index__] = 'Recurly';
$__sc_videotutorial__[$__index__] = wlm_video_tutorial ( 'integration', 'sc', $__index__ );

if (wlm_arrval($_GET, 'cart') == $__index__) {


	if (!$__INTERFACE__) {

		// BEGIN Initialization
		$recurlythankyou = $this->GetOption('recurlythankyou');
		$recurlyapikey = $this->GetOption('recurlyapikey');
		$recurlyconnections = $this->GetOption('recurlyconnections');

		$client = new WP_RecurlyClient($recurlyapikey);
		//cache me maybe?
		$plans = get_transient('recurlyconnections');
		if (empty($plans)) {
			$plans = $client->get_plans();
			set_transient('recurlyconnections', $plans, 60 * 10);
		}


		if (!$recurlythankyou) {
			$this->SaveOption('recurlythankyou', $recurlythankyou = $this->MakeRegURL());
		}
		$recurlythankyou_url = $wpm_scregister . $recurlythankyou;

		// save POST URL
		if (wlm_arrval($_POST, 'recurlyapikey')) {
			$recurlyapikey = $_POST['recurlyapikey'];
			$this->SaveOption('recurlyapikey', $recurlyapikey);
			echo "<div class='updated fade'>" . __('<p>Recurly API Key has been updated.</p>', 'wishlist-member') . "</div>";
		}

		if (wlm_arrval($_POST, 'connections')) {
			$connections = $_POST['connections'];
			foreach ($connections as $i => $k) {
				if (!empty($k) && $recurlyconnections[$i] != $k) {
					//changed so re-integrate
					$url = $recurlythankyou_url . "?act=reg&amp;account_code={{account_code}}&amp;plan_code={{plan_code}}";
					$client->update_plan($k, array(
						'success_url' => $url,
						'accounting_code' => $i
					));
				}
			}
			$recurlyconnections = $connections;
			$this->SaveOption('recurlyconnections', $recurlyconnections);
		}

		if (wlm_arrval($_POST, 'recurlythankyou')) {
			$_POST['recurlythankyou'] = trim(wlm_arrval($_POST, 'recurlythankyou'));
			$wpmx = trim(preg_replace('/[^A-Za-z0-9]/', '', $_POST['recurlythankyou']));
			if ($wpmx == $_POST['recurlythankyou']) {
				if ($this->RegURLExists($wpmx, null, 'recurlythankyou')) {
					echo "<div class='error fade'>" . __('<p><b>Error:</b> Post to URL (' . $wpmx . ') is already in use by a Membership Level or another Shopping Cart.  Please try a different one.</p>', 'wishlist-member') . "</div>";
				} else {
					$this->SaveOption('recurlythankyou', $recurlythankyou = $wpmx);
					echo "<div class='updated fade'>" . __('<p>Post To URL Changed.</p>', 'wishlist-member') . "</div>";
				}
			} else {
				echo "<div class='error fade'>" . __('<p><b>Error:</b> Post To URL may only contain letters and numbers.</p>', 'wishlist-member') . "</div>";
			}
		}

		// END Initialization
	} else {
		// START Interface
		?>
		<!-- Generic -->
		<form method="post" action="">
			<h2 class="wlm-integration-steps"><?php _e('Step 1. Set up Recurly API Key:', 'wishlist-member'); ?></h2><br/>
			<p class="description"><?php _e("Enter the Recurly API Key in the field below and click the Save API Key button. Then proceed to Step 2.") ?></p>
			<?php _e('Recurly API Key', 'wishlist-member'); ?>: <input size="50" type="text" name="recurlyapikey" value="<?php echo $recurlyapikey ?>"/>
			<br><br><input type="submit" class="button button-primary" value="Save API Key">

			<h2 class="wlm-integration-steps"><?php _e('Step 2. Configure Membership Levels and Reculty Plans:', 'wishlist-member'); ?></h2><br/>
			<p class="description"><?php _e("Assign the Membership Levels to the corresponding Reculty Plans.") ?></p>
			<table class="widefat">
				<thead>
					<tr>
						<th scope="col"><?php _e('Membership Level', 'wishlist-member'); ?></th>
						<th scope="col" width="200"><?php _e('SKU', 'wishlist-member'); ?></th>
						<th scope="col" width="200"><?php _e('Plan Code', 'wishlist-member'); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php
					$alt = 0;
					foreach ((array) $wpm_levels AS $sku => $level):
						?>
						<tr class="<?php echo $alt++ % 2 ? '' : 'alternate'; ?>" id="wpm_level_row_<?php echo $sku ?>">
							<td><b><?php echo $level['name'] ?></b></td>
							<td><?php echo $sku; ?></td>
							<td>
								<select name="connections[<?php echo $sku ?>]">
									<option value=""><?php ?></option>
									<?php foreach ($plans as $p): ?>
										<?php $selected = ($recurlyconnections[$sku] == $p['plan_code']) ? 'selected="selected"' : null; ?>
										<option <?php echo $selected ?> value="<?php echo $p['plan_code'] ?>"><?php echo $p['name'] ?></option>
									<?php endforeach; ?>
								</select>
							</td>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			<br/>
			<h2 class="wlm-integration-steps"><?php _e('Step 3. Configure the Push Notification URL:', 'wishlist-member'); ?></h2><br/>
			<p class="description"><?php _e("Copy the link below and paste it into Recurly as the Post Notification URL.") ?></p>
			<a target="_blank" href="<?php echo $recurlythankyou_url ?>"><?php echo $recurlythankyou_url ?></a> <br/> <br/> <br/>

			<input type="submit" name="save" value="Save Settings" class="button-primary"/>
		</form>
		<!--
		<?php include_once($this->pluginDir . '/admin/integration.shoppingcart-payperpost-skus.php'); ?>
		-->
		</blockquote>
		<?php
		// END Interface
	}
}
?>
