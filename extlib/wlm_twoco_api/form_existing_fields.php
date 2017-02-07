
<div id="regform-<?php echo $sku ?>" class="regform">
	<div class="regform-container">
		<div class="regform-header"> 
			<?php if (!empty($logo)): ?>
				<img class="regform-logo" src="<?php echo $logo ?>"></img>
			<?php endif; ?>
			<h2>

				<?php $heading = empty($twoco_apisettings['formheading']) ? "Register to %level" : $twoco_apisettings['formheading'] ?>
				<?php echo str_replace('%level', $level_name, $heading) ?>
			</h2>

			<?php if(!is_user_logged_in()): ?>
			<p style="margin-bottom: 5px;">
				Existing users please <a href="" class="regform-open-login">login</a> before purchasing
			</p>
			<?php endif; ?>
			<a class="regform-close" href=""></a>
		</div>


		<div class="regform-error">
			<p>
			<?php if (isset($_GET['status']) && $_GET['status'] == 'fail') echo __("An error has occured while processing payment, please try again", "wishlist-member") ?>
			<?php if (!empty($_GET['reason'])) echo '<br/>Reason: ' . strip_tags(wlm_arrval($_GET,'reason'))  ?>
			</p>
		</div>
		
		<form action="<?php echo $thankyouurl; ?>" id="myCCForm-<?php echo $sku ?>" class="regform-logged <?php echo $class?>" method="post" onsubmit="return false" >
			<input type="hidden" name="nonce" value="<?php echo wp_create_nonce('regform-do-charge') ?>"/>
			<input type="hidden" name="regform_action" value="charge"/>
			<input type="hidden" name="charge_type" value="existing"/>
			<input type="hidden" name="subscription" value="<?php echo $settings['subscription'] ?>"/>
			<input type="hidden" name="redirect_to" value="<?php echo get_permalink() ?>"/>
			<input type="hidden" name="sku" value="<?php echo $sku ?>"/>
			
			<!-- 2CO hidden fields -->
			<input id="sellerId" type="hidden" value="<?php echo $twocheckoutapisettings['twocheckoutapi_seller_id']; ?>">
			<input id="publishableKey" type="hidden" value="<?php echo $twocheckoutapisettings['twocheckoutapi_publishable_key']; ?>">
			<input id="token" name="token" type="hidden" value="">

			<div class="txt-fld" style="display:none">
				<label for="">First Name:</label>
				<input id="" class="regform-first_name" name="first_name" type="text" value="<?php echo $current_user->first_name;?>" />
			</div>
			<div class="txt-fld"  style="display:none">
				<label for="">Last Name:</label>
				<input id="" class="regform-last_name" name="last_name" type="text" value="<?php echo $current_user->last_name;?>"/>
			</div>
			<div class="txt-fld"  style="display:none">
				<label for="">Email address:</label>
				<input id="" class="regform-email" name="email" type="text" value="<?php echo $current_user->user_email;?>" />
			</div>
			<div class="txt-fld">
				<label for="">Card Number:</label>
				<input id="ccNo" autocomplete="false" placeholder="●●●● ●●●● ●●●● ●●●●" class="regform-cardnumber" name="cc_number" type="text" />
			</div>
			<div class="widefield">
				
				<div class="txt-fld expires two-col-input">
					<label for="">Expires:</label>
					<input id="expMonth" autocomplete="false" placeholder="MM" maxlength="2"  class="regform-expmonth" name="cc_expmonth" type="text" />
					<input id="expYear" autocomplete="false" placeholder="YY" maxlength="2"  class="regform-expyear"  name="cc_expyear" type="text" />
				</div>

				<div class="txt-fld code two-col-input">
					<label for="">CVC:</label>
					<input id="cvv" autocomplete="false" maxlength="4" placeholder="CVC" id="" class="regform-cvc" name="cc_cvc" type="text" />
				</div>
			</div>

			<div class="btn-fld">
				<button class="regform-button" onclick="retrieveToken(<?php echo $sku; ?>)"><?php echo $panel_btn_label; ?><span class="regform-waiting">...</span> &nbsp;<?php echo $currency?> <?php echo $amt ?> </button>
			</div>
		</form>
	</div>
</div>