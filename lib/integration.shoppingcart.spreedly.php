<?php

/*
 * Pin Payments Shopping Cart Integration Functions (formerly known as Spreedly)
 * Original Author : Fel Jun Palawan
 * Version: $Id: integration.shoppingcart.spreedly.php 1960 2014-01-24 14:54:43Z mike $
 */

if (!class_exists('Spreedly')) {
	global $WishListMemberInstance;
	include_once($WishListMemberInstance->pluginDir . '/extlib/class.spreedly.inc');
}

if (!class_exists('WLMSpreedly')) { // this is not the class being called by WLM, refer to the class below.

	class WLMSpreedly {

		public $spreedlytoken = "";
		public $spreedlyname = "";
		public $athenticated = false;
		public $plans = array();

		function __construct($that) {
			$this->spreedlytoken = $that->GetOption('spreedlytoken');
			$this->spreedlyname = $that->GetOption('spreedlyname');

			if ($this->spreedlytoken && $this->spreedlyname) {
				Spreedly::configure($this->spreedlyname, $this->spreedlytoken);
				$plans = SpreedlySubscriptionPlan::get_all();
				if (isset($plans['ErrorCode'])) {
					$this->athenticated = false;
				} else {
					$this->athenticated = true;
					$dum = array();
					foreach ($plans as $id => $data) {
						$dum[$data->id] = $data;
					}
					$this->plans = $dum;
				}
			}
		}

		function get_subscriber($id) {
			if (!$this->athenticated)
				return null;
			return SpreedlySubscriber::find($id);
		}

		function add_subscriber($id, $screen_name, $email) {
			if (!$this->athenticated)
				return null;
			return SpreedlySubscriber::create($id, $email, $screen_name);
		}

		function get_plan_sku($plan_id) {
			$plans = $this->plans;
			if ($this->is_valid_plan($plan_id)) {
				return $plans[$plan_id]->feature_level;
			} else {
				return '';
			}
		}

		function is_valid_plan($plan_id) {
			return array_key_exists($plan_id, $this->plans);
		}

	}

}

