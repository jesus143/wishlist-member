<?php
$base_url = $this->QueryString('mode', 'level', 'mode2', 'action', 'id');
$regpage_base_url = $this->QueryString('mode2', 'action', 'id', 'level', 'form_id');
$config_base_url = $this->QueryString('mode2');
?>
<?php if ( isset($show_page_menu) && $show_page_menu == true) : ?>
	<ul class="wlm-sub-menu with-sub-two">
		<?php if ($this->access_control->current_user_can('wishlistmember_settings_configuration')): ?>
			<li<?php echo (!wlm_arrval($_GET,'mode')) ? ' class="current has-sub-menu"' : '' ?>><a href="?<?php echo $base_url; ?>"><?php _e('Configuration', 'wishlist-member'); ?></a></li>
		<?php endif; ?>

		<?php if ($this->access_control->current_user_can('wishlistmember_settings_email')): ?>
			<li<?php echo (wlm_arrval($_GET,'mode') == 'email') ? ' class="current"' : '' ?>><a href="?<?php echo $base_url; ?>&mode=email"><?php _e('Email Settings', 'wishlist-member'); ?></a></li>
		<?php endif; ?>

		<?php if ($this->access_control->current_user_can('wishlistmember_settings_regpage')): ?>
			<li<?php echo (wlm_arrval($_GET,'mode') == 'regpage') ? ' class="current has-sub-menu"' : '' ?>><a href="?<?php echo $base_url; ?>&mode=regpage"><?php _e('Registration Page', 'wishlist-member'); ?></a></li>
		<?php endif; ?>

		<?php if ($this->access_control->current_user_can('wishlistmember_settings_advanced')): ?>
			<li<?php echo (wlm_arrval($_GET,'mode') == 'advanced') ? ' class="current"' : '' ?>><a href="?<?php echo $base_url; ?>&mode=advanced"><?php _e('Advanced', 'wishlist-member'); ?></a></li>
		<?php endif; ?>

		<?php if ($this->access_control->current_user_can('wishlistmember_settings_backup')): ?>
			<li<?php echo (wlm_arrval($_GET,'mode') == 'backup') ? ' class="current"' : '' ?>><a href="?<?php echo $base_url; ?>&mode=backup"><?php _e('Backup', 'wishlist-member'); ?></a></li>
		<?php endif; ?>

		<?php if ($this->access_control->current_user_can('wishlistmember_settings_import')): ?>
			<li<?php echo (wlm_arrval($_GET,'mode') == 'import') ? ' class="current"' : '' ?>><a href="?<?php echo $base_url; ?>&mode=import"><?php _e('Import/Export', 'wishlist-member'); ?></a></li>
		<?php endif; ?>

		<?php if ($this->access_control->current_user_can('wishlistmember_settings_wizard')): ?>
			<li<?php echo (wlm_arrval($_GET,'mode') == 'wizard') ? ' class="current"' : '' ?>><a href="?<?php echo $base_url; ?>&mode=wizard"><?php _e('Setup Wizard', 'wishlist-member'); ?></a></li>
		<?php endif; ?>
	</ul>
	<?php if (wlm_arrval($_GET,'mode') == 'regpage') : ?>
		<ul class="wlm-sub-menu sub-sub">
			<li<?php echo (!wlm_arrval($_GET,'mode2')) ? ' class="current"' : '' ?>><a href="?<?php echo $regpage_base_url; ?>"><?php _e('Header/Footer', 'wishlist-member'); ?></a></li>
			<li<?php echo (wlm_arrval($_GET,'mode2') == 'redbox') ? ' class="current"' : '' ?>><a href="?<?php echo $regpage_base_url; ?>&mode2=redbox"><?php _e('Signup Text', 'wishlist-member'); ?></a></li>
			<li<?php echo (wlm_arrval($_GET,'mode2') == 'custom') ? ' class="current"' : '' ?>><a href="?<?php echo $regpage_base_url; ?>&mode2=custom"><?php _e('Custom Registration Forms', 'wishlist-member'); ?></a></li>
			<li<?php echo (wlm_arrval($_GET,'mode2') == 'css') ? ' class="current"' : '' ?>><a href="?<?php echo $regpage_base_url; ?>&mode2=css"><?php _e('Custom CSS', 'wishlist-member'); ?></a></li>
			<li<?php echo (wlm_arrval($_GET,'mode2') == 'recaptcha') ? ' class="current"' : '' ?>><a href="?<?php echo $regpage_base_url; ?>&mode2=recaptcha"><?php _e('reCaptcha', 'wishlist-member'); ?></a></li>
		</ul>
	<?php endif; ?>
	<?php if (empty($_GET['mode'])) : ?>
		<ul class="wlm-sub-menu sub-sub">
			<li<?php echo (!wlm_arrval($_GET,'mode2')) ? ' class="current"' : '' ?>><a href="?<?php echo $config_base_url; ?>"><?php _e('System Pages', 'wishlist-member'); ?></a></li>
			<li<?php echo (wlm_arrval($_GET,'mode2') == 'protectdefaults') ? ' class="current"' : '' ?>><a href="?<?php echo $regpage_base_url; ?>&mode2=protectdefaults"><?php _e('Protection Defaults', 'wishlist-member'); ?></a></li>
			<li<?php echo (wlm_arrval($_GET,'mode2') == 'customposttypes') ? ' class="current"' : '' ?>><a href="?<?php echo $regpage_base_url; ?>&mode2=customposttypes"><?php _e('Custom Post Types', 'wishlist-member'); ?></a></li>
			<li<?php echo (wlm_arrval($_GET,'mode2') == 'others') ? ' class="current"' : '' ?>><a href="?<?php echo $regpage_base_url; ?>&mode2=others"><?php _e('Miscellaneous', 'wishlist-member'); ?></a></li>
			<li<?php echo (wlm_arrval($_GET,'mode2') == 'cron') ? ' class="current"' : '' ?>><a href="?<?php echo $regpage_base_url; ?>&mode2=cron"><?php _e('Cron Settings', 'wishlist-member'); ?></a></li>
			<li<?php echo (wlm_arrval($_GET,'mode2') == 'logs') ? ' class="current"' : '' ?>><a href="?<?php echo $regpage_base_url; ?>&mode2=logs"><?php _e('Logs', 'wishlist-member'); ?></a></li>
		</ul>
	<?php endif; ?>
	<?php return;
endif;
?>
<?php
switch (wlm_arrval($_GET,'mode')) {
	case 'advanced':
		include($this->pluginDir . '/admin/settings.advanced.php');
		include_once($this->pluginDir . '/admin/tooltips/settings.advanced.tooltips.php');
		break;
	case 'regpage':
		include($this->pluginDir . '/admin/settings.regpage.php');
		break;
	case 'backup':
		include($this->pluginDir . '/admin/settings.backup.php');
		break;
	case 'email':
		include($this->pluginDir . '/admin/settings.email.php');
		include_once($this->pluginDir . '/admin/tooltips/settings.email.tooltips.php');
		break;
	case 'import':
		include($this->pluginDir . '/admin/settings.import.php');
		include($this->pluginDir . '/admin/tooltips/settings.import.tooltips.php');
		break;
	case 'wizard':
		include($this->pluginDir . '/admin/settings.wizard.php');
		include_once($this->pluginDir . '/admin/tooltips/settings.wizard.tooltips.php');
		break;
	default:
		include($this->pluginDir . '/admin/settings.default.php');
		include_once($this->pluginDir . '/admin/tooltips/settings.default.tooltips.php');
}
if (isset($show_page_menu) && $show_page_menu == true) {
	return;
}
?>
