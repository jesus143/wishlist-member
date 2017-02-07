<?php

/*
 * MAROPOST Autoresponder
 * Original Author : Erwin Atuli
 */

if (!class_exists('WPMaropost')) {
	require_once dirname(__FILE__) . '/../extlib/maropost/maropost.php';
}


if (!class_exists('WLM_AUTORESPONDER_MAROPOST')) {

	class WLM_AUTORESPONDER_MAROPOST {

		private $api;
		private $wlm;
		public function __construct() {
			global $WishListMemberInstance;
			$maropost = $WishListMemberInstance->GetOption('Autoresponders');
			$maropost = $maropost['maropost'];

			$this->api = new WPMaropost($maropost['account_id'], $maropost['auth_token']);
			$this->wlm = $WishListMemberInstance;

		}
		public function add_to_lists($lists, $user) {
			$wp_user = get_user_by('email', $user['email']);
			foreach($lists as $list) {
				$obj = $this->api->add_to_list($list, $user);
				if(!empty($obj)) {
					$this->wlm->Update_UserMeta($wp_user->ID, 'maropost-'.$list , $obj->id);
				}
			}
		}
		public function remove_from_lists($lists, $user) {
			$wp_user = get_user_by('email', $user);
			foreach($lists as $list) {
				$contact_id = $this->wlm->Get_UserMeta($wp_user->ID, 'maropost-'.$list);
				if(!empty($contact_id)) {
					$this->api->remove_from_list($list, $contact_id);
				}
			}
		}
		public function maropost_subscribe($that, $ar, $wpm_id, $email, $unsub = false) {
			$options = $that->GetOption('Autoresponders');
			$maps = $options['maropost']['maps'][$wpm_id];

			if(empty($maps)) {
				return;
			}

			try {
				if(!empty($maps)) {
					if($unsub && $options['maropost'][$wpm_id]['autoremove']) {
						$this->remove_from_lists($maps, $email);
					}
					if(!$unsub) {
						$this->add_to_lists($maps, array(
							'first_name' 	=> $that->ARSender['first_name'],
							'last_name'		=> $that->ARSender['last_name'],
							'email'			=> $that->ARSender['email'])
						);
					}

				}
			} catch (Exception $e) {
				error_log($e->getMessage());
			}
		}
	}
}