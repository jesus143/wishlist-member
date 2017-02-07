<?php

/*
 * SendStudio Autoresponder API
 * Original Author : Fel Jun Palawan
 * Version: $Id: integration.autoresponder.sendstudio.php 1672 2013-08-15 03:52:05Z mike $
 */

/*
  GENERAL PROGRAM NOTES: (This script was based on Mike's Autoresponder integrations.)
  Purpose: Containcs functions needed for SendStudio Integration
  Location: lib/
  Calling program : ARSubscribe() from PluginMethods.php
 */

//$__classname__ = 'WLM_AUTORESPONDER_SENDSTUDIO';
//$__optionname__ = 'sendstudio';
//$__methodname__ = 'AutoResponderSendStudio';  // this is the method name being called by the ARSubscribe function

if (!class_exists('WLM_AUTORESPONDER_SENDSTUDIO')) {

	class WLM_AUTORESPONDER_SENDSTUDIO {
		/* This is the required function, this is being called by ARSubscibe, function name should be the same with $__methodname__ variable above */

		function AutoResponderSendStudio($that, $ar, $wpm_id, $email, $unsub = false) {
			$listID = $ar['ssID'][$wpm_id]; // get the list ID of the Membership Level
			$ssUnsub = ($ar['ssUnsub'][$wpm_id] == 1 ? true : false);
			if ($listID) { //$listID should not be empty
				list($fName, $lName) = explode(" ", $that->ARSender['name'], 2); //split the name into First and Last Name
				$emailAddress = $that->ARSender['email'];
				$ssPath = $ar['sspath']; // get the SendStudio XML Path
				$ssUname = $ar['ssuname']; // get the SendStudio XML Username
				$ssToken = $ar['sstoken']; // get the SendStudio XML Token
				$ssFnameId = $ar['ssfnameid']; // get the SendStudio Custom Field First Name ID
				$ssLnameId = $ar['sslnameid']; // get the SendStudio Custom Field Last Name ID

				if ($unsub) { // if the Unsubscribe
					if ($ssUnsub) {
						$res = $this->ssListUnsubscribe($ssPath, $ssUname, $ssToken, $listID, $emailAddress);
					}
				} else { //else Subscribe
					$res = $this->ssListSubscribe($ssPath, $ssUname, $ssToken, $ssFnameId, $ssLnameId, $listID, $emailAddress, $fName, $lName);
				}
			}
		}

		/* Function for Subscribing Members */

		function ssListSubscribe($ssPath, $ssUname, $ssToken, $ssFnameId, $ssLnameId, $listID, $emailAddress, $fName, $lName) {
			/* Prepare the data */
			$xml = '<xmlrequest>
				<username>' . $ssUname . '</username>
				<usertoken>' . $ssToken . '</usertoken>
				<requesttype>subscribers</requesttype>
				<requestmethod>AddSubscriberToList</requestmethod>
				<details>
					<emailaddress>' . $emailAddress . '</emailaddress>
					<mailinglist>' . $listID . '</mailinglist>
					<format>html</format>
					<confirmed>yes</confirmed>
					<customfields>
						<item>
							<fieldid>' . ($ssFnameId == "" ? "2" : $ssFnameId) . '</fieldid>
							<value>' . $fName . '</value>
						</item>
						<item>
							<fieldid>' . ($ssLnameId == "" ? "3" : $ssLnameId) . '</fieldid>
							<value>' . $lName . '</value>
						</item>
					</customfields>
				</details>
			</xmlrequest>';
			return $this->SendRequest($xml, $ssPath);
		}

		/* Function for UnSubscribing Members */

		function ssListUnsubscribe($ssPath, $ssUname, $ssToken, $listID, $emailAddress) {
			/* Prepare the data */
			$xml = '<xmlrequest>
				<username>' . $ssUname . '</username>
				<usertoken>' . $ssToken . '</usertoken>
				<requesttype>subscribers</requesttype>
				<requestmethod>DeleteSubscriber</requestmethod>
				<details>
					<emailaddress>' . $emailAddress . '</emailaddress>
					<list>' . $listID . '</list>
				</details>
			</xmlrequest>';
			return $this->SendRequest($xml, $ssPath);
		}

		/* Function for Sending Request */

		function SendRequest($xml, $ssPath) {
			$ch = curl_init($ssPath);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
			$ret = "";
			$result = @curl_exec($ch);
			if ($result === false) {
				$ret = "Error performing request";
			} else {
				$ret = $result;
			}
			return $ret;
		}

		/* End of Funtions */
	}

}
?>