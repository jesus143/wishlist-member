<?php
// levels
$wpm_levels = $this->GetOption('wpm_levels');

// paypal products
$wlm_paypal_products = json_encode(array(
	'paypalpsproducts'  => $this->GetOption('paypalpsproducts'),
	'paypalecproducts'  => $this->GetOption('paypalecproducts'),
	'paypalproproducts' => $this->GetOption('paypalproproducts'),
	'paypalpayflowproducts' => $this->GetOption('paypalpayflowproducts'),
));

echo <<<STRING
<script>
var wlm_paypal_products = {$wlm_paypal_products};
</script>
STRING;
?>

<!-- paypal -->
<div style='display: none !important;' class='wlmtnmcelbox' id="wlmtnmce-paypal-lightbox">
	<div class="media-modal wp-core-ui" style="display: none !important;">
		<a class="media-modal-close" href="#" title="Close"><span class="media-modal-icon"></span></a>
		<div class="media-frame-title"><h1>PayPal Shortcode Builder</h1></div>
		<div class="media-modal-content">
			<!-- Main Contend Starts -->
			<div class="wlmtnmcelbox-content">
				<!-- Options -->
				<div class="options-holder">
					<table width="100%%" height="100%%" cellspacing="4" cellpadding="0">
						<tr height="1%%" valign="center">
							<!-- Products -->
							<td width="25%%">
								<p class="modal-field-label"><?php _e('Select Product', 'wishlist-member'); ?>:</p>
								<select class="wlmtnmcelbox-products shortcode-fields" style="width:95%%">
									<option value=""><?php _e('Select a Product','wishlist-member'); ?></option>
								</select>
							</td>
							<!-- Products Ends -->
							<!-- Button Selection -->
							<td width="35%%">
								<p class="modal-field-label"><?php _e('Select Button', 'wishlist-member'); ?>:</p>
								<select class="wlmtnmcelbox-buttons shortcode-fields" style="width:95%%">
									<option value="pp_pay"><?php _e('PayPal Button: Pay with PayPal','wishlist-member'); ?></option>
									<option value="pp_buy"><?php _e('PayPal Button: Buy now with PayPal','wishlist-member'); ?></option>
									<option value="pp_checkout"><?php _e('PayPal Button: Check out with PayPal','wishlist-member'); ?></option>
									<option value="custom_image"><?php _e('Custom Image','wishlist-member'); ?></option>
									<option value="plain_text"><?php _e('Plain Text','wishlist-member'); ?></option>
								</select>
							</td>
							<!-- Button Selection Ends -->
							<!-- Button Options -->
							<td width="40%%">
								<p class="modal-field-label">&nbsp;</p>
								<input style="display:none;width:100%%;box-sizing:border-box" class="wlmtnmcelbox-button-options plain_text" type="text" value="Buy Now">
								<input style="display:none;width:100%%;box-sizing:border-box" class="wlmtnmcelbox-button-options custom_image" type="text" value="" placeholder="http://">
								<select class="wlmtnmcelbox-button-options pp_pay pp_buy pp_checkout">
									<option selected="selected" value="s"><?php _e('Small','wishlist-member'); ?></option>
									<option value="m"><?php _e('Medium','wishlist-member'); ?></option>
									<option value="l"><?php _e('Large','wishlist-member'); ?></option>
								</select>
							</td>
							<!-- Button Options Ends -->
						</tr>
						<!-- Button Preview -->
						<tr height="1%%">
							<td colspan="3">
								<p class="modal-field-label"><?php _e('Button Preview','wishlist-member'); ?>:</p>
							</td>
						</tr>
						<tr>
							<td colspan="3" valign="center" height="90" style="text-align:center;border:1px dashed #ccc;height:100px;min-height:100px;max-height:100px">
								<p class="modal-field-label">
								<div class="wlmtnmcelbox-button-preview">
									<img style="display:none" class="pp_pay l" border="0" src="https://www.paypalobjects.com/webstatic/en_AU/i/buttons/btn_paywith_primary_l.png">
									<img style="display:none" class="pp_pay m" border="0" src="https://www.paypalobjects.com/webstatic/en_AU/i/buttons/btn_paywith_primary_m.png">
									<img style="display:none" class="pp_pay s" border="0" src="https://www.paypalobjects.com/webstatic/en_AU/i/buttons/btn_paywith_primary_s.png">
									<img style="display:none" class="pp_buy l" border="0" src="https://www.paypalobjects.com/webstatic/en_US/i/buttons/buy-logo-large.png">
									<img style="display:none" class="pp_buy m" border="0" src="https://www.paypalobjects.com/webstatic/en_US/i/buttons/buy-logo-medium.png">
									<img style="display:none" class="pp_buy s" border="0" src="https://www.paypalobjects.com/webstatic/en_US/i/buttons/buy-logo-small.png">
									<img style="display:none" class="pp_checkout l" border="0" src="https://www.paypalobjects.com/webstatic/en_US/i/buttons/checkout-logo-large.png">
									<img style="display:none" class="pp_checkout m" border="0" src="https://www.paypalobjects.com/webstatic/en_US/i/buttons/checkout-logo-medium.png">
									<img style="display:none" class="pp_checkout s" border="0" src="https://www.paypalobjects.com/webstatic/en_US/i/buttons/checkout-logo-small.png">
									<img style="display:none;max-width:400px;max-height:90px;" class="custom_image s" border="0" src="">
									<input style="display:none" class="plain_text s" type="button" value="">
								</div>
							</td>
						</tr>
						<!-- Button Preview Ends -->
						<!-- Shortcode Preview -->
						<tr>
							<td colspan="3" valign="bottom">
								<p class="modal-field-label"><?php _e('Shortcode Preview','wishlist-member'); ?>:</p>
								<textarea class="wlmtnmcelbox-preview-text" readonly="readonly" style="text-align:center"></textarea>
								<div style="text-align:right">
									<input tab="display_details" type="button" class="button button-primary wlmtnmcelbox-insertcode" value="<?php _e("Insert Mergecode", "wishlist-member")?>" />
								</div>
							</td>
						</tr>
						<!-- Shortcode Preview Ends -->
					</table>

				</div>
				<!-- Options Ends -->
			</div>
			<!-- Main Contend Ends -->
		</div>

	</div>
	<div class="media-modal-backdrop" style="display: none !important;"></div>
