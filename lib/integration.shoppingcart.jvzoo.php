<?php

/*
 * Clickbank Shopping Cart Integration Functions
 * Original Author : Mike Lopez
 * Version: $Id: integration.shoppingcart.jvzoo.php 2389 2014-10-22 15:59:18Z mike $
 */

//$__classname__ = 'WLM_INTEGRATION_JVZOO';
//$__optionname__ = 'jvzoothankyou';
//$__methodname__ = 'JVZoo';

if (!class_exists('WLM_INTEGRATION_JVZOO')) {

	class WLM_INTEGRATION_JVZOO {

		function JVZoo($that) {
			$key = $that->GetOption('jvzoosecret');

			$jvzooproducts = array();

			$jvzooupsells_ttl = $that->GetOption('jvzooupsells_ttl');
			if (empty($jvzooupsells_ttl))
				$jvzooupsells_ttl = 60 * 60;

			if ($this->ty_valid_req($key, $_GET)) {

				// check if product ID (item) is in cbproducts
				// if so, return the level for that product ID
				// if not, use $_GET['sku']
				$postedid = $_GET['item'];
				$wpm_levels = (array) $that->GetOption('wpm_levels');
				foreach ((array) $wpm_levels AS $sku => $level){
					if (in_array($postedid, $jvzooproducts[$sku])) {
						$level_id = $sku;
						break;
					}
				}					
					
				if (empty($level_id)){
					$xposts = $that->GetPayPerPosts(array('post_title', 'post_type'));
					foreach ($xposts AS $post_type => $posts){ 
						foreach ($posts AS $post){
							if (in_array($postedid, $jvzooproducts['payperpost-'.$post->ID])) {
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
				$transient_name = 'jvzoo_upsells_' . $receipt;
				$registered_levels = get_transient($transient_name);
				if (!empty($registered_levels))
					$_POST['additional_levels'] = $registered_levels;

				$that->ShoppingCartRegistration();
			}else {
				$post_vars = $this->extract_cb_postvars($_POST);
				error_log('status ' . serialize($this->ipn_verified($key, $post_vars)));
				if ($this->ipn_verified($key, $post_vars)) {

					// Is this necessary??
					if ($this->is_v2($post_vars)) {
						$_POST['lastname'] = $post_vars['ccustlastname'];
						$_POST['firstname'] = $post_vars['ccustfirstname'];
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
						if (in_array($postedid, $jvzooproducts[$sku])) {
							$level_id = $sku;
							break;
						}
					}

					if (empty($level_id)){
						$xposts = $that->GetPayPerPosts(array('post_title', 'post_type'));
						foreach ($xposts AS $post_type => $posts){ 
							foreach ($posts AS $post){
								if (in_array($postedid, $jvzooproducts['payperpost-'.$post->ID])) {
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
							$transient_name = 'jvzoo_upsells_' . $receipt;
							$registered_levels = get_transient($transient_name);
							if (empty($registered_levels))
								$registered_levels = array();

							$registered_levels[] = $_POST['wpm_id'] . "\t" . $post_vars['ctransreceipt'];
							set_transient($transient_name, $registered_levels, $jvzooupsells_ttl);
							break;
						case 'BILL': // we do nothing because registration is handled by the regular thank you url...
						case 'UNCANCEL-REBILL':
							$txn = $_POST['sctxnid'];
							$items = explode("-", $txn);
							$_POST['sctxnid'] = $items[0]; 

							// Add hook for Shoppingcart reactivate so that other plugins can hook into this
							$_POST['sc_type'] = 'jvzoo';
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


			$jvzoo_req = array();
			foreach ($f as $k) {
				#ignore missing fields
				if (isset($post[$k])) {
					$jvzoo_req[$k] = $post[$k];
				}
			}
			return $jvzoo_req;
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

		function ty_valid_req($secret_key, $get_vars) {
			$rcpt = $get_vars['cbreceipt'];
			$time = $get_vars['time'];
			$item = $get_vars['item'];
			$jvzoopop = $get_vars['cbpop'];

			$xxpop = sha1("$secret_key|$rcpt|$time|$item");
			$xxpop = strtoupper(substr($xxpop, 0, 8));
			return $jvzoopop == $xxpop;
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
