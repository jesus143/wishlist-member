<form method="post">
	<?php $pages = get_pages('exclude=' . implode(',', $this->ExcludePages(array(), true))); ?>
	<table class="form-table">
		<tr valign="top">
			<td colspan="2" style="border:none">
				<?php _e('Set the error pages that will be displayed to Members who attempt to access protected content.', 'wishlist-member'); ?>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row" style="border:none"><?php _e('Non-Members:', 'wishlist-member'); ?></th>
			<td style="border:none">
				<select name="<?php $this->Option('non_members_error_page_internal') ?>" onchange="this.form.non_members_error_page.disabled=this.selectedIndex>0">
					<option value="0"><?php _e('Enter an external URL below', 'wishlist-member'); ?></option>
					<?php foreach ($pages AS $page): ?>
						<option value="<?php echo $page->ID ?>"<?php $this->OptionSelected($page->ID); ?>><?php echo $page->post_title ?></option>
					<?php endforeach; ?>
				</select><?php echo $this->Tooltip("settings-default-tooltips-nonmemberspage"); ?>
				<br />
				<input<?php if ($this->GetOption('non_members_error_page_internal')) echo ' disabled="true"'; ?> type="text" name="<?php $this->Option('non_members_error_page'); ?>" value="<?php $this->OptionValue(); ?>" size="60" /><br />
				<?php _e('This page will be displayed to Non-Members who attempt to access protected content.', 'wishlist-member'); ?>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php _e('Wrong Membership Level:', 'wishlist-member'); ?></th>
			<td>
				<select name="<?php $this->Option('wrong_level_error_page_internal') ?>" onchange="this.form.wrong_level_error_page.disabled=this.selectedIndex>0">
					<option value="0"><?php _e('Enter an external URL below', 'wishlist-member'); ?></option>
					<?php foreach ($pages AS $page): ?>
						<option value="<?php echo $page->ID ?>"<?php $this->OptionSelected($page->ID); ?>><?php echo $page->post_title ?></option>
					<?php endforeach; ?>
				</select><?php echo $this->Tooltip("settings-default-tooltips-WrongMembershipLevel"); ?>
				<br />
				<input<?php if ($this->GetOption('wrong_level_error_page_internal')) echo ' disabled="true"'; ?> type="text" name="<?php $this->Option('wrong_level_error_page'); ?>" value="<?php $this->OptionValue(); ?>" size="60" /><br />
				<?php _e('This page will be displayed to Members if they attempt to access protected content that is assigned to a Membership Level they do not belong to. This is for use on sites with multiple Membership Levels.', 'wishlist-member'); ?>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php _e('Membership Cancelled:', 'wishlist-member'); ?></th>
			<td>
				<select name="<?php $this->Option('membership_cancelled_internal') ?>" onchange="this.form.membership_cancelled.disabled=this.selectedIndex>0">
					<option value="0"><?php _e('Enter an external URL below', 'wishlist-member'); ?></option>
					<?php foreach ($pages AS $page): ?>
						<option value="<?php echo $page->ID ?>"<?php $this->OptionSelected($page->ID); ?>><?php echo $page->post_title ?></option>
					<?php endforeach; ?>
				</select><?php echo $this->Tooltip("settings-default-tooltips-Membership-Cancelled"); ?>
				<br />
				<input<?php if ($this->GetOption('membership_cancelled_internal')) echo ' disabled="true"'; ?> type="text" name="<?php $this->Option('membership_cancelled'); ?>" value="<?php $this->OptionValue(); ?>" size="60" /><br />
				<?php _e('This page will be displayed when a Membership Level has been cancelled by one of the supported shopping carts.', 'wishlist-member'); ?>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php _e('Membership Expired:', 'wishlist-member'); ?></th>
			<td>
				<select name="<?php $this->Option('membership_expired_internal') ?>" onchange="this.form.membership_expired.disabled=this.selectedIndex>0">
					<option value="0"><?php _e('Enter an external URL below', 'wishlist-member'); ?></option>
					<?php foreach ($pages AS $page): ?>
						<option value="<?php echo $page->ID ?>"<?php $this->OptionSelected($page->ID); ?>><?php echo $page->post_title ?></option>
					<?php endforeach; ?>
				</select><?php echo $this->Tooltip("settings-default-tooltips-Membership-Expired"); ?>
				<br />
				<input<?php if ($this->GetOption('membership_expired_internal')) echo ' disabled="true"'; ?> type="text" name="<?php $this->Option('membership_expired'); ?>" value="<?php $this->OptionValue(); ?>" size="60" /><br />
				<?php _e('This page will be displayed when a Membership Level has expired.', 'wishlist-member'); ?>
			</td>
		</tr>

		<tr valign="top">
			<th scope="row"><?php _e('Membership Requires Approval:', 'wishlist-member'); ?></th>
			<td>
				<select name="<?php $this->Option('membership_forapproval_internal') ?>" onchange="this.form.membership_cancelled.disabled=this.selectedIndex>0">
					<option value="0"><?php _e('Enter an external URL below', 'wishlist-member'); ?></option>
					<?php foreach ($pages AS $page): ?>
						<option value="<?php echo $page->ID ?>"<?php $this->OptionSelected($page->ID); ?>><?php echo $page->post_title ?></option>
					<?php endforeach; ?>
				</select><?php echo $this->Tooltip("settings-default-tooltips-Membership-ForApproval"); ?>
				<br />
				<input<?php if ($this->GetOption('membership_forapproval_internal')) echo ' disabled="true"'; ?> type="text" name="<?php $this->Option('membership_forapproval'); ?>" value="<?php $this->OptionValue(); ?>" size="60" /><br />
				<?php _e('This page will be displayed to a Member after registration when a Membership Level requires admin approval.', 'wishlist-member'); ?>
			</td>
		</tr>

		<tr valign="top">
			<th scope="row"><?php _e('Membership Requires Confirmation:', 'wishlist-member'); ?></th>
			<td>
				<select name="<?php $this->Option('membership_forconfirmation_internal') ?>" onchange="this.form.membership_cancelled.disabled=this.selectedIndex>0">
					<option value="0"><?php _e('Enter an external URL below', 'wishlist-member'); ?></option>
					<?php foreach ($pages AS $page): ?>
						<option value="<?php echo $page->ID ?>"<?php $this->OptionSelected($page->ID); ?>><?php echo $page->post_title ?></option>
					<?php endforeach; ?>
				</select><?php echo $this->Tooltip("settings-default-tooltips-Membership-ForConfirmation"); ?>
				<br />
				<input<?php if ($this->GetOption('membership_forconfirmation_internal')) echo ' disabled="true"'; ?> type="text" name="<?php $this->Option('membership_forconfirmation'); ?>" value="<?php $this->OptionValue(); ?>" size="60" /><br />
				<?php _e('This page will be displayed to a Member after registration when a Membership Level requires email confirmation by the Member for approval.', 'wishlist-member'); ?>
			</td>
		</tr>
		<tr valign="top">
			<td colspan="2" style="border:none">
				<?php _e('Set the page a Member will be redirected to immediately after registration. The After Registration page will only be seen by the Member once as it will only be viewed after the Member registers.', 'wishlist-member'); ?>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php _e('After Registration:', 'wishlist-member'); ?></th>
			<td>
				<select name="<?php $this->Option('after_registration_internal') ?>" onchange="this.form.after_registration.disabled=this.selectedIndex>0">
					<option value="0"><?php _e('Enter an external URL below', 'wishlist-member'); ?></option>
					<?php foreach ($pages AS $page): ?>
						<option value="<?php echo $page->ID ?>"<?php $this->OptionSelected($page->ID); ?>><?php echo $page->post_title ?></option>
					<?php endforeach; ?>
				</select><?php echo $this->Tooltip("settings-default-tooltips-After-Registration-Page"); ?>
				<br />
				<input<?php if ($this->GetOption('after_registration_internal')) echo ' disabled="true"'; ?> type="text" name="<?php $this->Option('after_registration'); ?>" value="<?php $this->OptionValue(); ?>" size="60" /><br />
				<?php _e('This page will be displayed to Members after the registration process is finished. Note: Individual After Registration pages can be set for each Membership Level in the Levels tab.', 'wishlist-member'); ?>
			</td>
		</tr>
		<tr valign="top">
			<td colspan="2" style="border:none">
				<?php _e('Set the page a Member will be redirected to immediately after login:', 'wishlist-member'); ?>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php _e('After Login:', 'wishlist-member'); ?></th>
			<td>
				<select name="<?php $this->Option('after_login_internal') ?>" onchange="this.form.after_login.disabled=this.selectedIndex>0">
					<option value="0"><?php _e('Enter an external URL below', 'wishlist-member'); ?></option>
					<?php foreach ($pages AS $page): ?>
						<option value="<?php echo $page->ID ?>"<?php $this->OptionSelected($page->ID); ?>><?php echo $page->post_title ?></option>
					<?php endforeach; ?>
				</select><?php echo $this->Tooltip("settings-default-tooltips-After-Login-Page"); ?>
				<br />
				<input<?php if ($this->GetOption('after_login_internal')) echo ' disabled="true"'; ?> type="text" name="<?php $this->Option('after_login'); ?>" value="<?php $this->OptionValue(); ?>" size="60" /><br />
				<?php _e('This page will be displayed to Members immediately after they login. Note: Individual After Login pages can be set for each Membership Level in the Levels tab.', 'wishlist-member'); ?>
			</td>
		</tr>
		<!-- start added by Andy -->
		<tr valign="top">
			<td colspan="2" style="border:none">
				<?php _e('Set the page a Member will be redirected to immediately after logout.', 'wishlist-member'); ?>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php _e('After Logout:', 'wishlist-member'); ?></th>
			<td>
				<select name="<?php $this->Option('after_logout_internal') ?>" onchange="this.form.after_logout.disabled=this.selectedIndex>0">
					<option value="0"><?php _e('Enter an external URL below', 'wishlist-member'); ?></option>
					<?php foreach ($pages AS $page): ?>
						<option value="<?php echo $page->ID ?>"<?php $this->OptionSelected($page->ID); ?>><?php echo $page->post_title ?></option>
					<?php endforeach; ?>
				</select><?php echo $this->Tooltip("settings-default-tooltips-After-Logout-Page"); ?>
				<br />
				<input<?php if ($this->GetOption('after_logout_internal')) echo ' disabled="true"'; ?> type="text" name="<?php $this->Option('after_logout'); ?>" value="<?php $this->OptionValue(); ?>" size="60" /><br />
				<?php _e('This page will be displayed to Members immediately after they logout.', 'wishlist-member'); ?>
			</td>
		</tr>
		<!-- end added by Andy -->

		<tr valign="top">
			<th scope="row"><?php _e('Custom Unsubscribe Confirmation:', 'wishlist-member'); ?></th>
			<td>
				<select name="<?php $this->Option('unsubscribe_internal') ?>" onchange="this.form.unsubscribe.disabled=this.selectedIndex>0">
					<option value="0"><?php _e('Enter an external URL below', 'wishlist-member'); ?></option>
					<?php foreach ($pages AS $page): ?>
						<option value="<?php echo $page->ID ?>"<?php $this->OptionSelected($page->ID); ?>><?php echo $page->post_title ?></option>
					<?php endforeach; ?>
				</select><?php echo $this->Tooltip("settings-default-tooltips-Custom-Unsubscribe-Confirmation-Page"); ?>
				<br />
				<input<?php if ($this->GetOption('unsubscribe_internal')) echo ' disabled="true"'; ?> type="text" name="<?php $this->Option('unsubscribe'); ?>" value="<?php $this->OptionValue(); ?>" size="60" /><br />
                                <?php _e('Set the page a Member will be redirected to immediately after they unsubscribe.', 'wishlist-member'); ?>
			</td>
		</tr>
		<!-- Pending period is now always disabled -->
		<input type="hidden" name="pending_period" value="" />
	</table>
	<p class="submit">
		<?php $this->Options();
		$this->RequiredOptions();
		?>
		<input type="hidden" name="WishListMemberAction" value="Save" />
		<input type="submit" class="button-primary" value="<?php _e('Save', 'wishlist-member'); ?>" />
	</p>
</form>