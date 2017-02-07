<?php

require_once dirname(__FILE__) . '/../extlib/eway/EwayWebServiceClient.php';
require_once dirname(__FILE__) . '/../extlib/eway/EwayRecurWebserviceClient.php';

if (!class_exists('WLM_INTEGRATION_EWAY')) {

	class WLM_INTEGRATION_EWAY {
		var $wlm;
		var $eway_ws;

		protected $sandbox = true;
		public function __construct() {
			add_action('admin_notices', array($this, 'notices'));

			global $WishListMemberInstance;
			$settings = $WishListMemberInstance->GetOption('ewaysettings');
			$this->eway_ws = new EwayRecurWebserviceClient($settings['eway_customer_id'], $settings['eway_username'], $settings['eway_password'], $settings['eway_sandbox']);
		}
		public function eway_process($wlm) {
			$this->wlm = $wlm;
			$action = trim(strtolower($_REQUEST['regform_action']));
			$valid_actions = array('charge', 'sync', 'update_payment', 'cancel', 'invoices', 'invoice','migrate');
			// if (!in_array($action, $valid_actions)) {
			// 	echo __("Permission Denied", "wishlist-member");
			// 	die();
			// }
			// if (($action != 'sync' && $action != 'migrate') && !wp_verify_nonce($_REQUEST['nonce'], "eway-do-$action")) {
			// 	echo __("Permission Denied", "wishlist-member");
			// 	die();
			// }
			switch ($action) {
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
				default:
					# code...
					break;
			}
		}
		public function charge($data = array()) {
			$settings = $this->wlm->GetOption('ewaysettings');

			try {
				$last_name = $data['last_name'];
				$first_name = $data['first_name'];

				if($data['charge_type'] == 'new') {
					if (empty($last_name) || empty($first_name) || empty($data['email'])) {
						throw new Exception("All fields are required");
					}
				}

				if(empty($data['cc_number']) || empty($data['cc_expmonth']) || empty($data['cc_expyear'])) {
					throw new Exception("All fields are required");
				}

				$_POST['level'] = $data['sku'];
				$_POST['lastname'] = $last_name;
				$_POST['firstname'] = $first_name;
				$_POST['action'] = 'wpm_register';
				$_POST['wpm_id'] = $data['sku'];
				$_POST['username'] = $data['email'];
				$_POST['email'] = $data['email'];
				$_POST['password1'] = $_POST['password2'] = $this->wlm->PassGen();
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
		public function cancel($data = array()) {
			//not implemented
		}

		public function update_payment($data = array()) {
			//not implemented
		}

		public static function sync($data = array()) {
			global $wpdb;
			global $WishListMemberInstance;

			$settings = $WishListMemberInstance->GetOption('ewaysettings');
			$eway_ws  = new EwayRecurWebserviceClient($settings['eway_customer_id'], $settings['eway_username'], $settings['eway_password'], $settings['eway_sandbox']);

			$results = $wpdb->get_results("SELECT * FROM {$WishListMemberInstance->Tables->userlevel_options} WHERE option_value like 'EWAYRB-%-%-%'");
			foreach($results as $row) {
				list($tmp, $rebill_id, $invoice_ref, $cust_id) = explode('-', $row->option_value);
				if(empty($rebill_id) || empty($cust_id) || empty($invoice_ref)) {
					continue;
				}

				$params = array(
					'RebillCustomerID' => $cust_id,
					'RebillID'         => $rebill_id,
				);

				$res        = $eway_ws->call("QueryTransactions", $params);
				if(empty($res)) {
					continue;
				}
				$rebills    = $res['QueryTransactionsResult']['rebillTransaction'];
				$last_trans = $rebills[0];

				foreach($rebills as $r) {
					if($r['Status'] == 'Future') {
						break;
					}
					$last_trans = $r;
				}
				$_POST['sctxnid'] = $row->option_value;
				if($last_trans['Status'] == 'Failed') {
					$WishListMemberInstance->ShoppingCartDeactivate();
				} else {
					$WishListMemberInstance->ShoppingCartReactivate();
				}
			}
		}
		public function add_to_level($user_id, $level_id, $txn_id) {
			$user = new WishListMemberUser($user_id);
			$levels = $user->Levels;

			$remaining_levels = array($level_id);
			foreach($levels as $i => $l) {
				$remaining_levels[] = $i;
			}

			$this->wlm->SetMembershipLevels($user_id, $remaining_levels);
			if($this->wlm->IsPPPLevel($level_id)) {
				list($tmp, $content_id) = explode('-', $level_id);
				$this->wlm->AddUserPostTransactionID($user_id, $content_id, $txn_id);
				$this->wlm->AddUserPostTimestamp($user_id, $content_id);
			} else {
				$this->wlm->SetMembershipLevelTxnID($user_id,  $level_id, $txn_id);
			}
		}
		public function charge_existing($data) {
			try {

				global $current_user;
				$cust_id = $this->wlm->Get_UserMeta($current_user->ID, 'eway_cust_id');
				//$cust_id = null;
				if(empty($cust_id)) {
					$cust_id = $this->create_customer($data);
					$this->wlm->Update_UserMeta($current_user->ID, 'eway_cust_id', $cust_id);
				}

				$txn_id  = $this->charge_customer($cust_id, $data, $data['sku']);

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
		private function create_customer($cust) {
			//create the cust
			$data['customerFirstName']  = $cust['first_name'];
			$data['customerLastName']   = $cust['last_name'];
			$data['customerEmail']      = $cust['email'];

			$res = $this->eway_ws->call('CreateRebillCustomer', $data);
			if(strtolower($res['CreateRebillCustomerResult']['Result']) !== 'success') {
				throw new Exception("Could not create customer");
			}
			return $res['CreateRebillCustomerResult']['RebillCustomerID'];
		}
		private function charge_customer($cust_id, $cc_data, $sku) {
			//now create the rebill
			//create an invoiceRef in order to track this txn
			//we need a txn id when querying the 24 hour txn api
			//note: eway gateway doesn't like non-numeric invoice ref
			//echo "<pre>";
			//date('d/m/Y',


			$wpm_levels 	= $this->wlm->GetOption('wpm_levels');

			$settings       = $this->wlm->GetOption('ewaysettings');
			$level_settings = $settings['connections'][$sku];
			$invoice_ref    = microtime(true);
			$invoice_ref    = preg_replace('/\D/','', $invoice_ref);
			$end_date       = strtotime($level_settings['rebill_end_date']);
			$start_date     = time() + 3600*24;
			$init_date      = $start_date;


			//computation table for the correct start-date
			$next_interval_types = array(
				1 => time() + 3600*24*2, //start next day instead of the same day as initdate
				2 => time() + 3600*24*7,
				3 => strtotime("+1 month", time()),
				4 => strtotime("+1 year", time()),
			);

			$rebill_init_amount  = (int) $level_settings['rebill_init_amount']*100;
			$rebill_recur_amount = (int) $level_settings['rebill_recur_amount']*100;
			$start_date          = $next_interval_types[$level_settings['rebill_interval_type']];

			if($level_settings['subscription'] != 1) {
				//we will create a subscription with a single rebill event
				$level_settings['rebill_interval']      = 1;
				$level_settings['rebill_interval_type'] = 3;//1 month subscription
				//we will use the init amount instead of the the recur amount
				$rebill_recur_amount                    = $rebill_init_amount;
				$rebill_init_amount                     = 0;
				//set the start date to next day and end date to the following day
				//We set interval_type to month beforehand so this will ensure
				//there will only be a single rebill
				$start_date                             = $init_date;
				$end_date                               = strtotime("+1 day", $start_date);
			}



			$data                       = array();
			$data['RebillCustomerID']   = $cust_id;
			$data['RebillInvRef']       = $invoice_ref;
			$data['RebillInvDes']       = $wpm_levels[$sku]['name'] . ' Subscription';
			$data['RebillCCName']       = $cc_data['first_name'] . ' ' . $cc_data['last_name'];
			$data['RebillCCNumber']     = $cc_data['cc_number'];
			$data['RebillCCExpMonth']   = $cc_data['cc_expmonth'];
			$data['RebillCCExpYear']    = $cc_data['cc_expyear'];
			$data['RebillInitAmt']      = $rebill_init_amount;
			$data['RebillInitDate']     = date('d/m/Y', $init_date);
			$data['RebillRecurAmt']     = $rebill_recur_amount;
			$data['RebillStartDate']    = date('d/m/Y', $start_date);
			$data['RebillInterval']     = (int) $level_settings['rebill_interval'];
			$data['RebillIntervalType'] = (int) $level_settings['rebill_interval_type'];
			$data['RebillEndDate']      = date('d/m/Y', $end_date);
			$res                        = $this->eway_ws->call('CreateRebillEvent', $data);

			$txn_id = sprintf('EWAYRB-%s-%s-%s', $res['CreateRebillEventResult']['RebillID'], $invoice_ref, $cust_id);
			if(strtolower($res['CreateRebillEventResult']['Result']) !== 'success') {
				throw new Exception("Payment processing failed");
			}
			return $txn_id;
		}
		public function charge_new($data) {
			try {
				//create the customer
				$cust_id = $this->create_customer($data);
				$txn_id  = $this->charge_customer($cust_id, $data, $data['sku']);
				$_POST['sctxnid'] = $txn_id;
				$this->wlm->ShoppingCartRegistration(true, false);

				$user = get_user_by('login', 'temp_' . md5($data['email']));
				$this->wlm->Update_UserMeta($user->ID, 'eway_cust_id', $cust_id);
				$url = $this->wlm->GetContinueRegistrationURL($data['email']);
				wp_redirect($url);
				die();
			} catch (Exception $e) {
				if(!empty($cust_id)) {
					//something went wrong while charging
					//delete the stripe customer so we don't get cluttered
					//with unlinked customers
					$resp = $this->eway_ws->call('DeleteRebillCustomer', array(
						'RebillCustomerID' => $cust_id
					));
				}

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
			wp_redirect($uri);
			die();
		}
	}



}
