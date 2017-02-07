<?php
/*
 * PayPal Shopping Cart Integration
 * Original Author : Mike Lopez
 * Version: $Id: integration.shoppingcart.paypal.php 3153 2016-11-29 16:40:31Z mike $
 */

$__index__ = 'pp';
$__sc_options__[$__index__] = 'PayPal <!-- 2 -->Payments Standard';
$__sc_affiliates__[$__index__] = 'http://wlplink.com/go/paypal';
$__sc_videotutorial__[$__index__] = $video_tutorial = wlm_video_tutorial ( 'integration', 'sc', 'ppps' );

if (wlm_arrval($_GET,'cart') == $__index__) {
	if (!$__INTERFACE__) {
		// BEGIN Initialization

		// Save Stuff
		
		$pptoken = $this->GetOption('pptoken');
		$ppemail = $this->GetOption('ppemail');
		$ppsandbox = $this->GetOption('ppsandbox');
		$ppsandboxtoken = $this->GetOption('ppsandboxtoken');
		$ppsandboxemail = $this->GetOption('ppsandboxemail');
		$ppthankyou = $this->GetOption('ppthankyou');

		if (wlm_arrval($_POST, 'ppssubmit')) {
			// save pp email
			if(trim(wlm_arrval($_POST,'ppemail')) != $ppemail){
				echo "<div class='updated fade'>" . __('<p>PayPal Email Updated.</p>', 'wishlist-member') . "</div>";
			}
			$this->SaveOption('ppemail', $ppemail = trim(wlm_arrval($_POST,'ppemail')));

			// save pdt token
			if(trim(wlm_arrval($_POST,'pptoken')) != $pptoken) {
				echo "<div class='updated fade'>" . __('<p>PayPal PDT Identity Token Updated.</p>', 'wishlist-member') . "</div>";
			}
			$this->SaveOption('pptoken', $pptoken = trim(wlm_arrval($_POST,'pptoken')));

			// save sandbox status
			if(wlm_arrval($_POST,'ppsandbox') != $ppsandbox) {
				if (wlm_arrval($_POST,'ppsandbox') == 1) {
					echo "<div class='updated fade'>" . __('<p>PayPal Sandbox Enabled.</p>', 'wishlist-member') . "</div>";
				} else {
					echo "<div class='updated fade'>" . __('<p>PayPal Sandbox Disabled.</p>', 'wishlist-member') . "</div>";
				}
				$this->SaveOption('ppsandbox', $ppsandbox = trim(wlm_arrval($_POST,'ppsandbox')));
			}

			// save pp sandbox email
			if(trim(wlm_arrval($_POST,'ppsandboxemail')) != $ppsandboxemail){
				echo "<div class='updated fade'>" . __('<p>PayPal Sandbox Email Updated.</p>', 'wishlist-member') . "</div>";
			}
			$this->SaveOption('ppsandboxemail', $ppsandboxemail = trim(wlm_arrval($_POST,'ppsandboxemail')));

			// save pdt sandbox token
			if(trim(wlm_arrval($_POST,'ppsandboxtoken')) != $ppsandboxtoken) {
				echo "<div class='updated fade'>" . __('<p>PayPal Sandbox PDT Identity Token Updated.</p>', 'wishlist-member') . "</div>";
			}
			$this->SaveOption('ppsandboxtoken', $ppsandboxtoken = trim(wlm_arrval($_POST,'ppsandboxtoken')));
		}

		$ppsandbox = (int) $ppsandbox;
		if (!$ppthankyou) {
			$this->SaveOption('ppthankyou', $ppthankyou = $this->MakeRegURL());
		}

		// save POST URL
		if (wlm_arrval($_POST,'ppthankyou')) {
			$_POST['ppthankyou'] = trim(wlm_arrval($_POST,'ppthankyou'));
			$wpmx = trim(preg_replace('/[^A-Za-z0-9]/', '', $_POST['ppthankyou']));
			if ($wpmx == $_POST['ppthankyou']) {
				if ($this->RegURLExists($wpmx, null, 'ppthankyou')) {
					echo "<div class='error fade'>" . __('<p><b>Error:</b> PayPal Thank You URL (' . $wpmx . ') is already in use by a Membership Level or another Shopping Cart.  Please try a different one.</p>', 'wishlist-member') . "</div>";
				} else {
					if($wpmx != $ppthankyou){
						echo "<div class='updated fade'>" . __('<p>PayPal Thank You URL Changed.&nbsp; Make sure to update your PayPal products with the same Thank You URL to make it work.</p>', 'wishlist-member') . "</div>";
					}				
					$this->SaveOption('ppthankyou', $ppthankyou = $wpmx);				
				}
			} else {
				echo "<div class='error fade'>" . __('<p><b>Error:</b>PayPal Thank You URL may only contain letters and numbers.</p>', 'wishlist-member') . "</div>";
			}
		}

		$ppthankyou_url = $wpm_scregister . $ppthankyou;
		$payment_link_format = $ppthankyou_url . '?pid=';
		$cancellation_settings_msg = false;
		$eotcancel = array();
		if(wlm_arrval($_POST,"eot_cancel")){
			if(is_array(wlm_arrval($_POST,"eot_cancel"))) $eotcancel = $_POST["eot_cancel"];
			else $eotcancel = array();
			$this->SaveOption('eotcancel', maybe_serialize($eotcancel));
			$cancellation_settings_msg = true;
		}elseif($_POST){
			if (wlm_arrval($_POST, 'ppssubmit')) 
				$this->SaveOption('eotcancel', maybe_serialize(array()));
		}
		$subscr_cancel = array();
		if(wlm_arrval($_POST,"subscr_cancel")){
			if(is_array(wlm_arrval($_POST,"subscr_cancel"))) $subscr_cancel = $_POST["subscr_cancel"];
			else $eotcancel = array();
			$this->SaveOption('subscrcancel', maybe_serialize($subscr_cancel));
			$cancellation_settings_msg = true;
		}elseif($_POST){
			if (wlm_arrval($_POST, 'ppssubmit')) 
				$this->SaveOption('subscrcancel', maybe_serialize(array()));
		}	

		if($cancellation_settings_msg != ""){
			echo "<div class='updated fade'>" . __('<p>Cancellation Settings saved!.</p>', 'wishlist-member') . "</div>";
		}

		$eotcancel = $this->GetOption('eotcancel');
		if($eotcancel) $eotcancel = maybe_unserialize($eotcancel);
		else $eotcancel = array();

		$subscrcancel = $this->GetOption('subscrcancel');
		if($subscrcancel) $subscrcancel = maybe_unserialize($subscrcancel);
		else $subscrcancel = false; //if false its default to checked

		// END Initialization
	} else {
		// START Interface
		$xposts = $this->GetPayPerPosts(array('post_title', 'post_type'));
		$post_types = get_post_types('', 'objects');

		foreach ($xposts AS $post_type => $posts) {
			foreach ((array) $posts AS $post) {
				$level_names['payperpost-' . $post->ID] = str_replace("'", "", $post->post_title); 
			}
		}
		?>
		<!-- PayPal -->
		<style type="text/css">
			.col-edit { 
				display: none;
			}
			.config-modify, .config-box, .config-complete {
				display: none;
			}

			.config-modify {
				float: right;
				font-size: 14px;
			}

			.config-complete {
				color: #008800;
				padding-left:.5em;
			}
		</style>
		<script>
			jQuery(function($) {
				var config_good = true;
				jQuery('.config-required').each(function(i, o){
					if(!jQuery(o).val().trim()) {
						config_good = false;
					}
				});

				if(config_good) {
					jQuery('.config-box').hide();
					jQuery('.config-complete').show();
					jQuery('.config-modify').show();
					jQuery('#setup-products').show();
				} else {
					jQuery('.config-box').show();
					jQuery('.config-complete').hide();
					jQuery('.config-modify').hide();
					jQuery('#setup-products').hide();
				}

				jQuery('.config-modify a').click(function() {
					if(jQuery('.config-box').is(':visible')) {
						jQuery('#settings-chevron').switchClass('icon-chevron-down', 'icon-chevron-right');
						jQuery('.config-box').hide('slow');
						jQuery('.config-complete').show();
						jQuery('.config-box form')[0].reset();
					} else {
						jQuery('#settings-chevron').switchClass('icon-chevron-right', 'icon-chevron-down');
						jQuery('.config-box').show('slow');
						jQuery('.config-complete').hide();
					}
				});

				jQuery('select.new-product-level').change(function() {
					jQuery('button.new-product').prop('disabled', this.selectedIndex == 0);
				});

			});

			
		</script>

		<div style="float:right;margin-right:15px"><a href="#TB_inline&width=900&height=500&inlineId=paypal-legacy" title="PayPal Legacy Instructions" class="thickbox"><?php _e('Show Legacy Instructions', 'wishlist-member'); ?></a></div>
		<p id="pppersonalx">* <a href="javascript:void(0)" onclick="document.getElementById('pppersonal').style.display='block';document.getElementById('pppersonalx').style.display='none'"><?php _e('PayPal Personal Account Users Click Here', 'wishlist-member'); ?></a></p>
		<p id="pppersonal" style="display:none">
			<b><?php _e('PayPal Personal Account Users Upgrade Instructions:', 'wishlist-member'); ?></b><br /><br />
			<?php printf(__('1. Go to <a href="%1$s" target="_blank">%1$s</a>', 'wishlist-member'), 'https://www.paypal.com/cgi-bin/webscr?cmd=_registration-run'); ?><br />
			<?php _e('2. Click on the Upgrade Your Account link.', 'wishlist-member'); ?><br />
			<?php _e('3. Click on the Upgrade Now Button.', 'wishlist-member'); ?><br />
			<?php _e('4. If the existing account is a Personal PayPal account, there will be a choice to upgrade to a Premier or Business account.', 'wishlist-member'); ?><br />
			<?php _e('5. Choose to upgrade to a Premier or Business account and follow the instructions.', 'wishlist-member'); ?><br />
			<?php _e('6. If the existing account is a Premier PayPal account, the ability to upgrade to a Business account will be presented with instructions that can be followed.', 'wishlist-member'); ?><br />
		</p>

		<h2 class="wlm-integration-steps config-title" id="paypal-settings">
			<div class="config-modify">
				<i class="icon-gear"></i>
				<a href="#">
					<?php _e('Modify Settings','wishlist-member'); ?>
					<i id="settings-chevron" class="icon-chevron-right"></i>
				</a>
			</div>
			<?php _e('PayPal Settings:', 'wishlist-member'); ?>
			<span class="config-complete">
				<i class="icon-ok"></i>
				<?php _e('OK','wishlist-member'); ?>
			</span>
		</h2>
		<div class="config-box">
			<form method="post">
				<blockquote>
					<table class="form-table">
						<tr>
							<th scope="row"><?php _e('Your PayPal Email', 'wishlist-member'); ?></th>
							<td>
								<input type="text" size="75" name="ppemail" value="<?php echo $ppemail; ?>" class="config-required">
								<?php echo $this->Tooltip("integration-shoppingcart-paypal-tooltips-email"); ?>
							</td>
						</tr>
					</table>
					<h2 class="wlm-integration-steps" style="border:none"><?php _e('Enable Payment Data Transfer:','wishlist-member'); ?></h2>
					<p><?php _e('Locate your PDT Identity Token and set the following options in the Profile > My Selling Tools > Website Preferences section of PayPal: ', 'wishlist-member'); ?></p>
					<table style="margin-left:15px" cellspacing="5">
						<tr valign="top">
							<td style="width:195px">Auto Return: </td>
							<td>On</td>
						</tr>
						<tr valign="top">
							<td>Return URL: </td>
							<td><?php _e('Any URL can be used but it cannot be left blank. The site homepage URL is recommend.', 'wishlist-member'); ?></td>
						</tr>
						<tr valign="top">
							<td>Payment Data Transfer: </td>
							<td>On</td>
						</tr>
					</table>
					<table class="form-table">
						<tr>
							<th scope="row"><?php _e('PDT Identity Token', 'wishlist-member'); ?></th>
							<td>
								<input type="text" size="75" name="pptoken" value="<?php echo $pptoken; ?>" class="config-required">
								<?php echo $this->Tooltip("integration-shoppingcart-paypal-tooltips-pdt"); ?>
								<p><em><?php _e('The PayPal PDT Identity Token is located in the My Account > My Selling Tools > Website Preferences section of PayPal.', 'wishlist-member'); ?></em></p>
							</td>
						</tr>
					</table>
					<h2 class="wlm-integration-steps" style="border:none"><?php _e('Enable Instant Payment Notifications:','wishlist-member'); ?></h2>
					<p><?php _e('Set the following options in the Profile > My Selling Tools > Instant Payment Notifications > Choose IPN Settings OR Edit Settings section of PayPal.', 'wishlist-member'); ?></p>
					<table style="margin-left:15px" cellspacing="5">
						<tr valign="top">
							<td style="width:195px">Notification URL: </td>
							<td><?php _e('Any URL can be used but it cannot be left blank. The site homepage URL is recommend.', 'wishlist-member'); ?></td>
						</tr>
						<tr valign="top">
							<td>IPN messages: </td>
							<td>Receive IPN messages (Enabled)</td>
						</tr>
					</table>
					<h2>&nbsp;</h2>
					<table class="form-table">
						<tr>
							<th scope="row"><?php _e('PayPal Sandbox Testing', 'wishlist-member'); ?></th>
							<td>
								<label>
									<input type="checkbox" name="ppsandbox" value="1" <?php $this->Checked($ppsandbox, 1); ?> class="sandbox_mode">
									<?php _e('Enable PayPal Sandbox','wishlist-member'); ?>
								</label>
								<p><em><?php printf(__('The optional <a href="%1$s" target="_blank">PayPal Sandbox</a> can be enabled in order to test the PayPal integration. ', 'wishlist-member'), 'http://www.sandbox.paypal.com/'); ?></em></p>
							</td>
						</tr>

						<tr class="sandbox-mode">
							<th scope="row"><?php _e('Your PayPal Sandbox Email', 'wishlist-member'); ?></th>
							<td>
								<input type="text" size="75" name="ppsandboxemail" value="<?php echo $ppsandboxemail; ?>" />
								<?php echo $this->Tooltip("integration-shoppingcart-paypal-tooltips-emailsandbox"); ?>
							</td>
						</tr>

						<tr class="sandbox-mode">
							<th scope="row"><?php _e('Sandbox PDT Identity Token', 'wishlist-member'); ?></th>
							<td>
								<input type="text" size="75" name="ppsandboxtoken" value="<?php echo $ppsandboxtoken; ?>" />
								<?php echo $this->Tooltip("integration-shoppingcart-paypal-tooltips-pdtsandbox"); ?>
							</td>
						</tr>
					</table>

					<h2 class="wlm-integration-steps"><?php _e('Cancellation Settings:', 'wishlist-member'); ?></h2>
					<table class="widefat">
						<thead>
							<tr>
								<th scope="col" width="200"><?php _e('Membership Level', 'wishlist-member'); ?></th>
								<th scope="col"  class="num">
									<?php _e('Cancel Membership at End of<br>PayPal Subscription', 'wishlist-member'); ?>
									<?php echo $this->Tooltip('integration-shoppingcart-paypal-cancel-at-end-of-subscription'); ?>
								</th>
								<th scope="col"  class="num">
									<?php _e('Cancel Membership Immediately After<br>PayPal Subscription is Cancelled', 'wishlist-member'); ?>
									<?php echo $this->Tooltip('integration-shoppingcart-paypal-cancel-on-cancel'); ?>
								</th>
							</tr>
						</thead>
						<tbody>
							<?php $alt = 0;
							foreach ((array) $wpm_levels AS $sku => $level):
								?>
								<tr class="<?php echo $alt++ % 2 ? '' : 'alternate'; ?>" id="wpm_level_row_<?php echo $sku ?>">
									<td><b><?php echo $level['name'] ?></b></td>
									<td class="num">
										<?php $ischecked = isset($eotcancel[$sku]) && $eotcancel[$sku] == 1 ? true : false; ?>
										<input type="checkbox" name="eot_cancel[<?php echo $sku; ?>]" value="1" <?php echo $ischecked ? "checked='checked'" : ""; ?> />
									</td>
									<td class="num">
										<?php 
											if($subscrcancel === false){
												$ischecked = true;
											}else{
												$ischecked = isset($subscrcancel[$sku]) && $subscrcancel[$sku] == 1 ? true : false; 
											}									
										?>
										<input type="checkbox" name="subscr_cancel[<?php echo $sku; ?>]" value="1" <?php echo $ischecked ? "checked='checked'" : ""; ?> />
									</td>							
								</tr>
							<?php
								endforeach; 
							?>
						</tbody>
					</table>
					<input type="hidden" name="ppssubmit" value="1">
					<p><input type="submit" class="button-primary" value="<?php _e('Save Settings', 'wishlist-member'); ?>" /></p>
				</blockquote>
			</form>
		</div>

		<?php
			// products
			$table_handler_action_prefix = 'wlm_paypalps_';
			include_once($this->pluginDir . '/resources/forms/integration.shoppingcart.paypalproducts-formtemplate.php');
		?>




		<style type="text/css">
			#logo-preview img { width: 90px; height: 40px;}
			.sandbox-mode { display: none; }
		</style>
		<script type="text/javascript">



			var level_names = JSON.parse('<?php echo json_encode($level_names)?>');
			var send_to_editor = function(html) {
				imgurl = jQuery('img', html).attr('src');
				var el = jQuery('#stripe-logo');
				el.val(imgurl);
				tb_remove();
				//also update the img preview
				jQuery('#logo-preview').html('<img src="' + imgurl + '">');
			}

			jQuery(function($) {
				jQuery('.dropmenu').on('click', function(ev) {
					ev.preventDefault();
					jQuery('li.dropme ul').not( jQuery(this).parent()).hide();
					console.log(jQuery(this).parent().find('ul'));
				});

				function update_fields(el, tr) {
					if (el.val() == 1) {
						tr.find('.amount').find('input').attr('disabled', true).val('');
						tr.find('.plans').find('select').removeAttr('disabled');
					} else {
						tr.find('.plans').find('select').attr('disabled', true).val('');
						tr.find('.amount').find('input').removeAttr('disabled');
					}
				}

				function update_sandbox_tbl(cb) {
					if(cb.prop('checked')) {
						jQuery('.sandbox-mode').show('slow');
					} else {
						jQuery('.sandbox-mode').hide('slow');
					}

				}

				jQuery('.sandbox_mode').on('change', function(ev) {
					update_sandbox_tbl(jQuery(this));
				});

				update_sandbox_tbl(jQuery('.sandbox_mode'));

				var my_interval = setInterval(function() {
					if(jQuery('.notice-dismiss').html() !== undefined) {
						jQuery('.notice-dismiss').click(function() {
							var data = {
								action : 'wlm_dismiss_nag',
								nag_name : 'new-paypal-message-dismissed'
							}
							jQuery.post(ajaxurl, data);
						});
						clearInterval(my_interval);
					}
				}, 100);
			});
		</script>
		<?php
		include_once($this->pluginDir . '/admin/tooltips/integration.shoppingcart.paypal.tooltips.php');
		if(!$this->GetOption('new-paypal-message-dismissed')) {
			?>
<div class="update-nag notice is-dismissible" style="display:block">
	<p>Please note that the PayPal Payments Standard integration has replaced the previous PayPal integration.</p>
	<p>All previously created PayPal integration buttons will still function.</p>
	<p>You can view the <a href="<?php echo $video_tutorial; ?>" target="_blank">PayPal Payments Standard integration tutorial video here</a> for more details.
</div>
			<?php
		}
		if(!$this->GetOption('ppemail')) {
			echo '<div class="error"><p>Please enter your PayPal email address into the <a href="#paypal-settings">field below</a> to set up additional PayPal integrations with membership levels.</p></div>';
		}
		// END Interface
		include($this->pluginDir . '/resources/lightbox/integration-paypal-legacy.php');
	}
}

?>