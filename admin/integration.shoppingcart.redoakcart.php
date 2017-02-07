<?php
/*
 * RedOakCart Shopping Cart Integration
 * Original Author : Mike Lopez
 * Version: $Id: integration.shoppingcart.redoakcart.php 2813 2015-07-29 14:30:25Z mike $
 */

$__index__ = 'redoakcart';
$__sc_options__[$__index__] = 'RedOakCart';
$__sc_affiliates__[$__index__] = 'http://wlplink.com/go/redoak';
$__sc_videotutorial__[$__index__] = wlm_video_tutorial ( 'integration', 'sc', $__index__ );

if (wlm_arrval($_GET,'cart') == $__index__) {
	if (!$__INTERFACE__) {
		// BEGIN Initialization
		$redoakcartthankyou = $this->GetOption('redoakcartthankyou');
		if (!$redoakcartthankyou) {
			$this->SaveOption('redoakcartthankyou', $redoakcartthankyou = $this->MakeRegURL());
		}
		$redoakcartsecret = $this->GetOption('redoakcartsecret');
		if (!$redoakcartsecret) {
			$this->SaveOption('redoakcartsecret', $redoakcartsecret = $this->PassGen() . $this->PassGen());
		}

		// save POST URL
		if (wlm_arrval($_POST,'redoakcartthankyou')) {
			$_POST['redoakcartthankyou'] = trim(wlm_arrval($_POST,'redoakcartthankyou'));
			$wpmx = trim(preg_replace('/[^A-Za-z0-9]/', '', $_POST['redoakcartthankyou']));
			if ($wpmx == $_POST['redoakcartthankyou']) {
				if ($this->RegURLExists($wpmx, null, 'redoakcartthankyou')) {
					echo "<div class='error fade'>" . __('<p><b>Error:</b> Post to URL (' . $wpmx . ') is already in use by a Membership Level or another Shopping Cart.  Please try a different one.</p>', 'wishlist-member') . "</div>";
				} else {
					$this->SaveOption('redoakcartthankyou', $redoakcartthankyou = $wpmx);
					echo "<div class='updated fade'>" . __('<p>Post To URL Changed.</p>', 'wishlist-member') . "</div>";
				}
			} else {
				echo "<div class='error fade'>" . __('<p><b>Error:</b> Post To URL may only contain letters and numbers.</p>', 'wishlist-member') . "</div>";
			}
		}
		// save Secret Key
		if (wlm_arrval($_POST,'redoakcartsecret')) {
			$_POST['redoakcartsecret'] = trim(wlm_arrval($_POST,'redoakcartsecret'));
			$wpmy = trim(preg_replace('/[^A-Za-z0-9]/', '', $_POST['redoakcartsecret']));
			if ($wpmy == $_POST['redoakcartsecret']) {
				$this->SaveOption('redoakcartsecret', $redoakcartsecret = $wpmy);
				echo "<div class='updated fade'>" . __('<p>Secret Key Changed.</p>', 'wishlist-member') . "</div>";
			} else {
				echo "<div class='error fade'>" . __('<p><b>Error:</b> Secret key may only contain letters and numbers.</p>', 'wishlist-member') . "</div>";
			}
		}
		$redoakcartthankyou_url = $wpm_scregister . $redoakcartthankyou;
		// END Initialization
	} else {
		// START Interface
		?>
		<!-- RedOakCart -->
		<blockquote>
			<form method="post">
				<h2 class="wlm-integration-steps"><?php _e('Step 1. Configure Post To URL:', 'wishlist-member'); ?></h2>
				<p><?php _e('The Post To URL can be copied from below and pasted into RedOackCart in the following section: <br><br> Setup > Membership Sites > URL', 'wishlist-member'); ?></p>
				<p>&nbsp;&nbsp;<a href="<?php echo $redoakcartthankyou_url ?>" onclick="return false"><?php echo $redoakcartthankyou_url ?></a> &nbsp; (<a href="javascript:;" onclick="document.getElementById('redoakcartthankyou').style.display='block';"><?php _e('change', 'wishlist-member'); ?></a>)
					<?php echo $this->Tooltip("integration-shoppingcart-redoakcart-tooltips-thankyouurl"); ?>

				</p>
				<div id="redoakcartthankyou" style="display:none">
					<p>&nbsp;&nbsp;<?php echo $wpm_scregister ?><input type="text" name="redoakcartthankyou" value="<?php echo $redoakcartthankyou ?>" size="8" /> <input type="submit" class="button-secondary" value="<?php _e('Change', 'wishlist-member'); ?>" /></p>
				</div>
			</form>
			<form method="post">
				<h2 class="wlm-integration-steps"><?php _e('Step 2. Configure Secret Word:', 'wishlist-member'); ?></h2>
				<p><?php _e('The Secret Key can be copied from below and pasted into RedOackCart in the following section: <br><br> Setup > Membership Sites > Secret Key', 'wishlist-member'); ?></p>
				<p>&nbsp;&nbsp;<input type="text" name="redoakcartsecret" value="<?php echo $redoakcartsecret ?>" size="20" maxlength='16' /> <input type="submit" class="button-secondary" value="<?php _e('Change', 'wishlist-member'); ?>" />
					<?php echo $this->Tooltip("integration-shoppingcart-redoakcart-tooltips-redoakcartsecret"); ?>

				</p>
			</form>
			<h2 class="wlm-integration-steps"><?php _e('Step 3. Create a product for each Membership Level or Pay Per Post using the assigned SKUs listed below:', 'wishlist-member'); ?></h2>
			<p><?php _e('The Membership Level SKUs specify the Membership Levels that should be connected to each transaction.', 'wishlist-member'); ?></p>
			<table class="widefat">
				<thead>
					<tr>
						<th scope="col" width="200"><?php _e('Membership Level', 'wishlist-member'); ?></th>
						<th scope="col"><?php _e('SKU', 'wishlist-member'); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php $alt = 0;
					foreach ((array) $wpm_levels AS $sku => $level):
						?>
						<tr class="<?php echo $alt++ % 2 ? '' : 'alternate'; ?>" id="wpm_level_row_<?php echo $sku ?>">
							<td><b><?php echo $level['name'] ?></b></td>
							<td><u style="font-size:1.2em"><?php echo $sku ?></u>
			<?php echo $this->Tooltip("integration-shoppingcart-redoakcart-tooltips-sku"); ?>

					</td>
					</tr>
		<?php endforeach; ?>
				</tbody>
			</table>
		<?php include_once($this->pluginDir . '/admin/integration.shoppingcart-payperpost-skus.php'); ?>
		</blockquote>
		<?php
		include_once($this->pluginDir . '/admin/tooltips/integration.shoppingcart.redoakcart.tooltips.php');
		// END Interface
	}
}
?>
