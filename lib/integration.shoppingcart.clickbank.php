<?php

/*
 * Clickbank Shopping Cart Integration Functions
 * Original Author : Mike Lopez
 * Version: $Id: integration.shoppingcart.clickbank.php 2813 2015-07-29 14:30:25Z mike $
 */

//$__classname__ = 'WLM_INTEGRATION_CLICKBANK';
//$__optionname__ = 'cbthankyou';
//$__methodname__ = 'ClickBank';

if (!class_exists('WLM_INTEGRATION_CLICKBANK')) {

	class WLM_INTEGRATION_CLICKBANK {

		function ClickBank($that) {
			$key = $that->GetOption('cbsecret');

			$cbproducts = $that->GetOption('cbproducts');
			if (empty($cbproducts))
				$cbproducts = array();

			$cbupsells_ttl = $that->GetOption('cbupsells_ttl');
			if (empty($cbupsells_ttl))
				$cbupsells_ttl = 60 * 60;

			if ($this->ty_valid_req($key, $_GET)) {

				// check if product ID (item) is in cbproducts
				// if so, return the level for that product ID
				// if not, use $_GET['sku']
				$postedid = $_GET['item'];
				$wpm_levels = (array) $that->GetOption('wpm_levels');
				foreach ((array) $wpm_levels AS $sku => $level){
					if (in_array($postedid, (array) $cbproducts[$sku])) {
						$level_id = $sku;
						break;
					}
				}					
					
				if (empty($level_id)){
					$xposts = $that->GetPayPerPosts(array('post_title', 'post_type'));
					foreach ($xposts AS $post_type => $posts){ 
						foreach ($posts AS $post){
							if (in_array($postedid, (array) $cbproducts['payperpost-'.$post->ID])) {
								$level_id = 'payperpost-'.$post->ID;
								break;
								}
								}
					}
				}
				$_POST['wpm_id'] = empty($level_id) ? $_GET['sku'] : $level_id;

				$user_id = $that->GetUserIDFromTxnID(wlm_arrval($_GET,'cbreceipt'));
				if ($user_id) {
					if (!$that->IsTempUser($user_id)) {
						header('Location:' . $that->GetRegistrationURL($_POST['wpm_id'], true, $dummy) . '&registered=1');
						exit;
					}
				}
				if (!trim(wlm_arrval($_GET,'cname')))
					$_GET['cname'] = 'Firstname Lastname';
				$name = explode(' ', $_GET['cname']);
				$_POST['lastname'] = array_pop($name);
				$_POST['firstname'] = implode(' ', $name);
				$_POST['action'] = 'wpm_register';

				$_POST['username'] = $_GET['cemail'];
				$_POST['email'] = $_GET['cemail'];
				$_POST['password1'] = $_POST['password2'] = $that->PassGen();
				$_POST['sctxnid'] = $_GET['cbreceipt'];

				/*
				 * send upsells as additional levels
				 */
				$receipt = empty($_GET['cupsellreceipt']) ? $_GET['cbreceipt'] : $_GET['cupsellreceipt'];
				$transient_name = 'cb_upsells_' . $receipt;
				$registered_levels = get_transient($transient_name);
				if (!empty($registered_levels))
					$_POST['additional_levels'] = $registered_levels;

				$that->ShoppingCartRegistration();
			}else {

				$post_vars = $this->extract_cb_postvars($_POST);
				error_log('status ' . serialize($this->ipn_verified($key, $post_vars)));

				$ipn_verified = $this->ipn_verified($key, $post_vars);

				// $ipn_verified was false? Try version 6.0
				if(!$ipn_verified) {

					// get JSON from raw body...
					$message = json_decode(file_get_contents('php://input'));

					$ipn_verified = $this->verify_ipn_6($key, $message);
					$ipn_version_6 = true;
				}

				if ($ipn_verified) {

					// Is this necessary??
					if ($this->is_v2($post_vars)) {
						$_POST['lastname'] = $post_vars['ccustlastname'];
						$_POST['firstname'] = $post_vars['ccustfirstname'];
					} elseif($ipn_verified->version == 6) {
						$_POST['lastname'] = $ipn_verified->customer->billing->firstName;
						$_POST['firstname'] = $ipn_verified->customer->billing->lastName;
						$post_vars['ccustemail'] = $ipn_verified->customer->billing->email;
						$post_vars['ctransreceipt'] = $ipn_verified->receipt;
						$post_vars['ctransaction'] = $ipn_verified->transactionType;
						$post_vars['cproditem'] = $ipn_verified->lineItems[0]->itemNo;
						$post_vars['cupsellreceipt'] = $ipn_verified->upsell->upsellOriginalReceipt;
					} else {
						if (!trim($post_vars['ccustname'])) {
							$post_vars['ccustname'] = 'Firstname Lastname';
						}
						$name = explode(' ', $_REQUEST['ccustname']);
						$_POST['lastname'] = array_pop($name);
						$_POST['firstname'] = implode(' ', $name);
					}
					$_POST['action'] = 'wpm_register';

					// the passed sku...
					$passedparams = parse_str($post_vars['cvendthru']);

					// check if product ID (cproditem) is in cbproducts
					// if so, return the level for that product ID
					// if not, use $passedparams['sku']
					$postedid = $post_vars['cproditem'];
					$wpm_levels = (array) $that->GetOption('wpm_levels');
					foreach ((array) $wpm_levels AS $sku => $level){
						if (in_array($postedid, (array) $cbproducts[$sku])) {
							$level_id = $sku;
							break;
						}
					}

					if (empty($level_id)){
						$xposts = $that->GetPayPerPosts(array('post_title', 'post_type'));
						foreach ($xposts AS $post_type => $posts){ 
							foreach ($posts AS $post){
								if (in_array($postedid, (array) $cbproducts['payperpost-'.$post->ID])) {
									$level_id = 'payperpost-'.$post->ID;
									break;
									}
									}
						}
					}
					$_POST['wpm_id'] = empty($level_id) ? $passedparams['sku'] : $level_id;

					$_POST['username'] = $post_vars['ccustemail'];
					$_POST['email'] = $post_vars['ccustemail'];
					$_POST['sctxnid'] = $post_vars['ctransreceipt'];

					switch ($post_vars['ctransaction']) {
						case 'SALE':
						case 'TEST_SALE':
							// we only save upsell info on sale in INS
							$receipt = empty($post_vars['cupsellreceipt']) ? $post_vars['ctransreceipt'] : $post_vars['cupsellreceipt'];
							$transient_name = 'cb_upsells_' . $receipt;
							$registered_levels = get_transient($transient_name);
							if (empty($registered_levels))
								$registered_levels = array();

							$registered_levels[] = $_POST['wpm_id'] . "\t" . $post_vars['ctransreceipt'];
							set_transient($transient_name, $registered_levels, $cbupsells_ttl);
							break;
						case 'BILL': // we do nothing because registration is handled by the regular thank you url...
						case 'TEST_BILL':
						case 'UNCANCEL-REBILL':

							// Add hook for Shoppingcart reactivate so that other plugins can hook into this
							$_POST['cbitem']  = $post_vars['cproditem'];
							$_POST['sc_type'] = 'cb';
							do_action('wlm_shoppingcart_rebill', $_POST);

							$that->ShoppingCartReactivate();

							break;

						case 'RFND':
						case 'CGBK':
						case 'INSF':
						case 'CANCEL-REBILL':
						case 'CANCEL-TEST-REBILL':
							$that->ShoppingCartDeactivate();
							break;
					}
				}
			}
		}

		function extract_cb_postvars($post) {
			$fields_v4 = array(
				'cprodtitle', 'ctranspaymentmethod', 'cfuturepayments', 'ccustzip', 'ccustshippingzip', 'ccustemail', 'crebillfrequency', 'crebillstatus', 'ctransaffiliate', 'cupsellreceipt', 'corderamount', 'ccustcounty', 'ccurrency', 'ccustfirstname', 'crebillamnt', 'ctransaction', 'ccuststate', 'corderlanguage', 'caccountamount', 'ctid', 'ccustshippingcountry', 'cnextpaymentdate', 'cverify', 'cprocessedpayments', 'cnoticeversion', 'cprodtype', 'ccustcc', 'ccustshippingstate', 'ctransreceipt', 'ccustfullname', 'cbf', 'cbfid', 'cshippingamount', 'cvendthru', 'ctransvendor', 'ctransrole', 'ctaxamount', 'cbfpath', 'ccustaddr2', 'ccustaddr1', 'ccustcity', 'ccustlastname', 'ctranstime', 'cproditem'
			);
			$fields_v2 = array(
				'ccustfullname', 'ccustfirstname', 'ccustlastname', 'ccuststate', 'ccustzip', 'ccustcc', 'ccustaddr1', 'ccustaddr2', 'ccustcity', 'ccustcounty', 'ccustshippingstate', 'ccustshippingzip', 'ccustshippingcountry', 'ccustemail', 'cproditem', 'cprodtitle', 'cprodtype', 'ctransaction', 'ctransaffiliate', 'caccountamount', 'corderamount', 'ctranspaymentmethod', 'ccurrency', 'ctranspublisher', 'ctransreceipt', 'ctransrole', 'cupsellreceipt', 'crebillamnt', 'cprocessedpayments', 'cfuturepayments', 'cnextpaymentdate', 'crebillstatus', 'ctid', 'cvendthru', 'cverify', 'ctranstime'
			);
			sort($fields_v2);
			sort($fields_v4);

			$fields_v1 = array(
				'ccustname', 'ccustemail', 'ccustcc', 'ccuststate', 'ctransreceipt', 'cproditem', 'ctransaction', 'ctransaffiliate', 'ctranspublisher', 'cprodtype', 'cprodtitle', 'ctranspaymentmethod', 'ctransamount', 'caffitid', 'cvendthru', 'cverify'
			);
			//support physical medias
			if (strpos($cprodtype, "PHYSICAL") !== false) {
				array_push($fields_v1, 'ccustaddr1', 'ccustaddrd', 'ccustcity', 'ccustcounty', 'ccustzip');
			}
			$version_fields = array(
				1 => $fields_v1,
				2 => $fields_v2,
				4 => $fields_v4,
			);
			$f = $this->get_fields_for_version($version_fields, $post);


			$cb_req = array();
			foreach ($f as $k) {
				#ignore missing fields
				if (isset($post[$k])) {
					$cb_req[$k] = $post[$k];
				}
			}
			return $cb_req;
		}

		function ipn_verified($secret_key, $post_vars) {
			$unescape = get_magic_quotes_gpc();
			$pop = "";
			$ipn_fields = array();
			foreach ($post_vars as $key => $value) {
				if ($key == "cverify") {
					continue;
				}
				$ipn_fields[] = $key;
			}
			//no more field sorting, this assumes that fields
			//are already properly sorted
			foreach ($ipn_fields as $field) {
				if ($unescape) {
					$pop = $pop . stripslashes($post_vars[$field]) . "|";
				} else {
					$pop = $pop . $post_vars[$field] . "|";
				}
			}
			$pop = $pop . $secret_key;
			$calced_verify = sha1(mb_convert_encoding($pop, "UTF-8"));
			$calced_verify = strtoupper(substr($calced_verify, 0, 8));
			return $calced_verify == $post_vars["cverify"];
		}

		function verify_ipn_6($key, $message) {

			// Pull out the encrypted notification and the initialization vector for
			// AES/CBC/PKCS5Padding decryption
			$encrypted = $message->{'notification'};

			$iv = $message->{'iv'};

			// decrypt the body...
			$decrypted = trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_128,
			substr(sha1($key), 0, 32),
			base64_decode($encrypted),
			MCRYPT_MODE_CBC,
			base64_decode($iv)), "\0..\32");

			$order = json_decode($decrypted);

			return $order;
		}

		function ty_valid_req($secret_key, $get_vars) {
			$rcpt = $get_vars['cbreceipt'];
			$time = $get_vars['time'];
			$item = $get_vars['item'];
			$cbpop = $get_vars['cbpop'];

			$xxpop = sha1("$secret_key|$rcpt|$time|$item");
			$xxpop = strtoupper(substr($xxpop, 0, 8));
			return $cbpop == $xxpop;
		}

		function is_v2($post_vars = array()) {
			return isset($post_vars['ccustfullname']);
		}

		function get_fields_for_version($fields, $post) {
			if ($post['cnoticeversion'] == '4.0') {
				return $fields[4];
			}

			if (isset($post['ccustfullname'])) {
				return $fields[2];
			}
			return $fields[1];
		}
	}

}