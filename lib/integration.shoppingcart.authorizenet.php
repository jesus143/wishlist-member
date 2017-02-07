<?php

/*
 * Authorize.net Shopping Cart Integration Functions
 * Original Author : Peter Indiola
 * Version: $Id: integration.shoppingcart.authorizenet.php 1928 2013-12-26 15:31:25Z mike $
 */

//$__classname__ = 'WLM_INTEGRATION_AuthorizeNet';
//$__optionname__ = 'anthankyou';
//$__methodname__ = 'AuthorizeNet';

if (!class_exists('WLM_INTEGRATION_AuthorizeNet')) {

	class WLM_INTEGRATION_AuthorizeNet {

		function AuthorizeNet($that) {

			require_once($that->pluginDir . '/extlib/anet_sdk/AuthorizeNet.php');
			define("AUTHORIZENET_API_LOGIN_ID", $that->GetOption('anloginid'));
			define("AUTHORIZENET_TRANSACTION_KEY", $that->GetOption('antransid'));
			define("AUTHORIZENET_MD5_SETTING", $that->GetOption('anmd5hash'));			
                        $anetsandbox = $that->GetOption('anetsandbox');
                        
			$request = new AuthorizeNetTD;
			if ((int)$anetsandbox != 1) 
			   $request->setSandbox(false);			
			$response = $request->getTransactionDetails(wlm_arrval($_GET,'x_trans_id'));

			// Check if transaction response, transaction_id and authCode if present.
			if (!isset($response->xml->transaction->responseCode) || !isset($response->xml->transaction->authCode) ||
					!isset($response->xml->transaction->transId)) {
				return;
			}

			// Check if transaction code is approved.
			if ($response->xml->transaction->responseCode != 1) {
				return;
			}

			foreach ($response->xml->transaction->lineItems->lineItem as $lineItem) {
				$_POST['wpm_id'] = (string) $lineItem->itemId;
			}

			foreach ($response->xml->transaction->billTo as $billTo) {
				$_POST['lastname'] = (string) $billTo->lastName;
				$_POST['firstname'] = (string) $billTo->firstName;
				$_POST['password1'] = $_POST['password2'] = 'sldkfjsdlkfj';
			}

			foreach ($response->xml->transaction->customer as $customer) {
				$_POST['username'] = (string) $customer->email;
				$_POST['email'] = (string) $customer->email;
			}

			$_POST['action'] = 'wpm_register';
			$_POST['sctxnid'] = (string) $response->xml->transaction->transId;

			$amount = (string) $response->xml->transaction->authAmount;
			$transaction_id = (string) $response->xml->transaction->transId;
			$x_md5_hash = (string) $_GET['x_MD5_Hash'];

			$amount = isset($amount) ? $amount : "0.00";

			// Generate hash for checking with authorize.net submitted hash value.
			$generated_hash = strtoupper(md5(AUTHORIZENET_MD5_SETTING . AUTHORIZENET_API_LOGIN_ID . $transaction_id . $amount));

			// Let's verify is authorize.net and generate hash is valid.
			if ($x_md5_hash === $generated_hash) {
				$that->ShoppingCartRegistration();
			} else {
				$that->ShoppingCartDeactivate();
			}
		}

	}

}
?>
