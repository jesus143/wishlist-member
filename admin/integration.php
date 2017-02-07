<?php if ($show_page_menu) : ?>
	<ul class="wlm-sub-menu">
		<?php $querystring = $this->QueryString('mode', 'cart'); ?>

		<?php if ($this->access_control->current_user_can('wishlistmember_integration_shoppingcart')): ?>
			<li<?php echo (!wlm_arrval($_GET,'mode')) ? ' class="current"' : '' ?>><a href="?<?php echo $querystring; ?>"><?php _e('Shopping Cart', 'wishlist-member'); ?></a></li>
		<?php endif; ?>

		<?php if ($this->access_control->current_user_can('wishlistmember_integration_ar')): ?>
			<li<?php echo (wlm_arrval($_GET,'mode') == 'ar') ? ' class="current"' : '' ?>><a href="?<?php echo $querystring; ?>&mode=ar"><?php _e('AutoResponder', 'wishlist-member'); ?></a></li>
		<?php endif; ?>

		<?php if ($this->access_control->current_user_can('wishlistmember_integration_webinar')): ?>
			<li<?php echo (wlm_arrval($_GET,'mode') == 'webinar') ? ' class="current"' : '' ?>><a href="?<?php echo $querystring; ?>&mode=webinar"><?php _e('Webinar', 'wishlist-member'); ?></a></li>
		<?php endif; ?>

		<?php if ($this->access_control->current_user_can('wishlistmember_integration_other')): ?>
			<li<?php echo (wlm_arrval($_GET,'mode') == 'other') ? ' class="current"' : '' ?>><a href="?<?php echo $querystring; ?>&mode=other"><?php _e('Other', 'wishlist-member'); ?></a></li>
		<?php endif; ?>

		<?php do_action('wishlistmember_integration_menu', $_GET['mode'], '<li><a href="?' . $querystring . '&mode=%s">', '<li class="current"><a href="?' . $querystring . '&mode=%s">', '</a></li>'); ?>
	</ul>
	<?php return;
endif;
?>
<?php
$wpm_levels = $this->GetOption('wpm_levels');
$wpm_scregister = get_bloginfo('url') . '/index.php/register/';
?>
<?php
if (wlm_arrval($_GET,'mode') == 'ar') {
	echo '<h2>' . __('Integration', 'wishlist-member') . ' &raquo; ' . __('AutoResponder', 'wishlist-member') . ' &raquo; <span id="integration-name"></span></h2>';
	include($this->pluginDir . '/admin/integration.autoresponder.php');
} elseif (wlm_arrval($_GET,'mode') == 'webinar') {
	$current_webinar_provider = $this->GetOption('WebinarProvider');
	echo '<h2>' . __('Integration', 'wishlist-member') . ' &raquo; ' . __('Webinar', 'wishlist-member') . ((empty($current_webinar_provider)) ? '' : ' &raquo;').' <span id="integration-name"></span></h2>';
	include($this->pluginDir . '/admin/integration.webinar.php');
} elseif (wlm_arrval($_GET,'mode') == 'other') {
	$is_other_integration_active = $this->GetOption('lastother_integrationviewed');
	echo '<h2>' . __('Integration', 'wishlist-member') . ' &raquo; ' . __('Other', 'wishlist-member') . (($_GET['other_integration'] == '' && empty($is_other_integration_active)) ? '' : ' &raquo;').' <span id="integration-name"></span></h2>';
	include($this->pluginDir . '/admin/integration.other.php');
} elseif (wlm_arrval($_GET,'mode') == '') {
	$is_cart_active = $this->GetOption('lastcartviewed');
	echo '<h2> ' . __('Integration', 'wishlist-member') . ' &raquo; ' . __('Shopping Cart', 'wishlist-member') . (($_GET['cart'] == '' && empty($is_cart_active)) ? '' : ' &raquo;').' <span id="integration-name"></span></h2>';
	include($this->pluginDir . '/admin/shoppingcart.php');
}
do_action('wishlistmember_integration_page', $_GET['mode'], $this);
?>
<script>
	jQuery(function($) {
		jQuery('#integration-name').html('<?php echo $provider_name; ?>');
	});
</script>