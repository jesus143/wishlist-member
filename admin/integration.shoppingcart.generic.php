<?php
/*
 * Generic Shopping Cart Integration
 * Original Author : Mike Lopez
 * Version: $Id: integration.shoppingcart.generic.php 2813 2015-07-29 14:30:25Z mike $
 */

$__index__ = 'generic';
$__sc_options__[$__index__] = 'Generic';
//$__sc_videotutorial__[$__index__] = wlm_video_tutorial ( 'integration', 'sc', $__index__ );

if (wlm_arrval($_GET,'cart') == $__index__) {
	if (!$__INTERFACE__) {
		// BEGIN Initialization
		$genericthankyou = $this->GetOption('genericthankyou');
		if (!$genericthankyou) {
			$this->SaveOption('genericthankyou', $genericthankyou = $this->MakeRegURL());
		}
		$genericsecret = $this->GetOption('genericsecret');
		if (!$genericsecret) {
			$this->SaveOption('genericsecret', $genericsecret = $this->PassGen() . $this->PassGen());
		}

		// save POST URL
		if (wlm_arrval($_POST,'genericthankyou')) {
			$_POST['genericthankyou'] = trim(wlm_arrval($_POST,'genericthankyou'));
			$wpmx = trim(preg_replace('/[^A-Za-z0-9]/', '', $_POST['genericthankyou']));
			if ($wpmx == $_POST['genericthankyou']) {
				if ($this->RegURLExists($wpmx, null, 'genericthankyou')) {
					echo "<div class='error fade'>" . __('<p><b>Error:</b> Post to URL (' . $wpmx . ') is already in use by a Membership Level or another Shopping Cart.  Please try a different one.</p>', 'wishlist-member') . "</div>";
				} else {
					$this->SaveOption('genericthankyou', $genericthankyou = $wpmx);
					echo "<div class='updated fade'>" . __('<p>Post To URL Changed.</p>', 'wishlist-member') . "</div>";
				}
			} else {
				echo "<div class='error fade'>" . __('<p><b>Error:</b> Post To URL may only contain letters and numbers.</p>', 'wishlist-member') . "</div>";
			}
		}
		// save Secret Key
		if (wlm_arrval($_POST,'genericsecret')) {
			$_POST['genericsecret'] = trim(wlm_arrval($_POST,'genericsecret'));
			$wpmy = trim(preg_replace('/[^A-Za-z0-9]/', '', $_POST['genericsecret']));
			if ($wpmy == $_POST['genericsecret']) {
				$this->SaveOption('genericsecret', $genericsecret = $wpmy);
				echo "<div class='updated fade'>" . __('<p>Secret Key Changed.</p>', 'wishlist-member') . "</div>";
			} else {
				echo "<div class='error fade'>" . __('<p><b>Error:</b> Secret key may only contain letters and numbers.</p>', 'wishlist-member') . "</div>";
			}
		}
		$genericthankyou_url = $wpm_scregister . $genericthankyou;
		// END Initialization
	} else {
		// START Interface
		?>
		<!-- Generic -->
		<p><?php _e('The Generic 3rd Party System Integration provides a site owner with the opportunity to integrate a custom shopping cart solution with WishList Member.', 'wishlist-member'); ?></p>
		<p><a href="http://wishlistproducts.com/documents/WLM-Generic-Integration.zip"><?php _e('Click Here to download Generic Integration Instructions.', 'wishlist-member'); ?></a></p>
		<blockquote>
			<form method="post">
				<h2 class="wlm-integration-steps"><?php _e('Step 1. Configure Post To URL:', 'wishlist-member'); ?></h2>
				<p><?php _e('The Post To URL is where the information it sent to.', 'wishlist-member'); ?></p>
				<p>&nbsp;&nbsp;<a href="<?php echo $genericthankyou_url ?>" onclick="return false"><?php echo $genericthankyou_url ?></a> &nbsp; (<a href="javascript:;" onclick="document.getElementById('genericthankyou').style.display='block';"><?php _e('change', 'wishlist-member'); ?></a>)
					<?php echo $this->Tooltip("integration-shoppingcart-generic-tooltips-thankyouurl"); ?>

				</p>
				<div id="genericthankyou" style="display:none">
					<p>&nbsp;&nbsp;<?php echo $wpm_scregister ?><input type="text" name="genericthankyou" value="<?php echo $genericthankyou ?>" size="8" /> <input type="submit" class="button-secondary" value="<?php _e('Change', 'wishlist-member'); ?>" /></p>
				</div>
			</form>
			<form method="post">
				<h2 class="wlm-integration-steps"><?php _e('Step 2. Configure Secret Word:', 'wishlist-member'); ?></h2>
				<p><?php _e('The Secret Word is used to generate a hash key for security purposes.', 'wishlist-member'); ?></p>
				<p>&nbsp;&nbsp;<input type="text" name="genericsecret" value="<?php echo $genericsecret ?>" size="20" maxlength='16' /> <input type="submit" class="button-secondary" value="<?php _e('Change', 'wishlist-member'); ?>" />
					<?php echo $this->Tooltip("integration-shoppingcart-generic-tooltips-genericsecret"); ?>

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
					<?php
					$alt = 0;
					foreach ((array) $wpm_levels AS $sku => $level):
						?>
						<tr class="<?php echo $alt++ % 2 ? '' : 'alternate'; ?>" id="wpm_level_row_<?php echo $sku ?>">
							<td><b><?php echo $level['name'] ?></b></td>
							<td><u style="font-size:1.2em"><?php echo $sku ?></u>
			<?php echo $this->Tooltip("integration-shoppingcart-generic-tooltips-sku"); ?>

					</td>
					</tr>
		<?php endforeach; ?>
				</tbody>
			</table>

		<?php include_once($this->pluginDir . '/admin/integration.shoppingcart-payperpost-skus.php'); ?>

		</blockquote>
		<?php
		include_once($this->pluginDir . '/admin/tooltips/integration.shoppingcart.generic.tooltips.php');
		// END Interface
	}
}
?>
