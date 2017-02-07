<p><?php _e('A Cron Job allows WishList Member to execute scheduled tasks in a more reliable manner. Some examples of WishList Member scheduled tasks include sequential upgrades and the sending of queued email messages.', 'wishlist-member'); ?></p>
<p><?php _e("Anyone who is unfamiliar or uncomfortable with setting up a Cron Job can contact their hosting provider and provide them with the information below. The hosting provider can then set up the Cron Job. Setting the Cron Job to once a day is recommended.", "wishlist-member")?></p>
<p><?php _e("Setting the Cron Job to once a day is recommended.", "wishlist-member")?></p>
<h3><?php _e('Cron Job Details', 'wishlist-member'); ?></h3>
<p><?php _e('Settings:', 'wishlist-member'); ?></p>
<pre style="margin-left:25px">0 0 * * *</pre>
<p><?php _e('Command:', 'wishlist-member'); ?></p>
<pre style="margin-left:25px">/usr/bin/wget -O - -q -t 1 <?php echo get_bloginfo('url'); ?>/?wlmcron=1</pre>
<p>&minus; <?php _e('Copy and paste the line above into the command line of the Cron Job.', 'wishlist-member'); ?></p>
<p>&minus; <?php _e('Note: If the above command does not work, please try the following command:', 'wishlist-member'); ?></p>
<pre style="margin-left:25px">/usr/bin/GET -d <?php echo get_bloginfo('url'); ?>/?wlmcron=1</pre>
<br /><br />
<?php
include_once($this->pluginDir . '/admin/tooltips/settings.cron.tooltips.php');
?>