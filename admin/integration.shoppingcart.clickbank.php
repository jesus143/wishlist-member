<?php
/*
 * ClickBank Shopping Cart Integration
 * Original Author : Mike Lopez
 * Version: $Id: integration.shoppingcart.clickbank.php 2813 2015-07-29 14:30:25Z mike $
 */

$__index__ = 'cb';
$__sc_options__[$__index__] = 'ClickBank';
$__sc_affiliates__[$__index__] = 'http://wlplink.com/go/clickbank';
$__sc_videotutorial__[$__index__] = wlm_video_tutorial ( 'integration', 'sc', $__index__ );

if (wlm_arrval($_GET, 'cart') == $__index__) {
	if (!$__INTERFACE__) {
		// BEGIN Initialization
		$cbthankyou = $this->GetOption('cbthankyou');
		if (!$cbthankyou) {
			$this->SaveOption('cbthankyou', $cbthankyou = $this->MakeRegURL());
		}
		$cbsecret = $this->GetOption('cbsecret');
		if (!$cbsecret) {
			$this->SaveOption('cbsecret', $cbsecret = strtoupper($this->PassGen() . $this->PassGen()));
		}
		$cbvendor = strtolower( $this->GetOption('cbvendor') );

		$cbproducts = (array) $this->GetOption('cbproducts');
		if (!$cbproducts) {
			$this->SaveOption('cbproducts', array());
		}

		// save POST URL
		if (wlm_arrval($_POST, 'cbthankyou')) {
			$_POST['cbthankyou'] = trim(wlm_arrval($_POST, 'cbthankyou'));
			$wpmx = trim(preg_replace('/[^A-Za-z0-9]/', '', $_POST['cbthankyou']));
			if ($wpmx == $_POST['cbthankyou']) {
				if ($this->RegURLExists($wpmx, null, 'cbthankyou')) {
					echo "<div class='error fade'>" . __('<p><b>Error:</b> ClickBank Thank You URL (' . $wpmx . ') is already in use by a Membership Level or another Shopping Cart.  Please try a different one.</p>', 'wishlist-member') . "</div>";
				} else {
					$this->SaveOption('cbthankyou', $cbthankyou = $wpmx);
					echo "<div class='updated fade'>" . __('<p>The Thank You URL has been Updated. Please ensure that any active corresponding ClickBank products with the same Thank You URL are also updated within ClickBank if needed.</p>', 'wishlist-member') . "</div>";
				}
			} else {
				echo "<div class='error fade'>" . __('<p><b>Error:</b> Thank You URL may only contain letters and numbers.</p>', 'wishlist-member') . "</div>";
			}
		}
		// save Secret Key and account nickname
		if (wlm_arrval($_POST, 'cbsecret')) {
			$_POST['cbsecret'] = trim(strtoupper(wlm_arrval($_POST, 'cbsecret')));
			$wpmy = trim(preg_replace('/[^A-Za-z0-9]/', '', $_POST['cbsecret']));
			if ($wpmy == $_POST['cbsecret']) {
				$this->SaveOption('cbsecret', $cbsecret = $wpmy);
				echo "<div class='updated fade'>" . __('<p>Secret Key Updated.&nbsp; Make sure to update ClickBank with the same Secret key to make it work.</p>', 'wishlist-member') . "</div>";
			} else {
				echo "<div class='error fade'>" . __('<p><b>Error:</b> Secret key may only contain letters and numbers.</p>', 'wishlist-member') . "</div>";
			}

			$cbvendor = strtolower( trim($_POST['cbvendor']) );
			$this->SaveOption('cbvendor', $cbvendor);
			echo "<div class='updated fade'>" . __('<p>Account Nickname Updated.</p>', 'wishlist-member') . "</div>";
		}
		$cbthankyou_url = $wpm_scregister . $cbthankyou;

		if (wlm_arrval($_POST, 'cbproducts')) {

			$cbproducts_trimmed = array();
			foreach( (array) $_POST['cbproducts'] as $key => $cbproduct) {
				foreach($cbproduct as $itemid) {
					if(!empty($itemid))
						$cbproducts_trimmed[$key][] = $itemid;
				}
			}


			$this->SaveOption('cbproducts', $cbproducts_trimmed);
			$cbproducts = $cbproducts_trimmed;
			echo "<div class='updated fade'>" . __('<p>Product IDs were updated.</p>', 'wishlist-member') . "</div>";
		}
		// END Initialization
	} else {
		// START Interface
		?>
		<!-- ClickBank -->
		<blockquote>
			<form method="post">
				<h2 class="wlm-integration-steps"><?php _e('Step 1. Configure the ClickBank Account Information:', 'wishlist-member'); ?></h2>
				<table class="form-table">
					<tr>
						<th scope="row"><?php _e('ClickBank Secret Key', 'wishlist-member'); ?></th>
						<td>
							<input type="text" name="cbsecret" value="<?php echo $cbsecret ?>" size="32" />
							<?php echo $this->Tooltip("integration-shoppingcart-clickbank-tooltips-Specify-a-Secret-Word"); ?>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php _e('ClickBank Account Nickname', 'wishlist-member'); ?></th>
						<td>
					<input type="text" name="cbvendor" value="<?php echo strtolower($cbvendor); ?>" size="20" />
							<?php echo $this->Tooltip("integration-shoppingcart-clickbank-tooltips-ClickBank-Vendor"); ?>
						</td>
					</tr>
				</table>
				<p class="submit">
					&nbsp;&nbsp;<input type="submit" class="button-secondary" value="<?php _e('Save Account Information', 'wishlist-member'); ?>" />
				</p>
			</form>
			<form method="post">
				<h2 class="wlm-integration-steps"><?php _e('Step 2. Set the Thank You URL and Instant Notification URL in ClickBank to the following URL:', 'wishlist-member'); ?></h2>
				<p>&nbsp;&nbsp;<a href="<?php echo $cbthankyou_url ?>" onclick="return false"><?php echo $cbthankyou_url ?></a> &nbsp; (<a href="javascript:;" onclick="document.getElementById('cbthankyou').style.display = 'block';"><?php _e('change', 'wishlist-member'); ?></a>)
					<?php echo $this->Tooltip("integration-shoppingcart-clickbank-tooltips-thankyouurl"); ?>
				</p>
				<div id="cbthankyou" style="display:none">
					<p>&nbsp;&nbsp;<?php echo $wpm_scregister ?><input type="text" name="cbthankyou" value="<?php echo $cbthankyou ?>" size="8" /> <input type="submit" class="button-secondary" value="<?php _e('Update', 'wishlist-member'); ?>" /></p>
				</div>
			</form>

			<?php
			$cbproducts_json = json_encode($cbproducts);			
			?>
			<form method="post">
				<h2 class="wlm-integration-steps"><?php _e('Step 3: Set the ClickBank Item IDs for each of the Membership Levels:', 'wishlist-member'); ?></h2>
				<table class="widefat">
					<col width="200"></col><col width="200"></col>
					<thead>
						<tr>
							<th scope="col"><?php _e('Membership Level', 'wishlist-member'); ?></th>
							<th scope="col"><?php _e('ClickBank Item ID', 'wishlist-member'); ?></th>
							<th scope="col"><?php _e('ClickBank Payment Link', 'wishlist-member'); ?></th>
						</tr>
					</thead>
						<tbody>
							<?php
							$alt = 0;
							foreach ((array) $wpm_levels AS $sku => $level):
							$product_ids = $cbproducts[$sku];
							?>
							<tr class="<?php echo $alt++ % 2 ? '' : 'alternate'; ?>" id="wpm_level_row_<?php echo $sku ?>">
								<td>
									<b><span><?php echo stripslashes($level['name']) ?></span></b>
								</td>
								<td>
									<?php foreach ((array)$product_ids as $key => $product_id):?>
										<input type="text" name="cbproducts[<?php echo $sku; ?>][]" value="<?php echo strtolower($product_id); ?>" size="16" style="text-align:center">
									<?php endforeach; ?>
										<div class="content" id="wl_<?php echo $sku; ?>">
										<?php if (count($cbproducts[$sku]) == 0) :?>
											<input type="text" name="cbproducts[<?php echo $sku; ?>][]" value="" size="16" style="text-align:center"><br/>
										<?php endif ?>
										</div>
										<a href="#<?php echo $sku; ?>" onclick="cbpID_fields(<?php echo $sku; ?>); "><strong>Add Another Item</strong></a>
								</td>
								<td>
									<?php foreach ((array)$product_ids as $key => $product_id):?>
										<div>
											<span id="cb_pay_link-<?php echo $sku . '_' . $product_id; ?>"></span>
										</div><br>
									<?php endforeach; ?>
								</td>
							<?php
							endforeach;
							?>
							</tr>
						</tbody>
					</table>
				<?php echo $ppp_table_end = '<p><input class="button-secondary" type="submit" value="Save Item IDs"></p>'; ?>
				<?php
				$cbproducts = $cbproducts;
				$ppph2 = __('Pay Per Post Links', 'wishlist-member');
				$pppdesc = '';
				$pppsku_header = __('ClickBank Item ID', 'wishlist-member');
				$pppsku_text = '<input type="text" name="cbproducts[%s][]" value="%s" size="16" style="text-align:center">';
				$ppp_extraheaders = array(__('ClickBank Payment Link', 'wishlist-member'));
				$ppp_extracolumns = array('<span id="cb_pay_link-%s"></span>');
				$ppp_colset = '<col width="200"></col><col width="200"></col>';
				include_once($this->pluginDir . '/admin/integration.shoppingcart-payperpost-skus.php');
				?>
			</form>

		</blockquote>
		<script>
			var cbproducts = <?php echo $cbproducts_json; ?>;
			var cbvendor = '<?php echo trim($cbvendor) ? trim($cbvendor) : '<span style="color:red">ACCOUNT_NICKNAME</span>'; ?>';

			cbvendor=cbvendor.toLowerCase();
			
			for (var index in cbproducts) {
				var p_id = cbproducts[index];				

				for (var id in p_id) {
					if (cbproducts[index] && p_id[id] != '') {
						var cb_p_id = p_id[id].toLowerCase();						
						jQuery('span#cb_pay_link-' + index + '_' + p_id[id]).html('http://' + cb_p_id + '.' + cbvendor + '.pay.clickbank.net');						
					}
				}
			}
			
			function cbpID_fields(id) {
				var cbpinputname = 'cbproducts[';
				var cbpinputname = cbpinputname.concat(id) + '][]';
				var divID = 'wl_';
				var divID = divID.concat(id);	
				
				document.getElementById(divID).innerHTML += '<input size="16" type="text" name="'+cbpinputname+'" style="text-align:center">\r\n';
			}			
			
		</script>

		<?php
		include_once($this->pluginDir . '/admin/tooltips/integration.shoppingcart.clickbank.tooltips.php');
		// END Interface
	}
}