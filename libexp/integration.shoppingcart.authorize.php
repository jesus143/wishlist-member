<?php

/*
 * Authorize.net Payment Integration
 * Original Author : Mike Lopez / Ramil R. Lacambacal
 * Version: $Id: integration.shoppingcart.authorize.php 2389 2014-10-22 15:59:18Z mike $
 */

/*
  This script process response parameters posted by Authorize.net
 */

$__classname__ = 'WLM_INTEGRATION_AUTHORIZE';
$__optionname__ = 'anthankyou';
$__methodname__ = 'Authorize';

if (!class_exists($__classname__)) {

	class WLM_INTEGRATION_AUTHORIZE {

		function Authorize(&$that) {
			if (!empty($_REQUEST["x_response_reason_code"])) {
				//process response sent be Authorize.net
				//The overall status of the transaction
				//1 = Approved 2 = Declined 3 = Error 4 = Held for Review
				$ResponseCode = $_POST["x_response_code"];
				//A code that represents more details about the result of the transaction
				$ResponseReasonCode = $_POST["x_response_reason_code"];
				$ResponseReasonText = $_POST["x_response_reason_text"];

				//The Address Verification Service (AVS) response code
				$AVS = $_POST["x_avs_code"];

				//The payment gateway assigned identification number for the transaction
				$TransID = $_POST["x_trans_id"];

				//The authorization or approval code
				$AuthCode = $_POST["x_auth_code"];

				//The cardholder authentication verification response code
				$CVV = $_POST["x_cavv_response"];

				//Internal transaction number to match & update w/ local DB record
				//$Invoice			= $_REQUEST["x_invoice_num"];
				$Amount = $_POST["x_amount"];

				$testmode = false;
				//Test if  test transaction only.
				if ($TransID == "0" && $ResponseCode == "1") {
					echo "TEST MODE - transaction not processed. <br />";
					$testmode = true;
				}


//					add_filter('rewrite_rules_array',array(&$this,'RewriteRules'));
//					$GLOBALS['wp_rewrite']->flush_rules();
				$_POST['lastname'] = $_POST['x_last_name'];
				$_POST['firstname'] = $_POST['x_first_name'];
				$_POST['action'] = 'wpm_register';


				$_POST['username'] = $_POST['x_email'];
				$_POST['email'] = $_POST['x_email'];
				$_POST['password1'] = $_POST['password2'] = $that->PassGen();

				$address = array();
				$address['company'] = $_POST['x_company'] ? $_POST['x_company'] : $_POST['x_address'];
				//$address['address1']=$_POST['address_street'];
				//$address['address2']='';
				$address['city'] = $_POST['x_city'];
				$address['state'] = $_POST['x_state'];
				$address['zip'] = $_POST['x_zip'];
				$address['country'] = $_POST['x_country'];


				$_POST['sctxnid'] = $TransID;

				//Check Trasaction status
				switch ($ResponseCode) {
					case "1" :
						$StatusText = "Transaction Approved";
						$_POST['wpm_useraddress'] = $address;
						// we have a subscription sign-up so we register it...
						$that->ShoppingCartRegistration();
						break;
					case "2" :
						$StatusText = "Transaction Declined";
						break;
					default :
						$StatusText = "Error occured processing transaction";
						break;
				}
			}
		}

		function anMergeCode(&$that, $content) {
			//this function generates the form for paypal based on MERGE CODE
			$anurl = $that->GetOption('anurl');
			if ($anurl == '1') {
				//for live mode :
				$url = "https://secure.authorize.net/gateway/transact.dll";
				$testMode = "false";
			} else {
				//for test mode :
				$url = "https://test.authorize.net/gateway/transact.dll";
				$testMode = "true";
			}



			$loginID = $that->GetOption('anloginid');
			$transactionKey = $that->GetOption('antransid');

			$amounts = $that->GetOption('anamt');
			$levels = $that->GetOption('wpm_levels');

			preg_match_all('/\[authorize\.net ([0-9]*)\]/', $content, $matches);

			for ($i = 0; $i < count($matches[0]); $i++) {

				$mergecode = $matches[0][$i];
				$itemcode = $matches[1][$i];
				$amount = $amounts[$itemcode];
				$description = $levels[$itemcode]['name'];

				// generate random sequence number (for fingerprint requirement by Authorize.net)
				$sequence = rand(1, 1000);
				// timestamp is generated for fingerprint requirement by Authorize.net
				$timeStamp = time();
				if (phpversion() >= '5.1.2') {
					$fingerprint = hash_hmac("md5", $loginID . "^" . $sequence . "^" . $timeStamp . "^" . $amount . "^", $transactionKey);
				} else {
					$fingerprint = bin2hex(mhash(MHASH_MD5, $loginID . "^" . $sequence . "^" . $timeStamp . "^" . $amount . "^", $transactionKey));
				}

				$form = <<<STRING
<FORM method='post' action='$url' >
<INPUT type='hidden' name='x_login' value='$loginID' />
<INPUT type='hidden' name='x_item_code' value='$itemcode' />
<INPUT type='hidden' name='x_description' value='$description' />
<INPUT type='hidden' name='x_amount' value='$amount' />
<INPUT type='hidden' name='x_fp_sequence' value='$sequence' />
<INPUT type='hidden' name='x_fp_timestamp' value='$timeStamp' />
<INPUT type='hidden' name='x_fp_hash' value='$fingerprint' />
<INPUT type='hidden' name='x_test_request' value='$testMode' />
<INPUT type='hidden' name='x_show_form' value='PAYMENT_FORM' />
<input type='submit' value='Pay Now: $description - $amount' />
</FORM>
STRING;

				$content = str_replace($mergecode, $form, $content);
			}
			return $content;
		}

	}

	add_filter('the_content', array(&$WishListMemberInstance, 'anMergeCode'));
}
?>