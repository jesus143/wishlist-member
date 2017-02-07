<?php

/*
 * Active Campaign Autoresponder Integration Functions
 * Original Author : Erwin Atuli
 * Version: $Id: integration.autoresponder.activecampaign.php 2389 2014-10-22 15:59:18Z mike $
 */

if (!class_exists('AWeberAPI')) {
	require_once dirname(__FILE__) . '/../extlib/active-campaign/active-campaign.php';
}

//$__classname__ = 'WLM_AUTORESPONDER_ACTIVECAMPAIGN';
//$__optionname__ = 'activecampaign';
//$__methodname__ = 'subscribe';

if (!class_exists('WLM_AUTORESPONDER_ACTIVECAMPAIGN')) {

	class WLM_AUTORESPONDER_ACTIVECAMPAIGN {

		public function activecampaign_subscribe($that, $ar, $wpm_id, $email, $unsub = false) {
			$options = $that->GetOption('Autoresponders');
			$maps = $options['activecampaign']['maps'][$wpm_id];

			if(empty($maps)) {
				return;
			}


			$api_url = $options['activecampaign']['api_url'];
			$api_key = $options['activecampaign']['api_key'];

			$ac = new WpActiveCampaign($api_url, $api_key);

			try {
				if(!empty($maps)) {
					if($unsub && $options['activecampaign'][$wpm_id]['autoremove']) {
						$ac->remove_from_lists($maps, $email);
					}
					if(!$unsub) {
						$ac->add_to_lists($maps, array(
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