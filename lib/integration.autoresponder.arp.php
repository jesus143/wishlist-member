<?php

/*
 * Generic Autoresponder Integration Functions
 * Original Author : Mike Lopez
 * Version: $Id: integration.autoresponder.arp.php 1672 2013-08-15 03:52:05Z mike $
 */

//$__classname__ = 'WLM_AUTORESPONDER_ARP';
//$__optionname__ = 'arp';
//$__methodname__ = 'AutoResponderARP';

if (!class_exists('WLM_AUTORESPONDER_ARP')) {

	class WLM_AUTORESPONDER_ARP {

		function AutoResponderARP($that, $ar, $wpm_id, $email, $unsub = false) {
			if (function_exists('curl_init')) {
				$autoresponderID = $ar['arID'][$wpm_id];
				$arUnsub = ($ar['arUnsub'][$wpm_id] == 1 ? true : false);
				if ($autoresponderID) {
					$fullName = $that->ARSender['name'];
					$emailAddress = $that->ARSender['email'];

					$httpAgent = "ARPAgent";
					$arpURL = $ar['arpurl'];
					$postData = "id={$autoresponderID}&full_name={$fullName}&split_name={$fullName}&email={$emailAddress}&subscription_type=E";
					if ($unsub) {
						if ($arUnsub) {
							$postData.='&arp_action=UNS';
							$ch = curl_init();
							curl_setopt($ch, CURLOPT_USERAGENT, $httpAgent);
							curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
							curl_setopt($ch, CURLOPT_URL, $arpURL);
							curl_setopt($ch, CURLOPT_POST, true);
							curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
							curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
							curl_exec($ch);
							curl_close($ch);
						}
					} else {
						$ch = curl_init();
						curl_setopt($ch, CURLOPT_USERAGENT, $httpAgent);
						curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
						curl_setopt($ch, CURLOPT_URL, $arpURL);
						curl_setopt($ch, CURLOPT_POST, true);
						curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
						curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
						curl_exec($ch);
						curl_close($ch);
					}
				}
			}
		}

	}

}
?>
