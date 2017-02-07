<form method="post">
	<table class="form-table">
		<tr valign="top">
			<th scope="row"><?php _e('Vendor ID','wishlist-member'); ?></th>
			<td>
				<input type="text" name="twocovendorid" value="<?php echo $twocovendorid ?>" size="20" maxlength='16' />
				<?php echo $this->Tooltip("integration-shoppingcart-2co-tooltips-vendorid"); ?>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php _e('Secret Word','wishlist-member'); ?></th>
			<td>
				<input type="text" name="twocosecret" value="<?php echo $twocosecret ?>" size="20" maxlength='16' />
				<?php echo $this->Tooltip("integration-shoppingcart-2co-tooltips-secret"); ?>
			</td>
		</tr>
	</table>
	<input type="submit" class="button button-secondary" value="<?php _e('Save Account Information','wishlist-member'); ?>">
</form>
