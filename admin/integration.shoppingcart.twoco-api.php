<?php
/*
 * Stripe Integration Admin Interface
 * Original Author : Mike Lopez
 * Version: $Id: integration.shoppingcart.twoco-api.php 2950 2015-12-14 16:00:04Z mike $
 */

// NOTES
// 1. Days as interval has been removed as it seems this isn't supported in the current PAYMENT API Yet.

$__index__ = 'twocheckoutapi';
$__sc_options__[$__index__] = '2Checkout - Payment API';
$__sc_affiliates__[$__index__] = 'https://www.2checkout.com/payment-api';
//$__sc_videotutorial__[$__index__] = wlm_video_tutorial ( 'integration', 'sc', $__index__ );

$interval_types = array(
	2 => 'WEEKS',
	3 => 'MONTHS',
	4 => 'YEARS'
);
 
$interval_t = explode(',', '1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30');
$currencies = array('USD', 'ARS', 'AUD', 'BRL', 'GBP', 'CAD', 'DKK', 'EUR', 'HKD', 'INR', 'ILS', 'JPY', 'LTL', 'MYR', 'MXN', 'NZD', 'NOK', 'PHP', 'RON', 'RUB', 'SGD', 'ZAR', 'SEK', 'CHF', 'TRY', 'AED');

