<?php
/*
 * Stripe Shopping Cart Integration Functions
 * Original Author : Erwin Atuli
 * Version: $Id: integration.shoppingcart.stripe.php 3101 2016-09-13 15:39:16Z mike $
 */

//$__classname__ = 'WLM_INTEGRATION_STRIPE';
//$__optionname__ = 'stripethankyou';
//$__methodname__ = 'stripe';

if (!class_exists('WLM_INTEGRATION_STRIPE')) {

	class WLM_INTEGRATION_STRIPE {
		var $wlm;
		public function __construct() {
			$sc = new WLM_Stripe_ShortCodes();
			add_action('admin_notices', array($this, 'notices'));
		}
		public function stripe($wlm) {
			$this->wlm = $wlm;
			$action = trim(strtolower($_REQUEST['stripe_action']));
			$valid_actions = array('charge', 'sync', 'update_payment', 'cancel', 'invoices', 'invoice','migrate', 'check_coupon', 'get_coupon');
			if (!in_array($action, $valid_actions)) {
				echo __("Permission Denied", "wishlist-member");
				die();
			}
			if (($action != 'sync' && $action != 'migrate') && !wp_verify_nonce($_REQUEST['nonce'], "stripe-do-$action")) {
				echo __("Permission Denied", "wishlist-member");
				die();
			}
			switch ($action) {
				case 'migrate':
					$this->migrate();
					break;
				case 'charge':
					# code...
					$this->charge($_POST);
					break;
				case 'sync':
					$this->sync($_POST);
					break;
				case 'update_payment':
					$this->update_payment($_POST);
					break;
				case 'cancel':
					$this->cancel($_POST);
					break;
				case 'invoices':
					$this->invoices($_POST);
					break;
				case 'invoice':
					$this->invoice($_POST);
					break;
				case 'check_coupon':
					$this->check_coupon($_POST);
					break;
				case 'get_coupon':
					$this->get_coupon($_POST);
					break;
				default:
					# code...
					break;
			}
		}
		public function check_coupon($data = array()) {
			$stripeapikey = $this->wlm->GetOption('stripeapikey');
			Stripe::setApiKey($stripeapikey);

			try {
				$coupon = Stripe_Coupon::retrieve($data['coupon']);
				echo json_encode(true);
			} catch (Exception $e) {
				echo json_encode(false);
			}

			die();
		}
		public function get_coupon($data = array()) {
			$stripeapikey = $this->wlm->GetOption('stripeapikey');
			Stripe::setApiKey($stripeapikey);

			try {
				$coupon = Stripe_Coupon::retrieve($data['coupon']);
				$coupons = array (
						'c_type' => $coupon->amount_off ? 'amount_off' : 'percent_off',
						'c_amount' => $coupon->amount_off ? $coupon->amount_off : $coupon->percent_off
						);
				
				echo json_encode($coupons);
			} catch (Exception $e) {
				echo json_encode('');
			}

			die();
		}
		public function migrate() {
			$users = get_users();
			echo sprintf("migrating %s stripe users<br/>\n", count($users));

			$live = $_GET['live'];
			foreach($users as $u) {
				$cust_id = $this->wlm->Get_UserMeta($u->ID, 'custom_stripe_cust_id');

				echo sprintf("migrating user %s with stripe_cust_id: <br/>", $u->ID, $cust_id);
				if($live || !empty($cust_id)) {
					$this->wlm->Update_UserMeta($u->ID, 'stripe_cust_id', $cust_id);
				}
			}
		}
		public function cancel($data = array()) {
			global $current_user;
			if (empty($current_user->ID)) {
				return;
			}
			$stripeapikey = $this->wlm->GetOption('stripeapikey');
			$stripe_cust_id = $this->wlm->Get_UserMeta($current_user->ID, 'stripe_cust_id');
			$stripesettings = $this->wlm->GetOption('stripesettings');
			$connections = $this->wlm->GetOption('stripeconnections');
			Stripe::setApiKey($stripeapikey);

			try {
				//also handle onetime payments
				//$this->wlm->ShoppingCartDeactivate();
				$stripe_level_settings = $connections[wlm_arrval($_POST,'wlm_level')];
				if(!empty($stripe_level_settings['subscription'])) {
					$cust = Stripe_Customer::retrieve($stripe_cust_id);
					$at_period_end = false;
					if (!empty($stripesettings['endsubscriptiontiming']) && $stripesettings['endsubscriptiontiming'] == 'periodend') {
						$at_period_end = true;
					}
					// Check if customer has more than 1 subscription, if so then get the 
					// subscription ID and only cancel the subscription that matches the STRIPE PLAN
					// passed in the $_POST data
					if(count($cust->subscriptions->data) > 1) {
						list($c_id, $plan_id) = explode('-', $data['txn_id']);
						foreach($cust->subscriptions->data as $d) {
							if($d->plan->id == $plan_id) {
								$subscription_id = $d->id;
								$this->cancel_multiple_subscription(array('at_period_end' => $at_period_end), $subscription_id, $cust->instanceUrl());
							}
						}
					} else {
						$cust->cancelSubscription(array('at_period_end' => $at_period_end));
					}
					
				} else {
					$_POST['sctxnid'] = $_REQUEST['txn_id'];
					$this->wlm->ShoppingCartDeactivate();
				}
				$status = 'ok';
			} catch (Exception $e) {
				$status = "fail&err=" . $e->getMessage();
			}
			$uri = $data['redirect_to'];
			if (!empty($stripesettings['cancelredirect'])) {
				$uri = get_permalink($stripesettings['cancelredirect']);
			}
			if (stripos($uri, '?') !== false) {
				$uri .= "&status=$status";
			} else {
				$uri .= "?&status=$status";
			}
			wp_redirect($uri);
			die();
		}


		/**
		* @param array|null $params
		* @param int $subscription_id 
		* @returns Stripe_Subscription The cancelled subscription.
		*/
		public function cancel_multiple_subscription($params=null, $subscription_id, $instance_url) {
			$requestor = new Stripe_ApiRequestor($this->_apiKey);

		    $url = $instance_url . '/subscriptions/'.$subscription_id;
		    list($response, $apiKey) = $requestor->request('delete', $url, $params);
		    return $this->$response;
		}

		public function update_payment($data = array()) {
			$stripeapikey = $this->wlm->GetOption('stripeapikey');
			Stripe::setApiKey($stripeapikey);

			try {
				global $current_user;
				if(empty($current_user->ID)) {
					throw new Exception(__("An error occured while processing the request, Please try again"));
				}
				$cust_id = $this->wlm->Get_UserMeta($current_user->ID, 'stripe_cust_id');
				if(empty($cust_id)) {
					//user is a member but not linked
					//try to create this user in stripe
					$cust_details = array(
						"description" => sprintf("%s %s", $current_user->first_name, $current_user->last_name),
						"email" => $current_user->user_email
					);
					$cust = Stripe_Customer::create($cust_details);
					$cust->card = $data['stripeToken'];
					$status = $cust->save();
					$this->wlm->Update_UserMeta($current_user->ID, 'stripe_cust_id', $cust->id);
				} else {
					$cust = Stripe_Customer::retrieve($cust_id);
					$cust->card = $data['stripeToken'];
					$cust->save();
				}
				$status = 'ok';
			} catch (Exception $e) {
				$err = preg_replace('/\s+/', '+', $e->getMessage());
				$status = "fail&err=" . $err;
			}

			$uri = $data['redirect_to'];
			if (stripos($uri, '?') !== false) {
				$uri .= "&status=$status";
			} else {
				$uri .= "?&status=$status";
			}
			wp_redirect($uri);
			die();
		}

		public function sync($data = array()) {
			$this->wlm->SyncMembership();
			$obj = json_decode(file_get_contents('php://input'));
			$id = null;
			$action = null;
			Stripe::setApiKey($this->wlm->GetOption('stripeapikey'));

			// If $obj is empty then just return, otherwise it will show errors when viewed in browser
			if(empty($obj)) 
				return;

			// Means this came from a test web hook URL
			// Skip sync process to avoid 500 internal server error as 
			// the Sync process will throw errors 
			if($obj->id == 'evt_00000000000000')
				return;


			//Request for the stripe event object to
			//make sure this is a legit stripe notification
			$obj = Stripe_Event::retrieve($obj->id);

			switch ($obj->type) {
				// do not handler creates anymore
				// case 'customer.subscription.created':
				// 	$cust_id = $obj->data->object->customer;
				// 	$plan_id = $obj->data->object->plan->id;
				// 	$id = $cust_id . "-" . $plan_id;
				// 	$action = 'move';
				// 	break;
				case 'customer.subscription.deleted':
					$cust_id = $obj->data->object->customer;
					$plan_id = $obj->data->object->plan->id;
					$id = $cust_id . "-" . $plan_id;
					$action = 'deactivate';
					break;

				case 'customer.subscription.created':
				case 'customer.subscription.updated':
					$cust_id = $obj->data->object->customer;
					$plan_id = $obj->data->object->plan->id;
					$id = $cust_id . "-" . $plan_id;

					switch ($obj->data->object->status) {
						case 'trialing':
						case 'past_due':
							$action = 'reactivate';
							break;
						case 'active':
							$action = 'reactivate';
							if(!empty($obj->data->previous_attributes->plan->id)) {
								//we are changing subscriptions
								$prev_id = sprintf("%s-%s", $cust_id, $obj->data->previous_attributes->plan->id);
								$action = 'move';
							}
							break;
						case 'unpaid':
						case 'cancelled':
						default:
							$action = 'deactivate';
							break;
					}

					//This is an active subscription
					//reactivate? No need
					break;
				case 'invoice.payment_failed':
					//no need, we'll also be able to catch this under charge_failed
					break;

				case 'customer.deleted':
					$cust_id = $obj->data->object->id;
					$user_id = $this->wlm->Get_UserID_From_UserMeta('stripe_cust_id', $cust_id);
					$levels = $this->wlm->GetMembershipLevels($user_id, null, true, null, true);
					if (empty($levels)) {
						return;
					}
					$id = $this->wlm->GetMembershipLevelsTxnID($user_id, $levels[0]);
					$action = 'deactivate';
					break;
				case 'charge.refunded':
					$id = $obj->data->object->id;
					$action = 'deactivate';
					break;
				case 'charge.failed':
					// no need to handle as failed charges are handled
					// in the merchant site
					// $cust_id = $obj->data->object->customer;
					// $customer = Stripe_Customer::retrieve($cust_id);
					// if (empty($customer->plan)) {
					// 	return;
					// }
					// $id = sprintf("%s-%s", $cust_id, $customer->plan->id);
					// $action = 'deactivate';
					//
					break;
			}

			$_POST['sctxnid'] = $id;
			switch ($action) {
				case 'deactivate':
					echo 'info(deact): deactivating subscription: '.$id;
					$_POST['sctxnid'] = $id;
					$this->wlm->ShoppingCartDeactivate();
					break;
				case 'reactivate':
					echo 'info(react): reactivating subscription: '.$id;
					$_POST['sctxnid'] = $id;
					
					$_POST['sc_type'] = 'Stripe';
					do_action('wlm_shoppingcart_rebill', $_POST);

					$this->wlm->ShoppingCartReactivate();

					break;
				case 'move':
					//activate the new one
					$connections = $this->wlm->GetOption('stripeconnections');

					//get the correct level
					$wpm_level      = $this->stripe_plan_id_to_sku($connections, $obj->data->object->plan->id);
					$prev_wpm_level = $this->stripe_plan_id_to_sku($connections, $obj->data->previous_attributes->plan->id);

					//get the correct user
					$user_id = $this->wlm->Get_UserID_From_UserMeta('stripe_cust_id', $cust_id);

					if(!empty($wpm_level) && !empty($user_id)) {
						//remove from previous level
						$current_levels = $this->wlm->GetMembershipLevels($user_id, null, null, true);
						$levels = array_diff($current_levels, array($prev_wpm_level));
						echo 'removing from ' . $prev_wpm_level;
						$this->wlm->SetMembershipLevels($user_id, $levels);

						echo "info(move): moving user:$user_id to new subscription:$wpm_level with tid:$id";
						$this->add_to_level($user_id, $wpm_level, $id);
					}
					break;
			}
			echo "\n";
		}
		public function stripe_plan_id_to_sku($connections, $id) {
			foreach($connections as $c) {
				if($c['plan'] == $id) {
					return $c['sku'];
				}
			}
		}
		public function add_to_level($user_id, $level_id, $txn_id) {
			$user = new WishListMemberUser($user_id);

			 $wpm_levels = $this->wlm->GetOption('wpm_levels');
			 
			 $this->wlm->SetMembershipLevels($user_id, array($level_id), null, null, null, null, null, null, true);
			 
			if (isset( $wpm_levels[$level_id]["registrationdatereset"]) ){	
			    $timestamp = time();            
			    $this->wlm->UserLevelTimestamp($user_id, $level_id, $timestamp );
			}
			
				
			if($this->wlm->IsPPPLevel($level_id)) {
				list($tmp, $content_id) = explode('-', $level_id);
				$this->wlm->AddUserPostTransactionID($user_id, $content_id, $txn_id);
				$this->wlm->AddUserPostTimestamp($user_id, $content_id);
			} else {
				$this->wlm->SetMembershipLevelTxnID($user_id,  $level_id, $txn_id);
			}
		}
		public function charge_existing($data) {
 
			global $WishListMemberInstance;
			$this->wlm = $WishListMemberInstance;

			$connections = $this->wlm->GetOption('stripeconnections');
			$stripesettings = $this->wlm->GetOption('stripesettings');
			$stripe_plan = $connections[$data['wpm_id']]['plan'];
			$settings = $connections[$data['wpm_id']];

			try {

				global $current_user;
				$stripe_cust_id = $this->wlm->Get_UserMeta($current_user->ID, 'stripe_cust_id');


				if(!empty($stripe_cust_id)) {
					$cust = Stripe_Customer::retrieve($stripe_cust_id);

				} else {
					if(empty($data['stripeToken'])) {
						throw new Exception("Could not verify credit card information");
					}
					$cust_details = array(
						"description" => sprintf("%s %s", $data['firstname'], $data['lastname']),
						"email" => $data['email']
					);
					$cust = Stripe_Customer::create($cust_details);
					$cust->card = $data['stripeToken'];
					$status = $cust->save();
					$this->wlm->Update_UserMeta($current_user->ID, 'stripe_cust_id', $cust->id);
				}


				$prorate = true;
				if (!empty($stripesettings['prorate']) && $stripesettings['prorate'] == 'no') {
					$prorate = false;
				}

				if(!empty($stripe_plan)) {
					foreach($cust->subscriptions->data as $sub) {
						if($sub->plan->id == $stripe_plan) {
							throw new Exception(__("Cannot purchase an active plan", "wishlist-member"));
						}
					}
				}

				if($data['subscription']) {
					$params = array("plan" => $stripe_plan, 'prorate' => $prorate, 'coupon' => $data['coupon']);
					if(empty($data['coupon'])) {
						unset($params['coupon']);
					}
					$status = $cust->subscriptions->create($params);
					$txn_id = sprintf('%s-%s', $cust->id, $stripe_plan);

				} else {
					$currency = empty($stripesettings['currency'])? 'USD' : $stripesettings['currency'];

					//override amount and currency if set in shortcode
					$currency = isset( $data['stripe_currency'] ) ? strtoupper( $data['stripe_currency'] ) : $currency;
					$amt      = isset( $data['stripe_amount'] ) ? (float) $data['stripe_amount'] : $settings['amount'];

					$charge = Stripe_Charge::create(array(
						"amount" => number_format($amt*100, 0, '.', ''),
						"currency" => $currency,
						"customer" => $cust->id, // obtained with Stripe.js
						"description" => sprintf("%s Subscription", $settings['membershiplevel']))
					);
					$txn_id = $charge->id;
				}

				

				//add user to level and redirect to the after reg url
				$this->add_to_level($current_user->ID, $data['sku'], $txn_id);
				$url = $this->wlm->GetAfterRegRedirect($data['sku']);
				wp_redirect($url);
				die();
			} catch (Exception $e) {
				$this->fail(array(
					'msg' 	=> $e->getMessage(),
					'sku'	=> $data['wpm_id']
				));
			}
		}
		public function charge_new($data) {
			global $WishListMemberInstance;
			$this->wlm = $WishListMemberInstance;

			$connections = $this->wlm->GetOption('stripeconnections');
			$stripesettings = $this->wlm->GetOption('stripesettings');
			$stripe_plan = $connections[$data['wpm_id']]['plan'];
			$settings = $connections[$data['wpm_id']];

			try {
				$cust_details = array(
					"description" => sprintf("%s %s", $data['firstname'], $data['lastname']),
					"email" => $data['email']
				);

				$cust = Stripe_Customer::create($cust_details);
				$cust->card = $data['stripeToken'];
				$status = $cust->save();

				$prorate = true;
				if (!empty($stripesettings['prorate']) && $stripesettings['prorate'] == 'no') {
					$prorate = false;
				}

				if($data['subscription']) {
					$params = array("plan" => $stripe_plan, 'prorate' => $prorate, 'coupon' => $data['coupon']);
					if(empty($data['coupon'])) {
						unset($params['coupon']);
					}
					$status = $cust->subscriptions->create($params);
					$txn_id = sprintf('%s-%s', $cust->id, $stripe_plan);
				} else {
					$currency = empty($stripesettings['currency'])? 'USD' : $stripesettings['currency'];

					//override amount and currency if set in shortcode
					$currency = isset( $data['stripe_currency'] ) ? strtoupper( $data['stripe_currency'] ) : $currency;
					$amt      = isset( $data['stripe_amount'] ) ? (float) $data['stripe_amount'] : $settings['amount'];

					$charge = Stripe_Charge::create(array(
						"amount" => number_format($amt*100, 0, '.', ''),
						"currency" => $currency,
						"customer" => $cust->id, // obtained with Stripe.js
						"description" => sprintf("%s Subscription", $settings['membershiplevel']))
					);
					$txn_id = $charge->id;
				}
				$_POST['sctxnid'] = $txn_id;
				$this->wlm->ShoppingCartRegistration(true, false);

				$user = get_user_by('login', 'temp_' . md5($data['email']));
				$this->wlm->Update_UserMeta($user->ID, 'stripe_cust_id', $cust->id);
				$url = $this->wlm->GetContinueRegistrationURL($data['email']);
				wp_redirect($url);
				die();
			} catch (Exception $e) {
				//something went wrong while charging
				//delete the stripe customer so we don't get cluttered
				//with unlinked customers
				$cust->delete();
				$this->fail(array(
					'msg' 	=> $e->getMessage(),
					'sku'	=> $data['wpm_id']
				));
			}

		}
		public function fail($data) {
			$uri = $_REQUEST['redirect_to'];
			if (stripos($uri, '?') !== false) {
				$uri .= "&status=fail&reason=" . preg_replace('/\s+/', '+', $data['msg']);
			} else {
				$uri .= "?&status=fail&reason=" . preg_replace('/\s+/', '+', $data['msg']);
			}
			$uri .= "#regform-" . $data['sku'];
			//error_log($uri);
			wp_redirect($uri, 307);

			die();
		}

		public function charge($data = array()) {
			$stripeconnections = $this->wlm->GetOption('stripeconnections');
			$stripeapikey = $this->wlm->GetOption('stripeapikey');
			$settings = $stripeconnections[$data['sku']];
			Stripe::setApiKey($stripeapikey);

			try {
				$btn_hash        = isset( $data['btn_hash'] ) ? $data['btn_hash'] : false;
				$custom_amount   = isset( $data['custom_amount'] ) ? $data['custom_amount'] : false;
				$custom_currency = isset( $data['custom_currency'] ) ? $data['custom_currency'] : false;
				if ( $custom_amount !== false || $custom_currency !== false  ) {
					if ( ! wp_verify_nonce( $btn_hash, "{$stripeapikey}-{$custom_amount}-{$custom_currency}" ) ) {
						throw new Exception("Your request is invalid or expired. Please try again.");
					}
				}

				$last_name = $data['last_name'];
				$first_name = $data['first_name'];
				if($charge_type == 'new') {
					if (empty($last_name) || empty($first_name) || empty($data['email'])) {
						throw new Exception("All fields are required");
					}

					if (empty($data['stripeToken'])) {
						throw new Exception("Payment Processing Failed");
					}
				}

				$_POST['stripe_wlm_level'] = $data['sku'];
				$_POST['lastname'] = $last_name;
				$_POST['firstname'] = $first_name;
				$_POST['action'] = 'wpm_register';
				$_POST['wpm_id'] = $data['sku'];
				$_POST['username'] = $data['email'];
				$_POST['email'] = $data['email'];
				$_POST['password1'] = $_POST['password2'] = $this->wlm->PassGen();

				//lets add custom currency and amount
				if ( $custom_amount ) $_POST['stripe_amount']     = $custom_amount;
				if ( $custom_currency ) $_POST['stripe_currency'] = trim( $custom_currency );

				if($data['charge_type'] == 'new') {
					$this->charge_new($_POST);
				} else {
					$this->charge_existing($_POST);
				}
			} catch (Exception $e) {
				$this->fail(array(
					'msg' 	=> $e->getMessage(),
					'sku'	=> $data['sku']
				));
			}
		}

		//following functions are used to query invoices
		//and returns content ready for display for member profile
		public function invoice($data) {
			global $current_user;
			if (empty($current_user->ID)) {
				return;
			}

			try {
				$stripeapikey = $this->wlm->GetOption('stripeapikey');
				Stripe::setApiKey($stripeapikey);

				$inv = Stripe_Invoice::retrieve($data['txn_id']);
				$cust = Stripe_Customer::retrieve($inv['customer']);
				include $this->get_view_path('invoice_details');
				die();
			} catch (Exception $e) {

			}
		}

		public function invoices($data) {
			global $WishListMemberInstance;
			global $current_user;
			if (empty($current_user->ID)) {
				return;
			}
			$cust_id = $this->wlm->Get_UserMeta($current_user->ID, 'stripe_cust_id');
			try {
				$stripeapikey = $this->wlm->GetOption('stripeapikey');
				$txns = $this->wlm->GetMembershipLevelsTxnIDs($current_user->ID);
				Stripe::setApiKey($stripeapikey);

				$inv = Stripe_Invoice::all(array('count' => 100, 'customer' => $cust_id));
				$invoices = array();
				if (!empty($inv['data'])) {
					$invoices = array_merge($invoices, $inv['data']);
				}
				//try to get manual charges
				//$manual_charges = Stripe_Charge::all(array("count" => 100, 'customer' => $cust_id));
				// $invoices = array_merge($invoices, $inv['data']);
				//var_dump($manual_charges);

				include $this->get_view_path('invoice_list');
				die();
			} catch (Exception $e) {
				echo __("<p>No invoices found for this user</p>", "wishlist-member");
				die();
			}
		}
		public function get_view_path($handle) {
			global $WishListMemberInstance;
			return sprintf($WishListMemberInstance->pluginDir .'/extlib/wlm_stripe/%s.php', $handle);
		}

	}

}
