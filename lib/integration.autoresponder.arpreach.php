<?php

/*
 * arpReach Integration Functions
 * Original Author : Fel Jun Palawan
 * Version: $Id: integration.autoresponder.arpreach.php 1672 2013-08-15 03:52:05Z mike $
 */

//$__classname__ = 'WLM_AUTORESPONDER_ARPREACH';
//$__optionname__ = 'arpreach';
//$__methodname__ = 'AutoResponderARPReach';

if (!class_exists('WLM_AUTORESPONDER_ARPREACH')) {

	class WLM_AUTORESPONDER_ARPREACH {

		function AutoResponderARPReach($that, $ar, $wpm_id, $email, $unsub = false) {
			if (function_exists('curl_init')) {
				$postURL = $ar['postURL'][$wpm_id];
				$arUnsub = ($ar['arUnsub'][$wpm_id] == 1 ? true : false);
				if ($postURL) {
					$emailAddress = $that->ARSender['email'];
					list($fName, $lName) = explode(" ", $that->ARSender['name'], 2); //split the name into First and Last Name
					$httpAgent = "WLMARPREACH_AGENT";
					$postData = array(
						"email_address" => $emailAddress,
						"first_name" => $fName,
						"last_name" => $lName
					);
					if ($unsub) {
						if ($arUnsub) {
							$postData["unsubscribe"] = 1;
						}
					} else {
						$postData["unsubscribe"] = 0;
					}

					if (isset($postData["unsubscribe"])) {
						$ch = curl_init();
						curl_setopt($ch, CURLOPT_USERAGENT, $httpAgent);
						curl_setopt($ch, CURLOPT_URL, $postURL);
						curl_setopt($ch, CURLOPT_POST, true);
						curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
						curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
						curl_exec($ch);
						curl_close($ch);
					}
				}
			}
		}

	}

}
?>
