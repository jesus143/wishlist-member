<?php

/*
 * Generic Autoresponder Integration Functions
 * Original Author : Ramil Lacambacal
 * Version: $Id: integration.autoresponder.icontact.php 1672 2013-08-15 03:52:05Z mike $
 */

/*
  This script is called by WishList Member core to subscribe/unsubscribe users from autoresponder lists
 */

//$__classname__ = 'WLM_AUTORESPONDER_ICONTACT';
//$__optionname__ = 'icontact';
//$__methodname__ = 'AutoResponderIContact';

if (!class_exists('WLM_AUTORESPONDER_ICONTACT')) {

	class WLM_AUTORESPONDER_ICONTACT {
		/* This is the required function, this is being called by ARSubscibe, function name should be the same with $__methodname__ variable above */

		function AutoResponderIContact($that, $ar, $wpm_id, $email, $unsub = false) {
			$listID = $ar['icID'][$wpm_id];  // listID used for membership level
			if ($listID) {
				list($fName, $lName) = explode(" ", $that->ARSender['name'], 2);
				$emailAddress = $that->ARSender['email'];
				//retrieve Icontact credentials
				$icUserName = $ar['icusername'];
				$icAppPassword = $ar['icapipassword'];
				$icAppID = $ar['icapiid'];
				$icAcctID = $ar['icaccountid'];
				$icFolderID = $ar['icfolderid'];
				$iclog = $ar['iclog'];
				$icID = $ar['icID'];
				//get client info
				$params = array(array(
						'firstName' => $fName,
						'lastName' => $lName,
						'email' => $emailAddress));
				if (!$unsub) {
					$contactId = $this->addContact($icUserName, $icAppPassword, $icAppID, $icAcctID, $icFolderID, $params);
					if (is_numeric($contactId)) {
						$res = $this->contactListSubscription($icUserName, $icAppPassword, $icAppID, $icAcctID, $icFolderID, $contactId, $listID, 'normal');
					}
				} else {
					if ($iclog[$wpm_id] == 1 && $icID[$wpm_id] != "") {
						$date = date("F j, Y, h:i:s A");
						$logfile = ABSPATH . $icID[$wpm_id] . ".txt";
						if (file_exists($logfile)) {
							$logfilehandler = fopen($logfile, 'a');
						}
						if ($logfilehandler) {
							$txt = '[' . $fName . ' ' . $lName . ']: ' . $emailAddress;
							$log = '[' . $date . '] ' . $txt . "\n";
							fwrite($logfilehandler, $log);
							fclose($logfilehandler);
						}
					}
				}
			}
		}

		// Add contact to list
		//function addContact($icUserName,$icAppPassword,$icAppID,$icAcctID,$icFolderID,$params){
		function addContact($icUserName, $icAppPassword, $icAppID, $icAcctID, $icFolderID, $params) {
			$contactId = null;
			$errorMessage = "";
			$response = $this->callResource($icUserName, $icAppPassword, $icAppID, "/a/{$icAcctID}/c/{$icFolderID}/contacts", 'POST', $params);
			if ($response['code'] == 200) {
				$contactId = $response['data']['contacts'][0]['contactId'];
				$warningCount = 0;
				if (!empty($response['data']['warnings'])) {
					$warningCount = count($response['data']['warnings']);
				}
				if ($warningCount > 0) {
					$errorMessage = "<p>Added contact {$contactId}, with {$warningCount} warnings.</p>\n";
				}
			} else {
				$errorMessage = "<h1>Error - Add Contact {$response['code']}</h1>\n";
			}
			return ($errorMessage == "" ? $contactId : $errorMessage);
		}

		//After adding the contact you can subscribe it to the list, this is the function to subscribe the user to list
		function contactListSubscription($icUserName, $icAppPassword, $icAppID, $icAcctID, $icFolderID, $contactId, $listID, $status) {
			global $welcomeMessageId;
			$response = $this->callResource($icUserName, $icAppPassword, $icAppID, "/a/{$icAcctID}/c/{$icFolderID}/subscriptions", 'POST', array(
				array(
					'contactId' => $contactId,
					'listId' => $listID,
					'status' => $status,
				),
					));

			if ($response['code'] == 200) {
				$errorMessage = "";
				$warningCount = 0;
				if (!empty($response['data']['warnings'])) {
					$warningCount = count($response['data']['warnings']);
				}
				if ($warningCount > 0) {
					$errorMessage = "<p>Subscribed/Unsubscribe contact {$contactId} to list {$listId}, with {$warningCount} warnings.</p>\n";
				}
			} else {
				$errorMessage = "<h1>Error - Subscribe Contact to List</h1>\n";
				$errorMessage .= "<p>Error Code: {$response['code']}</p>\n";
			}
			return $errorMessage;
		}

		//This function is used to make request & pull data from Icontact, parameters are Icontact Credentials saved on autoresponder settings page)
		function callResource($icUserName, $icAppPassword, $icAppID, $url, $method, $data = null) {
			$headers = array(
				'Accept: application/json',
				'Content-Type: application/json',
				'Api-Version: 2.0',
				'Api-AppId: ' . $icAppID,
				'Api-Username: ' . $icUserName,
				'Api-Password: ' . $icAppPassword,
			);
			$apiUrl = 'https://app.icontact.com/icp';
			$url = $apiUrl . $url;
			$handle = curl_init();
			curl_setopt($handle, CURLOPT_URL, $url);
			curl_setopt($handle, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false);

			switch ($method) {
				case 'POST':
					curl_setopt($handle, CURLOPT_POST, true);
					curl_setopt($handle, CURLOPT_POSTFIELDS, json_encode($data));
					break;
				case 'PUT':
					curl_setopt($handle, CURLOPT_PUT, true);
					$file_handle = fopen($data, 'r');
					curl_setopt($handle, CURLOPT_INFILE, $file_handle);
					break;
				case 'DELETE':
					curl_setopt($handle, CURLOPT_CUSTOMREQUEST, 'DELETE');
					break;
			}

			$response = curl_exec($handle);
			$response = json_decode($response, true);
			$code = curl_getinfo($handle, CURLINFO_HTTP_CODE);
			curl_close($handle);
			return array(
				'code' => $code,
				'data' => $response,
			);
		}

		/* End of Functions */
	}

}
?>