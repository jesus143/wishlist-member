<?php

/*
 * 2Checkout Shopping Cart Integration Functions
 * Original Author : Glen Barnhardt
 * Version: $Id: integration.shoppingcart.twoco.php 2262 2014-08-15 14:19:23Z mike $
 */

//$__classname__ = 'WLM_INTEGRATION_TWOCO';
//$__optionname__ = 'twocothankyou';
//$__methodname__ = 'TwocoSC';

if (!class_exists('WLM_INTEGRATION_TWOCO')) {

	class WLM_INTEGRATION_TWOCO {

		function TwocoSC($that) {
			// we accept both GET and POST for this interface
			if (wlm_arrval($_GET,'cmd'))
				$_POST = array_merge($_GET, $_POST);

			// prepare data
			// $data = $_POST;
			$data = $_REQUEST; // we now use $_REQUEST to support 2CO's header return
			unset($data['WishListMemberAction']);
			extract($data);
			unset($data['md5_hash']);
			// grab the message type
			$cmd = $message_type;
			// valid commands
			$commands = array('ORDER_CREATED', 'REFUND_ISSUED', 'RECURRING_INSTALLMENT_SUCCESS', 'RECURRING_INSTALLMENT_FAILED', 'RECURRING_STOPPED', 'RECURRING_RESTARTED');

			// secret key
			$secret = $that->GetOption('twocosecret');

			// vendor id
			$vendor_id = $that->GetOption('twocovendorid');

			// Level
			if (!empty($item_id_1)) {
				$level = $item_id_1;
			} else {
				$level = $merchant_product_id;
			}

			// hash md5 ( sale_id + vendor_id + invoice_id + Secret Word )

			if (empty($md5_hash)) {
				// non INS transactions have a different hash secret word + vendor number + order number + total
				$md5_hash = $key;
				if ($demo == 'Y' && $that->GetOption('twocodemo') == 1) {
					$myhash = strtoupper(md5($x = $secret . $sid . '1' . $total));
				} else {
					$myhash = strtoupper(md5($x = $secret . $sid . $order_number . $total));
				}
				$customer_email = $email;
				$sale_id = $order_number;
				$cmd = "ORDER_CREATED";
			} else {
				$myhash = strtoupper(md5($x = $sale_id . $vendor_id . $invoice_id . $secret));
			}

			// additional POST data for our system to work
			$_POST['action'] = 'wpm_register';
			$_POST['wpm_id'] = $level;
			$_POST['lastname'] = $customer_last_name ? $customer_last_name : $last_name;
			$_POST['firstname'] = $customer_first_name ? $customer_first_name : $first_name;
			$_POST['username'] = $customer_email;
			$_POST['email'] = $customer_email;
			$_POST['password1'] = $_POST['password2'] = $that->PassGen();
			$_POST['sctxnid'] = $sale_id;

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

			if ($cmd == 'ORDER_CREATED') {
				if (!isset($wpm_levels[$level]) && !$that->IsPPPLevel($level)) {
					die("ERROR\nINVALID SKU");
				}
			}

			if (wlm_arrval($_POST,'sctxnid') === '') {
				die("ERROR\nSALE ID REQUIRED");
			}

			if ($md5_hash == $myhash && in_array($cmd, $commands)) {

				switch ($cmd) {
					case 'ORDER_CREATED':
						$that->ShoppingCartRegistration();
						exit;
						break;
					case 'REFUND_ISSUED':
					case 'RECURRING_STOPPED':
					case 'RECURRING_INSTALLMENT_FAILED':
						$that->ShoppingCartDeactivate();
						exit;
						break;
					case 'RECURRING_RESTARTED':
					case 'RECURRING_INSTALLMENT_SUCCESS':
						$that->ShoppingCartReactivate();
						
						// Add hook for Shoppingcart reactivate so that other plugins can hook into this
						$_POST['sc_type'] = '2Checkout';
						do_action('wlm_shoppingcart_rebill', $_POST);
						
						exit;
						break;
				}
			}
			print("ERROR\n");
			if ($myhash != $md5_hash) {
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