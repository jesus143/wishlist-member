<?php
/*
 * Aweber Autoresponder Interface
 * Original Author : Mike Lopez
 * Version: $Id: integration.autoresponder.aweber.php 2813 2015-07-29 14:30:25Z mike $
 */

$__index__ = 'aweber';
$__ar_options__[$__index__] = 'AWeber';
$__ar_affiliates__[$__index__] = 'http://wlplink.com/go/aweber';
$__ar_videotutorial__[$__index__] = 'http://member.wishlistproducts.com/26-aweber-integration/';

if ($data['ARProvider'] == $__index__):
	if ($__INTERFACE__):
		?>
		<form method="post">
			<input type="hidden" name="saveAR" value="saveAR" />
			<table class="widefat">
				<thead>
					<tr>
						<th scope="col"><?php _e('Membership Level', 'wishlist-member'); ?> </th>
						<th nowrap scope="col"><?php _e('Aweber List Name', 'wishlist-member'); ?> &nbsp; <span style="font-weight:normal"><?php _e('(ex: listname@aweber.com)', 'wishlist-member'); ?></span>
							<?php echo $this->Tooltip("integration-autoresponder-aweber-tooltips-Aweber-List-Name"); ?>

						</th>
						<th nowrap scope="col"><?php _e('Safe Unsubscribe Email', 'wishlist-member'); ?>

							<?php echo $this->Tooltip("integration-autoresponder-aweber-tooltips-Safe-Unsubscribe-Email"); ?>
						</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ((array) $wpm_levels AS $levelid => $level): ?>
						<tr>
							<th scope="row"><?php echo $level['name']; ?></th>
							<td><input type="text" name="ar[email][<?php echo $levelid; ?>]" value="<?php echo $data['aweber']['email'][$levelid]; ?>" size="40" /></td>
							<td><input type="text" name="ar[remove][<?php echo $levelid; ?>]" value="<?php echo $data['aweber']['remove'][$levelid]; ?>" size="40" /></td>
						<?php endforeach; ?>
				</tbody>
			</table>
			<p class="submit">
				<input type="submit" class="button-primary" value="<?php _e('Update AutoResponder Settings', 'wishlist-member'); ?>" />
			</p>
			<p>* <?php _e('Note: The WishList Member Email Parser must also be selected within the AWeber account. It is located in the My Lists section. Select Email Parser and then scroll to the section for Membership Sites and Podcasting Tools. Then check the box next to WishList Member. This must be done for each list in the AWeber account that will be integrated.', 'wishlist-member'); ?></p>
		</form>
		<?php
		include_once($this->pluginDir . '/admin/tooltips/integration.autoresponder.aweber.tooltips.php');
	endif;
endif;
?>
