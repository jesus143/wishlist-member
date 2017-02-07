<?php

/*
 * Generic Autoresponder Integration Functions
 * Original Author : Mike Lopez
 * Version: $Id: integration.autoresponder.aweber.php 1672 2013-08-15 03:52:05Z mike $
 */

//$__classname__ = 'WLM_AUTORESPONDER_AWEBER';
//$__optionname__ = 'aweber';
//$__methodname__ = 'AutoResponderAweber';

if (!class_exists('WLM_AUTORESPONDER_AWEBER')) {

	class WLM_AUTORESPONDER_AWEBER {

		function AutoResponderAweber($that, $ar, $wpm_id, $email, $unsub = false) {
			$headers = "Content-type: text/plain; charset=us-ascii\r\n";
			if ($ar['email'][$wpm_id]) {
				$sendto = $ar['email'][$wpm_id];
				if (strpos($sendto, '@') === false)
					$sendto.='@aweber.com';
				if (!$unsub) {
					$name = $that->ARSender['name'];
					$message = "{$email}\n{$name}";
					$that->ARSender = array('name' => "Aweber Subscribe Parser", 'email' => $that->GetOption('email_sender_address'));
					wp_mail($sendto, 'A New Member has Registered', $message, $headers);
				} else {
					$that->ARSender = array('name' => "Aweber Remove", 'email' => $ar['remove'][$wpm_id]);
					$subject = 'REMOVE#' . $email . '#WLMember';
					wp_mail($sendto, $subject, 'AWEBER UNSUBSCRIBE', $headers);
				}
			}
		}

	}

}
?>
