<?php

/*
 * MailChimp Autoresponder Integration Functions
 * Original Author : Fel Jun Palawan
 * Version: $Id: integration.autoresponder.mailchimp.php 3151 2016-11-29 06:15:31Z mike $
 */

/*
  GENERAL PROGRAM NOTES: (This script was based on Mike's Autoresponder integrations.)
  Purpose: Containcs functions needed for MailChimp Integration
  Location: lib/
  Calling program : ARSubscribe() from PluginMethods.php
 */

//$__classname__ = 'WLM_AUTORESPONDER_MAILCHIMP';
//$__optionname__ = 'mailchimp';
//$__methodname__ = 'AutoResponderMailChimp';  // this is the method name being called by the ARSubscribe function

if (!class_exists('WLM_AUTORESPONDER_MAILCHIMP')) {

	class WLM_AUTORESPONDER_MAILCHIMP {
		/* This is the required function, this is being called by ARSubscibe, function name should be the same with $__methodname__ variable above */

		function AutoResponderMailChimp($that, $ar, $wpm_id, $email, $unsub = false) {

			$listID = $ar['mcID'][$wpm_id]; // get the list ID of the Membership Level
			$mcAPI = $ar['mcapi']; // get the MailChimp API
			$is_v3 = isset( $ar['api_v3'] ) ? true : false; // get the MailChimp API

			$WishlistAPIQueueInstance = new WishlistAPIQueue;
			$WLM_AUTORESPONDER_MAILCHIMP_INIT = new WLM_AUTORESPONDER_MAILCHIMP_INIT;

			if ( $listID ) { //$listID should not be empty
				list( $fName, $lName ) = explode(" ", $that->ARSender['name'], 2); //split the name into First and Last Name
				$emailAddress = $that->ARSender['email'];
				$data = false;
				if ( $unsub ) { // if the Unsubscribe
					$mcOnRemCan = isset($ar['mcOnRemCan'][$wpm_id]) ? $ar['mcOnRemCan'][$wpm_id] : "";
					if ( $mcOnRemCan == "unsub" ) {
						$data = array(
							"apikey"=> $mcAPI,
							"action"=>"unsubscribe",
							"listID"=> $listID,
							"email"=>$emailAddress,
							"delete_member"=>true
						);
					} elseif ( $mcOnRemCan == "move" || $mcOnRemCan == "add" ) {

						$gp = $ar['mcRCGp'][$wpm_id];
						$gping = $ar['mcRCGping'][$wpm_id];
						$interests = array();
						if ( $is_v3 ) {
							$interests = (is_array( $gping ) && count( $gping ) > 0) ? $gping : array();
							$merge_vars = array('FNAME' => $fName, 'LNAME' => $lName );
						} else {
							$groupings = array();
							if ( $gp != "" && $gping != "" ) {
								$groupings = array(array('name' => $gp, 'groups' => $gping));
							}
							#add name or else this will still fail
							$merge_vars = array('FNAME' => $fName, 'LNAME' => $lName, 'NAME' => "$fName $lName", 'GROUPINGS' => $groupings); // populate the 
						}
						$replace_interests = $mcOnRemCan == "move" ? true : false;
						$optin = $ar['optin']; // get the MailChimp API
						$optin = $optin == 1 ? false:true;
						$data = array(
							"apikey"=> $mcAPI,
							"action"=> "subscribe",
							"listID"=> $listID,
							"email"=> $emailAddress,
							"mergevars"=> $merge_vars,
							"optin"=> $optin,
							"update_existing"=>true,
							"replace_interests"=>$replace_interests,
							"interests" => $interests,
						);
					}
				} else { //else Subscribe
					$gp = $ar['mcGp'][$wpm_id];
					$gping = $ar['mcGping'][$wpm_id];
					$interests = array();
					if ( $is_v3 ) {
						$interests = (is_array( $gping ) && count( $gping ) > 0) ? $gping : array();
						$merge_vars = array('FNAME' => $fName, 'LNAME' => $lName );
					} else {
						$groupings = array();
						if ( $gp != "" && $gping != "" ) {
							$groupings = array(array('name' => $gp, 'groups' => $gping));
						}
						#add name or else this will still fail
						$merge_vars = array('FNAME' => $fName, 'LNAME' => $lName, 'NAME' => "$fName $lName", 'GROUPINGS' => $groupings); // populate the 
					}
					$optin = $ar['optin']; // get the MailChimp API
					$optin = $optin == 1 ? false:true;
					$data = array(
						"apikey"=> $mcAPI,
						"action"=>"subscribe",
						"listID"=> $listID,
						"email"=>$emailAddress,
						"mergevars"=>$merge_vars,
						"optin"=>$optin,
						"update_existing"=>true,
						"replace_interests"=>false,
						"interests" => $interests,
					);
				}
				if ( $data ) {
					if ( $is_v3 ) $data['is_v3'] = 1;
					$qname = "mailchimp_" .time();
					$data = maybe_serialize($data);
					$WishlistAPIQueueInstance->add_queue($qname,$data,"For Queueing");
					$WLM_AUTORESPONDER_MAILCHIMP_INIT->mcProcessQueue();
				}
			}
		}

		/* End of Functions */
	}

}