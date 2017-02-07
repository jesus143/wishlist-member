<?php
if (!class_exists('WLM_INTEGRATION_AUTHORIZENET_ARB')) {

	class WLM_INTEGRATION_AUTHORIZENET_ARB {

		private $wlm;

		private $thankyou_url;
		private $settings;

		public function __construct() {
			global $WishListMemberInstance;
			$this->wlm      = $WishListMemberInstance;
			$this->subscriptions = $this->wlm->GetOption('anetarbsubscriptions');

			$anetarbthankyou    = $this->wlm->GetOption('anetarbthankyou');
			$wpm_scregister     = get_bloginfo('url') . '/index.php/register/';
			$this->thankyou_url = $wpm_scregister . $anetarbthankyou;


			$settings = $this->wlm->GetOption('anetarbsettings');

			$this->settings = array(
				'acct.api_login_id'      => $settings['api_login_id'],
				'acct.transaction_key'   => $settings['api_transaction_key'],
				'mode'                   => $settings['sandbox_mode'] ? 'sandbox' : null,
				'gateway'                => $settings['sandbox_mode'] ? 'https://test.authorize.net/gateway/transact.dll' : 'https://secure.authorize.net/gateway/transact.dll',
			);

			include $this->wlm->pluginDir .'/extlib/wlm_authorizenet_arb/authnet_aim.php';
			include $this->wlm->pluginDir .'/extlib/wlm_authorizenet_arb/authnet_arb.php';
		}

		public function authorizenet_arb( $that ) {

			$action = strtolower( trim( $_REQUEST['action'] ) );
			$action = $action ? $action : "";

			$valid_actions = array('purchase-direct', 'silent-post', 'sync-arb');

			if ( ! in_array( $action, $valid_actions ) ) {
				echo __( "Invalid Action.", "wishlist-member" );
				die();
			}

			switch ( $action ) {
				case 'purchase-direct':
					$this->purchase_direct();
					break;
				case 'silent-post':
					$this->silent_post();
					break;
				case 'sync-arb':
					$this->syn_arb();
					break;
				default:
					break;
			}
		}

		private function syn_arb(){
			$wlm_aurthorizenet_arb_init = new WLMAuthorizeNetARB();
			$ret = $wlm_aurthorizenet_arb_init->syn_arb();
			$end     = isset( $ret['end'] ) ? $ret['end'] : "-unknown-";
			$message = isset( $ret['message'] ) ? $ret['message'] : "empty";
			$count   = isset( $ret['count'] ) ? $ret['count'] : 0;
			echo "{$end} {$message} ({$count} records)";
			die();
		}
		private function purchase_direct() {

			$subscriptions = $this->subscriptions;

			$id = isset( $_POST['id'] ) ? $_POST['id'] : '';
			if ( ! isset( $subscriptions[ $id ] ) ) {
				echo __( "Invalid Transaction ID. Transaction was not processed.", "wishlist-member" );
				die();
			}

			$nonce = isset( $_POST['nonce'] ) ? $_POST['nonce'] : '';
			if ( ! wp_verify_nonce( $nonce, "purchase-direct-{$id}" ) ) {
				echo __( "Permission Denied. Transaction was not processed.", "wishlist-member" );
				die();
			}

			$subscription = $subscriptions[ $id ];
			$recurring = isset( $subscription["recurring"] ) && $subscription["recurring"]  ? true : false;
			$sctxnid = "";
			$response = "Unknown error occured.";
			try {

				$login   = $this->settings['acct.api_login_id'];
				$key     = $this->settings['acct.transaction_key'];
				$sandbox = $this->settings['mode'] == 'sandbox' ? true : false;

				$first_name = isset( $_POST["first_name"] ) ? trim( $_POST['first_name'] ) : "";
				$last_name  = isset( $_POST["last_name"] ) ? trim( $_POST['last_name'] ) : "";
				$email      = isset( $_POST["email"] ) ? trim( $_POST['email'] ) : "";

				// Instanciate our AIM class
				$aim = new AuthnetAIM( $login, $key, $sandbox );

				if ( $recurring ) { //if recurring, lets creat subscription

					//lets validate the card first by sending a 0.01 charge
					$validation_data = $this->prepare_validation( $_POST );
					$aim->do_apicall( $validation_data ); //process the payment, also throws exception errors if needed

					//if charge went through, card is valid
					//it wont go this far if the card is invalid
					if ( $aim->isApproved() ) {

						//lets VOID the validation transaction
						$transid = $aim->getTransactionID();
						$void_data = $this->prepare_void( $transid );
						$aim->do_apicall( $void_data );
						//wether the transaction is voided or not, lets continue with subscription
						//unvoided validation transactions will EXPIRE in 30 days
						//so no worries becuase it wont charge

						// Instanciate our ARB class
						$sub = $this->prepare_subscription( $id, $subscription, $_POST );
						$arb = new AuthnetARB( $login, $key, $sandbox );
						$arb->do_apicall("ARBCreateSubscriptionRequest", array( "subscription"=> $sub ) ); //process the subscription, also throws exception errors if needed
						// If successful let's get the subscription ID
						if ( $arb->isSuccessful() ) {
							$sctxnid = $arb->getSubscriberID();
							$sctxnid = "arb-{$sctxnid}";
						} else {
							$response = $arb->getResponse();
						}
					}
				} else { //for non recurring
					$pay_details = $this->prepare_payment( $id, $subscription, $_POST );
					$aim->do_apicall( $pay_details ); //process the payment, also throws exception errors if needed
					// If successful let's get the transaction ID
					if ( $aim->isApproved() ) {
						$sctxnid = $aim->getTransactionID();
					} else {
						$response = $arb->getResponseText();
					}
				}

				if ( ! $sctxnid ) { //if not transaction id
					$this->fail( array( 'msg' 	=> $response, 'sku'	=> $id ) );
				}

			} catch (Exception $e) {
				$this->fail( array( 'msg' 	=> $e->getMessage(), 'sku'	=> $id ) );
			}

			$_POST['lastname']  = $last_name;
			$_POST['firstname'] = $first_name;
			$_POST['action']    = 'wpm_register';
			$_POST['wpm_id']    = $subscription['sku'];
			$_POST['username']  = $email;
			$_POST['email']     = $email;
			$_POST['sctxnid']   = $sctxnid;
			$_POST['password1'] = $_POST['password2'] = $this->wlm->PassGen();

			$this->wlm->ShoppingCartRegistration();
		}

		private function silent_post() {
			$this->wlm->SyncMembership();
			$sctxnid = false;
			if ( isset( $_POST['x_subscription_id'] ) ) { //for recurring
				$sctxnid = $_POST['x_subscription_id'];
				$sctxnid = "arb-{$sctxnid}";
			} else {
				$sctxnid = isset( $_POST['x_trans_id'] ) ? $_POST['x_trans_id'] : false; //for one time purchase
			}

			if ( $sctxnid ) {
				// Get the response code. 1 is success, 2 is decline, 3 is error
				$response_code = (int) $_POST['x_response_code'];
				// Get the reason code. 8 is expired card.
				$reason_code = (int) $_POST['x_response_reason_code'];
				// Get the type of transaction
				$x_type = isset( $_POST['x_type'] ) ? strtolower( $_POST['x_type'] ) : false;

				$_POST['sctxnid'] = $sctxnid;

				switch ( $response_code ) {
					case 1:
						switch ( $x_type ) {
							case 'credit': // for refund transactions
							case 'void': //when one time payment transaction is marked as void
								$this->wlm->ShoppingCartDeactivate();
							default: //probably its "auth_capture" which is sent after payment
								break;
						}
						break;
					case 2:
					case 3:
					case 8:
						$this->wlm->ShoppingCartDeactivate();
						break;
					default:
						break;
				}
			}
		}

		private function prepare_subscription( $id, $subscription, $post_data ) {

			$first_name 	= isset( $post_data["first_name"] ) ? trim( $post_data['first_name'] ) : "";
			$last_name  	= isset( $post_data["last_name"] ) ? trim( $post_data['last_name'] ) : "";
			$email      	= isset( $post_data["email"] ) ? trim( $post_data['email'] ) : "";

			$address    = isset( $post_data["address"] ) ? preg_replace('/[^ \w]/', '', $post_data["address"]) : "";
			$city      	= isset( $post_data["city"] ) ? preg_replace('/[^ \w]/', '', $post_data["city"])  : "";
			$state      = isset( $post_data["state"] ) ? preg_replace('/[^ \w]/', '', $post_data["state"])  : "";
			$zip      	= isset( $post_data["zip"] ) ? preg_replace('/[^ \w]/', '', $post_data["zip"])  : "";
			$country    = isset( $post_data["country"] ) ? preg_replace('/[^ \w]/', '', $post_data["country"])  : "";

			$cc_number      = isset( $post_data["cc_number"] ) ? trim( $post_data['cc_number'] ) : "";
			$cc_expmonth    = isset( $post_data["cc_expmonth"] ) ? trim( $post_data['cc_expmonth'] ) : "";
			$cc_expyear     = isset( $post_data["cc_expyear"] ) ? trim( $post_data['cc_expyear'] ) : "";
			$cc_expiration  = $cc_expmonth .$cc_expyear;
			$cc_cvv         = isset( $post_data["cc_cvc"] ) ? trim( $post_data['cc_cvc'] ) : "";

			$recurinng_amount  = isset( $subscription["recur_amount"] ) ? ( float ) $subscription['recur_amount'] : 0;
			$subscription_name = isset( $subscription['name'] ) ? $subscription['name'] : $id; //lets use id if no name is set

			// Format the text for interval unit, supported values are (days, weeks, months, years)
			switch ( $subscription['recur_billing_period'] ) {
				case 'Day':
					$interval_units = 'days';
					$interval_unit = 'day';
					break;
				case 'Month':
					$interval_units = 'months';
					$interval_unit = 'month';
					break;
			}
			$frequency     = (int) $subscription['recur_billing_frequency'];
			$cycle         = $subscription['recur_billing_cycle'] ? (int) $subscription['recur_billing_cycle'] : 0;
			$trial_cycle   = $subscription['trial_billing_cycle'] ? (int) $subscription['trial_billing_cycle'] : 0;
			$trial_amount  = $subscription['trial_amount'] ? $subscription['trial_amount'] : 0;
			$trial_amount  = $trial_cycle ? (float) $trial_amount : 0.00;

			$cycle         = $cycle > 0 ? ($trial_cycle + $cycle) : 9999;
			$cycle         = $cycle > 9999 ? 9999 : $cycle;


			$sub = array(
				"name" 			  => $subscription_name,
				"paymentSchedule" => array(
					"interval" => array(
						"length" => (int) $subscription['recur_billing_frequency'],
						"unit"   => $interval_units,
					),
					"startDate"=> date( "Y-m-d", strtotime( "+1 day" ) ),
					"totalOccurrences" => $cycle, //unli 999, or cycle
					"trialOccurrences" => $trial_cycle, //number of cycle for trial, must included in totalOccurences

				),
				"amount" => $recurinng_amount,
				"trialAmount" => $trial_amount,
				"payment" => array(
					"creditCard" => array(
						"cardNumber"     => $cc_number,
						"expirationDate" => $cc_expiration,
						"cardCode"		 => $cc_cvv,
					),
				),
				"customer" => array(
					"email" => $email,
				),
				"billTo" => array(
					"firstName" => $first_name,
					"lastName"  => $last_name,
				),
			);

			if ( ! empty( $address ) ) 	$sub['billTo']["address"] 	= $address;
			if ( ! empty( $city ) ) 	$sub['billTo']["city"] 		= $city;
			if ( ! empty( $state ) ) 	$sub['billTo']["state"] 	= $state;
			if ( ! empty( $zip ) ) 		$sub['billTo']["zip"] 		= $zip;
			if ( ! empty( $country ) ) 	$sub['billTo']["country"] 	= $country;

			return $sub;
		}

		private function prepare_payment( $id, $subscription, $post_data ) {

			$first_name = isset( $post_data["first_name"] ) ? trim( $post_data['first_name'] ) : "";
			$last_name  = isset( $post_data["last_name"] ) ? trim( $post_data['last_name'] ) : "";
			$email      = isset( $post_data["email"] ) ? trim( $post_data['email'] ) : "";

			$address    = isset( $post_data["address"] ) ? preg_replace('/[^ \w]/', '', $post_data["address"]) : "";
			$city      	= isset( $post_data["city"] ) ? preg_replace('/[^ \w]/', '', $post_data["city"])  : "";
			$state      = isset( $post_data["state"] ) ? preg_replace('/[^ \w]/', '', $post_data["state"])  : "";
			$zip      	= isset( $post_data["zip"] ) ? preg_replace('/[^ \w]/', '', $post_data["zip"])  : "";
			$country    = isset( $post_data["country"] ) ? preg_replace('/[^ \w]/', '', $post_data["country"])  : "";

			$cc_number      = isset( $post_data["cc_number"] ) ? trim( $post_data['cc_number'] ) : "";
			$cc_expmonth    = isset( $post_data["cc_expmonth"] ) ? trim( $post_data['cc_expmonth'] ) : "";
			$cc_expyear     = isset( $post_data["cc_expyear"] ) ? trim( $post_data['cc_expyear'] ) : "";
			$cc_expiration  = $cc_expmonth .$cc_expyear;
			$cc_cvv         = isset( $post_data["cc_cvc"] ) ? trim( $post_data['cc_cvc'] ) : "";

			$recurring = isset( $subscription["recurring"] ) && $subscription["recurring"]  ? true : false;
			$product_name = isset( $subscription['name'] ) ? $subscription['name'] : $id; //lets use id if no name is set
			if ( $recurring ) { //for recurring
				$init_amount      = isset( $subscription["init_amount"] ) ? ( float ) $subscription['init_amount'] : "";
			} else { //non recurring
				$init_amount      = isset( $subscription["amount"] ) ? ( float ) $subscription['amount'] : "";
			}

			$invoice = null;
			$tax = null;
			$array_data = array(
				"x_delim_data"     => "TRUE",
				"x_delim_char"     => "|",
				"x_relay_response" => "FALSE",
				"x_url"            => "FALSE",
				"x_version"        => "3.1",
				"x_method"         => "CC",
				"x_type"           => "AUTH_CAPTURE",
				"x_card_num"  	   => $cc_number ,
				"x_exp_date"  	   => $cc_expiration ,
				"x_amount"    	   => $init_amount ,
				"x_po_num"    	   => $invoice ,
				"x_tax"       	   => $tax ,
				"x_card_code" 	   => $cc_cvv ,
				"x_description"    => $product_name,
				"x_first_name"	   => $first_name ,
				"x_last_name" 	   => $last_name ,
				"x_email"     	   => $email ,
			);
			if ( ! empty( $address ) ) 	$array_data["x_address"] 	= $address;
			if ( ! empty( $city ) ) 	$array_data["x_city"] 		= $city;
			if ( ! empty( $state ) ) 	$array_data["x_state"] 		= $state;
			if ( ! empty( $zip ) ) 		$array_data["x_zip"] 		= $zip;
			if ( ! empty( $country ) ) 	$array_data["x_country"] 	= $country;

			return $array_data;
		}

		private function prepare_validation( $post_data ) {
			$first_name = isset( $post_data["first_name"] ) ? trim( $post_data['first_name'] ) : "";
			$last_name  = isset( $post_data["last_name"] ) ? trim( $post_data['last_name'] ) : "";
			$email      = isset( $post_data["email"] ) ? trim( $post_data['email'] ) : "";

			$address    = isset( $post_data["address"] ) ? preg_replace('/[^ \w]/', '', $post_data["address"]) : "";
			$city      	= isset( $post_data["city"] ) ? preg_replace('/[^ \w]/', '', $post_data["city"])  : "";
			$state      = isset( $post_data["state"] ) ? preg_replace('/[^ \w]/', '', $post_data["state"])  : "";
			$zip      	= isset( $post_data["zip"] ) ? preg_replace('/[^ \w]/', '', $post_data["zip"])  : "";
			$country    = isset( $post_data["country"] ) ? preg_replace('/[^ \w]/', '', $post_data["country"])  : "";

			$cc_number      = isset( $post_data["cc_number"] ) ? trim( $post_data['cc_number'] ) : "";
			$cc_expmonth    = isset( $post_data["cc_expmonth"] ) ? trim( $post_data['cc_expmonth'] ) : "";
			$cc_expyear     = isset( $post_data["cc_expyear"] ) ? trim( $post_data['cc_expyear'] ) : "";
			$cc_expiration  = $cc_expmonth .$cc_expyear;
			$cc_cvv         = isset( $post_data["cc_cvc"] ) ? trim( $post_data['cc_cvc'] ) : "";

			$product_name = "WLM ARB Integration. Card Validation Transaction.";
			$amount = 0.01;

			$invoice = null;
			$tax = null;
			$array_data = array(
				"x_delim_data"     => "TRUE",
				"x_delim_char"     => "|",
				"x_relay_response" => "FALSE",
				"x_url"            => "FALSE",
				"x_version"        => "3.1",
				"x_method"         => "CC",
				"x_type"           => "AUTH_ONLY",
				"x_card_num"  	   => $cc_number ,
				"x_exp_date"  	   => $cc_expiration ,
				"x_amount"    	   => $amount ,
				"x_card_code" 	   => $cc_cvv ,
				"x_description"    => $product_name,
				"x_first_name"	   => $first_name ,
				"x_last_name" 	   => $last_name ,
				"x_email"     	   => $email ,
			);
			if ( ! empty( $address ) ) 	$array_data["x_address"] 	= $address;
			if ( ! empty( $city ) ) 	$array_data["x_city"] 		= $city;
			if ( ! empty( $state ) ) 	$array_data["x_state"] 		= $state;
			if ( ! empty( $zip ) ) 		$array_data["x_zip"] 		= $zip;
			if ( ! empty( $country ) ) 	$array_data["x_country"] 	= $country;

			return $array_data;
		}

		private function prepare_void( $transid ) {
			$array_data = array(
				"x_delim_data"     => "TRUE",
				"x_delim_char"     => "|",
				"x_relay_response" => "FALSE",
				"x_url"            => "FALSE",
				"x_version"        => "3.1",
				"x_method"         => "CC",
				"x_type"           => "VOID",
				"x_trans_id"	   => $transid,
			);
			return $array_data;
		}

		public function fail($data) {
			$uri = $_REQUEST['redirect_to'];
			if (stripos($uri, '?') !== false) {
				$uri .= "&status=fail&reason=" . preg_replace('/\s+/', '+', $data['msg']);
			} else {
				$uri .= "?&status=fail&reason=" . preg_replace('/\s+/', '+', $data['msg']);
			}

			$uri .= "#regform-" . $data['sku'];
			wp_redirect($uri);
			die();
		}
	}
}
