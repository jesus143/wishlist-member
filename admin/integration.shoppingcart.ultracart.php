<?php
/*
 * UltraCart Shopping Cart Integration
 * Original Author : Mike Lopez
 * Version: $Id: integration.shoppingcart.ultracart.php 2813 2015-07-29 14:30:25Z mike $
 */

$__index__ = 'ultracart';
$__sc_options__[$__index__] = 'UltraCart';
//$__sc_videotutorial__[$__index__] = wlm_video_tutorial ( 'integration', 'sc', $__index__ );

if (wlm_arrval($_GET,'cart') == $__index__) {
	if (!$__INTERFACE__) {
		// BEGIN Initialization
		$ultracartthankyou = $this->GetOption('ultracartthankyou');
		if (!$ultracartthankyou) {
			$this->SaveOption('ultracartthankyou', $ultracartthankyou = $this->MakeRegURL());
		}
		$ultracartsecret = $this->GetOption('ultracartsecret');
		if (!$ultracartsecret) {
			$this->SaveOption('ultracartsecret', $ultracartsecret = $this->PassGen() . $this->PassGen());
		}

		// save POST URL
		if (wlm_arrval($_POST,'ultracartthankyou')) {
			$_POST['ultracartthankyou'] = trim(wlm_arrval($_POST,'ultracartthankyou'));
			$wpmx = trim(preg_replace('/[^A-Za-z0-9]/', '', $_POST['ultracartthankyou']));
			if ($wpmx == $_POST['ultracartthankyou']) {
				if ($this->RegURLExists($wpmx, null, 'ultracartthankyou')) {
					echo "<div class='error fade'>" . __('<p><b>Error:</b> Post to URL (' . $wpmx . ') is already in use by a Membership Level or another Shopping Cart.  Please try a different one.</p>', 'wishlist-member') . "</div>";
				} else {
					$this->SaveOption('ultracartthankyou', $ultracartthankyou = $wpmx);
					echo "<div class='updated fade'>" . __('<p>Post To URL Changed.</p>', 'wishlist-member') . "</div>";
				}
			} else {
				echo "<div class='error fade'>" . __('<p><b>Error:</b> Post To URL may only contain letters and numbers.</p>', 'wishlist-member') . "</div>";
			}
		}
		// save Secret Key
		if (wlm_arrval($_POST,'ultracartsecret')) {
			$_POST['ultracartsecret'] = trim(wlm_arrval($_POST,'ultracartsecret'));
			$wpmy = trim(preg_replace('/[^A-Za-z0-9]/', '', $_POST['ultracartsecret']));
			if ($wpmy == $_POST['ultracartsecret']) {
				$this->SaveOption('ultracartsecret', $ultracartsecret = $wpmy);
				echo "<div class='updated fade'>" . __('<p>Secret Key Changed.</p>', 'wishlist-member') . "</div>";
			} else {
				echo "<div class='error fade'>" . __('<p><b>Error:</b> Secret key may only contain letters and numbers.</p>', 'wishlist-member') . "</div>";
			}
		}
		$ultracartthankyou_url = $wpm_scregister . $ultracartthankyou;
		// END Initialization
	} else {
		// START Interface
		?>
		<!-- UltraCart -->
		<blockquote>
			<form method="post">
				<h2 class="wlm-integration-steps"><?php _e('Step 1. Configure Post To URL:', 'wishlist-member'); ?></h2>
				<p><?php _e('The Post To URL can be copied from below and pasted into Ultracart.', 'wishlist-member'); ?></p>
				<p>&nbsp;&nbsp;<a href="<?php echo $ultracartthankyou_url ?>" onclick="return false"><?php echo $ultracartthankyou_url ?></a> &nbsp; (<a href="javascript:;" onclick="document.getElementById('ultracartthankyou').style.display='block';"><?php _e('change', 'wishlist-member'); ?></a>)
					<?php echo $this->Tooltip("integration-shoppingcart-ultracart-tooltips-thankyouurl"); ?>

				</p>
				<div id="ultracartthankyou" style="display:none">
					<p>&nbsp;&nbsp;<?php echo $wpm_scregister ?><input type="text" name="ultracartthankyou" value="<?php echo $ultracartthankyou ?>" size="8" /> <input type="submit" class="button-secondary" value="<?php _e('Change', 'wishlist-member'); ?>" /></p>
				</div>
			</form>
			<form method="post">
				<h2 class="wlm-integration-steps"><?php _e('Step 2. Configure Secret Word:', 'wishlist-member'); ?></h2>
				<p><?php _e('The Secret Key can be copied from below and pasted into Ultracart.', 'wishlist-member'); ?></p>
				<p>&nbsp;&nbsp;<input type="text" name="ultracartsecret" value="<?php echo $ultracartsecret ?>" size="20" maxlength='16' /> <input type="submit" class="button-primary" value="<?php _e('Change', 'wishlist-member'); ?>" />
					<?php echo $this->Tooltip("integration-shoppingcart-ultracart-tooltips-ultracartsecret"); ?>

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
			<?php echo $this->Tooltip("integration-shoppingcart-ultracart-tooltips-sku"); ?>

					</td>
					</tr>
		<?php endforeach; ?>
				</tbody>
			</table>

		<?php include_once($this->pluginDir . '/admin/integration.shoppingcart-payperpost-skus.php'); ?>

		</blockquote>
		<?php
		include_once($this->pluginDir . '/admin/tooltips/integration.shoppingcart.ultracart.tooltips.php');
		// END Interface
	}
}
?>
