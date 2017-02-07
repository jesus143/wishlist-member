<?php
/*
 * Manage Content -> Pay Per Post
 */

$pages = get_pages('exclude=' . implode(',', $this->ExcludePages(array(), true)));
$level = $this->GetOption('payperpost');
?>
<p class="alignright" style="margin-top:0"><a href="<?php echo wlm_video_tutorial ( 'levels', 'payperpost' ); ?>" target="_blank"><?php _e('Watch Video Tutorial', 'wishlist-member'); ?></a></p>
<form method="post">
	<table class="form-table">
		<p><?php _e('Select the After Registration and After Login pages that will appear to those who gain access to a Pay Per Post.', 'wishlist-member'); ?></p>
		<tr>
			<th scope="row"><?php _e('After Registration', 'wishlist-member'); ?></th>
			<td>
				<select name="payperpost[afterregredirect]">
					<option value='---'><?php _e('Default After Registration Page', 'wishlist-member'); ?></option>
					<option value='backtopost'<?php $this->Selected('backtopost', $level['afterregredirect'], true); ?>><?php _e('Redirect Back to Post', 'wishlist-member'); ?></option>
					<option value=''<?php $this->Selected('', $level['afterregredirect'], true); ?>><?php _e('Home Page', 'wishlist-member'); ?></option>
					<optgroup label="Pages">
						<?php foreach ((array) $pages AS $page): ?>
							<option value="<?php echo $page->ID ?>"<?php $this->Selected($page->ID, $level['afterregredirect']); ?>><?php echo $page->post_title ?></option>
						<?php endforeach; ?>
					</optgroup>
				</select>
				<?php echo $this->Tooltip("membershiplevels-payperpost-tooltips-After-Registration"); ?>
			</td>
		</tr>
		<tr>
			<th scope="row"><?php _e('After Login', 'wishlist-member'); ?></th>
			<td>
				<select name="payperpost[loginredirect]">
					<option value='---'><?php _e('Default After Login Page', 'wishlist-member'); ?></option>
					<option value=''<?php $this->Selected('', $level['loginredirect'], true); ?>><?php _e('Home Page', 'wishlist-member'); ?></option>
					<optgroup label="Pages">
						<?php foreach ((array) $pages AS $page): ?>
							<option value="<?php echo $page->ID ?>"<?php $this->Selected($page->ID, $level['loginredirect']); ?>><?php echo $page->post_title ?></option>
						<?php endforeach; ?>
					</optgroup>
				</select>
				<?php echo $this->Tooltip("membershiplevels-payperpost-tooltips-After-Login"); ?>
			</td>
		</tr>
		<tr>
			<th scope="row" style="white-space:nowrap"><?php _e('Require Captcha Image on Registration Page', 'wishlist-member'); ?></th>
			<td>
				<label><input type="radio" name="payperpost[requirecaptcha]" value="1" <?php $this->Checked(1, $level['requirecaptcha']); ?> /> <?php _e('Yes', 'wishlist-member'); ?></label>
				&nbsp;
				<label><input type="radio" name="payperpost[requirecaptcha]" value="0" <?php $this->Checked(0, $level['requirecaptcha']); ?> /> <?php _e('No', 'wishlist-member'); ?></label>
				<?php echo $this->Tooltip("membershiplevels-payperpost-tooltips-Require-Captcha"); ?>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php _e('Default Pay Per Post Setting', 'wishlist-member'); ?></th>
			<td>
				<label><input type="radio" name="<?php $this->Option('default_ppp'); ?>" value="1"<?php $this->OptionChecked(1); ?> /> <?php _e('On', 'wishlist-member'); ?></label>
				&nbsp;
				<label><input type="radio" name="<?php $this->Option(); ?>" value="0"<?php $this->OptionChecked(0); ?> /> <?php _e('Off', 'wishlist-member'); ?></label>
				<?php echo $this->Tooltip("membershiplevels-payperpost-tooltips-Default-Setting"); ?>
			</td>
		</tr>
	</table>
	<p class="submit">
		<?php
		echo '<!-- ';
		$this->Option('payperpost');
		echo ' -->';
		$this->Options();
		$this->RequiredOptions();
		?>
		<input type="hidden" name="WLSaveMessage" value="Pay Per Post Settings Updated" />
		<input type="hidden" name="WishListMemberAction" value="Save" />
		<input type="submit" class="button-primary" value="<?php _e('Save', 'wishlist-member'); ?>" />
	</p>
</form>
