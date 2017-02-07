<?php if ( isset($show_page_menu) && $show_page_menu) : ?>
	<ul class="wlm-sub-menu">
		<?php if ($this->access_control->current_user_can('wishlistmember_members_manage')): ?>
			<li<?php echo (!wlm_arrval($_GET,'mode')) ? ' class="current"' : '' ?>><a href="?<?php echo $this->QueryString('usersearch', 'mode', 'level') ?>"><?php _e('Manage Members', 'wishlist-member'); ?></a></li>
		<?php endif; ?>

		<?php if ($this->access_control->current_user_can('wishlistmember_members_import')): ?>
			<li<?php echo (wlm_arrval($_GET,'mode') == 'import') ? ' class="current"' : '' ?>><a href="?<?php echo $this->QueryString('usersearch', 'mode', 'level') ?>&mode=import"><?php _e('Import', 'wishlist-member'); ?></a></li>
		<?php endif; ?>

		<?php if ($this->access_control->current_user_can('wishlistmember_members_export')): ?>
			<li<?php echo (wlm_arrval($_GET,'mode') == 'export') ? ' class="current"' : '' ?>><a href="?<?php echo $this->QueryString('usersearch', 'mode', 'level') ?>&mode=export"><?php _e('Export', 'wishlist-member'); ?></a></li>
		<?php endif; ?>

		<?php if ($this->access_control->current_user_can('wishlistmember_members_broadcast')): ?>
			<li<?php echo (wlm_arrval($_GET,'mode') == 'broadcast' || wlm_arrval($_GET,'mode') == 'sendbroadcast') ? ' class="current"' : '' ?>><a href="?<?php echo $this->QueryString('usersearch', 'mode', 'level') ?>&mode=broadcast"><?php _e('Email Broadcast', 'wishlist-member'); ?></a></li>
		<?php endif; ?>

		<?php if ($this->access_control->current_user_can('wishlistmember_members_blacklist')): ?>
			<li<?php echo (wlm_arrval($_GET,'mode') == 'blacklist') ? ' class="current"' : '' ?>><a href="?<?php echo $this->QueryString('usersearch', 'mode', 'level') ?>&mode=blacklist"><?php _e('Blacklist', 'wishlist-member'); ?></a></li>
		<?php endif; ?>
	</ul>
	<?php return;
endif;
?>
<?php
// Get Membership Levels
$wpm_levels = $this->GetOption('wpm_levels');
if ($_POST) {
	foreach ((array) $_POST as $pk => $pv) {
		if (!is_array($pv))
			$_POST[$pk] = trim($pv);
	}
}
?>
<?php
if (wlm_arrval($_GET,'mode') == 'blacklist') {
	include('members.blacklist.php');
	include_once($this->pluginDir . '/admin/tooltips/members.blacklist.tooltips.php');
} elseif (wlm_arrval($_GET,'mode') == 'broadcast') {
	include('members.broadcast.php');
	include_once($this->pluginDir . '/admin/tooltips/members.broadcast.tooltips.php');
} elseif (wlm_arrval($_GET,'mode') == 'sendbroadcast') {
	include('members.sendbroadcast.php');
	include_once($this->pluginDir . '/admin/tooltips/members.broadcast.tooltips.php');
} elseif (wlm_arrval($_GET,'mode') == 'import') {
	include('members.import.php');
	include_once($this->pluginDir . '/admin/tooltips/members.import.tooltips.php');
} elseif (wlm_arrval($_GET,'mode') == 'export') {
	include('members.export.php');
	include_once($this->pluginDir . '/admin/tooltips/members.export.tooltips.php');
} else {
	include('members.default.php');
	include_once($this->pluginDir . '/admin/tooltips/members.default.tooltips.php');
}
?>