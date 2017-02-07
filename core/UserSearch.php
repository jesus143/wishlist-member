<?php

if (!defined('ABSPATH'))
	die();

if (!class_exists('WishListMemberUserSearch')) {
	if(get_bloginfo('version') >= '3.1') {
		require_once(ABSPATH . '/wp-includes/user.php');
		include dirname(__FILE__) . '/UserSearch_WP31.php';
	} elseif (get_bloginfo('version') >= '3') {
		require_once(ABSPATH . '/wp-admin/includes/user.php');
		include dirname(__FILE__) . '/UserSearch_WP30.php';
	} else {
		require_once(ABSPATH . '/wp-admin/includes/user.php');
		include dirname(__FILE__) . '/UserSearch_WP29.php';
	}
}