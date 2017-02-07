
<div id="regform-<?php echo $id ?>" class="regform <?php echo $additional_classes; ?>">
	<div class="regform-container">
		<div class="regform-header">
			<?php if (!empty($logo)): ?>
				<img class="regform-logo" src="<?php echo $logo ?>"></img>
			<?php endif; ?>
			<h2><?php echo $heading?></h2>

			<?php if(!is_user_logged_in() && $showlogin): ?>
			<p class="regform-login-link-holder">
				<?php _e('Existing users please <a href="" class="regform-open-login">login</a> before purchasing ', 'wishlist-member'); ?>
			</p>
			<?php endif; ?>
			<a class="regform-close" href="javascript:void(0)">x</a>
		</div>


		<div class="regform-error">
			<p>
			<?php if (isset($_GET['status']) && $_GET['status'] == 'fail') echo __("An error has occured while processing payment, please try again", "wishlist-member") ?>
			<?php if (!empty($_GET['reason'])) echo '<br/>Reason: ' . strip_tags(wlm_arrval($_GET,'reason'))  ?>
			</p>
		</div>

		<div class="regform-new">
			<form action="<?php echo $form_action ?>" class="regform-form" method="post">
			<?php if (!empty($panel_beforetext)): ?>
				<div class="regform-description">
					<p class="regform-aftertext"><?php echo $panel_beforetext; ?></p>
				</div>
			<?php endif; ?>
			<?php
			foreach($fields as $f) {
				switch ($f['type']) {
					case 'hidden':
						echo sprintf('<input type="hidden" name="%s" value="%s"/>%s', $f['name'], $f['value'], "\n");
						break;
					case 'text':
						echo sprintf('<div class="txt-fld two-col-input %1$s"><label for="%1$s">%2$s</label><input id=""'
						.' class="regform-%1$s %5$s" name="%1$s" type="text" placeholder="%3$s" value="%4$s" /></div>',
						$f['name'],
						$f['label'],
						$f['placeholder'],
						$f['value'],
						$f['class']);

					default:
						# code...
						break;
				}
			}
			?>


			<?php if($fields['cc_type']): ?>
			<div class="txt-fld two-col-input">
				<label for=""><?php _e('Card Type:', 'wishlist-member'); ?></label>
					<select name="cc_type">
						<?php if ( empty( $card_types ) ): ?>
							<option value="Visa" selected="selected"><?php _e('Visa', 'wishlist-member'); ?></option>
							<option value="MasterCard"><?php _e('MasterCard', 'wishlist-member'); ?></option>
							<option value="Discover"><?php _e('Discover', 'wishlist-member'); ?></option>
							<option value="Amex"><?php _e('American Express', 'wishlist-member'); ?></option>
						<?php else: ?>
							<?php foreach( (array)$card_types as $fld => $name ): ?>
								<option value="<?php echo $fld; ?>"><?php echo $name; ?></option>
							<?php endforeach; ?>
						<?php endif; ?>
					</select>
			</div>
			<?php endif; ?>

			<?php if($fields['cc_number']): //treating card fields as special?>
			<div class="txt-fld two-col-input">
				<label for=""><?php _e('Card Number:', 'wishlist-member'); ?></label>
				<input autocomplete="false" placeholder="●●●● ●●●● ●●●● ●●●●" class="regform-cardnumber" name="cc_number" type="text" />
			</div>
			<?php endif; ?>

			<?php if($fields['cc_expmonth'] || $fields['cc_expyear'] || $fields['cc_cvc']): ?>
			<div class="widefield">
				<?php if($fields['cc_expmonth'] || $fields['cc_expyear']): ?>
				<div class="txt-fld expires two-col-input">
					<label for=""><?php _e('Expires:', 'wishlist-member'); ?></label>
					<input autocomplete="false" placeholder="MM" maxlength="2"  class="regform-expmonth" name="cc_expmonth" type="text" />
					<input autocomplete="false" placeholder="YY" maxlength="2"  class="regform-expyear"  name="cc_expyear" type="text" />
				</div>
				<?php endif; ?>

				<?php if($fields['cc_cvc']): ?>
				<div class="txt-fld code two-col-input">
					<label for=""><?php _e('Card Code:', 'wishlist-member'); ?></label>
					<input autocomplete="false" maxlength="4" placeholder="CVC" id="" class="regform-cvc" name="cc_cvc" type="text" />
				</div>
				<?php endif; ?>
			</div>
			<?php endif; ?>
			<?php
				foreach($fields as $f) {
					switch ($f['type']) {
						case 'address':
							$col = isset( $f['col'] ) ? $f['col'] : "two-col-input";
							$required = isset( $f['required'] ) ? "required='required'" : "";
							echo sprintf('<div class="txt-fld %6$s %1$s"><label for="%1$s">%2$s</label><input id=""'
							.' class="regform-%1$s %5$s" name="%1$s" type="text" placeholder="%3$s" value="%4$s" %7$s /></div>',
							$f['name'],
							$f['label'],
							$f['placeholder'],
							$f['value'],
							$f['class'],
							$col,
							$required);
							break;
						case 'select':
							$col = isset( $f['col'] ) ? $f['col'] : "two-col-input";
							$value = (array) $f['value'];
							echo '<div class="txt-fld ' .$col .'">
								<label for="">' .$f['label'] .'</label>
									<select name="' .$f['name'] .'">';
									foreach( $value as $index => $val ) {
										echo '<option value="' .$index .'">' .$val .'</option>';
									}

							echo '  </select>
							</div>';
							break;
						default:
							# code...
							break;
					}
				}
			?>
			<?php if (!empty($panel_aftertext)): ?>
				<div class="regform-description">
					<p class="regform-aftertext"><?php echo $panel_aftertext; ?></p>
				</div>
			<?php endif; ?>
			<div class="btn-fld">
				<button class="regform-button" name="regform-button"><?php echo $panel_button_label ?><span class="regform-waiting">...</span> &nbsp;<?php echo $currency?> <?php echo $amt ?> </button>
				<button hidden="hidden" name = "product_price" id ="amount" value=""> <?php echo $button_label ?> </button>				
			</div>
			</form>
		</div>

		<?php if(!is_user_logged_in()): ?>
		<div class="regform-login">
			<form method="post" action="<?php echo get_bloginfo('wpurl')?>/wp-login.php">
				<div class="txt-fld">
					<label for=""><?php _e('Username:', 'wishlist-member'); ?></label>
					<input id="" class="regform-username" name="log" type="text" />
				</div>
				<div class="txt-fld">
					<label for=""><?php _e('Password:', 'wishlist-member'); ?></label>
					<input id="" class="regform-password" name="pwd" type="password" />
				</div>
				<input type="hidden" name="wlm_redirect_to" value="<?php echo get_permalink()?>#regform-<?php echo $id ?>" />
				<div class="btn-fld">
					<div><a href="" class="regform-close-login"><?php _e('Cancel', 'wishlist-member'); ?></a></div>
					<button class="regform-button"><?php _e("Login", "wishlist-member")?></button>
				</div>
			</form>
		</div>
		<?php endif; ?>
	</div>
</div>