</div>

<!-- private tags -->
<div style='display: none !important;' class='wlmtnmcelbox' id="wlmtnmce-private-tags-lightbox">
	<div class="media-modal wp-core-ui" style="display: none !important;">
		<a class="media-modal-close" href="#" title="Close"><span class="media-modal-icon"></span></a>
		<div class="media-frame-title"><h1>Private Tags</h1></div>
		<div class="media-modal-content">
			<!-- Main Contend Starts -->
			<div class="wlmtnmcelbox-content">

				<!-- Options -->
				<div class="options-holder">
						<p class="modal-field-label">
							<input type='checkbox' value='1' class='wlmtnmcelbox-reverse' /> Reverse Private Tags
						</p>
						<p class="modal-field-label">Membership Levels:</p>
						<select class="wlmtnmcelbox-levels" multiple="multiple" data-placeholder=' ' >
						<option value="all">Select All</option>
						<?php foreach( $wpm_levels as $sku => $level ){
							if (is_numeric($sku)){
								$levelname=$level['name'];
								$levelname=str_replace("%","&#37;",$levelname);
								?>
								<option value="<?php echo $sku; ?>"><?php echo trim($levelname); ?></option>
								<?php 
								}
							}
						?>
						</select>
						<p class="modal-field-label">Content:</p>
						<textarea class="wlmtnmcelbox-content-text"></textarea>
				</div>
				<!-- Options Ends -->

				<!-- Preview -->
				<div class="wlmtnmcelbox-preview">
					<div class="wlmtnmcelbox-preview-msg" >
						<input tab="display_details" type="button" class="button button-primary wlmtnmcelbox-insertcode" value="<?php _e("Insert Mergecode", "wishlist-member")?>" />
						Shortcode Preview:
					</div>
					<textarea class="wlmtnmcelbox-preview-text"></textarea>
				</div>
				<!-- Preview Ends -->
			</div>
			<!-- Main Contend Ends -->
		</div>

	</div>
	<div class="media-modal-backdrop" style="display: none !important;"></div>
</div>

<!-- reg form shortcodes-->
<div style='display: none !important;' class='wlmtnmcelbox wlmtnmcelbox-regform-modal' id="wlmtnmce-reg-form-lightbox">
	<div class="media-modal wp-core-ui wlmtnmcelbox-regform-modal" style="display: none !important;">
		<a class="media-modal-close" href="#" title="Close"><span class="media-modal-icon"></span></a>
		<div class="media-frame-title"><h1>Registration Form</h1></div>
		<div class="media-modal-content">
			<!-- Main Contend Starts -->
			<div class="wlmtnmcelbox-content">

				<!-- Options -->
				<div class="options-holder">
						<p class="modal-field-label">Membership Level:</p>
						<select class="reg-form-wlmtnmcelbox-levels" data-placeholder=' '  >
						<option value="all">Select All</option>
						<?php foreach( $wpm_levels as $sku => $level ){
							if (is_numeric($sku)){
								$levelname=$level['name'];
								$levelname=str_replace("%","&#37;",$levelname);
								?>
								<option value="<?php echo $sku; ?>"><?php echo trim($levelname); ?></option>
								<?php 
								}
							}
						?>
						</select>
						<div class="wlmtnmcelbox-preview-msg" > <br><br>
							Shortcode Preview:
						</div>
						<textarea class="wlmtnmcelbox-preview-text"></textarea>
						<input tab="display_details" type="button" class="button button-primary wlmtnmcelbox-insertcode" value="<?php _e("Insert Shortcode", "wishlist-member")?>" />
				</div>
				<!-- Options Ends -->

				<!-- Preview -->
				<div class="wlmtnmcelbox-preview">
					
				</div>
				<!-- Preview Ends -->
			</div>
			<!-- Main Contend Ends -->
		</div>

	</div>
	<div class="media-modal-backdrop" style="display: none !important;"></div>
</div>