<?php
/*
 * Cydec Shopping Cart Integration
 * Original Author : Mike Lopez
 * Version: $Id: integration.shoppingcart.cydec.php 2813 2015-07-29 14:30:25Z mike $
 */

$__index__ = 'cydec';
$__sc_options__[$__index__] = 'Cydec';
//$__sc_videotutorial__[$__index__] = wlm_video_tutorial ( 'integration', 'sc', $__index__ );

if (wlm_arrval($_GET,'cart') == $__index__) {
	if (!$__INTERFACE__) {
		// BEGIN Initialization
		$cydecthankyou = $this->GetOption('cydecthankyou');
		if (!$cydecthankyou) {
			$this->SaveOption('cydecthankyou', $cydecthankyou = $this->MakeRegURL());
		}
		$cydecsecret = $this->GetOption('cydecsecret');
		if (!$cydecsecret) {
			$this->SaveOption('cydecsecret', $cydecsecret = $this->PassGen() . $this->PassGen());
		}

		// save POST URL
		if (wlm_arrval($_POST,'cydecthankyou')) {
			$_POST['cydecthankyou'] = trim(wlm_arrval($_POST,'cydecthankyou'));
			$wpmx = trim(preg_replace('/[^A-Za-z0-9]/', '', $_POST['cydecthankyou']));
			if ($wpmx == $_POST['cydecthankyou']) {
				if ($this->RegURLExists($wpmx, null, 'cydecthankyou')) {
					echo "<div class='error fade'>" . __('<p><b>Error:</b> Post To URL (' . $wpmx . ') is already in use by a Membership Level or another Shopping Cart.  Please try a different one.</p>', 'wishlist-member') . "</div>";
				} else {
					$this->SaveOption('cydecthankyou', $cydecthankyou = $wpmx);
					echo "<div class='updated fade'>" . __('<p>Post To URL Changed.&nbsp; Make sure to update Cydec with the same Post To URL to make it work.</p>', 'wishlist-member') . "</div>";
				}
			} else {
				echo "<div class='error fade'>" . __('<p><b>Error:</b> Post To URL may only contain letters and numbers.</p>', 'wishlist-member') . "</div>";
			}
		}
		// save Secret Key
		if (wlm_arrval($_POST,'cydecsecret')) {
			$_POST['cydecsecret'] = trim(wlm_arrval($_POST,'cydecsecret'));
			$wpmy = trim(preg_replace('/[^A-Za-z0-9]/', '', $_POST['cydecsecret']));

			if($cydecsecret == trim($_POST['cydecsecret'])) {
				echo "<div class='error fade'>" . __('<p>The Secret Word has not been Updated. Please edit the characters in the Secret Word field and then click the Update button in order to process the Update.</p>', 'wishlist-member') . "</div>";
			} else {

				if ($wpmy == $_POST['cydecsecret']) {
					$this->SaveOption('cydecsecret', $cydecsecret = $wpmy);
					echo "<div class='updated fade'>" . __('<p>The Secret Word has been Updated. Please ensure that the Secret Word is also updated in the Cydec site if there is an existing Integration.</p>', 'wishlist-member') . "</div>";
				} else {
					echo "<div class='error fade'>" . __('<p><b>Error:</b> Secret key may only contain letters and numbers.</p>', 'wishlist-member') . "</div>";
				}
			}
		}
		$cydecthankyou_url = $wpm_scregister . $cydecthankyou;
		// END Initialization
	} else {
		// START Interface
		?>
		<!-- Cydec / Quick Pay Pro -->
		<blockquote>
			<form method="post">
				<h2 class="wlm-integration-steps"><?php _e('Step 1. Set the Post To URL in Cydec or the Post To URL for each product to the following URL:', 'wishlist-member'); ?></h2>
				<p>&nbsp;&nbsp;<a href="<?php echo $cydecthankyou_url ?>" onclick="return false"><?php echo $cydecthankyou_url ?></a> &nbsp; (<a href="javascript:;" onclick="document.getElementById('cydecthankyou').style.display='block';"><?php _e('change', 'wishlist-member'); ?></a>)
					<?php echo $this->Tooltip("integration-shoppingcart-cydec-tooltips-thankyouurl"); ?>
				</p>
				<div id="cydecthankyou" style="display:none">
					<p>&nbsp;&nbsp;<?php echo $wpm_scregister ?><input type="text" name="cydecthankyou" value="<?php echo $cydecthankyou ?>" size="8" /> <input type="submit" class="button-secondary" value="<?php _e('Change', 'wishlist-member'); ?>" /></p>
				</div>
			</form>
			<form method="post">
				<h2 class="wlm-integration-steps"><?php _e('Step 2. Set a Secret Word:', 'wishlist-member'); ?></h2>
				<p>&nbsp;&nbsp;<input type="text" name="cydecsecret" value="<?php echo $cydecsecret ?>" size="20" maxlength='16' /> <input type="submit" class="button-secondary" value="<?php _e('Update', 'wishlist-member'); ?>" />
					<?php echo $this->Tooltip("integration-shoppingcart-cydec-tooltips-cydecsecret"); ?>

				</p>
			</form>
			<h2 class="wlm-integration-steps"><?php _e('Step 3. Create a product for each Membership Level or Pay Per Post using the assigned SKUs listed below:', 'wishlist-member'); ?></h2>
			<table class="widefat">
				<thead>
					<tr>
						<th scope="col" width="200"><?php _e('Membership Level', 'wishlist-member'); ?></th>
						<th scope="col">
							<?php _e('SKU', 'wishlist-member'); ?>
							<?php echo $this->Tooltip("integration-shoppingcart-cydec-tooltips-sku"); ?>
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
		</blockquote>
		<?php
		include_once($this->pluginDir . '/admin/tooltips/integration.shoppingcart.cydec.tooltips.php');
		// END Interface
	}
}
?>
