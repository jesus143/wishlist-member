<?php
/*
 * Stripe Integration Admin Interface
 * Original Author : Mike Lopez
 * Version: $Id: integration.shoppingcart.stripe.php 2950 2015-12-14 16:00:04Z mike $
 */
$__index__ = 'stripe';
$__sc_options__[$__index__] = 'Stripe';
$__sc_affiliates__[$__index__] = 'https://stripe.com/';
$__sc_videotutorial__[$__index__] = wlm_video_tutorial ( 'integration', 'sc', $__index__ );

define('MAX_PLAN_COUNT', 999);
$currencies = array('USD','AED','AFN','ALL','AMD','ANG','AOA','ARS','AUD','AWG','AZN','BAM','BBD','BDT','BGN','BIF','BMD','BND','BOB','BRL','BSD','BWP','BZD','CAD','CDF','CHF','CLP','CNY','COP','CRC','CVE','CZK','DJF','DKK','DOP','DZD','EEK','EGP','ETB','EUR','FJD','FKP','GBP','GEL','GIP','GMD','GNF','GTQ','GYD','HKD','HNL','HRK','HTG','HUF','IDR','ILS','INR','ISK','JMD','JPY','KES','KGS','KHR','KMF','KRW','KYD','KZT','LAK','LBP','LKR','LRD','LSL','LTL','LVL','MAD','MDL','MGA','MKD','MNT','MOP','MRO','MUR','MVR','MWK','MXN','MYR','MZN','NAD','NGN','NIO','NOK','NPR','NZD','PAB','PEN','PGK','PHP','PKR','PLN','PYG','QAR','RON','RSD','RUB','RWF','SAR','SBD','SCR','SEK','SGD','SHP','SLL','SOS','SRD','STD','SVC','SZL','THB','TJS','TOP','TRY','TTD','TWD','TZS','UAH','UGX','UYU','UZS','VEF','VND','VUV','WST','XAF','XCD','XOF','XPF','YER','ZAR','ZMW');

