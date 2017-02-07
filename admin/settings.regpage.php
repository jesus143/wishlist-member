<?php $h2text = __('Settings &raquo; Registration Page', 'wishlist-member'); ?>
<?php

switch (wlm_arrval($_GET,'mode2')) {
	case 'css':
		printf("<h2>$h2text &raquo; %s</h2>", __('Custom CSS', 'wishlist-member'));
		include($this->pluginDir . '/admin/settings.registration.css.php');
		include_once($this->pluginDir . '/admin/tooltips/settings.registration.css.tooltips.php');
		break;
	case 'custom':
		printf("<h2>$h2text &raquo; %s</h2>", __('Custom Registration Forms', 'wishlist-member'));
		include($this->pluginDir . '/admin/settings.registration.custom.php');
		include_once($this->pluginDir . '/admin/tooltips/settings.registration.custom.tooltips.php');
		break;
	case 'redbox':
		printf("<h2>$h2text &raquo; %s</h2>", __('Signup Text', 'wishlist-member'));
		include($this->pluginDir . '/admin/settings.registration.redbox.php');
		include_once($this->pluginDir . '/admin/tooltips/settings.registration.redbox.tooltips.php');
		break;
	case 'recaptcha':
		printf("<h2>$h2text &raquo; %s</h2>", __('reCaptcha', 'wishlist-member'));
		include($this->pluginDir . '/admin/settings.registration.recaptcha.php');
		include_once($this->pluginDir . '/admin/tooltips/settings.registration.recaptcha.tooltips.php');
		break;
	case '':
		printf("<h2>$h2text &raquo; %s</h2>", __('Header/Footer', 'wishlist-member'));
		include($this->pluginDir . '/admin/settings.registration.php');
		include_once($this->pluginDir . '/admin/tooltips/settings.registration.tooltips.php');
		break;
}
?>
