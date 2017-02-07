<?php

/*
 * GetResponse (API) Autoresponder Integration Functions
 * Original Author : Mike Lopez
 * Version: $Id: integration.autoresponder.getresponseapi.php 2178 2014-06-26 16:05:02Z mike $
 */

//$__classname__ = 'WLM_AUTORESPONDER_GETRESPONSEAPI';
//$__optionname__ = 'getresponseAPI';
//$__methodname__ = 'AutoResponderGetResponseAPI';

if (!class_exists('WLM_AUTORESPONDER_GETRESPONSEAPI')) {

	class WLM_AUTORESPONDER_GETRESPONSEAPI {

		function AutoResponderGetResponseAPI($that, $ar, $wpm_id, $email, $unsub = false) {
			global $wpdb;
			require_once $that->pluginDir . '/extlib/jsonRPCClient.php';
			if ($ar['campaign'][$wpm_id]) {

				$campaign = trim($ar['campaign'][$wpm_id]);
				$name = trim($that->ARSender['name']);
				$email = trim($that->ARSender['email']);
				$api_key = trim($ar['apikey']);
				$api_url = empty($ar['api_url'])? "http://api2.getresponse.com" : trim($ar['api_url']);
				$grUnsub = ($ar['grUnsub'][$wpm_id] == 1 ? true : false);

				$uid = $wpdb->get_var("SELECT ID FROM {$wpdb->users} WHERE `user_email`='" . esc_sql($that->ARSender['email']) . "'");
				$ip = trim($that->Get_UserMeta($uid, 'wpm_login_ip'));
				$ip = ($ip) ? $ip : trim($that->Get_UserMeta($uid, 'wpm_registration_ip'));
				$ip = ($ip) ? $ip : trim($_SERVER['REMOTE_ADDR']);

				try {
					if (!extension_loaded('curl') || !extension_loaded('json')) {
						# these extensions are a must
						throw new Exception("CURL and JSON are modules required to use"
								. " the GetResponse Integration");
					}

					$api = new jsonRPCClient($api_url);
					#get the campaign id
					$resp = $api->get_campaigns($api_key);
					$cid = null;
					if (!empty($resp)) {
						foreach ($resp as $i => $item) {
							if (strtolower($item['name']) == strtolower($campaign)) {
								$cid = $i;
							}
						}
					}
					if (empty($cid)) {
						throw new Exception("Could not find campaign $campaign");
					}

					if ($unsub) {
						if ($grUnsub) {
							//list contacts
							$contacts = $api->get_contacts(
									$api_key, array(
								'campaigns' => array($cid),
								'email' => array('EQUALS' => "$email")
									)
							);
							if (empty($contacts)) {
								#could not find the contact, nothing to remove
								return;
							}
							$pid = key($contacts);
							$res = $api->delete_contact($api_key, array('contact' => $pid));
							if (empty($res)) {
								throw new Exception("Empty server response while deleting contact");
							}
						}
					} else {
						$resp = $api->add_contact(
								$api_key, array(
							'campaign' => $cid,
							'name' => $name,
							'email' => $email,
							'ip' => $ip,
							'cycle_day' => 0,
								)
						);
						if (empty($resp)) {
							throw new Exception("Empty server response while sending");
						}
					}
				} catch (Exception $e) {
					return;
				}
			}
		}

	}

}