if (!class_exists('WLM_INTEGRATION_SPREEDLY')) {

	class WLM_INTEGRATION_SPREEDLY {

		private $that = false;

		function Spreedly($that) {
			$this->that = $that;
			$wlmspreedly = new WLMSpreedly($that);
			$wpm_levels = $that->GetOption('wpm_levels');
			$current_user = wp_get_current_user();
			$spreedly_user = "";

			/* LETS END EVERYTHING IF WE CANT CONNECT TO SPREEDLY */
			if (!$wlmspreedly->athenticated) { //
				die("Oppss!! Something went wrong.Theres an error connecting to Pin Payments, please try again.");
			}

			/* REDIRECT AFTER REGISTRATION */
			if (isset($_GET['reg_id'])) {
				$plan_id = $_GET['reg_id'];
				$sku = $wlmspreedly->get_plan_sku($plan_id);
				/* Get/Create Spreedly user */
				if (array_key_exists($sku, $wpm_levels)) { //make sure that its a correct membership level id
					if ($current_user->ID != 0) { //make sure that the user is logged in
						$wl_user = new WishListMemberUser($current_user->ID); //get wlm user details
						$wl_user_levels = $wl_user->Levels; //get user levels
						//check if he has a for approval membership level using spreedly
						if (array_key_exists($sku, $wl_user_levels) && $wl_user_levels[$sku]->Pending == "Pin Payments Confirmation") {
							//get user spreedly account
							$spreedly_user = $wlmspreedly->get_subscriber($wl_user_levels[$sku]->TxnID);
							//if no user, lets create
							if (is_null($spreedly_user)) {
								$spreedly_user = $wlmspreedly->add_subscriber($wl_user_levels[$sku]->TxnID, $current_user->user_login, $current_user->user_email);
							}
						}
					}
				}

				/* Now we have our spreedly user account for this member */
				if (!is_null($spreedly_user) && isset($spreedly_user->customer_id)) {

					$name = explode(" ", $current_user->display_name, 2);
					$user_data = array("id" => $spreedly_user->customer_id,
						"email" => $current_user->user_email,
						"first_name" => $name[0],
						"last_name" => $name[1]
					);

					/* Redirect to spreedly payment form */
					header('Location:' . $this->generate_subscription_url($wlmspreedly->spreedlyname, $plan_id, $user_data));
					exit(0);
				}
			}

			/* REDIRECT AFTER MEMBER PAYS FROM SPREEDLY AND CLICK "CONTINUE" LINK */
			if (isset($_GET['sku']) && array_key_exists($_GET['sku'], $wpm_levels)) {//make sure that its a correct membership level id
				if ($current_user->ID != 0) { //make sure that the user is logged in
					//get user membership levels
					$wl_user = new WishListMemberUser($current_user->ID);
					$wl_user_levels = $wl_user->Levels;

					//based on the txnid, get the spreedly user for this member
					$user = $wlmspreedly->get_subscriber($wl_user_levels[wlm_arrval($_GET,'sku')]->TxnID);

					if (!is_null($user)) {

						$txn_detail = array("txnid" => $wl_user_levels[wlm_arrval($_GET,'sku')]->TxnID, "user_id" => $current_user->ID, "level_id" => $_GET['sku']);

						$this->process_membership($txn_detail, $user);

						$afterreg = $this->get_after_reg_url($wpm_levels, $_GET['sku']);
						header('Location:' . $afterreg);
						exit(0);
					}
				}
			}

			/* SPREEDLY NOTIFICATION FOR CHANGES IN USERS AND THERE TRANSACTIONS */
			if (isset($_POST['subscriber_ids'])) {
				$ids = $_POST['subscriber_ids'];
				$ids = explode(",", $ids);

				foreach ($ids as $id) {
					$user = $wlmspreedly->get_subscriber($id);
					$txn_details = $this->get_txn_details($id);
					foreach ($txn_details as $txn_detail) {
						$this->process_membership($txn_detail, $user);
					}
				}
			}
		}

		private function generate_subscription_url($spreedlyname, $spreedlyplan, $user_data) {
			$user_spreedly_id = $user_data['id'];
			$user_spreedly_email = $user_data['email'];
			$user_spreedly_fname = $user_data['first_name'];
			$user_spreedly_lname = $user_data['last_name'];
			return "https://subs.pinpayments.com/{$spreedlyname}/subscribers/{$user_spreedly_id}/subscribe/{$spreedlyplan}?email={$user_spreedly_email}&first_name={$user_spreedly_fname}&last_name={$user_spreedly_lname}";
		}

		private function get_txn_details($txnid) {
			global $wpdb;
			$txn_details = array();
			$query = "SELECT `userlevel_id`,`option_value` FROM `{$this->that->Tables->userlevel_options}` WHERE `option_value`='{$txnid}'";
			$users = $wpdb->get_results($query);
			foreach ((array) $users as $user) {
				$query = "SELECT `user_id`,`level_id` FROM `{$this->that->Tables->userlevels}` WHERE ID={$user->userlevel_id}";
				$userlvl = $wpdb->get_row($query);
				if ($userlvl) {
					$txn_details[] = array("txnid" => $user->option_value, "user_id" => $userlvl->user_id, "level_id" => $userlvl->level_id);
				}
			}
			return $txn_details;
		}

		private function process_membership($txn_detail, $user = null) {
			if (!is_null($user)) {
				if ($user->active) {
					$x = $this->that->LevelForApproval($txn_detail["level_id"], $txn_detail["user_id"]);
					if ($x && $x == "Pin Payments Confirmation") { //if for approval and status is for comfirmation
						$this->that->LevelForApproval($txn_detail["level_id"], $txn_detail["user_id"], false); //approve user
					} else { //if active in spreedly and cancelled in our membership level, lets un-cancel him
						$x = $this->that->LevelCancelled($txn_detail["level_id"], $txn_detail["user_id"]);
						if ($x) {
							$this->that->LevelCancelled($txn_detail["level_id"], $txn_detail["user_id"], false);
						}
					}
				} else {
					if ($user->lifetime_subscription) {
						$this->that->LevelCancelled($txn_detail["level_id"], $txn_detail["user_id"], true);
					} else {
						$this->that->LevelCancelled($txn_detail["level_id"], $txn_detail["user_id"], true, $user->active_until);
					}
				}
			} else { // if the user does not have account with spreedly anymore, lets cancel him from our membership level
				$this->that->LevelCancelled($txn_detail["level_id"], $txn_detail["user_id"], true);
			}
		}

		private function get_after_reg_url($wpm_levels, $sku) {
			$wpm_level = $wpm_levels[$sku];
			// Get after registration page
			if ($wpm_level['afterregredirect'] == '---') {
				// Get default after registration page
				$afterreg = $this->that->GetOption('after_registration_internal');
				$afterreg = $afterreg ? get_permalink($afterreg) : trim($this->that->GetOption('after_registration'));
			}

			// Check if level require email confirmation to show email confirm page after registration.
			if ($wpm_level['requireemailconfirmation']) {
				$afterreg = $this->that->GetOption('membership_forconfirmation_internal');
				$afterreg = $afterreg ? get_permalink($afterreg) : trim($this->that->GetOption('membership_forconfirmation'));
			}

			return $afterreg ? $afterreg : get_bloginfo('url');
		}

	}

}
?>
