<?php
/*
 * Stripe Integration Admin Interface
 * Original Author : Mike Lopez
 * Version: $Id: integration.shoppingcart.eway.php 2813 2015-07-29 14:30:25Z mike $
 */
$__index__ = 'eway';
$__sc_options__[$__index__] = 'eWAY';
$__sc_affiliates__[$__index__] = '#';
//$__sc_videotutorial__[$__index__] = wlm_video_tutorial ( 'integration', 'sc', $__index__ );

$interval_types = array(
	1 => 'DAYS',
	2 => 'WEEKS',
	3 => 'MONTHS',
	4 => 'YEARS'
);

$interval_t = explode(',', '1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30');

if (wlm_arrval($_GET, 'cart') == $__index__) {
	if (!$__INTERFACE__) {
		// BEGIN Initialization
		$ewaythankyouurl = $this->GetOption('ewaythankyouurl');
		if (!$ewaythankyouurl) {
			$this->SaveOption('ewaythankyouurl', $ewaythankyouurl = $this->MakeRegURL());
		}

		// save POST URL
		if (wlm_arrval($_POST, 'ewaythankyouurl')) {
			$_POST['ewaythankyouurl'] = trim(wlm_arrval($_POST, 'ewaythankyouurl'));
			$wpmx = trim(preg_replace('/[^A-Za-z0-9]/', '', $_POST['ewaythankyouurl']));
			if ($wpmx == $_POST['ewaythankyouurl']) {
				if ($this->RegURLExists($wpmx, null, 'ewaythankyouurl')) {
					echo "<div class='error fade'>" . __('<p><b>Error:</b> stripe Thank You URL (' . $wpmx . ') is already in use by a Membership Level or another Shopping Cart.  Please try a different one.</p>', 'wishlist-member') . "</div>";
				} else {
					$this->SaveOption('ewaythankyouurl', $ewaythankyouurl = $wpmx);
					echo "<div class='updated fade'>" . __('<p>Thank You URL Changed.&nbsp; Make sure to update stripe with the same Thank You URL to make it work.</p>', 'wishlist-member') . "</div>";
				}
			} else {
				echo "<div class='error fade'>" . __('<p><b>Error:</b> Thank You URL may only contain letters and numbers.</p>', 'wishlist-member') . "</div>";
			}
		}

		if (isset($_POST['ewaysettings'])) {
			$ewaysettings = $_POST['ewaysettings'];
			$this->SaveOption('ewaysettings', $ewaysettings);
		}


		$ewaysettings    = $this->GetOption('ewaysettings');
		// END Initialization
	} else {
		// START Interface
		?>
		<form method="post" id="stripe_form">
			<h2 class="wlm-integration-steps"><?php _e('Step 1. Set up eWay API Keys:','wishlist-member'); ?></h2>
			<table class="form-table">
				<tr>
					<th scope="row"><?php _e('Customer ID', 'wishlist-member'); ?></th>
					<td>
						<?php $eway_customer_id = $ewaysettings['eway_customer_id'] ?>
						<?php $warn = empty($eway_customer_id) ? 'wlmwarn' : null; ?>
						<input class="<?php echo $warn ?>" type="text" name="ewaysettings[eway_customer_id]" autocomplete="off" value="<?php echo $eway_customer_id ?>" size="24" />
						<span class="description">Use 87654321 for testing purposes</span>
					</td>

				</tr>
				<tr>
					<th scope="row"><?php _e('eWAY Username', 'wishlist-member'); ?></th>
					<td>
						<?php $eway_username = $ewaysettings['eway_username'] ?>
						<?php $warn = empty($eway_username) ? 'wlmwarn' : null; ?>
						<input class="<?php echo $warn ?>" type="text" name="ewaysettings[eway_username]" autocomplete="off" value="<?php echo $eway_username ?>" size="24" />
						<span class="description">Use test@eway.com.au for testing purposes</span>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php _e('eWAY Password', 'wishlist-member'); ?></th>
					<td>
						<?php $eway_password = $ewaysettings['eway_password'] ?>
						<?php $warn = empty($eway_password) ? 'wlmwarn' : null; ?>
						<input class="<?php echo $warn ?>" type="password" name="ewaysettings[eway_password]" autocomplete="off" value="<?php echo $eway_password ?>" size="24" />
						<span class="description">Use test123 for testing purposes</span>
					</td>
				</tr>

				<tr>
					<th scope="row"><?php _e('Enable Sandbox Mode', 'wishlist-member'); ?></th>
					<td>
						<?php $checked = $ewaysettings['eway_sandbox'] == 1? 'checked="checked"': null ?>
						<input <?php echo $checked?> type="checkbox" name="ewaysettings[eway_sandbox]" value="1"/>
					</td>
				</tr>
			</table>
			<br/>
			<input type="submit" class="button-secondary" value="Save eWAY API Settings"/>
			<h2 class="wlm-integration-steps"><?php _e('Step 2. Configure eWay Billing Settings:','wishlist-member'); ?></h2>
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
						<th scope="col" width="200"><?php _e('Last Rebill Date', 'wishlist-member'); ?></th>
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
								<?php $checked = $ewaysettings['connections'][$sku]['subscription'] == 1? 'checked="checked"': null ?>
								<input <?php echo $checked?> type="checkbox" name="ewaysettings[connections][<?php echo $sku ?>][subscription]" value="1"/>
							</td>
							<td>
								<input size="7" class="" type="text" name="ewaysettings[connections][<?php echo $sku ?>][rebill_init_amount]" value="<?php echo $ewaysettings['connections'][$sku]['rebill_init_amount'] ?>"/>
							</td>
							<td>
								<?php $warn = empty($ewaysettings['connections'][$sku]['rebill_recur_amount']) ? 'wlmwarn' : null; ?>
								<input size="7" class="checktoggle <?php echo $warn ?>" type="text" name="ewaysettings[connections][<?php echo $sku ?>][rebill_recur_amount]" value="<?php echo $ewaysettings['connections'][$sku]['rebill_recur_amount'] ?>"/>
							</td>
							<td>
								<?php $warn = empty($ewaysettings['connections'][$sku]['rebill_interval']) ? 'wlmwarn' : null; ?>
								<select class="checktoggle" name="ewaysettings[connections][<?php echo $sku ?>][rebill_interval]">
									<?php foreach ($interval_t as $it): ?>
										<?php $selected = $ewaysettings['connections'][$sku]['rebill_interval'] == $it ? 'selected="selected"' : '' ?>
										<option <?php echo $selected ?> value="<?php echo $it ?>"><?php echo $it ?></option>
									<?php endforeach; ?>
								</select>

							</td>
							<td>
								<select class="checktoggle" name="ewaysettings[connections][<?php echo $sku ?>][rebill_interval_type]">
									<?php foreach ($interval_types as $k => $v): ?>
										<?php $selected = ($ewaysettings['connections'][$sku]['rebill_interval_type'] == $k) ? 'selected="selected"' : null; ?>
										<option <?php echo $selected ?> value="<?php echo $k ?>"><?php echo $v ?></option>
									<?php endforeach; ?>
								</select>
							</td>
							<td>
							<?php $d = (strtotime($ewaysettings['connections'][$sku]['rebill_end_date'])) ? date('m/d/Y', strtotime($ewaysettings['connections'][$sku]['rebill_end_date'])) : null; ?>
							<?php $warn = empty($d) ? 'wlmwarn' : null; ?>
								<input type="text" class="checktoggle datepicker <?php echo $warn ?>" id="" name="ewaysettings[connections][<?php echo $sku ?>][rebill_end_date]" value="<?php echo $d ?>"/>
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
								<input size="7" class="" type="text" name="ewaysettings[connections][<?php echo $sku ?>][rebill_init_amount]" value="<?php echo $ewaysettings['connections'][$sku]['rebill_init_amount'] ?>"/>
							</td>
							<td>
								<?php $warn = empty($ewaysettings['connections'][$sku]['rebill_recur_amount']) ? 'wlmwarn' : null; ?>
								<input size="7" class="<?php echo $warn ?>" type="text" name="ewaysettings[connections][<?php echo $sku ?>][rebill_recur_amount]" value="<?php echo $ewaysettings['connections'][$sku]['rebill_recur_amount'] ?>"/>
							</td>
							<td>
								<?php $warn = empty($ewaysettings['connections'][$sku]['rebill_interval']) ? 'wlmwarn' : null; ?>
								<select name="ewaysettings[connections][<?php echo $sku ?>][rebill_interval]">
									<?php foreach ($interval_t as $it): ?>
										<?php $selected = $ewaysettings['connections'][$sku]['rebill_interval'] == $it ? 'selected="selected"' : '' ?>
										<option <?php echo $selected ?> value="<?php echo $it ?>"><?php echo $it ?></option>
									<?php endforeach; ?>
								</select>

							</td>
							<td>
								<select name="ewaysettings[connections][<?php echo $sku ?>][rebill_interval_type]">
									<?php foreach ($interval_types as $k => $v): ?>
										<?php $selected = ($ewaysettings['connections'][$sku]['rebill_interval_type'] == $k) ? 'selected="selected"' : null; ?>
										<option <?php echo $selected ?> value="<?php echo $k ?>"><?php echo $v ?></option>
									<?php endforeach; ?>
								</select>
							</td>
							<td>
							<?php $d = (strtotime($ewaysettings['connections'][$sku]['rebill_end_date'])) ? date('m/d/Y', strtotime($ewaysettings['connections'][$sku]['rebill_end_date'])) : null; ?>
							<?php $warn = empty($d) ? 'wlmwarn' : null; ?>
								<input type="text" class="datepicker <?php echo $warn ?>" id="" name="ewaysettings[connections][<?php echo $sku ?>][rebill_end_date]" value="<?php echo $d ?>"/>
							</td>
						</tr>
				<?php endforeach; ?>
						</tbody>
					</table>
				</div>
				<?php echo $ppp_table_end; ?>
			<?php endforeach; ?>


			<h2 class="wlm-integration-steps"><?php _e('Step 3. Configure eWay Payment Form:','wishlist-member'); ?></h2>
			<table class="form-table">
				<tr>
					<th><?php _e("Heading", "wishlist-member") ?></th>
					<?php $formheading = empty($ewaysettings['formheading']) ? "Register to %level" : $ewaysettings['formheading']; ?>
					<td><input type="text" name="ewaysettings[formheading]" value="<?php echo $formheading ?>"/></td>
				</tr>
				<tr>
					<th><?php _e("Heading Logo", "wishlist-member") ?></th>
					<?php $logo = empty($ewaysettings['logo']) ? "" : $ewaysettings['logo']; ?>
					<td><div id="logo-preview"><?php if (!empty($logo)): ?> <img src="<?php echo $logo ?>" style="width: 90px; height: 40px;"></img><?php endif; ?></div><input id="stripe-logo" type="text" name="ewaysettings[logo]" value="<?php echo $logo ?>"/> <a href="media-upload.php?type=image&amp;TB_iframe=true" class="thickbox logo-upload button-secondary">Browse</a>
					<?php echo $this->Tooltip("integration-shoppingcart-eway-tooltips-browse-logo"); ?>
				</tr>
				<tr>
					<th><?php _e("Button Label", "wishlist-member") ?></th>
					<?php $buttonlabel = empty($ewaysettings['buttonlabel']) ? "Join %level" : $ewaysettings['buttonlabel']; ?>
					<td><input type="text" name="ewaysettings[buttonlabel]" value="<?php echo $buttonlabel ?>"/>
					</td>
				</tr>
				<tr>
					<th><?php _e("Panel Button Label", "wishlist-member") ?></th>
					<?php $panelbuttonlabel = empty($ewaysettings['panelbuttonlabel']) ? "Pay" : $ewaysettings['panelbuttonlabel']; ?>
					<td><input type="text" name="ewaysettings[panelbuttonlabel]" value="<?php echo $panelbuttonlabel ?>"/></td>
				</tr>
				<tr>
					<th><?php _e("Support Email", "wishlist-member") ?></th>
					<?php $supportemail = empty($ewaysettings['supportemail']) ? "Pay" : $ewaysettings['supportemail']; ?>
					<td><input type="text" name="ewaysettings[supportemail]" value="<?php echo $supportemail ?>"/></td>
				</tr>
			</table>
			<!--<h2><?php _e("Misc", "wishlist-member") ?></h2> -->
			<table class="form-table" style="display:none">
				<tr>
					<th><?php _e("Cancellation Redirect", "wishlist-member") ?></th>
					<td>
						<?php $pages = get_pages('exclude=' . implode(',', $this->ExcludePages(array(), true))); ?>
						<select name="ewaysettings[cancelredirect]">
							<option value="">Select A Page</option>
							<?php foreach ($pages as $p): ?>
								<?php $selected = ($p->ID == $ewaysettings['cancelredirect']) ? 'selected="selected"' : null ?>
								<option <?php echo $selected ?> value="<?php echo $p->ID ?>"><?php echo $p->post_title ?></option>
						<?php endforeach; ?>
						</select>
					</td>
				</tr>
				<tr style="">
					<th>Primary Currency</th>
					<td>
						<?php $currency = empty($ewaysettings['currency']) ? "yes" : $ewaysettings['currency']; ?>
						<select name="ewaysettings[currency]">
							<option <?php if ($currency == 'AUD') echo 'selected="selected"' ?> name="">AUD</option>
						</select>
					</td>
				</tr>
			</table>
			<input type="submit" class="button-primary" value="Save Settings"/></p>
		</form>
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
					r.find('.checktoggle').prop('readonly', false);
					r.find('select.checktoggle').prop('disabled', false);
				} else {
					r.find('.checktoggle').prop('readonly', true);
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
		include_once($this->pluginDir . '/admin/tooltips/integration.shoppingcart.eway.tooltips.php');
		// END Interface
	}
}
?>
