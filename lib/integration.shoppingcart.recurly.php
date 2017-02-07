<?php

/*
 * Generic Shopping Cart Integration Functions
 * Original Author : Erwin Atuli
 */

//$__classname__ = 'WLM_INTEGRATION_RECURLY';
//$__optionname__ = 'recurlythankyou';
//$__methodname__ = 'recurly';
if (!class_exists('WLM_INTEGRATION_RECURLY')) {

	class WLM_INTEGRATION_RECURLY {

		function recurly($that) {
			require_once($that->pluginDir . '/extlib/WP_RecurlyClient.php');
			$client = new WP_RecurlyClient($that->GetOption('recurlyapikey'));
			if (wlm_arrval($_GET,'act') == 'reg') {

				$plan_code = $_GET['plan_code'];
				$account_code = $_GET['account_code'];
				$account = $client->get_account($account_code);
				$subscriptions = $client->get_subscriptions($account_code);

				if (empty($account) || empty($subscriptions)) {
					//maybe redirect to cancel url?
					return;
				}

				//check that this subscription is actually in the users subscriptions
				$current_subscription = null;
				$found = false;
				foreach ($subscriptions as $s) {
					if ($s['plan_code'] == $plan_code) {
						$found = true;
						$current_subscription = $s;
					}
				}

				if (!$found) {
					//cheatin huh?
					return;
				}

				if ($current_subscription['state'] != 'active') {
					return;
				}

				$plan = $client->get_plan($plan_code);
				$_POST['lastname'] = $account['last_name'];
				$_POST['firstname'] = $account['first_name'];
				$_POST['action'] = 'wpm_register';
				$_POST['wpm_id'] = $plan['accounting_code'];
				$_POST['username'] = $account['email'];
				$_POST['email'] = $account['email'];
				$_POST['password1'] = $_POST['password2'] = 'sldkfjsdlkfj';
				$_POST['sctxnid'] = $current_subscription['uuid'];
				$that->ShoppingCartRegistration();
			} else {

				$listen = array(
					'canceled_subscription_notification',
					'expired_subscription_notification',
					'renewed_subscription_notification',
					'updated_subscription_notification'
				);

				$notif = file_get_contents("php://input");
				$type = $client->get_notification_type($notif);


				if (in_array($type, $listen)) {
					$subscription = $client->get_subscription_from_notif($notif);
					$_POST['sctxnid'] = $subscription['uuid'];
					if ($subscription['state'] == 'active') {
						$that->ShoppingCartReactivate();
					} else {
						$that->ShoppingCartDeactivate();
					}
				} else {
					//nothing to do
				}
			}
		}

	}

}
?>