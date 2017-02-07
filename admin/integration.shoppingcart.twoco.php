<?php
/*
 * 2Checkout Shopping Cart Integration
 * Original Author : Glen Barnhardt
 * Version: $Id: integration.shoppingcart.twoco.php 2813 2015-07-29 14:30:25Z mike $
 */

$__index__ = '2Checkout';
$__sc_options__[$__index__] = '2Checkout';
$__sc_videotutorial__[$__index__] = wlm_video_tutorial ( 'integration', 'sc', $__index__ );

if (wlm_arrval($_GET,'cart') == $__index__) {
	if (!$__INTERFACE__) {
		// BEGIN Initialization
		$twocothankyou = $this->GetOption('twocothankyou');
		if (!$twocothankyou) {
			$this->SaveOption('twocothankyou', $twocothankyou = $this->MakeRegURL());
		}
		$twocosecret = (string) $this->GetOption('twocosecret');

		$twocovendorid = $this->GetOption('twocovendorid');
		// save POST URL
		if (wlm_arrval($_POST,'twocothankyou')) {
			$_POST['twocothankyou'] = trim(wlm_arrval($_POST,'twocothankyou'));
			$wpmx = trim(preg_replace('/[^A-Za-z0-9]/', '', $_POST['twocothankyou']));
			if ($wpmx == $_POST['twocothankyou']) {
				if ($this->RegURLExists($wpmx, null, 'twocothankyou')) {
					echo "<div class='error fade'>" . __('<p><b>Error:</b> Instant Notification URL (' . $wpmx . ') is already in use by a Membership Level or another Shopping Cart.  Please try a different one.</p>', 'wishlist-member') . "</div>";
				} else {
					$this->SaveOption('twocothankyou', $twocothankyou = $wpmx);
					echo "<div class='updated fade'>" . __('<p>Instant Notification URL Changed.</p>', 'wishlist-member') . "</div>";
				}
			} else {
				echo "<div class='error fade'>" . __('<p><b>Error:</b> Instant Notification URL may only contain letters and numbers.</p>', 'wishlist-member') . "</div>";
			}
		}
		// save Secret Key
		if (wlm_arrval($_POST,'twocosecret')) {
			$_POST['twocosecret'] = trim(wlm_arrval($_POST,'twocosecret'));
			$wpmy = trim(preg_replace('/[^A-Za-z0-9]/', '', $_POST['twocosecret']));
			if ($wpmy == $_POST['twocosecret']) {
				$this->SaveOption('twocosecret', $twocosecret = $wpmy);
				echo "<div class='updated fade'>" . __('<p>Secret Word Updated.</p>', 'wishlist-member') . "</div>";
			} else {
				echo "<div class='error fade'>" . __('<p><b>Error:</b> Secret key may only contain letters and numbers.</p>', 'wishlist-member') . "</div>";
			}
		}
		// save vendor id
		if (wlm_arrval($_POST,'twocovendorid')) {
			$_POST['twocovendorid'] = trim(wlm_arrval($_POST,'twocovendorid'));
			$wpmy = trim(preg_replace('/[^A-Za-z0-9]/', '', $_POST['twocovendorid']));
			if ($wpmy == $_POST['twocovendorid']) {
				$this->SaveOption('twocovendorid', $twocovendorid = $wpmy);
				echo "<div class='updated fade'>" . __('<p>Vendor ID Updated.</p>', 'wishlist-member') . "</div>";
			} else {
				echo "<div class='error fade'>" . __('<p><b>Error:</b> Vendor ID may only contain letters and numbers.</p>', 'wishlist-member') . "</div>";
			}
		}

		// save Demo ModeL
		if (isset($_POST['twocodemo'])) {
			$x = $this->SaveOption('twocodemo', $_POST['twocodemo'] + 0);
			echo "<div class='updated fade'>" . __('<p>2Checkout Demo Mode Updated.</p>', 'wishlist-member') . "</div>";
		}
		$twocodemo = $this->GetOption('twocodemo') + 0;

		$twocothankyou_url = $wpm_scregister . $twocothankyou;
		// END Initialization
	} else {
		// START Interface
		?>
		<!-- 2Checkout -->
		<blockquote>
			<h2 class="wlm-integration-steps"><?php _e('Step 1. Enter 2Checkout Account Information:','wishlist-member'); ?></h2>
			<?php include('form.integration.shoppingcart.twoco.accountinfo.php'); ?>
			<h2 class="wlm-integration-steps"><?php _e('Step 2. Use the URL below as the Instant Notification URL in 2Checkout.','wishlist-member'); ?></h2>
			<?php include('form.integration.shoppingcart.twoco.notifurl.php'); ?>
			<h2 class="wlm-integration-steps"><?php _e('Step 3. Create a product for each Membership Level or Pay Per Post using the assigned SKUs listed below:','wishlist-member') ;?></h2>
			<table class="widefat">
				<thead>
					<tr>
						<th scope="col" width="200"><?php _e('Membership Level', 'wishlist-member'); ?></th>
						<th scope="col">
							<?php _e('SKU', 'wishlist-member'); ?>
							<?php echo $this->Tooltip("integration-shoppingcart-2co-tooltips-sku"); ?>
						</th>
					</tr>
				</thead>
				<tbody>
					<?php $alt = 0;
					foreach ((array) $wpm_levels AS $sku => $level):
						?>
						<tr class="<?php echo $alt++ % 2 ? '' : 'alternate'; ?>" id="wpm_level_row_<?php echo $sku ?>">
							<td><b><?php echo $level['name'] ?></b></td>
							<td><u style="font-size:1.2em"><?php echo $sku ?></u></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php include_once($this->pluginDir . '/admin/integration.shoppingcart-payperpost-skus.php'); ?>
			<form method="post">
				<h2 class="wlm-integration-steps"><?php _e('2Checkout Demo Mode', 'wishlist-member'); ?></h2>
				<p><?php _e("Using this setting, all sales will be treated as demo regardless of any parameter value.", "wishlist-member"); ?></p>
				<blockquote>
					<label>
						<input type="radio" name="twocodemo" value="1" <?php $this->Checked($twocodemo, 1); ?> />
		<?php _e('Enable Demo Mode', 'wishlist-member'); ?>
					</label>
					<br>
					<label>
						<input type="radio" name="twocodemo" value="0" <?php $this->Checked($twocodemo, 0); ?> />
		<?php _e('Disable Demo Mode', 'wishlist-member'); ?>
					</label>
					<br><br>
					<input type="submit" class="button-secondary" value="<?php _e('Save Demo Mode Settings', 'wishlist-member'); ?>" />
				</blockquote>
		</blockquote>
		<?php
		include_once($this->pluginDir . '/admin/tooltips/integration.shoppingcart.2co.tooltips.php');
		// END Interface
	}
}
?>
