<?php if (isset($show_page_menu) && $show_page_menu) : ?>
	<ul class="wlm-sub-menu">
		<?php if ($this->access_control->current_user_can('wishlistmember_membershiplevels_levels')): ?>
			<li<?php echo (!wlm_arrval($_GET,'mode')) ? ' class="current"' : '' ?>><a href="?<?php echo $this->QueryString('mode', 'level', 'offset', 's') ?>"><?php _e('Membership Levels', 'wishlist-member'); ?></a></li>
		<?php endif; ?>

		<?php if ($this->access_control->current_user_can('wishlistmember_membershiplevels_payperpost')): ?>
			<li<?php echo (wlm_arrval($_GET,'mode') == 'payperpost') ? ' class="current"' : '' ?>><a href="?<?php echo $this->QueryString('mode', 'level', 'offset', 's') ?>&mode=payperpost"><?php _e('Pay Per Post', 'wishlist-member'); ?></a></li>
		<?php endif; ?>

		<?php if ($this->access_control->current_user_can('wishlistmember_membershiplevels_movemembership')): ?>
			<li<?php echo (wlm_arrval($_GET,'mode') == 'movemembership') ? ' class="current"' : '' ?>><a href="?<?php echo $this->QueryString('mode', 'level', 'offset', 's') ?>&mode=movemembership"><?php _e('Move/Add Members ', 'wishlist-member'); ?></a></li>
		<?php endif; ?>
	</ul>
	<?php return;
endif;
?>
<?php
/* Registration URL */
$registerurl = WLMREGISTERURL;

/* new ID */
$wpm_newid = time();
sleep(1);

/* WishList Member Levels */
$wpm_levels = $this->GetOption('wpm_levels');

$prevurls = array();
foreach ((array) array_keys((array) $wpm_levels) AS $level) {
	$prevurls[] = $level['url'];
}

/* new URL */
$found = false;
while (!$found) {
	srand((float) microtime() * 10000000);
	$newurl = $this->MakeRegURL();
	$found = !in_array($newurl, $prevurls);
}

/* headings */
$heading[''] = __('Manage Membership Levels', 'wishlist-member');
$heading['payperpost'] = __('Membership Levels &raquo; Pay Per Post', 'wishlist-member');
$heading['movemembership'] = __('Membership Levels &raquo; Move Membership Levels', 'wishlist-member');
?>
<h2><?php echo $heading[wlm_arrval($_GET,'mode')]; ?></h2>
<br />
<?php
if (wlm_arrval($_GET,'mode') == 'movemembership') {
	include($this->pluginDir . '/admin/membershiplevels.movemembership.php');
	include_once($this->pluginDir . '/admin/tooltips/membershiplevels.movemembership.tooltips.php');
} elseif (wlm_arrval($_GET,'mode') == 'payperpost') {
	include($this->pluginDir . '/admin/membershiplevels.payperpost.php');
	include_once($this->pluginDir . '/admin/tooltips/membershiplevels.payperpost.tooltips.php');
} elseif(wlm_arrval($_GET,'mode') == '') {
	include($this->pluginDir . '/admin/membershiplevels.default.php');
	include_once($this->pluginDir . '/admin/tooltips/membershiplevels.default.tooltips.php');
}
?>