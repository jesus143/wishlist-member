<?php

/*
 * Generic Shopping Cart Integration Functions
 * Original Author : Mike Lopez
 * Version: $Id: integration.shoppingcart.redoakcart.php 1671 2013-08-15 01:34:55Z mike $
 */

//$__classname__ = 'WLM_INTEGRATION_REDOAKCART';
//$__optionname__ = 'redoakcartthankyou';
//$__methodname__ = 'RedOakCart';

if (!class_exists('WLM_INTEGRATION_REDOAKCART')) {

	class WLM_INTEGRATION_REDOAKCART {

		function RedOakCart($that) {
			/**
			 * This method expects the following POST data
			 * cmd = CREATE / ACTIVATE / DEACTIVATE / PING
			 * hash = hash - md5 of cmd + __ + secret key + __ + post data minus the hash key merged with | in uppercase
			 * lastname = client's lastname
			 * firstname = client's firstname
			 * email = client's email address
			 * level = membership level
			 * transaction_id = transaction ID.  has to be the same for all related transactions
			 *
			 * OPTIONAL DATA are:
			 * company, address1, address2, city, state, zip, country, phone, fax
			 */
			// we accept both GET and POST for this interface
			if (wlm_arrval($_GET,'cmd'))
				$_POST = array_merge($_GET, $_POST);

			// prepare data
			$data = $_POST;
			unset($data['WishListMemberAction']);
			extract($data);
			unset($data['hash']);

			// valid commands
			$commands = array('CREATE', 'DEACTIVATE', 'ACTIVATE', 'PING');
			// secret key
			$secret = $that->GetOption('redoakcartsecret');
			// hash
			$myhash = md5($x = $cmd . '__' . $secret . '__' . strtoupper(implode('|', $data)));

			// additional POST data for our system to work
			$_POST['action'] = 'wpm_register';
			$_POST['wpm_id'] = $level;
			$_POST['username'] = $email;
			$_POST['password1'] = $_POST['password2'] = $that->PassGen();
			$_POST['sctxnid'] = trim($transaction_id);

			// save address (originally for kunaki)
			$address = array();
			$address['company'] = $company;
			$address['address1'] = $address1;
			$address['address2'] = $address2;
			$address['city'] = $city;
			$address['state'] = $state;
			$address['zip'] = $zip;
			$address['country'] = $country;
			$address['phone'] = $phone;
			$address['fax'] = $fax;
			$_POST['wpm_useraddress'] = $address;

			$wpm_levels = $that->GetOption('wpm_levels');

			if ($cmd == 'CREATE') {
				if (!isset($wpm_levels[$level]) && !$that->IsPPPLevel($level)) {
					die("ERROR\nINVALID SKU");
				}
			}


			if (wlm_arrval($_POST,'sctxnid') === '' && $cmd != 'PING') {
				die("ERROR\nTRANSACTION ID REQUIRED");
			}

			if ($hash == $myhash && in_array($cmd, $commands)) {
//				add_filter('rewrite_rules_array',array(&$that,'RewriteRules'));
//				$GLOBALS['wp_rewrite']->flush_rules();
				switch ($cmd) {
					case 'CREATE':
						$temp = $autocreate == 1 ? false : true;
						$wpm_errmsg = $that->ShoppingCartRegistration($temp, false);
						if ($wpm_errmsg) {
							print("ERROR\n");
							print(strtoupper($wpm_errmsg));
						} else {
							print($cmd);
							if ($temp)
								print("\n" . $that->GetContinueRegistrationURL($email));
						}
						exit;
						break;
					case 'DEACTIVATE':
						print($cmd);
						$that->ShoppingCartDeactivate();
						exit;
						break;
					case 'ACTIVATE':
						print($cmd);
						$that->ShoppingCartReactivate();
						exit;
						break;
					case 'PING':
						print($cmd);
						print("\nOK");
						exit;
				}
			}
			print("ERROR\n");
			if ($hash != $myhash) {
				die("INVALID HASH");
			}
			if (!in_array($cmd, $commands)) {
				die("INVALID COMMAND");
			}
			die("UNKNOWN ERROR");
		}

	}

}
?>