if (wlm_arrval($_GET, 'cart') == $__index__) {
	if (!$__INTERFACE__) {
		// BEGIN Initialization
		$stripethankyou = $this->GetOption('stripethankyou');
		if (!$stripethankyou) {
			$this->SaveOption('stripethankyou', $stripethankyou = $this->MakeRegURL());
		}

		// save POST URL
		if (wlm_arrval($_POST, 'stripethankyou')) {
			$_POST['stripethankyou'] = trim(wlm_arrval($_POST, 'stripethankyou'));
			$wpmx = trim(preg_replace('/[^A-Za-z0-9]/', '', $_POST['stripethankyou']));
			if ($wpmx == $_POST['stripethankyou']) {
				if ($this->RegURLExists($wpmx, null, 'stripethankyou')) {
					echo "<div class='error fade'>" . __('<p><b>Error:</b> stripe Thank You URL (' . $wpmx . ') is already in use by a Membership Level or another Shopping Cart.  Please try a different one.</p>', 'wishlist-member') . "</div>";
				} else {
					$this->SaveOption('stripethankyou', $stripethankyou = $wpmx);
					echo "<div class='updated fade'>" . __('<p>Thank You URL Changed.&nbsp; Make sure to update stripe with the same Thank You URL to make it work.</p>', 'wishlist-member') . "</div>";
				}
			} else {
				echo "<div class='error fade'>" . __('<p><b>Error:</b> Thank You URL may only contain letters and numbers.</p>', 'wishlist-member') . "</div>";
			}
		}
		if (isset($_POST['stripeapikey'])) {
			$stripeapikey = trim($_POST['stripeapikey']);
			$this->SaveOption('stripeapikey', $stripeapikey);
		}

		if (isset($_POST['stripepublishablekey'])) {
			$stripepublishablekey = trim($_POST['stripepublishablekey']);
			$this->SaveOption('stripepublishablekey', $stripepublishablekey);
		}

		if (wlm_arrval($_POST, 'stripeconnections')) {
			$stripeconnections = $_POST['stripeconnections'];
			$this->SaveOption('stripeconnections', $stripeconnections);
		}

		if (isset($_POST['stripesettings'])) {
			$stripesettings = $_POST['stripesettings'];
			$this->SaveOption('stripesettings', $stripesettings);
		}

		$stripethankyou_url = $wpm_scregister . $stripethankyou;
		$stripeapikey = $this->GetOption('stripeapikey');
		$stripepublishablekey = $this->GetOption('stripepublishablekey');
		$stripeconnections = $this->GetOption('stripeconnections');
		$stripesettings = $this->GetOption('stripesettings');
		// END Initialization
	} else {
		// START Interface
		
		if (!function_exists('curl_init')) {
		  echo "<div class='error'>" . __('<p><strong>Important! : </strong> Stripe integration requires the CURL PHP extension. Please ask your web hosting provider to enable it.</p>', 'wishlist-member') . "</div>";
		}
		if (!function_exists('json_decode')) {
		  echo "<div class='error'>" . __('<p><strong>Important! : </strong> Stripe integration requires the JSON PHP extension. Please ask your web hosting provider to enable it.</p>', 'wishlist-member') . "</div>";
		}
		if (!function_exists('mb_detect_encoding')) {
			echo "<div class='error'>" . __('<p><strong>Important! : </strong> Stripe integration requires the Multibyte String PHP extension. Please ask your web hosting provider to enable it.</p>', 'wishlist-member') . "</div>";
		}

   ?>
		<?php
		$status = "<span class='wlm-color-warning'>Please provide Stripe API Keys.</span>";
		if ( !empty($stripeapikey) ) {
			try {
				$status = Stripe::setApiKey($stripeapikey);
				$plans = Stripe_Plan::all(array('count' => MAX_PLAN_COUNT));
				$api_type = strpos($stripeapikey, "test") === false ? "LIVE" : "TEST";
				$status = "<strong><span class='wlm-color-success'>Connected<span></strong> using a <strong>{$api_type}</strong> key.";
			} catch (Exception $e) {
				$status = "<strong><span class='wlm-color-error'>Unable to connect stripe api</span>. Stripe reason:</strong>" .$e->getMessage();
			}
		}
		?>
		<form method="post" id="stripe_form">
			<h2 class="wlm-integration-steps">Step 1. Configure API Keys:</h2>
			<p><?php _e('API Keys can be located in Stripe using the link below: <br> ', 'wishlist-member'); ?><a href="https://dashboard.stripe.com/account/apikeys" target="_blank">https://dashboard.stripe.com/account/apikeys</a></p>
			<table class="form-table">
				<tr>
					<th>Secret Key</th>
					<td><input type="text" style="width: 300px" name="stripeapikey" value="<?php echo $stripeapikey ?>"><br/></td>
				</tr>
				<tr>
					<th>Publishable Key</th>
					<td><input type="text" style="width: 300px" name="stripepublishablekey" value="<?php echo $stripepublishablekey ?>"><br/></td>
				</tr>
				<tr>
					<th>Status</th>
					<td>
						<em><?php echo $status; ?></em>
					</td>
				</tr>
			</table>
			<p class="submit">
				<input type="submit" class="button button-primary" value="Update API Settings">
			</p>
			<!--
			<h2 class="wlm-integration-steps">Error Page</h2>
			<?php $pages = get_pages('exclude=' . implode(',', $this->ExcludePages(array(), true))); ?>
			<p>
			<select name="stripesettings[errorpage]">
			<?php foreach ($pages as $p): ?>
				<?php $selected = $p->ID == $stripesettings['errorpage'] ? 'selected="selected"' : null ?>
							<option <?php echo $selected ?> value="<?php echo $p->ID ?>"><?php echo $p->post_title ?></option>
			<?php endforeach; ?>
			</select> Or automatically create one <input type="checkbox" name="stripesettings[createerrorpage]" value="1">
			</p>
			-->
			<h2 class="wlm-integration-steps">Step 2. Configure Billing Settings:</h2>
			<br/>
			<h3 class="wlm-integration">Membership Levels</h3>
			<table class="widefat">
				<thead>
					<tr>
						<th scope="col" width="200"><?php _e('Membership Level', 'wishlist-member'); ?></th>
						<th scope="col"><?php _e('Hook With', 'wishlist-member'); ?></th>
						<th scope="col"><?php _e('Amount', 'wishlist-member'); ?></th>
						<th scope="col"><?php _e('Stripe Plan', 'wishlist-member'); ?></th>
						<th scope="col"><?php _e('Button Code', 'wishlist-member'); ?></th>
					</tr>
				</thead>

				<tbody>
					<?php $alt = 0; foreach ((array) $wpm_levels AS $sku => $level):?>
						<tr class="<?php echo $alt++ % 2 ? '' : 'alternate'; ?>" id="wpm_level_row_<?php echo $sku ?>">
							<td>
								<input type="hidden" name="stripeconnections[<?php echo $sku ?>][sku]" value="<?php echo $sku ?>"/>
								<input type="hidden" name="stripeconnections[<?php echo $sku ?>][membershiplevel]" value="<?php echo $level['name'] ?>"/>
								<b><?php echo $level['name'] ?></b>
							</td>
							<td>
								<input class="is_subscription" <?php if ($stripeconnections[$sku]['subscription'] == 1) echo 'checked' ?> type="radio" name="stripeconnections[<?php echo $sku ?>][subscription]" value="1"/> Stripe Plan&nbsp;&nbsp;&nbsp;
								<input class="is_subscription" <?php if ($stripeconnections[$sku]['subscription'] == 0) echo 'checked' ?> type="radio" name="stripeconnections[<?php echo $sku ?>][subscription]" value="0"/> One-time Payment
							</td>
							<td class="amount"><input size="4" type="text" value="<?php echo $stripeconnections[$sku]['amount'] ?>" name="stripeconnections[<?php echo $sku ?>][amount]"/></td>
							<td class="plans">
								<!--<u style="font-size:1.2em"><?php echo $sku ?></u>-->
								<select name="stripeconnections[<?php echo $sku ?>][plan]">
									<option value="">Select a plan</option>
									<?php foreach ($plans->data as $p): ?>
										<?php $selected = $p['id'] == $stripeconnections[$sku]['plan'] ? 'selected="selected"' : null ?>
										<option <?php echo $selected ?> value="<?php echo $p['id'] ?>"><?php echo $p['name'] ?></option>
									<?php endforeach; ?>
								</select>
							</td>
							<td>[wlm_stripe_btn sku=<?php echo $sku ?>]</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			<p><a href="#" class="button-secondary" onclick="javascript:jQuery('#stripe_form')[0].reset();
					return false;">Reset All Settings</a> <input type="submit" class="button-primary" value="Update All Settings"/></p>
			<?php
				$xposts = $this->GetPayPerPosts(array('post_title', 'post_type'));
				$post_types = get_post_types('', 'objects');
				$has_ppp = false; //used for checking if PPP is empty..
			?>
			<?php foreach ($xposts AS $post_type => $posts) : ?>
				<?php if ( empty( $posts ) ) continue; //lets skipped if empty ?>
				<h3 class="wlm-integration"><?php echo $post_types[$post_type]->labels->name; ?></h3>
				<table class="widefat">
					<thead>
						<tr>
							<th scope="col" width="33%"><?php _e('Membership Level', 'wishlist-member'); ?></th>
							<th scope="col" width="33%"><?php _e('Amount', 'wishlist-member'); ?></th>
							<th scope="col" width="33%"><?php _e('Button Code', 'wishlist-member'); ?></th>
							</tr>
					</thead>
				</table>
				<div style="max-height:130px;overflow:auto;">
					<table class="widefat" style="border-top:none">
						<tbody>
							<?php
								$alt = 0;
								foreach ((array) $posts AS $post):
								$has_ppp = true;
								$sku = sprintf("payperpost-%s", $post->ID);
							?>
							<tr class="<?php echo $alt++ % 2 ? '' : 'alternate'; ?>" id="wpm_level_row_<?php echo $sku ?>">
								<td width="33%">
									<input type="hidden" name="stripeconnections[<?php echo $sku ?>][sku]" value="<?php echo $sku ?>"/>
									<input type="hidden" name="stripeconnections[<?php echo $sku ?>][membershiplevel]" value="<?php echo $post->post_title; ?>"/>
									<input type="hidden" name="stripeconnections[<?php echo $sku ?>][subscription]" value="0"/>
									<b><?php echo $post->post_title; ?></b>
								</td>
								<td class="amount" width="33%"><input size="4" type="text" value="<?php echo $stripeconnections[$sku]['amount'] ?>" name="stripeconnections[<?php echo $sku ?>][amount]"/></td>
								<td width="33%">[wlm_stripe_btn sku=<?php echo $sku ?>]</td>
							</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
				<?php echo $ppp_table_end; ?>
			<?php endforeach; ?>
			<?php if ( $has_ppp ) : //if ppp is not empty, we'll show this buttons ?>
				<p>
					<a href="#" class="button-secondary" onclick="javascript:jQuery('#stripe_form')[0].reset();return false;">Reset All Settings</a>
					<input type="submit" class="button-primary" value="Update All Settings"/>
				</p>
			<?php endif; ?>


			<h2 class="wlm-integration-steps"><?php _e("Step 3. Customize Payment Form:", "wishlist-member") ?></h2>
			<table class="form-table">
				<tr>
					<th><?php _e("Heading", "wishlist-member") ?></th>
		<?php $formheading = empty($stripesettings['formheading']) ? "Register to %level" : $stripesettings['formheading']; ?>
					<td><input type="text" name="stripesettings[formheading]" value="<?php echo $formheading ?>"/></td>
				</tr>
				<tr>
					<th><?php _e("Heading Logo", "wishlist-member") ?></th>
		<?php $logo = empty($stripesettings['logo']) ? "" : $stripesettings['logo']; ?>
					<td><div id="logo-preview"><?php if (!empty($logo)): ?> <img src="<?php echo $logo ?>" style="width: 90px; height: 40px;"></img><?php endif; ?></div><input id="stripe-logo" type="text" name="stripesettings[logo]" value="<?php echo $logo ?>"/> <a href="media-upload.php?type=image&amp;TB_iframe=true" class="thickbox logo-upload button-secondary">Change</a>
				</tr>
				<tr>
					<th><?php _e("Button Label", "wishlist-member") ?></th>
		<?php $buttonlabel = empty($stripesettings['buttonlabel']) ? "Join %level" : $stripesettings['buttonlabel']; ?>
					<td><input type="text" name="stripesettings[buttonlabel]" value="<?php echo $buttonlabel ?>"/>
					</td>
				</tr>
				<tr>
					<th><?php _e("Panel Button Label", "wishlist-member") ?></th>
		<?php $panelbuttonlabel = empty($stripesettings['panelbuttonlabel']) ? "Pay" : $stripesettings['panelbuttonlabel']; ?>
					<td><input type="text" name="stripesettings[panelbuttonlabel]" value="<?php echo $panelbuttonlabel ?>"/></td>
				</tr>
				<tr>
					<th><?php _e("Support Email", "wishlist-member") ?></th>
		<?php $supportemail = empty($stripesettings['supportemail']) ? "Pay" : $stripesettings['supportemail']; ?>
					<td><input type="text" name="stripesettings[supportemail]" value="<?php echo $supportemail ?>"/></td>
				</tr>
			</table>
			<h2 class="wlm-integration-steps"><?php _e("Step 4. Miscellaneous Settings:", "wishlist-member") ?></h2>
			<table class="form-table">
				<tr>
					<th><?php _e("Cancellation Redirect", "wishlist-member") ?></th>
					<td>
		<?php $pages = get_pages('exclude=' . implode(',', $this->ExcludePages(array(), true))); ?>
						<select name="stripesettings[cancelredirect]">
							<option value="">Select A Page</option>
							<?php foreach ($pages as $p): ?>
								<?php $selected = ($p->ID == $stripesettings['cancelredirect']) ? 'selected="selected"' : null ?>
								<option <?php echo $selected ?> value="<?php echo $p->ID ?>"><?php echo $p->post_title ?></option>
		<?php endforeach; ?>
						</select>
					</td>
				</tr>
				<tr>
					<th>Primary Currency</th>
					<td>
		<?php $currency = empty($stripesettings['currency']) ? "yes" : $stripesettings['currency']; ?>
						<select name="stripesettings[currency]">
							<?php foreach($currencies as $c) : ?>
								<?php $selected = $currency == $c? 'selected="selected"' : null ?>
								<option <?php echo $selected?> value="<?php echo $c?>"><?php echo $c?></option>
							<?php endforeach; ?>
						</select>
					</td>
				</tr>
				<tr>
					<th>When a subscription ends: </th>
					<td>
		<?php $endsubscriptiontiming = empty($stripesettings['endsubscriptiontiming']) ? "immediate" : $stripesettings['endsubscriptiontiming']; ?>
						<input type="radio" <?php if ($endsubscriptiontiming == 'immediate') echo 'checked="checked"' ?> name="stripesettings[endsubscriptiontiming]" value="immediate"/>&nbsp;&nbsp;Cancel the member immediately
						<br/>
						<input type="radio" <?php if ($endsubscriptiontiming == 'periodend') echo 'checked="checked"' ?> name="stripesettings[endsubscriptiontiming]" value="periodend"/>&nbsp;&nbsp;Cancel the member at the end of the current billing
					</td>
				</tr>
				<tr>
					<th>Prorate Upgrades</th>
					<td>
		<?php $prorate = empty($stripesettings['prorate']) ? "yes" : $stripesettings['prorate']; ?>
						<input type="radio" <?php if ($prorate == 'yes') echo 'checked="checked"' ?> name="stripesettings[prorate]" value="yes"/>&nbsp;&nbsp;Yes
						<br/>
						<input type="radio" <?php if ($prorate == 'no') echo 'checked="checked"' ?> name="stripesettings[prorate]" value="no"/>&nbsp;&nbsp;No
					</td>
				</tr>
			</table>
			<h2 class="wlm-integration-steps">Step 5. Configure Web Hook:</h2>
			<p><?php _e('Copy the URL below and paste it into Stripe in the following section: <br> ', 'wishlist-member'); ?> <a href="https://dashboard.stripe.com/account/webhooks" target="_blank">Stripe Webhooks</a></p>
			<p>
				<input readonly="readonly" style="width: 55%;padding:4px; color:#1A1A1A;" type="text" value="<?php echo $stripethankyou_url ?>?stripe_action=sync" />
			</p>
			<p>&nbsp;</p>
			<p><a href="#" class="button-secondary" onclick="javascript:jQuery('#stripe_form')[0].reset();
					return false;">Reset All Settings</a> <input type="submit" class="button-primary" value="Update All Settings"/></p>
		</form>
		<style type="text/css">
			#logo-preview img { width: 90px; height: 40px;}
		</style>
		<script type="text/javascript">
				var send_to_editor = function(html) {
					imgurl = jQuery('img', html).attr('src');
					var el = jQuery('#stripe-logo');
					el.val(imgurl);
					tb_remove();
					//also update the img preview
					jQuery('#logo-preview').html('<img src="' + imgurl + '">');
				}

				jQuery(function($) {
					function update_fields(el, tr) {
						if (el.val() == 1) {
							tr.find('.amount').find('input').attr('disabled', true).val('');
							tr.find('.plans').find('select').removeAttr('disabled');
						} else {
							tr.find('.plans').find('select').attr('disabled', true).val('');
							tr.find('.amount').find('input').removeAttr('disabled');
						}
					}
					$('.is_subscription').live('change', function() {
						var el = $(this);
						var tr = el.parents('tr');
						update_fields(el, tr);
					});

					$('.is_subscription:checked').each(function(i, el) {
						var el = $(this);
						var tr = el.parents('tr');
						update_fields(el, tr);
					});
				});
		</script>
		<?php
		include_once($this->pluginDir . '/admin/tooltips/integration.shoppingcart.stripe.tooltips.php');
		// END Interface
	}
}
?>
