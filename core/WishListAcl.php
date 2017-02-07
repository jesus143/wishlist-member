<?php

class WishListAcl {

	private $capability_prefix = 'wishlistmember_';

	public function __construct() {
		
	}

	function current_user_can($cap) {
		if (current_user_can('manage_options') || current_user_can($cap)) {
			return true;
		}
		return false;
	}

}