if (wlm_arrval($_GET, 'cart') == $__index__) {
	if (!$__INTERFACE__) {
		// BEGIN Initialization
		$twocheckoutapithankyouurl = $this->GetOption('twocheckoutapithankyouurl');
		if (!$twocheckoutapithankyouurl) {
			$this->SaveOption('twocheckoutapithankyouurl', $twocheckoutapithankyouurl = $this->MakeRegURL());
		}

		// save POST URL
		if (wlm_arrval($_POST, 'twocheckoutapithankyouurl')) {
			$_POST['twocheckoutapithankyouurl'] = trim(wlm_arrval($_POST, 'twocheckoutapithankyouurl'));
			$wpmx = trim(preg_replace('/[^A-Za-z0-9]/', '', $_POST['twocheckoutapithankyouurl']));
			if ($wpmx == $_POST['twocheckoutapithankyouurl']) {
				if ($this->RegURLExists($wpmx, null, 'twocheckoutapithankyouurl')) {
					echo "<div class='error fade'>" . __('<p><b>Error:</b> stripe Thank You URL (' . $wpmx . ') is already in use by a Membership Level or another Shopping Cart.  Please try a different one.</p>', 'wishlist-member') . "</div>";
				} else {
					$this->SaveOption('twocheckoutapithankyouurl', $twocheckoutapithankyouurl = $wpmx);
					echo "<div class='updated fade'>" . __('<p>Thank You URL Changed.&nbsp; Make sure to update stripe with the same Thank You URL to make it work.</p>', 'wishlist-member') . "</div>";
				}
			} else {
				echo "<div class='error fade'>" . __('<p><b>Error:</b> Thank You URL may only contain letters and numbers.</p>', 'wishlist-member') . "</div>";
			}
		}

		if (isset($_POST['twocheckoutapisettings'])) {
			$twocheckoutapisettings = $_POST['twocheckoutapisettings'];
			$this->SaveOption('twocheckoutapisettings', $twocheckoutapisettings);
		}

		// ======================================================
		// THIS IS THE SETTINGS FROM THE STANDARD 2CO INTEGRATION

		$twocothankyou = $this->GetOption('twocothankyou');
		if (!$twocothankyou) {
			$this->SaveOption('twocothankyou', $twocothankyou = $this->MakeRegURL());
		}

		$twocosecret = $this->GetOption('twocosecret');
		if (!$twocosecret) {
			$this->SaveOption('twocosecret', $twocosecret = $this->PassGen() . $this->PassGen());
		}

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
				echo "<div class='updated fade'>" . __('<p>Secret Key Changed.</p>', 'wishlist-member') . "</div>";
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
				echo "<div class='updated fade'>" . __('<p>Vendor ID Changed.</p>', 'wishlist-member') . "</div>";
			} else {
				echo "<div class='error fade'>" . __('<p><b>Error:</b> Vendor ID may only contain letters and numbers.</p>', 'wishlist-member') . "</div>";
			}
		}
		// END Standard 2CO Settings
		// ======================================================

		$twocheckoutapisettings    = $this->GetOption('twocheckoutapisettings');
		// END Initialization
	} else {
		// START Interface
		?>
		<form method="post" id="stripe_form">
			<h2 class="wlm-integration-steps"><?php _e('Step 1. Set up 2Checkout API Keys:','wishlist-member'); ?></h2>
			<table class="form-table">
				<tr>
					<th scope="row"><?php _e('Seller ID:', 'wishlist-member'); ?></th>
					<td>
						<?php $twocheckoutapi_customer_id = $twocheckoutapisettings['twocheckoutapi_seller_id'] ?>
						<?php $warn = empty($twocheckoutapi_customer_id) ? 'wlmwarn' : null; ?>
						<input class="<?php echo $warn ?>" type="text" name="twocheckoutapisettings[twocheckoutapi_seller_id]" autocomplete="off" value="<?php echo $twocheckoutapi_customer_id ?>" size="24" />

					</td>

				</tr>
				<tr>
					<th scope="row"><?php _e('Publishable Key:', 'wishlist-member'); ?></th>
					<td>
						<?php $twocheckoutapi_username = $twocheckoutapisettings['twocheckoutapi_publishable_key'] ?>
						<?php $warn = empty($twocheckoutapi_username) ? 'wlmwarn' : null; ?>
						<input class="<?php echo $warn ?>" type="text" name="twocheckoutapisettings[twocheckoutapi_publishable_key]" autocomplete="off" value="<?php echo $twocheckoutapi_username ?>" size="48" />

					</td>
				</tr>
				<tr>
					<th scope="row"><?php _e('Private Key:', 'wishlist-member'); ?></th>
					<td>
						<?php $twocheckoutapi_password = $twocheckoutapisettings['twocheckoutapi_private_key'] ?>
						<?php $warn = empty($twocheckoutapi_password) ? 'wlmwarn' : null; ?>
						<input class="<?php echo $warn ?>" type="text" name="twocheckoutapisettings[twocheckoutapi_private_key]" autocomplete="off" value="<?php echo $twocheckoutapi_password ?>" size="48" />

					</td>
				</tr>

				<tr>
					<th scope="row"><?php _e('Enable Sandbox Mode:', 'wishlist-member'); ?></th>
					<td>
						<?php $checked = $twocheckoutapisettings['twocheckoutapi_sandbox'] == 1? 'checked="checked"': null ?>
						<input <?php echo $checked?> type="checkbox" name="twocheckoutapisettings[twocheckoutapi_sandbox]" value="1"/>
					</td>
				</tr>
			</table>
			<br/>
			<input type="submit" class="button-secondary" value="Save 2Checkout API Settings"/>
			<h2 class="wlm-integration-steps"><?php _e('Step 2. Configure 2Checkout Billing Settings:','wishlist-member'); ?></h2>
			<br/>
			<h3 class="wlm-integration">Membership Levels</h3>
			<table class="widefat">
				<thead>
					<tr>
						<th scope="col" width="200"><?php _e('Membership Level', 'wishlist-member'); ?></th>
						<th scope="col" width="200"><?php _e('Recurring Payment', 'wishlist-member'); ?></th>
						<th scope="col" width="200"><?php _e('Initial Amount', 'wishlist-member'); ?></th>
						<th scope="col" width="200"><?php _e('Recurring Amount', 'wishlist-member'); ?></th>
						<th scope="col" width="200"><?php _e('Interval', 'wishlist-member'); ?></th>
						<th scope="col" width="200"><?php _e('Interval Type', 'wishlist-member'); ?></th>
					</tr>
				</thead>

				<tbody>
					<?php
					$alt = 0;
					foreach ((array) $wpm_levels AS $sku => $level):
						?>
						<tr class="wpm_level_row <?php echo $alt++ % 2 ? '' : 'alternate'; ?>" id="wpm_level_row_<?php echo $sku ?>">
							<td><b><?php echo $level['name'] ?></b></td>
							<td>
								<?php $checked = $twocheckoutapisettings['connections'][$sku]['subscription'] == 1? 'checked="checked"': null ?>
								<input <?php echo $checked?> type="checkbox" name="twocheckoutapisettings[connections][<?php echo $sku ?>][subscription]" value="1"/>
							</td>
							<td>
								<input size="7" class="" type="text" name="twocheckoutapisettings[connections][<?php echo $sku ?>][rebill_init_amount]" value="<?php echo $twocheckoutapisettings['connections'][$sku]['rebill_init_amount'] ?>"/>
							</td>
							<td>
								<?php $warn = empty($twocheckoutapisettings['connections'][$sku]['rebill_recur_amount']) ? 'wlmwarn' : null; ?>
								<input size="7" class="checktoggle <?php echo $warn ?>" type="text" name="twocheckoutapisettings[connections][<?php echo $sku ?>][rebill_recur_amount]" value="<?php echo $twocheckoutapisettings['connections'][$sku]['rebill_recur_amount'] ?>"/>
							</td>
							<td>
								<?php $warn = empty($twocheckoutapisettings['connections'][$sku]['rebill_interval']) ? 'wlmwarn' : null; ?>
								<select class="checktoggle" name="twocheckoutapisettings[connections][<?php echo $sku ?>][rebill_interval]">
									<?php foreach ($interval_t as $it): ?>
										<?php $selected = $twocheckoutapisettings['connections'][$sku]['rebill_interval'] == $it ? 'selected="selected"' : '' ?>
										<option <?php echo $selected ?> value="<?php echo $it ?>"><?php echo $it ?></option>
									<?php endforeach; ?>
								</select>

							</td>
							<td>
								<select class="checktoggle" name="twocheckoutapisettings[connections][<?php echo $sku ?>][rebill_interval_type]">
									<?php foreach ($interval_types as $k => $v): ?>
										<?php $selected = ($twocheckoutapisettings['connections'][$sku]['rebill_interval_type'] == $k) ? 'selected="selected"' : null; ?>
										<option <?php echo $selected ?> value="<?php echo $k ?>"><?php echo $v ?></option>
									<?php endforeach; ?>
								</select>
							</td>
						</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
			<?php
				$xposts = array();
				// $xposts = $this->GetPayPerPosts(array('post_title', 'post_type'));
				// $post_types = get_post_types('', 'objects');
			?>
			<?php foreach ($xposts AS $post_type => $posts) : ?>
				<?php if(empty($posts)) continue; ?>
				<h3 class="wlm-integration"><?php echo $post_types[$post_type]->labels->name; ?></h3>
				<table class="widefat">
					<thead>
						<th scope="col" width="200"><?php _e('Membership Level', 'wishlist-member'); ?></th>
						<th scope="col" width="200"><?php _e('Initial Amount', 'wishlist-member'); ?></th>
						<th scope="col" width="200"><?php _e('Recurring Amount', 'wishlist-member'); ?></th>
						<th scope="col" width="200"><?php _e('Interval', 'wishlist-member'); ?></th>
						<th scope="col" width="200"><?php _e('Interval Type', 'wishlist-member'); ?></th>
						<th scope="col" width="200"><?php _e('Last Rebill Date', 'wishlist-member'); ?></th>
					</thead>
				</table>
				<div style="max-height:130px;overflow:auto;">
					<table class="widefat" style="border-top:none">
						<tbody>
							<?php
							$alt = 0;
							foreach ((array) $posts AS $post):
							$sku = sprintf("payperpost-%s", $post->ID);
							?>
						<tr class="wpm_level_row <?php echo $alt++ % 2 ? '' : 'alternate'; ?>" id="wpm_level_row_<?php echo $sku ?>">
							<td><b><?php echo $level['name'] ?></b></td>

							<td>
								<input size="7" class="" type="text" name="twocheckoutapisettings[connections][<?php echo $sku ?>][rebill_init_amount]" value="<?php echo $twocheckoutapisettings['connections'][$sku]['rebill_init_amount'] ?>"/>
							</td>
							<td>
								<?php $warn = empty($twocheckoutapisettings['connections'][$sku]['rebill_recur_amount']) ? 'wlmwarn' : null; ?>
								<input size="7" class="<?php echo $warn ?>" type="text" name="twocheckoutapisettings[connections][<?php echo $sku ?>][rebill_recur_amount]" value="<?php echo $twocheckoutapisettings['connections'][$sku]['rebill_recur_amount'] ?>"/>
							</td>
							<td>
								<?php $warn = empty($twocheckoutapisettings['connections'][$sku]['rebill_interval']) ? 'wlmwarn' : null; ?>
								<select name="twocheckoutapisettings[connections][<?php echo $sku ?>][rebill_interval]">
									<?php foreach ($interval_t as $it): ?>
										<?php $selected = $twocheckoutapisettings['connections'][$sku]['rebill_interval'] == $it ? 'selected="selected"' : '' ?>
										<option <?php echo $selected ?> value="<?php echo $it ?>"><?php echo $it ?></option>
									<?php endforeach; ?>
								</select>

							</td>
							<td>
								<select name="twocheckoutapisettings[connections][<?php echo $sku ?>][rebill_interval_type]">
									<?php foreach ($interval_types as $k => $v): ?>
										<?php $selected = ($twocheckoutapisettings['connections'][$sku]['rebill_interval_type'] == $k) ? 'selected="selected"' : null; ?>
										<option <?php echo $selected ?> value="<?php echo $k ?>"><?php echo $v ?></option>
									<?php endforeach; ?>
								</select>
							</td>
							<td>
							<?php $d = (strtotime($twocheckoutapisettings['connections'][$sku]['rebill_end_date'])) ? date('m/d/Y', strtotime($twocheckoutapisettings['connections'][$sku]['rebill_end_date'])) : null; ?>
							<?php $warn = empty($d) ? 'wlmwarn' : null; ?>
								<input type="text" class="datepicker <?php echo $warn ?>" id="" name="twocheckoutapisettings[connections][<?php echo $sku ?>][rebill_end_date]" value="<?php echo $d ?>"/>
							</td>
						</tr>
				<?php endforeach; ?>
						</tbody>
					</table>
				</div>
				<?php echo $ppp_table_end; ?>
			<?php endforeach; ?>


			<h2 class="wlm-integration-steps"><?php _e("Step 3. Configure 2Checkout Payment Form:", "wishlist-member") ?></h2>
			<table class="form-table">
				<tr>
					<th><?php _e("Heading", "wishlist-member") ?></th>
					<?php $formheading = empty($twocheckoutapisettings['formheading']) ? "Register to %level" : $twocheckoutapisettings['formheading']; ?>
					<td><input type="text" name="twocheckoutapisettings[formheading]" value="<?php echo $formheading ?>"/></td>
				</tr>
				<tr>
					<th><?php _e("Heading Logo", "wishlist-member") ?></th>
					<?php $logo = empty($twocheckoutapisettings['logo']) ? "" : $twocheckoutapisettings['logo']; ?>
					<td><div id="logo-preview"><?php if (!empty($logo)): ?> <img src="<?php echo $logo ?>" style="width: 90px; height: 40px;"></img><?php endif; ?></div><input id="stripe-logo" type="text" name="twocheckoutapisettings[logo]" value="<?php echo $logo ?>"/> <a href="media-upload.php?type=image&amp;TB_iframe=true" class="thickbox logo-upload button-secondary">Browse</a>
					<?php echo $this->Tooltip("integration-shoppingcart-2co-tooltips-browse-logo"); ?>
				</tr>
				<tr>
					<th><?php _e("Button Label", "wishlist-member") ?></th>
					<?php $buttonlabel = empty($twocheckoutapisettings['buttonlabel']) ? "Join %level" : $twocheckoutapisettings['buttonlabel']; ?>
					<td><input type="text" name="twocheckoutapisettings[buttonlabel]" value="<?php echo $buttonlabel ?>"/>
					</td>
				</tr>
				<tr>
					<th><?php _e("Panel Button Label", "wishlist-member") ?></th>
					<?php $panelbuttonlabel = empty($twocheckoutapisettings['panelbuttonlabel']) ? "Pay" : $twocheckoutapisettings['panelbuttonlabel']; ?>
					<td><input type="text" name="twocheckoutapisettings[panelbuttonlabel]" value="<?php echo $panelbuttonlabel ?>"/></td>
				</tr>
				<tr>
					<th><?php _e("Support Email", "wishlist-member") ?></th>
					<?php $supportemail = empty($twocheckoutapisettings['supportemail']) ? "Pay" : $twocheckoutapisettings['supportemail']; ?>
					<td><input type="text" name="twocheckoutapisettings[supportemail]" value="<?php echo $supportemail ?>"/></td>
				</tr>
			</table>
			<h2 class="wlm-integration-steps"><?php _e("Currency Selection", "wishlist-member") ?></h2> 
			<table class="form-table">
				<tr>
					<th>Primary Currency</th>
					<td>
		<?php $currency = empty($twocheckoutapisettings['currency']) ? "yes" : $twocheckoutapisettings['currency']; ?>
						<select name="twocheckoutapisettings[currency]">
							<?php foreach($currencies as $c) : ?>
								<?php $selected = $currency == $c? 'selected="selected"' : null ?>
								<option <?php echo $selected?> value="<?php echo $c?>"><?php echo $c?></option>
							<?php endforeach; ?>
						</select>
					</td>
				</tr>
			</table>

			
			<input type="submit" class="button-primary" value="Update Settings"/></p>
		</form>
		
		<?php 
			$twocothankyou_url = $wpm_scregister . $twocothankyou;
		?>
			<h2 class="wlm-integration-steps"><?php _e('Full Integration Settings','wishlist-member'); ?></h2>
			<p><em>Note: These settings are the same as those in the standard 2Checkout Integration section. <br> Any changes made in this section will also be applied to the standard 2Checkout Integration section.</em></p>
			<?php include('form.integration.shoppingcart.twoco.accountinfo.php'); ?>
			<h2 class="wlm-integration-steps"><?php _e('Instant Notification URL','wishlist-member'); ?></h2>
			<p><?php _e('Use the URL below as the Instant Notification URL for 2Checkout:','wishlist-member'); ?></p>
			<?php include('form.integration.shoppingcart.twoco.notifurl.php'); ?>

		<style type="text/css">
			#logo-preview img { width: 90px; height: 40px;}
		</style>
		<script type="text/javascript">
		jQuery(function($) {

			$('.datepicker').datepicker({
				//prevent date change when field is readonly
				beforeShow: function (input, inst) {
					if ($(input).prop("readonly")) {
						return false;
					}
				}
			});

			function update_row(r) {
				var r = $(r);
				var cb = r.find('input[type=checkbox]');
				if(cb.attr('checked') == 'checked') {
					r.find(':input.checktoggle[type="text"]').prop('readonly', false);
					r.find('select.checktoggle').prop('disabled', false);
				} else {
					r.find(':input.checktoggle[type="text"]').prop('readonly', true);
					r.find('select.checktoggle').prop('disabled', true);
				}
			}

			$('.wpm_level_row').each(function(i, e) {
				update_row(e);
			});

			//prevent change when field is readonly
			$('.wpm_level_row select').on('focus', function(ev) {
				$.data(this, 'val', $(this).val());
			}).on('change', function(ev) {
				if($(this).prop('readonly')) {
					$(this).val($.data(this, 'val'));
				}
			});

			$('.wpm_level_row input[type=checkbox]').on('change', function(e) {
				update_row($(this).parents('tr'));
			});
		});
		var send_to_editor = function(html) {
			imgurl = jQuery('img', html).attr('src');
			var el = jQuery('#stripe-logo');
			el.val(imgurl);
			tb_remove();
			//also update the img preview
			jQuery('#logo-preview').html('<img src="' + imgurl + '">');
		}
		</script>
		<?php
		include_once($this->pluginDir . '/admin/tooltips/integration.shoppingcart.twoco-api.tooltips.php');
		// END Interface
	}
}
?>
