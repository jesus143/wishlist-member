<?php
if (wlm_arrval($_POST,'reg_form_css_reset')) {
	$this->DeleteOption('reg_form_css');
	$this->Activate();
}
?>
<form method="post">
	<h2><?php _e('Registration Form CSS', 'wishlist-member'); ?></h2>
	<table class="form-table">
		<tr>
			<th scope="row"><?php _e('CSS for the Registration Form', 'wishlist-member'); ?> <?php echo $this->Tooltip("settings-advanced-tooltips-CSS-for-the-Registration-Form"); ?></th>
			<td>
				<textarea name="<?php $this->Option('reg_form_css', true); ?>" cols="70" rows="20"><?php $this->OptionValue(); ?></textarea>
				<br />
				<label><input type="checkbox" name="reg_form_css_reset" value="1" /> Reset to Default <?php echo $this->Tooltip("settings-advanced-tooltips-Reset-to-Default"); ?></label>
			</td>
		</tr>
	</table>

	<p class="submit">
		<?php $this->Options();
		$this->RequiredOptions();
		?>
		<input type="hidden" name="WishListMemberAction" value="Save" />
		<input type="submit" class="button-primary" value="<?php _e('Save Settings', 'wishlist-member'); ?>" />
	</p>
</form>
