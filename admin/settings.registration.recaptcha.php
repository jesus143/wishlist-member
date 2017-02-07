<form method="post">
	<table class="form-table">
		<tr valign="top">
			<td colspan="2" style="border:none">
				<a name="recaptcha"></a>
				<b><?php _e('reCaptcha', 'wishlist-member'); ?></b><?php echo $this->Tooltip("settings-default-tooltips-reCaptcha-Settings"); ?>
				<p />
				<?php _e('Enter the corresponding keys in the fields to enable reCaptcha. Leave the fields blank in order to disable reCaptcha.', 'wishlist-member'); ?>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row">reCaptcha Public Key</th>
			<td><input type="text" name="<?php $this->Option('recaptcha_public_key'); ?>" value="<?php $this->OptionValue(); ?>" size="60" /><?php echo $this->Tooltip("settings-default-tooltips-reCaptcha-Settings"); ?></td>
		</tr>
		<tr valign="top">
			<th scope="row">reCaptcha Private Key</th>
			<td>
				<input type="text" name="<?php $this->Option('recaptcha_private_key'); ?>" value="<?php $this->OptionValue(); ?>" size="60" /><?php echo $this->Tooltip("settings-default-tooltips-reCaptcha-Private-Key"); ?><br />
				<?php _e('No reCaptcha key?', 'wishlist-member'); ?> <a href="https://www.google.com/recaptcha/admin" target="_blank"><?php _e('Click here to get one for free.', 'wishlist-member'); ?></a>
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