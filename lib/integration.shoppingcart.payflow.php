<?php


if(extension_loaded('curl')) {
	global $WishListMemberInstance;
	include_once($WishListMemberInstance->pluginDir . '/extlib/paypal/ppayflow.php');
	include_once($WishListMemberInstance->pluginDir . '/extlib/paypal/payflow.php');
	// PPAutoloader::register();
}

if (!class_exists('WLM_INTEGRATION_PAYPALPAYFLOW')) {
	class WLM_INTEGRATION_PAYPALPAYFLOW extends PPayflow {
		private $settings;
		private $wlm;

		private $thankyou_url;
		private $pp_settings;
		public function __construct() {

			global $WishListMemberInstance;
			$this->wlm      = $WishListMemberInstance;
			$this->products = $this->wlm->GetOption('paypalpayflowproducts');

			$settings           = $this->wlm->GetOption('paypalpayflowthankyou_url');
			$paypalpayflowthankyou  = $this->wlm->GetOption('payflowthankyou');
			$wpm_scregister     = get_bloginfo('url') . '/index.php/register/';
			$this->thankyou_url = $wpm_scregister . $paypalpayflowthankyou;


			$pp_settings = $this->wlm->GetOption('payflowsettings');

			$index = 'live';
			$sandbox = false;
			if($pp_settings['sandbox_mode']) {
				$index = 'sandbox';
				$sandbox = true;
			}

			$payflow_username = $pp_settings[$index]['api_username'];
			$payflow_password = $pp_settings[$index]['api_password'];
			$payflow_vendor = $pp_settings[$index]['merchant_name'];
			$payflow_partner = 'paypal';
			$payflow_signature = '';

			// Create PayPal object.
			$this->PayPalConfig = array(
				'Sandbox' => $sandbox, 
				'APIUsername' => $payflow_username, 
				'APIPassword' => $payflow_password, 
				'APISignature' => $payflow_signature,
				'APIVendor' => $payflow_vendor, 
				'APIPartner' => $payflow_partner, 
				'Verbosity' => 'HIGH'		// Detail level for API response.  Values are:  LOW, MEDIUM, HIGH
			  );

		}
			
		public function paypalpayflow($that) {
			$action = strtolower(trim($_GET['action']));

			switch ($action) {
				case 'purchase-direct':
					$this->purchase_direct($_GET['id']);
					break;
				default:
					# code...
					break;
			}
		}

		public function purchase_recurring($product) {

			$datenow = date('mdY', time() + 86400);

			$PayPal = new PayFlow($this->PayPalConfig);

			$cc_number = str_replace(' ', '', trim($_POST['cc_number']));

			// Prepare request arrays
			$PayPalRequestData = array(
				'tender'=>'C', 				// Required.  The method of payment.  Values are: A = ACH, C = Credit Card, D = Pinless Debit, K = Telecheck, P = PayPal
				'trxtype'=>'R', 				// Required.  Indicates the type of transaction to perform.  Values are:  A = Authorization, B = Balance Inquiry, C = Credit, D = Delayed Capture, F = Voice Authorization, I = Inquiry, L = Data Upload, N = Duplicate Transaction, S = Sale, V = Void
				'ACTION' => 'A',
				'PROFILENAME' => 'RegularSubscription',
				
				// Recurring payment Info
				'amt'=> $product['recur_amount'],				
				'START' => $datenow,
				'TERM' => $product['recur_billing_frequency'],
				'PAYPERIOD' => strtoupper($product['recur_billing_period']),
				'CURRENCY' => $product['currency'],

				// User info
				'FIRSTNAME'=> $_POST['first_name'],
				'LASTNAME'=> $_POST['last_name'],
				'EMAIL' => $_POST['email'], //This is the buyer's/customer's email
				'CITY'=> $_POST['city_name'],
				'STATE'=> $_POST['state'],
				'ZIP'=> $_POST['zip_code'],

				// Credit Card Info
				'acct'=> $cc_number,  // Required for credit card transaction.  Credit card or purchase card number.
				'expdate'=> $_POST['cc_expmonth'] . $_POST['cc_expyear'], 			// Required for credit card transaction.  Expiration date of the credit card.  Format:  MMYY
				'cvv2'=> $_POST['cc_cvc'], 

				'comment1'=>'Payment for '.$product['name'], 	// Merchant-defined value for reporting and auditing purposes.  128 char max
			);

			try {
				// Pass data into class for processing with PayPal and load the response array into $paypal_result
				$paypal_result = $PayPal->ProcessTransaction($PayPalRequestData);
			} catch (Exception $e) {
				$this->fail(array(
					'msg' 	=> $e->getMessage(),
					'sku'	=> $_POST['sku']
				));
			}

			if($paypal_result['RESULT'] > 0) {
				return array(
					'status' =>  'failed',
					'errmsg' => $paypal_result['RESPMSG']
				);
				
			} else {
				return array(
					'status' =>  'active',
					'id' => $paypal_result['RPREF'] .'-'. $paypal_result['PROFILEID']
				);
			}
		}
		public function purchase_one_time($product) {

			$PayPal = new PayFlow($this->PayPalConfig);
			$cc_number = str_replace(' ', '', trim($_POST['cc_number']));

			// Prepare request arrays
			$PayPalRequestData = array(
				'tender'=>'C', 				// Required.  The method of payment.  Values are: A = ACH, C = Credit Card, D = Pinless Debit, K = Telecheck, P = PayPal
				'trxtype'=>'S', 				// Required.  Indicates the type of transaction to perform.  Values are:  A = Authorization, B = Balance Inquiry, C = Credit, D = Delayed Capture, F = Voice Authorization, I = Inquiry, L = Data Upload, N = Duplicate Transaction, S = Sale, V = Void
				'PROFILENAME' => 'RegularSubscription',
				
				// Recurring payment Info
				'amt'=> $product['amount'],	
				'recurring'=>'',		
				'CURRENCY' => $product['currency'],	

				// User info
				'FIRSTNAME'=> $_POST['first_name'],
				'LASTNAME'=> $_POST['last_name'],
				'EMAIL' => $_POST['email'], //This is the buyer's/customer's email
				'CITY'=> $_POST['city_name'],
				'STATE'=> $_POST['state'],
				'ZIP'=> $_POST['zip_code'],

				// Credit Card Info
				'acct'=> $cc_number,  // Required for credit card transaction.  Credit card or purchase card number.
				'expdate'=> $_POST['cc_expmonth'] . $_POST['cc_expyear'], 			// Required for credit card transaction.  Expiration date of the credit card.  Format:  MMYY
				'cvv2'=> $_POST['cc_cvc'], 
				'CARDTYPE' => $_POST['cc_type'],

				'comment1'=>'Payment for '.$product['name'], 	// Merchant-defined value for reporting and auditing purposes.  128 char max
			);

			try {
				// Pass data into class for processing with PayPal and load the response array into $paypal_result
				$paypal_result = $PayPal->ProcessTransaction($PayPalRequestData);
			} catch (Exception $e) {
				$this->fail(array(
					'msg' 	=> $e->getMessage(),
					'sku'	=> $_POST['sku']
				));
			}

			if($paypal_result['RESULT'] > 0) {
				return array(
					'status' =>  'failed',
					'errmsg' => $paypal_result['RESPMSG']
				);
				
			} else {
				return array(
					'status' =>  'active',
					'id' => $paypal_result['PNREF']
				);
			}

		}
		public function purchase_direct($id) {

			$products = $this->products;
			$product = $products[$id];

			if(empty($product)) {
				return;
			}

			if($product['recurring']) {
				$result = $this->purchase_recurring($product);
			} else {
				$result = $this->purchase_one_time($product);
			}

			try {

				if($result['status'] == 'failed') {
					throw new Exception($result['errmsg']);
				}

			} catch (Exception $e) {
				$this->fail(array(
					'msg' 	=> $e->getMessage(),
					'sku'	=> $_POST['sku']
				));
			}

			$_POST['lastname']  = $_POST['last_name'];
			$_POST['firstname'] = $_POST['first_name'];
			$_POST['action']    = 'wpm_register';
			$_POST['wpm_id']    = $product['sku'];
			$_POST['username']  = $_POST['email'];
			$_POST['email']     = $_POST['email'];
			$_POST['sctxnid']   = $result['id'];
			$_POST['password1'] = $_POST['password2'] = $this->wlm->PassGen();

			$this->wlm->ShoppingCartRegistration();
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
		public function create_description($product) {
			$description = $product['name'] . ' (';
			if($product['trial'] && $product['trial_amount']) {
				$description .= sprintf(__("%0.2f %s for the first %d %s%s then ", 'wishlist-member'), $product['trial_amount'], $product['currency'], $product['trial_recur_billing_frequency'], strtolower($product['trial_recur_billing_period']), $product['trial_recur_billing_frequency'] > 1 ? 's' : '');
			}
			$description .= sprintf(__('%0.2f %s every %d %s%s','wishlist-member'), $product['recur_amount'], $product['currency'], $product['recur_billing_frequency'], strtolower($product['recur_billing_period']), $product['recur_billing_frequency'] > 1 ? 's' : '');
			if($product['recur_billing_cycles'] > 1) {
				$description .= sprintf(__(' for %d installments','wishlist-member'), $product['recur_billing_cycles']);
			}
			$description .= ')';
			return str_replace(' 1 ',' ', $description);
		}
	}
}
