<?php

/*
 * Generic Autoresponder Integration Functions
 * Original Author : Mike Lopez
 * Version: $Id: integration.autoresponder.generic.php 1672 2013-08-15 03:52:05Z mike $
 */

//$__classname__ = 'WLM_AUTORESPONDER_GENERIC';
//$__optionname__ = 'generic';
//$__methodname__ = 'AutoResponderGeneric';

if (!class_exists('WLM_AUTORESPONDER_GENERIC')) {

	class WLM_AUTORESPONDER_GENERIC {

		function AutoResponderGeneric($that, $ar, $wpm_id, $email, $unsub = false) {
			$headers = "Content-type: text/plain; charset=us-ascii\r\n";
			if ($ar['email'][$wpm_id]) {
				if (!$unsub) {
					wp_mail($ar['email'][$wpm_id], 'Subscribe', '.', $headers);
				} else {
					wp_mail($ar['remove'][$wpm_id], 'Unsubscribe', '.', $headers);
				}
			}
		}

	}

}
?>
