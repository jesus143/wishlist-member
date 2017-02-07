<?php

/*
 * Paypal Shopping Cart Integration Functions
 * Original Author : Mike Lopez
 * Version: $Id: integration.shoppingcart.paypal.php 3138 2016-10-18 11:15:00Z mike $
 */

//$__classname__ = 'WLM_INTEGRATION_PAYPAL';
//$__optionname__ = 'ppthankyou';
//$__methodname__ = 'Paypal';

if (!class_exists('WLM_INTEGRATION_PAYPAL')) {

	class WLM_INTEGRATION_PAYPAL {

		var $wlmversion;

		function __construct() {
			global $wlm_paypalps_init;
			$pid = wlm_arrval($_GET, 'pid');
			if($pid) {
				$redirect = $wlm_paypalps_init->paypal_link($pid);
				if($redirect) {
					wp_redirect( $redirect );
					exit;
				}

				$line1 = __('It appears the product you are trying to purchase no longer exists.', 'wishlist-member');
				$line2 = __('Please contact the site owner for more information.', 'wishlist-member');
				$line3 = sprintf(__('Go back to %s', 'wishlist-member'), get_bloginfo('name'));
				$link = get_bloginfo('url');

				wp_die(sprintf('<div style="text-align:center">%s<br><br>%s<br><br><a href="%s" style="font-size:small;color:gray">%s</a></div>', $line1, $line2, $link, $line3), __('Invalid PayPal Link', 'wishlist-member'));
			}
		}

		function Paypal($that) {
			$ppsandbox = (int) $that->GetOption('ppsandbox');
			if ($ppsandbox == 1) {
				$urls = 'ssl://www.sandbox.paypal.com';
				$url = 'www.sandbox.paypal.com';
				$pphosts = array(
					1=> "Host: www.sandbox.paypal.com\r\n",
					2=> "Host: http://www.sandbox.paypal.com\r\n"
				);				
			} else {
				$urls = 'ssl://www.paypal.com';
				$url = 'www.paypal.com';
				$pphosts = array(
					1=> "Host: www.paypal.com\r\n",
					2=> "Host: http://www.paypal.com\r\n"
				);				
			}

			$this->wlmversion = $that->Plugin_Latest_Version();
			/*
			 * Paypal Payment Data Transfer (PDT)
			 * This section of the code takes care of Paypal's PDT
			 * by processing the data passed to WishList Member and
			 * verifying it. If the data is valid, then we create
			 * a temporary account and redirect the user to the
			 * registration form to let him complete his registration.
			 */
			/* if $_GET['tx'] is passed then we do with PDT. */
			if (!empty($_GET['tx'])) { /* start of PDT */
				/*
				 * Verify that the data received is from Paypal.
				 * Verification code is based on Paypal's sample code
				 */

				//try without header HOST
				$req = 'cmd=_notify-synch';
				$token = $that->GetOption($ppsandbox ? 'ppsandboxtoken' : 'pptoken');
				$req.='&tx=' . $_GET['tx'] . '&at=' . $token;
			

				$lines = $this->verify("PDT",$urls, $url,$req,$pphosts);

				if($lines){
					do_action('wlmem_paypal_pdt_response');
				}
				/*
				 * at this point, we're sure that the data we received
				 * is indeed from Paypal so we continue with the registration
				 */
				$data = array();
				for ($i = 1; $i < count($lines); $i++) {
					list($key, $val) = explode("=", $lines[$i], 2);
					$data[urldecode($key)] = urldecode($val);
				}
				$_POST['lastname'] = $data['last_name'];
				$_POST['firstname'] = $data['first_name'];
				$_POST['wpm_id'] = $data['item_number'];
				$_POST['username'] = $data['payer_email'];
				$_POST['email'] = $data['payer_email'];
				$_POST['password1'] = $_POST['password2'] = $that->PassGen();

				/*
				 * Use the txn_id's id's by default but if we
				 * have subscr_id, then we use it instead
				 */
				$_POST['sctxnid'] = $data['parent_txn_id'] ? $data['parent_txn_id'] : $data['txn_id'];
				$_POST['sctxnid'] = $data['subscr_id'] ? $data['subscr_id'] : $_POST['sctxnid'];

				/*
				 * Assumes that this the first purchase, so we'll
				 * Only look at completion of payment
				 * No pending handling yet
				 */
				if (isset($data['payment_status']) && trim($data['payment_status']) == 'Completed') {
					/*
					 * create temporary account
					 */
					$that->ShoppingCartRegistration();
				} elseif (isset($data['payment_status']) && trim($data['payment_status']) == 'Pending') {
					/*
					 * create temporary account
					 */
					$that->ShoppingCartRegistration(null, null, 'Paypal Pending');
				}
				return;
			} /* end of PDT */

			/*
			 * Paypal Instant Payment Notification (IPN)
			 * 
			 * This section of the code processes IPN data
			 * sent by Paypal and handles the deactivation / reactivation
			 * of a user's Membership Level based on the transaction ID
			 * that was passed.
			 *
			 * IPN always send data via POST 
			 */
			if (!empty($_POST['payment_status']) || !empty($_POST['txn_type'])) { /* start of IPN */
				/*
				 * first, we validate the data that we received to
				 * confirm that it's valid IPN information from Paypal
				 */
				$req = 'cmd=_notify-validate';
				foreach ((array) $_POST AS $key => $value)
					$req.= ( '&' . $key . '=' . urlencode(stripslashes($value)));


				$verified = $this->verify("IPN",$urls, $url,$req,$pphosts);	
				if ($verified) {
					/*
					 * If Paypal returns VERIFIED then we proceed
					 */
					// hook for Blair Williams Affiliate Program.
					do_action('wlmem_paypal_ipn_response');

					$_POST['lastname'] = $_POST['last_name'];
					$_POST['firstname'] = $_POST['first_name'];
					$_POST['action'] = 'wpm_register';

					$_POST['wpm_id'] = $_POST['item_number'];
					$_POST['username'] = $_POST['payer_email'];
					$_POST['email'] = $_POST['payer_email'];
					$_POST['password1'] = $_POST['password2'] = $that->PassGen();

					$address = array();
					$address['company'] = $_POST['payer_business_name'] ? $_POST['payer_business_name'] : $_POST['address_name'];
					$address['address1'] = $_POST['address_street'];
					$address['address2'] = '';
					$address['city'] = $_POST['address_city'];
					$address['state'] = $_POST['address_state'];
					$address['zip'] = $_POST['address_zip'];
					$address['country'] = $_POST['address_country'];

					/*
					 * do we have custom variable and is it an IP address?
					 * if so, save it as transient for 8 hours
					 */
					if (isset($_POST['custom'])) {
						$that->SetTransientHash($_POST['custom'], $_POST['payer_email']);
					}

					/*
					 * determine the correct transaction ID to use
					 */
					if (wlm_arrval($_POST,'subscr_id')) {
						$_POST['sctxnid'] = $_POST['subscr_id'];
					} else {
						$_POST['sctxnid'] = $_POST['parent_txn_id'] ? $_POST['parent_txn_id'] : $_POST['txn_id'];
					}

					$status = $_POST['payment_status'] ? $_POST['payment_status'] : $_POST['txn_type'];

					switch ($status) {
						case 'subscr_signup':
							$_POST['wpm_useraddress'] = $address;
							// we have a subscription sign-up so we register it...
							$that->ShoppingCartRegistration(null, false);
							$that->CartIntegrationTerminate();
							break;
						case 'Completed':
							if (isset($_POST['echeck_time_processed'])) {
								// we remove the status "N:Paypal Pending" if paypal sends notification that echeck payment has been processed
								$that->ShoppingCartReactivate(1);
							} elseif (wlm_arrval($_POST,'txn_type') == 'subscr_payment') {
								// we reactivate the account for any subscr_payment notice
								$that->ShoppingCartReactivate();
								// Add hook for Shoppingcart reactivate so that other plugins can hook into this
								$_POST['sc_type'] = 'paypal';
								do_action('wlm_shoppingcart_rebill', $_POST);
							} else {
								$_POST['wpm_useraddress'] = $address;
								// if txn_type is not subscr_payment then it's a one-time payment so we register the user

								// Check if the email is already registered and that the txn_id for the level is already active 
								// avoid additional incomplete registrations with the same email in case the IPN is delayed
								if (wlm_arrval($_POST,'txn_type') == 'web_accept') {
									$user_data = wlmapi_get_member_by('user_email', $_POST['email']);
									$user_id = $user_data['members']['member'][0]['id'];

									$member_data = wlmapi_get_level_member_data($_POST['wpm_id'], $user_id);
									$txn_id = $member_data['member']['level']->TxnID;
									if($_POST['sctxnid'] != $txn_id) {
										$that->ShoppingCartRegistration(null, false);
										$that->CartIntegrationTerminate();
									}
								} else {
									$that->ShoppingCartRegistration(null, false);
									$that->CartIntegrationTerminate();
								}
							}
							break;
						case 'Canceled-Reversal':
							$that->ShoppingCartReactivate();
							break;
						case 'Processed':
							$that->ShoppingCartReactivate('Confirm');
							break;
						case 'Expired':
						case 'Failed':
						case 'Refunded':
						case 'Reversed':
						case 'subscr_failed':
						case 'recurring_payment_suspended_due_to_max_failed_payment': //Recurring payment suspended -- exceeded maximum number of failed payments allowed
							$that->ShoppingCartDeactivate();
							break;
						case 'subscr_eot':
							//get eot settings
							$eotcancel = $that->GetOption('eotcancel');
							if($eotcancel) $eotcancel = maybe_unserialize($eotcancel);
							else $eotcancel = array();

							if(isset($eotcancel[wlm_arrval($_POST,'wpm_id')]) && $eotcancel[wlm_arrval($_POST,'wpm_id')] == 1){
								$that->ShoppingCartDeactivate();
							}
							break;
						case 'subscr_cancel':
							//lets cancel for trial subscriptions
							$subscrcancel = $that->GetOption('subscrcancel');
							if($subscrcancel) $subscrcancel = maybe_unserialize($subscrcancel);
							else $subscrcancel = false;

							if (isset($_POST['amount1']) && wlm_arrval($_POST,'amount1') == "0.00") {
								$that->ShoppingCartDeactivate();
							} elseif (isset($_POST['mc_amount1']) && wlm_arrval($_POST,'mc_amount1') == "0.00") {
								$that->ShoppingCartDeactivate();
							}elseif($subscrcancel === false){ //default settings
								$that->ShoppingCartDeactivate();
							}elseif(isset($subscrcancel[wlm_arrval($_POST,'wpm_id')]) && $subscrcancel[wlm_arrval($_POST,'wpm_id')] == 1){
								$that->ShoppingCartDeactivate();
							}
							break;
						case 'recurring_payment':
						case 'recurring_payment_skipped':
						case 'recurring_payment_expired':
						case 'recurring_payment_failed':
						case 'recurring_payment_suspended':

							// Set transaction ID
							$_POST['sctxnid'] = $_POST['recurring_payment_id'];
							
							switch ($_POST['profile_status']) {

								case 'Active':
									$that->ShoppingCartReactivate();
									break;
								case 'Suspended':
								case 'Cancelled':
									$that->ShoppingCartDeactivate();
									break;
								default:
									//ignore
									break;
							}
							break;
					}
				}
				//we wont need to execute the code below for IPN notifications
				$that->CartIntegrationTerminate();
			} /* end of IPN */

			// 0 Trial offer goes here because it does not return tx id after payment. Also this is used for Delayed IPN
			/*
			 * Still here????
			 * Let's check for a transient email address based
			 * on the current user's IP address
			 * 
			 * we try 15 times with 1 second interval per try (15 seconds)
			 * 
			 */
			$tries = 15;
			while ($tries--) {
				$email = $that->GetTransientHash();
				if ($email) {
					$that->DeleteTransientHash();
					$url = $that->GetContinueRegistrationURL($email);
					header("Location:" . $url);
					exit;
				}
				usleep(1000000);
			}

			/*
			 * Wow!!! Still nothing from Paypal?
			 * Final fallback: Let's ask the client for his Paypal email
			 * and check if there is an incomplete registration for that
			 */

			if (isset($GLOBALS['wlm_cookies']->wlm_transient_hash) || true) {
				$fallback_url = $that->GetFallbackRegistrationURL();
				$that->DeleteTransientHash();
				header("Location:" . $fallback_url);
				exit;
			}
		}

		function verify($type,$urls, $url,$req,$pphosts){

			$res = $this->process_verification($type,$urls,$url,$req);
			$pphost = (array)$pphost;

			foreach($pphosts as $pphost){
				if (!$res) {
					//lets us HTTP/1.1
					$res = $this->process_verification($type,$urls, $url,$req,$pphost,"HTTP/1.1");
					if (!$res) {
						//lets us HTTP/1.0
						$res = $this->process_verification($type,$urls, $url,$req,$pphost,"HTTP/1.0");
					}
				}

				if($res){
					break;
				}

			}

			return $res;
		}

		function process_verification($type,$urls, $url,$req,$header_host="",$http=""){

			if($header_host == ""){

				$header = "POST /cgi-bin/webscr HTTP/1.0\r\n";
				$header .="Content-Type: application/x-www-form-urlencoded\r\n";
				$header .="Content-Length: " . strlen($req) . "\r\n";				

			}elseif($http != "" && $header_host != ""){

				$header = "POST /cgi-bin/webscr {$http}\r\n";
				$header .=$header_host;
				$header .="Content-Type: application/x-www-form-urlencoded\r\n";
				$header .="Content-Length: " . strlen($req) . "\r\n";
				$header .="User-Agent: WishList Member/".$this->wlmversion . "\r\n";
				$header .="Connection: close\r\n";			

			}

			if($type == "PDT"){
				return $this->verify_pdt($urls, $url, $header, $req);
			}elseif($type == "IPN"){
				return $this->verify_ipn($urls, $url, $header, $req);
			}
			return false;
		}

		function verify_ipn($urls, $url, $header, $req) {

			// let's try ssl first
			$fp = fsockopen($urls, 443, $errno, $errstr, 30);
			if (!$fp) {
				// now let's try unsecure
				$fp = fsockopen($url, 80, $errno, $errstr, 30);
				if (!$fp) {
					return false;
				}
			}

			$header = $header . "\r\n" . $req;

			fputs($fp, $header);
			while (!feof($fp)) {
				$res = fgets($fp, 1024);
			}

			fclose($fp);
			if (strcmp($res, "VERIFIED") != 0)
				return false;
			else
				return true;
		}

		function verify_pdt($urls, $url, $header, $req) {

			// let's try ssl first
			$fp = fsockopen($urls, 443, $errno, $errstr, 30);
			if (!$fp) {
				// now let's try unsecure
				$fp = fsockopen($url, 80, $errno, $errstr, 30);
				if (!$fp) {
					return false;
				}
			}

			$header = $header . "\r\n" . $req;

			fputs($fp, $header);
			$res = '';
			$headerdone = false;
			while (!feof($fp)) {
				$line = fgets($fp, 1024);
				if (strcmp($line, "\r\n") == 0) {
					$headerdone = true;
				} elseif ($headerdone) {
					$res.=$line;
				}
			}
			$lines = explode("\n", $res);

			/*
			 * terminate if PDT verification does not say SUCCESS
			 */
			fclose($fp);
			if (strcmp($lines[0], "SUCCESS") != 0) {
				// New change in Paypal Sandbox put the SUCCESS value in the second key array so we will also check in there.
				if (strcmp($lines[1], "SUCCESS") != 0)
					return false;
				else
					return $lines;
			} else {
				return $lines;
			}
		}

	}

}
?>