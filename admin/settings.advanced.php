<h2><?php _e('Settings &raquo; Advanced', 'wishlist-member'); ?></h2>
<?php
/*
 * Advanced Settings
 */
$mergecode = '<b>' . __('Merge Codes', 'wishlist-member') . '</b><small><br /><br />';
$mergecode.='[level] : ' . __('Membership Level', 'wishlist-member') . '<br /><br />' . __('Registration Links', 'wishlist-member') . '<br />';
$mergecode.='[newlink] : ' . __('New Member', 'wishlist-member') . '<br />';
$mergecode.='[existinglink] : ' . __('Existing Member', 'wishlist-member') . '</small><br />';

$reset = false;

if (wlm_arrval($_POST, 'sidebar_widget_css_reset')) {
	$this->DeleteOption('sidebar_widget_css');
	$reset = true;
}
if (wlm_arrval($_POST, 'login_mergecode_css_reset')) {
	$this->DeleteOption('login_mergecode_css');
	$reset = true;
}
if (wlm_arrval($_POST, 'reg_form_css_reset')) {
	$this->DeleteOption('reg_form_css');
	$reset = true;
}
if (wlm_arrval($_POST, 'reg_instructions_new_reset')) {
	$this->DeleteOption('reg_instructions_new');
	$reset = true;
}
if (wlm_arrval($_POST, 'reg_instructions_new_noexisting_reset')) {
	$this->DeleteOption('reg_instructions_new_noexisting');
	$reset = true;
}
if (wlm_arrval($_POST, 'reg_instructions_existing_reset')) {
	$this->DeleteOption('reg_instructions_existing');
	$reset = true;
}
if ($reset) {
	$this->Activate();
}
?>
<form method="post">
	<h2><?php _e('CSS Code', 'wishlist-member'); ?></h2>
	<h3><?php _e('Sidebar Widget CSS', 'wishlist-member'); ?></h3>
	<table class="form-table">
		<tr>
			<th scope="row"></th>
			<td>
				<textarea name="<?php $this->Option('sidebar_widget_css', true); ?>" cols="70" rows="20"><?php $this->OptionValue(); ?></textarea>
				<br />
				<label><input type="checkbox" name="sidebar_widget_css_reset" value="1" /> Reset to Default <?php echo $this->Tooltip("settings-advanced-tooltips-Reset-to-Default"); ?></label>
			</td>
		</tr>
	</table>
	<h3><?php _e('Login Form Merge Code CSS', 'wishlist-member'); ?></h3>
	<table class="form-table">
		<tr>
			<th scope="row"></th>
			<td>
				<textarea name="<?php $this->Option('login_mergecode_css', true); ?>" cols="70" rows="20"><?php $this->OptionValue(); ?></textarea>
				<br />
				<label><input type="checkbox" name="login_mergecode_css_reset" value="1" /> Reset to Default <?php echo $this->Tooltip("settings-advanced-tooltips-Reset-to-Default"); ?></label>
			</td>
		</tr>
	</table>


	<p class="submit">
		<?php
		$this->Options();
		$this->RequiredOptions();
		?>
		<input type="hidden" name="WishListMemberAction" value="Save" />
		<input type="submit" class="button-primary" value="<?php _e('Save', 'wishlist-member'); ?>" />
	</p>
</form>
