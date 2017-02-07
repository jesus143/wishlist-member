<?php

/*
 * ConstantContact Autoresponder Integration Functions
 * Original Author : Fel Jun Palawan
 * Version: $Id: integration.autoresponder.constantcontact.php 1672 2013-08-15 03:52:05Z mike $
 */

/*
  GENERAL PROGRAM NOTES: (This script was based on Mike's Autoresponder integrations.)
  Purpose: Containcs functions needed for ConstantContact Integration
  Location: lib/
  Calling program : ARSubscribe() from PluginMethods.php
 */

//$__classname__ = 'WLM_AUTORESPONDER_CONSTANTCONTACT';
//$__optionname__ = 'constantcontact';
//$__methodname__ = 'AutoResponderConstantContact';  // this is the method name being called by the ARSubscribe function

if (!class_exists('WLM_AUTORESPONDER_CONSTANTCONTACT')) {

	class WLM_AUTORESPONDER_CONSTANTCONTACT {
		/* This is the required function, this is being called by ARSubscibe, function name should be the same with $__methodname__ variable above */

		function AutoResponderConstantContact($that, $ar, $wpm_id, $email, $unsub = false) {
			require_once($that->pluginDir . '/extlib/ConstantContact.php');
			$listID = $ar['ccID'][$wpm_id]; // get the list ID of the Membership Level
			$ccUnsub = ($ar['ccUnsub'][$wpm_id] == 1 ? true : false);
			$ccusername = $ar['ccusername'];
			$ccpassword = $ar['ccpassword'];
			$ccerror = "";
			if ($ccusername != "" && $ccpassword != "") { //username and password should not be empty
				$new_cc = New ConstantContact($ccusername, $ccpassword);

				if (is_object($new_cc) && $new_cc->get_service_description()) {
					if (!is_object($new_cc)) {
						$ccerror = "There's an unknown error that occured. Please contact support.";
					}
					// Otherwise, if there is a response code, deal with the connection error
				} elseif (is_object($new_cc) AND isset($new_cc->http_response_code)) {
					$error = $new_cc->http_get_response_code_error($new_cc->http_response_code);
					$ccerror = $error;
				}

				//if no error was found, continue the process
				if ($ccerror == "") {
					if ($listID) { //$listID should not be empty
						list($fName, $lName) = explode(" ", $that->ARSender['name'], 2); //split the name into First and Last Name
						$emailAddress = $that->ARSender['email'];
						if ($unsub) { // if Unsubscribe
							if ($ccUnsub) {
								//check if email exist
								$contact = $new_cc->get_contact_by_email(urlencode($emailAddress));
								if ($contact) { //if email exist unsubscribe it from the list
									$lists = $contact["lists"]; //get current contact's list
									$key = array_search($listID, $lists);
									if ($key) {
										unset($lists[$key]);
									}
									$additional_fields = array("FirstName" => $fName, "LastName" => $lName);
									$new_cc->update_contact($contact["id"], $emailAddress, $lists, $additional_fields);
								}
							}
						} else {
							//check if email exist
							$contact = $new_cc->get_contact_by_email(urlencode($emailAddress));
							if ($contact) { //if email exist update the contact
								$lists = $contact["lists"]; //get current contact's list
								if (!in_array($listID, $lists)) {
									$lists[] = $listID;
								} //add the list id to this contact's lists
								$additional_fields = array("FirstName" => $fName, "LastName" => $lName);
								$new_cc->update_contact($contact["id"], $emailAddress, $lists, $additional_fields);
							} else {  //else create a new contact
								$lists = array($listID);
								$additional_fields = array("FirstName" => $fName, "LastName" => $lName);
								$new_cc->create_contact($emailAddress, $lists, $additional_fields);
							}
						}
					}
				}
			}
		}

		/* End of Functions */
	}

}
?>