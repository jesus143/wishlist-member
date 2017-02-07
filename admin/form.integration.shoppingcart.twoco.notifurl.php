<form method="post">
	<p>&nbsp;&nbsp;<a href="<?php echo $twocothankyou_url ?>" onclick="return false"><?php echo $twocothankyou_url ?></a> &nbsp; (<a href="javascript:;" onclick="document.getElementById('twocothankyou').style.display='block';"><?php _e('change', 'wishlist-member'); ?></a>)
		<?php echo $this->Tooltip("integration-shoppingcart-2co-tooltips-thankyouurl"); ?>

	</p>
	<div id="twocothankyou" style="display:none">
		<p>&nbsp;&nbsp;<?php echo $wpm_scregister ?><input type="text" name="twocothankyou" value="<?php echo $twocothankyou ?>" size="8" /> <input type="submit" class="button-secondary" value="<?php _e('Change', 'wishlist-member'); ?>" /></p>
	</div>
</form>
