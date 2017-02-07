<?php

/*
 * Premium Web Cart Integration Functions
 * Original Author : Glen Barnhardt, Mike Lopez, and Daniel Walrous
 * Version: $Id: integration.shoppingcart.premiumwebcart.php 1876 2013-11-25 18:15:28Z mike $
 */

//$__classname__ = 'WLM_INTEGRATION_PREMIUMWEBCART';
//$__optionname__ = 'pwcthankyou';
//$__methodname__ = 'PremiumWebCartSC';

if (!class_exists('WLM_INTEGRATION_PREMIUMWEBCART')) {

	class WLM_INTEGRATION_PREMIUMWEBCART {

		function PremiumWebCartSC($that) {
			/**
			 * This method expects the following POST data
			 * cmd = CREATE / ACTIVATE / DEACTIVATE
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

			if (wlm_arrval($_GET,'oid'))
				$oid = $_GET['oid'];

			// prepare data
			$data = $_POST;

			// Populate the data from Premium WebCart

			unset($data['WishListMemberAction']);
			extract($data);
			unset($data['hash']);

			// Look for the return from Premium cart via the thank you page
			if (!empty($oid)) {
				$secret = $that->GetOption('genericsecret');
				$merchantid = $that->GetOption('pwcmerchantid');
				$apikey = $that->GetOption('pwcapikey');

				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, 'https://www.secureinfossl.com/api/getOrderInfo.html');
				curl_setopt($ch, CURLOPT_POST, 1);
				$request = 'merchantid=' . urlencode($merchantid)
						. '&signature=' . urlencode($apikey)
						. '&orderid=' . urlencode($oid);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_VERBOSE, 1);
				$response = curl_exec($ch);
				if (curl_errno($ch)) {
					die("Error in connecting with merchant system.");
					exit;
				} else {
					curl_close($ch);
				}

				$parser = new simpleXMLElement($response);
				foreach ($parser as $row) {
					$email = $row->customer->email;
				}
				header('Location: ' . $that->GetContinueRegistrationURL($email));
				exit;
			}

			// Valid Transaction Types
			$valid_transaction_types = array("onetime", "recurring", "failed", "cancelled", "refund");

			// Check for valid Transaction Types
			if (!in_array($transaction_type, $valid_transaction_types)) {
				die('Invalid transaction type');
				exit;
			}

			// Set the command that is needed
			switch ($transaction_type) {
				case 'onetime':
					$cmd = 'CREATE';
					break;
				case 'recurring':
					$transaction_id = ($subscription_id == null) ? $transaction_id : $subscription_id;
					$cmd = 'ACTIVATE';
					break;
				case 'refund':
					$cmd = 'DEACTIVATE';
					break;
				case 'cancelled':
					//added for cancellation
					$transaction_id = ($subscription_id == null) ? $transaction_id : $subscription_id;
					$cmd = 'DEACTIVATE';
					break;
				case 'failed':
					$cmd = 'DEACTIVATE';
			}

			// valid commands
			$commands = array('CREATE', 'DEACTIVATE', 'ACTIVATE');
			// secret key
			$secret = $that->GetOption('pwcsecret');
			// hash
			$myhash = md5($x = $cmd . '__' . $secret . '__' . strtoupper(implode('|', $data)));

			// PWC has it's own hashing routine which we check below so we fudge our hash here
			$wlmhash = md5($x = $cmd . '__' . $secret . '__' . strtoupper(implode('|', $data)));

			// Check PWC Hash for Security
			$apikey = $that->GetOption('pwcapikey');

			$hashstring = $transaction_type . $product_sku . $customer_email;
			$len = strlen($apikey);
			$saltedhashstring = substr($apikey, 0, round($len / 2)) . $hashstring . substr($apikey, round($len / 2), $len);
			$securityhash = md5($saltedhashstring);

			if ($hash != $securityhash) {
				die('Invalid hash. Possible hacking attempt logged.');
				exit;
			} else {
				$hash = $wlmhash;
				$_POST['hash'] = $hash;
			}

			// additional POST data for our system to work
			$_POST['action'] = 'wpm_register';
			$_POST['wpm_id'] = $product_sku;
			$_POST['username'] = $customer_email;
			$_POST['password1'] = $_POST['password2'] = $that->PassGen();
			$_POST['sctxnid'] = $transaction_id;
			$_POST['firstname'] = $customer_first_name;
			$_POST['lastname'] = $customer_last_name;
			$_POST['email'] = $customer_email;

			// save address (originally for kunaki)
			$address = array();
			$address['company'] = $shipping_company_name;
			$address['address1'] = $billing_address_line1;
			$address['address2'] = $billing_address_line2;
			$address['city'] = $billing_customer_city;
			$address['state'] = $billing_customer_state;
			$address['zip'] = $billing_customer_zip;
			$address['country'] = $billing_customer_country;
			$address['phone'] = $phone;
			$address['fax'] = $fax;
			$_POST['wpm_useraddress'] = $address;

			$wpm_levels = $that->GetOption('wpm_levels');

			if ($cmd == 'CREATE') {
				if (!isset($wpm_levels[$level]) && !$that->IsPPPLevel($level)) {
					die("ERROR\nINVALID SKU");
				}
			}
			if ($hash == $myhash && in_array($cmd, $commands)) {
				switch ($cmd) {
					case 'CREATE':
						$that->ShoppingCartRegistration();
						break;
					case 'DEACTIVATE':
						$that->ShoppingCartDeactivate();
						break;
					case 'ACTIVATE':
						$that->ShoppingCartReactivate();
						
						// Add hook for Shoppingcart reactivate so that other plugins can hook into this
						$_POST['sc_type'] = 'pwc';
						do_action('wlm_shoppingcart_rebill', $_POST);
						
						break;
				}
			}
			die('ERROR');
		}

	}

}
?>