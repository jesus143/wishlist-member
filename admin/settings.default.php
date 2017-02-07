<!-- Configuration -->
<?php
if ($_POST)
	$this->FileProtectHtaccess(!($this->GetOption('file_protection') == 1));

if ($_POST) {
	$this->RemoveAllHtaccessFromProtectedFolders();
	if ($this->GetOption('folder_protection') == 1) {
		$this->AddHtaccessToProtectedFolders();
	}
}

$h2text = __('Settings &raquo; Configuration', 'wishlist-member');
?>
<?php
switch (wlm_arrval($_GET,'mode2')) {
	case 'others':
		printf("<h2>$h2text &raquo; %s</h2>", __('Miscellaneous', 'wishlist-member'));
		include($this->pluginDir . '/admin/settings.default.other.php');
		break;
	case 'protectdefaults':
		printf("<h2>$h2text &raquo; %s</h2>", __('Protection Defaults', 'wishlist-member'));
		include($this->pluginDir . '/admin/settings.default.protectdefaults.php');
		break;
	case 'cron':
		printf("<h2>$h2text &raquo; %s</h2>", __('Cron Settings', 'wishlist-member'));
		include($this->pluginDir . '/admin/settings.default.cron.php');
		break;
	case 'customposttypes':
		printf("<h2>$h2text &raquo; %s</h2>", __('Custom Post Types', 'wishlist-member'));
		include($this->pluginDir . '/admin/settings.default.customposttypes.php');
		break;
	case 'logs':
		printf("<h2>$h2text &raquo; %s</h2>", __('Logs', 'wishlist-member'));
		include($this->pluginDir . '/admin/settings.default.logs.php');
		break;
	case '':
		printf("<h2>$h2text &raquo; %s</h2>", __('System Pages', 'wishlist-member'));
		include($this->pluginDir . '/admin/settings.default.systempages.php');
		break;
}
include_once($this->pluginDir . '/admin/tooltips/settings.cron.tooltips.php');
?>
