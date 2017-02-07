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
		$('.config-required').each(function(i, o){
			if(!$(o).val().trim()) {
				config_good = false;
			}
		});

		if(config_good) {
			$('.config-box').hide();
			$('.config-complete').show();
			$('.config-modify').show();
			$('#setup-products').show();
		} else {
			$('.config-box').show();
			$('.config-complete').hide();
			$('.config-modify').hide();
			$('#setup-products').hide();
		}

		$('.config-modify a').click(function() {
			if($('.config-box').is(':visible')) {
				$('#settings-chevron').switchClass('icon-chevron-down', 'icon-chevron-right');
				$('.config-box').hide('slow');
				$('.config-complete').show();
				$('.config-box form')[0].reset();
			} else {
				$('#settings-chevron').switchClass('icon-chevron-right', 'icon-chevron-down');
				$('.config-box').show('slow');
				$('.config-complete').hide();
			}
		});

		$('select.new-product-level').change(function() {
			$('button.new-product').prop('disabled', this.selectedIndex == 0);
		});

	});
</script>

<h2 class="wlm-integration-steps config-title">
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
	<form method="post" id="stripe_form">
		<!-- <p><?php _e('Locate your API Credentials in the Profile > My Selling Tools > API Access > View API Signature section of PayPal','wishlist-member'); ?></p> -->
		<h2 class="wlm-integration-steps" style="border:none"><?php _e('Paypal Manager Credentials:','wishlist-member'); ?></h2>
		<p><a href="https://www.paypal.com/us/cgi-bin/webscr?cmd=_get-api-signature&generic-flow=true" target="paypal-api-get-signature" onclick="window.open(this.href, 'paypal-api-get-signature', 'height=500,width=360')"><?php _e('<a href="https://manager.paypal.com" target="_blank">Click here </a> to get the Paypal Manager User and Password, and then go to Account Administrator >> Manage Users. <br>You can create a new User or use an existing one.','wishlist-member'); ?></a></p>
		<p><?php _e('<b>Note:</b> The "Recurring Billing" feature on your PayPal Manager account will need to be enabled when integrating with Payflow Recurring Products.','wishlist-member'); ?></a></p>
		<table class="form-table">
			<tr>
				<th><?php _e('Paypal Manager Username','wishlist-member'); ?></th>
				<td><input class="config-required" type="text" style="width:100%; max-width: 450px" name="<?php echo $paypal_api_settings_variable_name; ?>[live][api_username]" value="<?php echo ${$paypal_api_settings_variable_name}['live']['api_username'] ?>"><br/></td>
			</tr>
			<tr>
				<th><?php _e('Paypal Manager Password','wishlist-member'); ?></th>
				<td><input class="config-required" type="password" style="width:100%; max-width: 450px" name="<?php echo $paypal_api_settings_variable_name; ?>[live][api_password]" value="<?php echo ${$paypal_api_settings_variable_name}['live']['api_password']  ?>"><br/></td>
			</tr>
			<tr>
				<th><?php _e('Paypal Manager Merchant Name','wishlist-member'); ?></th>
				<td><input class="config-required" type="text" style="width:100%; max-width: 450px" name="<?php echo $paypal_api_settings_variable_name; ?>[live][merchant_name]" value="<?php echo ${$paypal_api_settings_variable_name}['live']['merchant_name']  ?>"><br/>
					<i><?php _e('To get your Merchant Name go to Paypal Manager >> Account Administration >> Company Information.','wishlist-member'); ?></i>
				</td>
			</tr>
		</table>
		<h2></h2>
		<table class="form-table">
			<tr>
				<th scope="row"><?php _e('Payflow Testing', 'wishlist-member'); ?></th>
				<td>
					<label>
						<input type="checkbox" name="<?php echo $paypal_api_settings_variable_name; ?>[sandbox_mode]" value="1" <?php $this->Checked(${$paypal_api_settings_variable_name}['sandbox_mode'], 1); ?> class="sandbox_mode">
						<?php _e('Enable Payflow testing','wishlist-member'); ?>
					</label>
					<p><em><?php _e('Enabling this option will allow you to do test transactions with Payflow Integration. ', 'wishlist-member'); ?></em></p>
				</td>
			</tr>
			<tr class="sandbox-mode">
				<th><?php _e('Paypal Manager Username', 'wishlist-member'); ?></th>
				<td><input type="text" style="width:100%; max-width: 450px" name="<?php echo $paypal_api_settings_variable_name; ?>[sandbox][api_username]" value="<?php echo ${$paypal_api_settings_variable_name}['sandbox']['api_username'] ?>"><br/></td>
			</tr>
			<tr class="sandbox-mode">
				<th><?php _e('Paypal Manager Password', 'wishlist-member'); ?></th>
				<td><input type="password" style="width:100%; max-width: 450px" name="<?php echo $paypal_api_settings_variable_name; ?>[sandbox][api_password]" value="<?php echo ${$paypal_api_settings_variable_name}['sandbox']['api_password']  ?>"><br/></td>
			</tr>
			<tr class="sandbox-mode">
				<th><?php _e('Paypal Manager Merchant Name','wishlist-member'); ?></th>
				<td><input type="text" style="width:100%; max-width: 450px" name="<?php echo $paypal_api_settings_variable_name; ?>[sandbox][merchant_name]" value="<?php echo ${$paypal_api_settings_variable_name}['sandbox']['merchant_name']  ?>"><br/></td>
			</tr>
		</table>
		<p><input type="submit" class="button-primary" value="<?php _e('Save Settings', 'wishlist-member'); ?>" /></p>
	</form>
</div>
