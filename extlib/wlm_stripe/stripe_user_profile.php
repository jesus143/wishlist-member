<h3><?php _e('Stripe Info', 'wishlist-member') ?></h3>
<table class="form-table">
<tbody>
	<tr>
		<th><label for="stripe_cust_id"><?php _e('Stripe Customer ID', 'wishlist-member') ?></label></th>
		<td>
			<input type="text" name="stripe_cust_id" id="stripe_cust_id" value="<?php echo $stripe_cust_id?>" class="regular-text">
		</td>
	</tr>
</tbody>
</table>