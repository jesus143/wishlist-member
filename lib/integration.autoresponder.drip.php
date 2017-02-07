<?php

/*
 * Drip Autoresponder Integration Functions
 * Original Author : Mike Lopez
 * Version: $Id$
 */

if (!class_exists('WLM_AUTORESPONDER_DRIP')) {

	class WLM_AUTORESPONDER_DRIP {

		function AutoResponderDrip($that, $ar, $wpm_id, $email, $unsub = false) {
			global $wpdb;
			$token = trim($ar['apitoken']);
			if(empty($token)) return;
			
			require_once $that->pluginDir . '/extlib/wlm_drip/Drip_API.class.php';
			$drip_api = new WLM_Drip_Api($token);

			if ($ar['campaign'][$wpm_id]) {
				list($account_id, $campaign_id) = explode('-', $ar['campaign'][$wpm_id]);
				$params = array(
					'account_id' => $account_id,
					'campaign_id' => $campaign_id,
					'email' => $email
				);
				if($unsub) {
					if($ar['unsub'][$wpm_id]) {
						$drip_api->unsubscribe_subscriber($params);
					}
				}else{
					$params['double_optin'] = (bool) $ar['double'][$wpm_id];
					$drip_api->subscribe_subscriber($params);
				}
			}
		}

	}

}