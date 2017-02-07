<?php
/*
 * JVZoo Shopping Cart Integration
 * Original Author : Mike Lopez
 * Version: $Id: integration.shoppingcart.jvzoo.php 2813 2015-07-29 14:30:25Z mike $
 */

$__index__ = 'jvzoo';
$__sc_options__[$__index__] = 'JVZoo';
//$__sc_affiliates__[$__index__] = 'http://wlplink.com/go/jvzoo';
$__sc_videotutorial__[$__index__] = wlm_video_tutorial ( 'integration', 'sc', $__index__ );

if (wlm_arrval($_GET, 'cart') == $__index__) {
	if (!$__INTERFACE__) {
		// BEGIN Initialization
		$jvzoothankyou = $this->GetOption('jvzoothankyou');
		if (!$jvzoothankyou) {
			$this->SaveOption('jvzoothankyou', $jvzoothankyou = $this->MakeRegURL());
		}
		$jvzoosecret = $this->GetOption('jvzoosecret');
		if (!$jvzoosecret) {
			$this->SaveOption('jvzoosecret', $jvzoosecret = strtoupper($this->PassGen() . $this->PassGen()));
		}

		// save POST URL
		if (wlm_arrval($_POST, 'jvzoothankyou')) {
			$_POST['jvzoothankyou'] = trim(wlm_arrval($_POST, 'jvzoothankyou'));
			$wpmx = trim(preg_replace('/[^A-Za-z0-9]/', '', $_POST['jvzoothankyou']));
			if ($wpmx == $_POST['jvzoothankyou']) {
				if ($this->RegURLExists($wpmx, null, 'jvzoothankyou')) {
					echo "<div class='error fade'>" . __('<p><b>Error:</b> JVZoo Thank You URL (' . $wpmx . ') is already in use by a Membership Level or another Shopping Cart.  Please try a different one.</p>', 'wishlist-member') . "</div>";
				} else {
					$this->SaveOption('jvzoothankyou', $jvzoothankyou = $wpmx);
					echo "<div class='updated fade'>" . __('<p>Thank You URL Changed.&nbsp; Make sure to update your JVZoo products with the same Thank You URL to make it work.</p>', 'wishlist-member') . "</div>";
				}
			} else {
				echo "<div class='error fade'>" . __('<p><b>Error:</b> Thank You URL may only contain letters and numbers.</p>', 'wishlist-member') . "</div>";
			}
		}
		// save Secret Key and account nickname
		if (wlm_arrval($_POST, 'jvzoosecret')) {
			$_POST['jvzoosecret'] = trim(strtoupper(wlm_arrval($_POST, 'jvzoosecret')));
			$wpmy = trim(preg_replace('/[^A-Za-z0-9]/', '', $_POST['jvzoosecret']));
			if ($wpmy == $_POST['jvzoosecret']) {
				$this->SaveOption('jvzoosecret', $jvzoosecret = $wpmy);
				echo "<div class='updated fade'>" . __('<p>Secret Key Updated.&nbsp; Make sure to update JVZoo with the same Secret key to make it work.</p>', 'wishlist-member') . "</div>";
			} else {
				echo "<div class='error fade'>" . __('<p><b>Error:</b> Secret key may only contain letters and numbers.</p>', 'wishlist-member') . "</div>";
			}

		}
		$jvzoothankyou_url = $wpm_scregister . $jvzoothankyou;

		// END Initialization
	} else {
		// START Interface
		?>
		<!-- JVZoo -->
		<blockquote>
			<form method="post">
				<h2 class="wlm-integration-steps"><?php _e('Step 1. Configure JVZIPN Secret Key:', 'wishlist-member'); ?></h2>
				<p><?php _e ('Set the key below as the JVZIPN Secret Key in the My Accounts section of JVZoo.','wishlist-member'); ?></p>
				<table class="form-table">
					<tr>
						<th scope="row"><?php _e('JVZIPN Secret Key', 'wishlist-member'); ?></th>
						<td>
							<input type="text" name="jvzoosecret" value="<?php echo $jvzoosecret ?>" size="32" />
							<?php echo $this->Tooltip("integration-shoppingcart-jvzoo-tooltips-Specify-a-Secret-Word"); ?>
						</td>
					</tr>
				</table>
				<p class="submit">
					&nbsp;&nbsp;<input type="submit" class="button-secondary" value="<?php _e('Save JVZIPN Secret Key', 'wishlist-member'); ?>" />
				</p>
			</form>
			<form method="post">
				<h2 class="wlm-integration-steps"><?php _e('Step 2. Configure Thank You Page and JVZIPN URL:', 'wishlist-member'); ?></h2>
				<p><?php _e('Set the Thank You Page URL and JVZIPN URL in JVZoo for each product to the following URL:','wishlist-member'); ?></p>
				<p>&nbsp;&nbsp;<a href="<?php echo $jvzoothankyou_url ?>" onclick="return false"><?php echo $jvzoothankyou_url ?></a> &nbsp; (<a href="javascript:;" onclick="document.getElementById('jvzoothankyou').style.display = 'block';"><?php _e('change', 'wishlist-member'); ?></a>)
					<?php echo $this->Tooltip("integration-shoppingcart-jvzoo-tooltips-thankyouurl"); ?>
				</p>
				<div id="jvzoothankyou" style="display:none">
					<p>&nbsp;&nbsp;<?php echo $wpm_scregister ?><input type="text" name="jvzoothankyou" value="<?php echo $jvzoothankyou ?>" size="8" /> <input type="submit" class="button-secondary" value="<?php _e('Change', 'wishlist-member'); ?>" /></p>
				</div>
			</form>

			<h2 class="wlm-integration-steps"><?php _e('Step 3. Create a product for each Membership Level or Pay Per Post using the assigned SKUs listed below:', 'wishlist-member'); ?></h2>
			<p><?php _e('The Membership Level SKUs specify the Membership Levels that should be connected to each transaction.', 'wishlist-member'); ?></p>
			<table class="widefat">
				<thead>
					<tr>
						<th scope="col" width="200"><?php _e('Membership Level', 'wishlist-member'); ?></th>
						<th scope="col">
							<?php _e('SKU', 'wishlist-member'); ?>
							<?php echo $this->Tooltip("integration-shoppingcart-generic-tooltips-sku"); ?>
						</th>
					</tr>
				</thead>
				<tbody>
					<?php
					$alt = 0;
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

		<?php
		include_once($this->pluginDir . '/admin/tooltips/integration.shoppingcart.jvzoo.tooltips.php');
		// END Interface
	}
}