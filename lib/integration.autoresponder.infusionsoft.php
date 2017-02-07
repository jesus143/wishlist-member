<?php

/*
 * Infusionsoft Autoresponder Integration Functions
 * Original Author : Fel Jun Palawan
 * Version: $Id: integration.autoresponder.infusionsoft.php 3007 2016-04-12 13:36:46Z mike $
 */

/*
  GENERAL PROGRAM NOTES: (This script was based on Mike's Autoresponder integrations.)
  Purpose: Containcs functions needed for Infusionsoft Integration
  Location: lib/
  Calling program : ARSubscribe() from PluginMethods.php
 */

//this line is already set at integration.autoresponders.php
// $__classname__ = 'WLM_AUTORESPONDER_INFUSIONSOFT';
// $__optionname__ = 'infusionsoft';
// $__methodname__ = 'AutoResponderInfusionsoft';  // this is the method name being called by the ARSubscribe function

if ( ! class_exists('WLM_AUTORESPONDER_INFUSIONSOFT') ) {

	class WLM_AUTORESPONDER_INFUSIONSOFT {

		/* This is the required function, this is being called by ARSubscibe, function name should be the same with $__methodname__ variable above */
		function AutoResponderInfusionsoft($that, $ar, $wpm_id, $email, $unsub = false) {
			global $WishListMemberInstance;
			$ifsdk = false;
			//make sure that WLM active and infusiosnsoft connection is set
			if ( isset( $WishListMemberInstance ) && class_exists( 'WLM_Infusionsoft' ) ) {
				$machine_name = $WishListMemberInstance->GetOption('auto_ismachine');
				$api_key      = $WishListMemberInstance->GetOption('auto_isapikey');
				$log    	  = $WishListMemberInstance->GetOption('auto_isenable_log');
				$machine_name = $machine_name ? $machine_name : "";
				$api_key      = $api_key ? $api_key : "";

				$apilogfile = false;
				if ( $log ) {
	                $dirsep             = DIRECTORY_SEPARATOR; //directory seperator
	                $date_now           = date('m-d-Y');
	                $apilogfile         = $WishListMemberInstance->pluginDir .$dirsep ."ifs_logs_{$date_now}.csv";
				}

				if ( $api_key && $machine_name ) {
					$ifsdk = new WLM_Infusionsoft( $machine_name, $api_key, $apilogfile );
				}
			}

			if ( ! $ifsdk ) return;

			$campid     = $ar['isCID'][$wpm_id]; // get the campaign ID of the Membership Level
			$isUnsub = ($ar['isUnsub'][$wpm_id] == 1 ? true : false); // check if we will unsubscribe or not

			if ( $campid ) {

				list( $fName, $lName ) = explode(" ", $that->ARSender['name'], 2); //split the name into First and Last Name
				$email = $that->ARSender['email'];

			 	$contactid = $ifsdk->get_contactid_by_email( $email );

				if ( $unsub ) { // if the Unsubscribe
					//if email is found, remove it from campaign and if it will be unsubscribe once remove from level
					if ( $contactid && $isUnsub ) {
						$res = $ifsdk->remove_followup_sequence( $contactid, $campid );
					}
				} else { //else Subscribe
					//if email is existing, assign it to the campaign
					if ( $contactid ) {
						//optin email first
						$ifsdk->optin_contact_email( $email );
						$res = $ifsdk->assign_followup_sequence( $contactid, $campid );
					} else {
						//if email is new, assign it to the add it to the database
						$carray = array(
							'Email' => $email,
							'FirstName' => $fName,
							'LastName' => $lName,
						);
						$contactid = $ifsdk->create_contact( $carray );
						// if successfully addded, opt-in the contact
						if ( $contactid ) {
							$ifsdk->optin_contact_email( $email );
							$res = $ifsdk->assign_followup_sequence( $contactid, $campid );
						}
					}
				}
			}
		}
		//end AutoResponderInfusionsoft function
	}

}