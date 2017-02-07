<?php
/*
 * Premium Web Cart Shopping Cart Integration
 * Original Author : Glen Barnhardt
 * Version: $Id: integration.shoppingcart.premiumwebcart.php 2813 2015-07-29 14:30:25Z mike $
 */

$__index__ = 'premimwebcart';
$__sc_options__[$__index__] = 'Premium Web Cart';
$__sc_affiliates__[$__index__] = 'http://wlplink.com/go/premiumwebcart';
//$__sc_videotutorial__[$__index__] = wlm_video_tutorial ( 'integration', 'sc', $__index__ );

if (wlm_arrval($_GET,'cart') == $__index__) {
	if (!$__INTERFACE__) {
		// BEGIN Initialization

		// save POST URL
		if (wlm_arrval($_POST,'posturl_submit') == 'Change') {
			$_POST['pwcthankyou'] = trim(wlm_arrval($_POST,'pwcthankyou'));
			$wpmx = trim(preg_replace('/[^A-Za-z0-9]/', '', $_POST['pwcthankyou']));
			if ($wpmx == $_POST['pwcthankyou']) {
				if ($this->RegURLExists($wpmx, null, 'pwcthankyou')) {
					echo "<div class='error fade'>" . __('<p><b>Error:</b> Post to URL (' . $wpmx . ') is already in use by a Membership Level or another Shopping Cart.  Please try a different one.</p>', 'wishlist-member') . "</div>";
				} else {
					$this->SaveOption('pwcthankyou', $pwcthankyou = $wpmx);
					echo "<div class='updated fade'>" . __('<p>Post To URL Changed.</p>', 'wishlist-member') . "</div>";
				}
			} else {
				echo "<div class='error fade'>" . __('<p><b>Error:</b> Post To URL may only contain letters and numbers.</p>', 'wishlist-member') . "</div>";
			}
		}

		$pwcsecret = $this->GetOption('pwcsecret');
		if (!$pwcsecret) {
			$this->SaveOption('pwcsecret', $pwcsecret = $this->PassGen() . $this->PassGen());
		}
		if (wlm_arrval($_POST,'pwcsecret')) {
			$_POST['pwcsecret'] = trim(wlm_arrval($_POST,'pwcsecret'));
			$wpmy = trim(preg_replace('/[^A-Za-z0-9]/', '', $_POST['pwcsecret']));
			if($_POST['pwcsecret'] != $pwcsecret) {
				$pwcsecret = $_POST['pwcsecret'];
				if ($wpmy == $_POST['pwcsecret']) {
					$this->SaveOption('pwcsecret', $pwcsecret = $wpmy);
					echo "<div class='updated fade'>" . __('<p>Secret Key Updated.</p>', 'wishlist-member') . "</div>";
				} else {
					echo "<div class='error fade'>" . __('<p><b>Error:</b> Secret key may only contain letters and numbers.</p>', 'wishlist-member') . "</div>";
				}
			}
		}

		$pwcapikey = $this->GetOption('pwcapikey');
		if (!$pwcapikey) {
			$this->SaveOption('pwcapikey', '');
		}
		if (wlm_arrval($_POST,'pwcapikey')) {
			$_POST['pwcapikey'] = trim(wlm_arrval($_POST,'pwcapikey'));
			if($_POST['pwcapikey'] != $pwcapikey) {
				$this->SaveOption('pwcapikey', $pwcapikey = $_POST['pwcapikey']);
				echo "<div class='updated fade'>" . __('<p>API Key Updated.</p>', 'wishlist-member') . "</div>";
			}
		}

		$pwcmerchantid = $this->GetOption('pwcmerchantid');
		if (!$pwcmerchantid) {
			$this->SaveOption('pwcmerchantid', '');
		}
		if (wlm_arrval($_POST,'pwcmerchantid')) {
			$_POST['pwcmerchantid'] = trim(wlm_arrval($_POST,'pwcmerchantid'));
			if($_POST['pwcmerchantid'] != $pwcmerchantid) {
				$this->SaveOption('pwcmerchantid', $pwcmerchantid = $_POST['pwcmerchantid']);
				echo "<div class='updated fade'>" . __('<p>Merchant ID Updated.</p>', 'wishlist-member') . "</div>";
			}
		}


		$pwcthankyou = $this->GetOption('pwcthankyou');
		if (!$pwcthankyou) {
			$this->SaveOption('pwcthankyou', $pwcthankyou = $this->MakeRegURL());
		}
		$pwcthankyou_url = $wpm_scregister . $pwcthankyou;


		// END Initialization
	} else {
		// START Interface
		?>
		<!-- Premium Web Cart -->
		<blockquote>
			<h2 class="wlm-integration-steps">Step 1. Configure Premium Web Cart Settings:</h2>
			<form method="post">
				<table class="form-table">
					<tr valign="top">
						<th scope="row">
							<?php _e('Merchant ID', 'wishlist-member'); ?>
						</th>
						<td>
							<input type="text" name="pwcmerchantid" value="<?php echo $pwcmerchantid ?>" size="25" />
							<div><?php _e('Merchant ID is located in the following section: <br><br>Account Settings &raquo; Current Status.', 'wishlist-member'); ?></div>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<?php _e('API Key', 'wishlist-member'); ?>
						</th>
						<td>
							<input type="text" name="pwcapikey" value="<?php echo $pwcapikey ?>" size="60" />
							<div><?php _e('API Key is located in the following section: <br><br>Cart Settings &raquo; Advanced Integration &raquo; API Integration.', 'wishlist-member'); ?></div>
						</td>
					<tr valign="top">
						<th scope="row">
							<?php _e('Secret Word', 'wishlist-member'); ?>
						</th>
						<td>
							<input type="text" name="pwcsecret" value="<?php echo $pwcsecret ?>" size="20" maxlength='16' /><?php echo $this->Tooltip("integration-shoppingcart-pwc-tooltips-pwcsecret"); ?>
							<div><?php _e('The Secret Word is used to generate a hash key for security purposes.', 'wishlist-member'); ?></div>
						</td>
					</tr>
				</table>
				<p class="submit">
					<input type="submit" value="Update Settings" class="button button-primary">
				</p>

				<h2 class="wlm-integration-steps"><?php _e('Step 2. Use the URL below as the Post/Callback URL in Premium Web Cart:', 'wishlist-member'); ?></h2>
				<p><?php _e('The Post/Callback URL is used in the PremiumWebCart integraton and thank you pages.', 'wishlist-member'); ?></p>
				<p>&nbsp;&nbsp;<a href="<?php echo $pwcthankyou_url ?>" onclick="return false"><?php echo $pwcthankyou_url ?></a> &nbsp; (<a href="javascript:;" onclick="document.getElementById('pwcthankyou').style.display='block';"><?php _e('change', 'wishlist-member'); ?></a>)
					<?php echo $this->Tooltip("integration-shoppingcart-pwc-tooltips-thankyouurl"); ?>

				</p>
				<div id="pwcthankyou" style="display:none">
					<p>&nbsp;&nbsp;<?php echo $wpm_scregister ?><input type="text" name="pwcthankyou" value="<?php echo $pwcthankyou ?>" size="8" /> <input type="submit" class="button-secondary" name="posturl_submit" value="<?php _e('Change', 'wishlist-member'); ?>" /></p>
				</div>
			</form>
			<h2 class="wlm-integration-steps"><?php _e('Step 3. Create a product for each Membership Level or Pay Per Post using the assigned SKUs listed below:', 'wishlist-member'); ?></h2>
			<p><?php _e('The Membership Level SKUs specify the membership levels that should be connected to each transaction.', 'wishlist-member'); ?></p>
			<table class="widefat">
				<thead>
					<tr>
						<th scope="col" width="200"><?php _e('Membership Level', 'wishlist-member'); ?></th>
						<th scope="col">
							<?php _e('SKU', 'wishlist-member'); ?>
							<?php echo $this->Tooltip("integration-shoppingcart-pwc-tooltips-sku"); ?>
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
		include_once($this->pluginDir . '/admin/tooltips/integration.shoppingcart.pwc.tooltips.php');
		// END Interface
	}
}
?>
