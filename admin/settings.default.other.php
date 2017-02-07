<form method="post">
	<table class="form-table">
		<tr valign="top">
			<th scope="row"><?php _e('Notify Admin of Exceeded Logins:', 'wishlist-member'); ?></th>
			<td >
				<label><input type="radio" name="<?php $this->Option('login_limit_notify'); ?>" value="1"<?php $this->OptionChecked(1); ?> />
					<?php _e('Yes', 'wishlist-member'); ?></label>
				&nbsp;
				<label><input type="radio" name="<?php $this->Option(); ?>" value="0"<?php $this->OptionChecked(0); ?> />
					<?php _e('No', 'wishlist-member'); ?></label><?php echo $this->Tooltip("settings-default-tooltips-Notify-Admin-on-Exceeded-Logins"); ?>
			</td>
		</tr>

		<tr valign="top">
			<th scope="row" style="white-space:nowrap"><?php _e('Notify Admin of New User Registrations:', 'wishlist-member'); ?></th>
			<td>
				<label><input type="radio" name="<?php $this->Option('notify_admin_of_newuser'); ?>" value="1"<?php $this->OptionChecked(1); ?> />
					<?php _e('Yes', 'wishlist-member'); ?></label>
				&nbsp;
				<label><input type="radio" name="<?php $this->Option(); ?>" value="0"<?php $this->OptionChecked(0); ?> />
					<?php _e('No', 'wishlist-member'); ?></label> <?php echo $this->Tooltip("settings-default-tooltips-Notify-admin-of-new-user-registration"); ?>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row" style="white-space:nowrap"><?php _e('Notify Admin of License Activation Problem:', 'wishlist-member'); ?></th>
			<td>
				<label><input type="radio" name="<?php $this->Option('send_activation_problem_notice'); ?>" value="1"<?php $this->OptionChecked(1); ?> />
					<?php _e('Yes', 'wishlist-member'); ?></label>
				&nbsp;
				<label><input type="radio" name="<?php $this->Option(); ?>" value="0"<?php $this->OptionChecked(0); ?> />
					<?php _e('No', 'wishlist-member'); ?></label> <?php echo $this->Tooltip("settings-default-tooltips-send-activation-problem-notice"); ?>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php _e('Disable passwords in administrator emails:', 'wishlist-member'); ?></th>
			<td>
				<?php $mask = $this->GetOption('mask_passwords_in_emails'); ?>
				<?php if ($mask === false) $this->SaveOption('mask_passwords_in_emails', 1); ?>

				<label><input id="mask-passwords" type="radio" name="<?php $this->Option('mask_passwords_in_emails'); ?>" value="1"<?php $this->OptionChecked(1); ?> />
					<?php _e('Yes', 'wishlist-member'); ?></label>
				&nbsp;
				<label><input id="unmask-passwords" type="radio" name="<?php $this->Option(); ?>" value="0"<?php $this->OptionChecked(0); ?> />
					<?php _e('No', 'wishlist-member'); ?></label> <?php echo $this->Tooltip("settings-default-tooltips-Mask-Passwords"); ?>

				<script type="text/javascript">
					jQuery(function($) {
						function do_change() {
							if(confirm("<?php _e('I understand that I am putting my members\' passwords at risk by having them sent to me via email. I accept this risk and I assume all liability for any damages that may occur to my members as a result of exposing their passwords to this risk', "wishlist-member") ?>")) {
								return confirm("<?php _e("Are you REALLY sure you want send member passwords in your admin notification emails?", "wishlist-member") ?>");
							}
							return false;
						}
						$('#unmask-passwords').change(function(ev) {
							if($(this).val() == 0 && !do_change()) {
								$(this).removeAttr('checked');
								$('#mask-passwords').attr('checked', true);
							}
						});
					});
				</script>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row" style="white-space:nowrap"><?php _e('Prevent duplicate shopping cart registrations:', 'wishlist-member'); ?></th>
			<td>
				<label><input type="radio" name="<?php $this->Option('PreventDuplicatePosts'); ?>" value="1"<?php $this->OptionChecked(1); ?> onclick="document.getElementById('duplicate_post_error_page').style.display=this.checked?'':'none';" />
					<?php _e('Yes', 'wishlist-member'); ?></label>
				&nbsp;
				<label><input type="radio" name="<?php $this->Option(); ?>" value="0"<?php $this->OptionChecked(0); ?> onclick="document.getElementById('duplicate_post_error_page').style.display=this.checked?'none':'';"" />
					<?php _e('No', 'wishlist-member'); ?></label> <?php echo $this->Tooltip("settings-default-tooltips-Prevent-duplicate-shopping-cart-registrations"); ?>
			</td>
		</tr>
		<tr valign="top" id="duplicate_post_error_page" style="<?php echo $this->GetOption('PreventDuplicatePosts') == 1 ? '' : 'display:none'; ?>">
			<th scope="row"><?php _e('Duplicate shopping cart registration Page:', 'wishlist-member'); ?></th>
			<td>
				<select name="<?php $this->Option('duplicate_post_error_page_internal') ?>" onchange="this.form.duplicate_post_error_page.disabled=this.selectedIndex>0">
					<option value="0"><?php _e('Enter an external URL below', 'wishlist-member'); ?></option>
					<?php 
                                        if ($pages){
                                            foreach ($pages AS $page): ?>
                                            <option value="<?php echo $page->ID ?>"<?php $this->OptionSelected($page->ID); ?>><?php echo $page->post_title ?></option>
					<?php endforeach;
                                        }
                                        ?>
				</select><?php echo $this->Tooltip("settings-default-tooltips-Duplicate-Post-Error-Page"); ?>
				<br />
				<input<?php if ($this->GetOption('duplicate_post_error_page_internal')) echo ' disabled="true"'; ?> type="text" name="<?php $this->Option('duplicate_post_error_page'); ?>" value="<?php $this->OptionValue(); ?>" size="60" /><br />
				<?php _e('This page will be displayed when a duplicate registration post is detected.', 'wishlist-member'); ?>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row" style="white-space:nowrap"><?php _e('Enable Admin Approval for shopping cart integrations:', 'wishlist-member'); ?></th>
			<td>
				<label><input type="radio" name="<?php $this->Option('admin_approval_shoppingcart_reg'); ?>" value="1"<?php $this->OptionChecked(1); ?> />
					<?php _e('Yes', 'wishlist-member'); ?></label>
				&nbsp;
				<label><input type="radio" name="<?php $this->Option(); ?>" value="0"<?php $this->OptionChecked(0); ?> />
					<?php _e('No', 'wishlist-member'); ?></label> <?php echo $this->Tooltip("settings-default-tooltips-enaable-admin-approval-shoppingcart"); ?>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row" style="white-space:nowrap"><?php _e('Members can update their User Info:', 'wishlist-member'); ?></th>
			<td>
				<label><input type="radio" name="<?php $this->Option('members_can_update_info'); ?>" value="1"<?php $this->OptionChecked(1); ?> />
					<?php _e('Yes', 'wishlist-member'); ?></label>
				&nbsp;
				<label><input type="radio" name="<?php $this->Option(); ?>" value="0"<?php $this->OptionChecked(0); ?> />
					<?php _e('No', 'wishlist-member'); ?></label> <?php echo $this->Tooltip("settings-default-tooltips-Members-can-update-their-info"); ?>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row" style="border:none;white-space:nowrap"><?php _e('Show Affiliate Link in Footer:', 'wishlist-member'); ?></th>
			<td style="border:none">
				<label><input type="radio" name="<?php $this->Option('show_linkback'); ?>" value="1"<?php $this->OptionChecked(1); ?> />
					<?php _e('Yes', 'wishlist-member'); ?></label>
				&nbsp;
				<label><input type="radio" name="<?php $this->Option(); ?>" value="0"<?php $this->OptionChecked(0); ?> />
					<?php _e('No', 'wishlist-member'); ?></label> <?php echo $this->Tooltip("settings-default-tooltips-Show-Affiliate-Link-in-Footer"); ?>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row" style="border:none;white-space:nowrap"><?php _e('Unsubscribe expired members:', 'wishlist-member'); ?></th>
			<td style="border:none">
				<label><input type="radio" name="<?php $this->Option('unsubscribe_expired_members'); ?>" value="1"<?php $this->OptionChecked(1); ?> />
					<?php _e('Yes', 'wishlist-member'); ?></label>
				&nbsp;
				<label><input type="radio" name="<?php $this->Option(); ?>" value="0"<?php $this->OptionChecked(0); ?> />
					<?php _e('No', 'wishlist-member'); ?></label> <?php echo $this->Tooltip("settings-default-tooltips-Unsubscribe-Expired-Members"); ?>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row" style="border:none;white-space:nowrap"><?php _e('Do not send reminder emails when a member unsubscribes:', 'wishlist-member'); ?></th>
			<td style="border:none">
				<label><input type="radio" name="<?php $this->Option('dont_send_reminder_email_when_unsubscribed'); ?>" value="1"<?php $this->OptionChecked(1); ?> />
					<?php _e('Yes', 'wishlist-member'); ?></label>
				&nbsp;
				<label><input type="radio" name="<?php $this->Option(); ?>" value="0"<?php $this->OptionChecked(0); ?> />
					<?php _e('No', 'wishlist-member'); ?></label> <?php echo $this->Tooltip("settings-default-tooltips-Dont-Send-Reminder-Email-When-Unsubscribed"); ?>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row" style="border:none;white-space:nowrap"><?php _e('Redirect to Existing Member Registration:', 'wishlist-member'); ?></th>
			<td style="border:none">
				<label><input type="radio" name="<?php $this->Option('redirect_existing_member'); ?>" value="1"<?php $this->OptionChecked(1); ?> />
					<?php _e('Yes', 'wishlist-member'); ?></label>
				&nbsp;
				<label><input type="radio" name="<?php $this->Option(); ?>" value="0"<?php $this->OptionChecked(0); ?> />
					<?php _e('No', 'wishlist-member'); ?></label> <?php echo $this->Tooltip("settings-default-tooltips-Redirect-Existing-Member"); ?>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row" style="border:none;white-space:nowrap"><?php _e('Prevent deletion of Posts/Pages assigned as Pay Per Post :', 'wishlist-member'); ?></th>
			<td style="border:none">
				<label><input type="radio" name="<?php $this->Option('prevent_ppp_deletion'); ?>" value="1"<?php $this->OptionChecked(1); ?> />
					<?php _e('Yes', 'wishlist-member'); ?></label>
				&nbsp;
				<label><input type="radio" name="<?php $this->Option(); ?>" value="0"<?php $this->OptionChecked(0); ?> />
					<?php _e('No', 'wishlist-member'); ?></label> <?php echo $this->Tooltip("settings-default-tooltips-prevent-ppp-deletion"); ?>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php _e('Minimum Password Length:', 'wishlist-member'); ?></th>
			<td>
				<input type="text" name="<?php $this->Option('min_passlength'); ?>" value="<?php $this->OptionValue(false, 8); ?>" size="4" />
				<?php _e('Characters', 'wishlist-member'); ?><?php echo $this->Tooltip("settings-default-tooltips-Minimum-Password-Length"); ?><br />
				<?php _e('Minimum password length will be set to the entered amount when registering or importing users.', 'wishlist-member'); ?><br/>
				<?php _e('Default is set to 8. The [wlm_min_passlength] merge code can be added to a page or post using the blue WishList Member code insert button found in the edit section of all pages and posts.'); ?>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php _e('Require Strong Passwords:', 'wishlist-member'); ?></th>
			<td>
				<label><input type="radio" name="<?php $this->Option('strongpassword'); ?>" value="1"<?php $this->OptionChecked(1); ?> />
					<?php _e('Yes', 'wishlist-member'); ?></label>
				&nbsp;
				<label><input type="radio" name="<?php $this->Option(); ?>" value="0"<?php $this->OptionChecked(0); ?> />
					<?php _e('No', 'wishlist-member'); ?></label> <?php echo $this->Tooltip("settings-default-tooltips-strong-password"); ?>
			</td>
		</tr>

		<tr valign="top">
			<th scope="row" style="border:none;white-space:nowrap"><?php _e('Enable Password Hinting:', 'wishlist-member'); ?></th>
			<td style="border:none">
				<label><input type="radio" name="<?php $this->Option('password_hinting'); ?>" value="1"<?php $this->OptionChecked(1); ?> />
					<?php _e('Yes', 'wishlist-member'); ?></label>
				&nbsp;
				<label><input type="radio" name="<?php $this->Option(); ?>" value="0"<?php $this->OptionChecked(0); ?> />
					<?php _e('No', 'wishlist-member'); ?></label> <?php echo $this->Tooltip("settings-default-tooltips-Password-Hinting"); ?>
			</td>
		</tr>

		<tr valign="top">
			<th scope="row" style="border:none;white-space:nowrap"><?php _e('Enable Short Incomplete Registration Links:', 'wishlist-member'); ?></th>
			<td style="border:none">
				<label><input type="radio" name="<?php $this->Option('enable_short_registration_links'); ?>" value="1"<?php $this->OptionChecked(1); ?> />
					<?php _e('Yes', 'wishlist-member'); ?></label>
				&nbsp;
				<label><input type="radio" name="<?php $this->Option(); ?>" value="0"<?php $this->OptionChecked(0); ?> />
					<?php _e('No', 'wishlist-member'); ?></label> <?php echo $this->Tooltip("settings-default-tooltips-shortlinks"); ?>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row" style="border:none;white-space:nowrap"><?php _e('Allow WishList Member To Handle Login Redirect:', 'wishlist-member'); ?></th>
			<td style="border:none">
				<label><input type="radio" name="<?php $this->Option('enable_login_redirect_override'); ?>" value="1"<?php $this->OptionChecked(1); ?> />
					<?php _e('Yes', 'wishlist-member'); ?></label>
				&nbsp;
				<label><input type="radio" name="<?php $this->Option(); ?>" value="0"<?php $this->OptionChecked(0); ?> />
					<?php _e('No', 'wishlist-member'); ?></label> <?php echo $this->Tooltip("settings-default-tooltips-login-redirect-override"); ?>
			</td>
		</tr>
        <tr valign="top">
			<th scope="row" style="border:none;white-space:nowrap"><?php _e('Allow WishList Member To Handle Logout  Redirect:', 'wishlist-member'); ?></th>
			<td style="border:none">
				<label><input type="radio" name="<?php $this->Option('enable_logout_redirect_override'); ?>" value="1"<?php $this->OptionChecked(1); ?> />
					<?php _e('Yes', 'wishlist-member'); ?></label>
				&nbsp;
				<label><input type="radio" name="<?php $this->Option(); ?>" value="0"<?php $this->OptionChecked(0); ?> />
					<?php _e('No', 'wishlist-member'); ?></label> <?php echo $this->Tooltip("settings-default-tooltips-logout-redirect-override"); ?>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row" style="border:none;white-space:nowrap"><?php _e('Allow WishList Member To Handle Retrieve Passwords:', 'wishlist-member'); ?></th>
			<td style="border:none">
				<label><input type="radio" name="<?php $this->Option('enable_retrieve_password_override'); ?>" value="1"<?php $this->OptionChecked(1); ?> />
					<?php _e('Yes', 'wishlist-member'); ?></label>
				&nbsp;
				<label><input type="radio" name="<?php $this->Option(); ?>" value="0"<?php $this->OptionChecked(0); ?> />
					<?php _e('No', 'wishlist-member'); ?></label> <?php echo $this->Tooltip("settings-default-tooltips-retrieve-password-override"); ?>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row" style="border:none;white-space:nowrap"><?php _e('Disable support for legacy WishList Member Registration Shortcodes:', 'wishlist-member'); ?></th>
			<td style="border:none">
				<label><input type="radio" name="<?php $this->Option('disable_legacy_reg_shortcodes'); ?>" value="1"<?php $this->OptionChecked(1); ?> />
					<?php _e('Yes', 'wishlist-member'); ?></label>
				&nbsp;
				<label><input type="radio" name="<?php $this->Option(); ?>" value="0"<?php $this->OptionChecked(0); ?> />
					<?php _e('No', 'wishlist-member'); ?></label> <?php echo $this->Tooltip("settings-default-tooltips-disable-legacy-reg-shortcodes"); ?>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row" style="border:none;white-space:nowrap"><?php _e('Disable support for legacy WishList Member Private Tags Mergecodes:', 'wishlist-member'); ?></th>
			<td style="border:none">
				<label><input type="radio" name="<?php $this->Option('disable_legacy_private_tags'); ?>" value="1"<?php $this->OptionChecked(1); ?> />
					<?php _e('Yes', 'wishlist-member'); ?></label>
				&nbsp;
				<label><input type="radio" name="<?php $this->Option(); ?>" value="0"<?php $this->OptionChecked(0); ?> />
					<?php _e('No', 'wishlist-member'); ?></label> <?php echo $this->Tooltip("settings-default-tooltips-disable-legacy-private-tags"); ?>
			</td>
		</tr>
		<?php
			global $wp_roles;
			$roles = $wp_roles->roles;
     	?>
        <tr valign="top">
			<th scope="row" style="border:none;white-space:nowrap"><?php _e('WishList Member Shortcode/Mergecode Inserter Access:', 'wishlist-member'); ?></th>
			<td style="border:none">
				<select name="<?php $this->Option('wlmshortcode_role_access'); ?>[]" data-placeholder='Select a role...' style="width:300px;" class='chzn-select' multiple="multiple" >
					<?php
						$selected_roles = $this->GetOption("wlmshortcode_role_access");
						$selected_roles = $selected_roles === false ? false : $selected_roles;
						$selected_roles = is_string( $selected_roles ) ? array() : $selected_roles;
						if ( is_array( $selected_roles ) ) {
							$selected_roles[] = "administrator";
							$selected_roles = array_unique( $selected_roles );
						} else {
							$selected_roles = false;
						}
					?>
			     	<?php foreach( $roles as $rk => $role ) : ?>
			     	<?php $caps = isset( $role['capabilities'] ) ? (array) $role['capabilities'] : array();  ?>
			     	<?php if ( isset( $caps['edit_posts'] ) || isset( $caps['edit_pages'] ) ) : ?>
			     		<?php $disabled = ( $rk == "administrator" ?  $disable = 'disabled="disabled"' : '' ); ?>
			     		<?php $selected = ( $selected_roles === false || in_array( $rk, $selected_roles ) ?  $selected = 'selected="selected"' : '' ); ?>
			     		<option value="<?php echo $rk; ?>" <?php echo $disabled; ?> <?php echo $selected; ?> ><?php echo $role['name']; ?></option>
			     	<?php endif; ?>
			     	<?php endforeach; ?>
				</select>
				<?php echo $this->Tooltip("settings-default-tooltips-wlmshortcode-role-access"); ?>
			</td>
		</tr>
        <tr valign="top">
			<th scope="row" style="border:none;white-space:nowrap"><?php _e('WishList Member  Page/Post Options Access:', 'wishlist-member'); ?></th>
			<td style="border:none">
				<select name="<?php $this->Option('wlmpageoptions_role_access'); ?>[]" data-placeholder='Select a role...' style="width:300px;" class='chzn-select' multiple="multiple" >
					<?php
						$selected_roles = $this->GetOption("wlmpageoptions_role_access");
						$selected_roles = $selected_roles === false ? false : $selected_roles;
						$selected_roles = is_string( $selected_roles ) ? array() : $selected_roles;
						if ( is_array( $selected_roles ) ) {
							$selected_roles[] = "administrator";
							$selected_roles = array_unique( $selected_roles );
						} else {
							$selected_roles = false;
						}
					?>
			     	<?php foreach( $roles as $rk => $role ) : ?>
			     	<?php $caps = isset( $role['capabilities'] ) ? (array) $role['capabilities'] : array();  ?>
			     	<?php if ( isset( $caps['edit_posts'] ) || isset( $caps['edit_pages'] ) ) : ?>
			     		<?php $disabled = ( $rk == "administrator" ?  $disable = 'disabled="disabled"' : '' ); ?>
			     		<?php $selected = ( $selected_roles === false || in_array( $rk, $selected_roles ) ?  $selected = 'selected="selected"' : '' ); ?>
			     		<option value="<?php echo $rk; ?>" <?php echo $disabled; ?> <?php echo $selected; ?> ><?php echo $role['name']; ?></option>
			     	<?php endif; ?>
			     	<?php endforeach; ?>
				</select>
				<?php echo $this->Tooltip("settings-default-tooltips-wlmpageoptions-role-access"); ?>
			</td>
		</tr>
		<tr valign="top">
			<td colspan="2" style="border:none">
				<hr />
			</td>
		</tr>
		<tr valign="top">
			<th scope="row" style="border:none"><?php _e('Default Login Limit:', 'wishlist-member'); ?></th>
			<td style="border:none"><input type="text" name="<?php $this->Option('login_limit'); ?>" value="<?php $this->OptionValue(); ?>" size="3" /> IPs per day <?php echo $this->Tooltip("settings-default-tooltips-Default-Login-Limit"); ?><br />Enter the number 0 (zero) or leave field blank to disable.</td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php _e('Login Limit Message:', 'wishlist-member'); ?></th>
			<td><input type="text" name="<?php $this->Option('login_limit_error'); ?>" value="<?php $this->OptionValue(false, '<b>Error:</b> You have reached your daily login limit.'); ?>" size="80" /><?php echo $this->Tooltip("settings-default-tooltips-Login-Limit-Message"); ?></td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php _e('Registration Session Timeout:', 'wishlist-member'); ?></th>
			<td><input type="text" name="<?php $this->Option('reg_cookie_timeout'); ?>" value="<?php $this->OptionValue(false, 600); ?>" size="3" /> <?php _e('Seconds', 'wishlist-member'); ?> <?php echo $this->Tooltip("settings-default-tooltips-Registration-Session-Limit"); ?></td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php _e('Registration Form Layout:', 'wishlist-member'); ?></th>
			<td>
				<label><input type="radio" name="<?php $this->Option('FormVersion'); ?>" value=""<?php $this->OptionChecked(""); ?> />
					<?php _e('Legacy', 'wishlist-member'); ?></label>
				&nbsp;
				<label><input type="radio" name="<?php $this->Option(); ?>" value="improved"<?php $this->OptionChecked("improved"); ?> />
					<?php _e('Improved', 'wishlist-member'); ?></label>
			</td>
		</tr>
		<tr valign="top">
			<td colspan="2" style="border:none">
				<hr />
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php _e('RSS Secret Key:', 'wishlist-member'); ?></th>
			<td>
				<input type="text" name="<?php $this->Option('rss_secret_key'); ?>" value="<?php $this->OptionValue(false, md5(time())); ?>" size="60" /><?php echo $this->Tooltip("settings-default-tooltips-RSS-Secret-Key"); ?><br />
				<?php _e('This key will be used to generate the unique RSS Feed URL for each member. Do not share this key.', 'wishlist-member'); ?>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php _e('Disable RSS Enclosures for non-authenticated feeds:', 'wishlist-member'); ?></th>
			<td>
				<label><input type="radio" name="<?php $this->Option('disable_rss_enclosures'); ?>" value="1"<?php $this->OptionChecked(1); ?> />
					<?php _e('Yes', 'wishlist-member'); ?></label>
				&nbsp;
				<label><input type="radio" name="<?php $this->Option(); ?>" value="0"<?php $this->OptionChecked(0); ?> />
					<?php _e('No', 'wishlist-member'); ?></label> <?php echo $this->Tooltip("settings-default-tooltips-Disable-RSS-Enclosures"); ?>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php _e('API Key:', 'wishlist-member'); ?></th>
			<td>
				<input type="text" name="<?php $this->Option('WLMAPIKey'); ?>" value="<?php $this->OptionValue(false, md5(microtime())); ?>" size="60" /><?php echo $this->Tooltip("settings-default-tooltips-API-Key"); ?><br />
				<?php _e('This key can be used to integrate with the WishList Member API. This API key can be repopulated if needed by clicking the Save Settings button. A random string of characters can also be added to create an API Key.', 'wishlist-member'); ?>
			</td>
		</tr>
		<tr valign="top">
			<td colspan="2" style="border:none">
				<hr />
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php _e('Cookie Prefix:', 'wishlist-member'); ?></th>
			<td>
				<input type="text" name="<?php $this->Option('CookiePrefix'); ?>" value="<?php $this->OptionValue(); ?>" size="10" maxlength="10" /><?php echo $this->Tooltip("settings-default-tooltips-Cookie-Prefix"); ?>
			</td>
		</tr>
		<tr valign="top">
			<td colspan="2" style="border:none">
				<hr />
			</td>
		</tr>

		<tr valign="top">
			<th scope="row" style="white-space:nowrap"><?php _e('Affiliate ID:', 'wishlist-member'); ?></th>
			<td>
				<input type="text" name="<?php $this->Option('affiliate_id'); ?>" value="<?php $this->OptionValue(); ?>" /> <a href="http://wishlistproducts.com/affiliates/" target="_blank">Sign Up Now</a> <?php echo $this->Tooltip("settings-default-tooltips-Show-Affiliate-ID"); ?>
			</td>
		</tr>
	</table>
	<p class="submit">
		<?php $this->Options();
		$this->RequiredOptions();
		?>
		<input type="hidden" name="WishListMemberAction" value="Save" />
		<input type="submit" class="button-primary" value="<?php _e('Save', 'wishlist-member'); ?>" />
	</p>
</form>
<script type="text/javascript">
	jQuery(document).ready(function() {
		jQuery(".chzn-select").chosen({width:'300px'});
	});
</script>