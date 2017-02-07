<!-- ToolTips for admin/sequential.php  -->
<!-- Last Update: Mon, April 19th, 2010   -->
<!-- Total ToolTips=4 -->

<div style="display: none;">
	<div id="sequential-tooltips-Upgrade-To">
		<?php _e('The Membership Level that will be upgraded to must be set.', 'wishlist-member'); ?>
	</div>
</div>
<div style="display: none;">
	<div id="sequential-tooltips-Method">
		<?php _e('The Move OR Add upgrade method must be selected.  If a Member is ADDED, they will be a part of both Membership Levels.  If the Member is MOVED, they will be removed from the previous Membership Level and will only be a part of the new membership Level.', 'wishlist-member'); ?>
	</div>
</div>
<div style="display: none;">
	<div id="sequential-tooltips-After">
		<p><?php _e('The number of days between the upgrade must be set.', 'wishlist-member'); ?></p>
		<p><?php _e('Note that each scheduled increment of time should be set based on the desired amount of time that should pass before the sequential upgrade occurs.', 'wishlist-member'); ?></p>
		<p><?php _e('Each sequential upgrade will calculate the upgrade based on the number added to the Schedule column.', 'wishlist-member'); ?></p>
		<p><?php _e('Example: If the desired increment of time between each sequential upgrade is 7 days, then each sequential upgrade should be set to occur every 7 days. The number 7 should be placed in the Schedule column for each sequential upgrade.', 'wishlist-member'); ?></p>
		<p><?php _e('The increments in time between multiple sequential upgrades do not need to be multiplied (Example: 7, 14, 21, etc.)', 'wishlist-member'); ?></p>
	</div>
</div>