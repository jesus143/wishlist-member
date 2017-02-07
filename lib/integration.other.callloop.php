<?php

/*
 * Call Loop Autoresponder Integration Functions
 * Original Author : Andy Depp
 * Version: $Id: 
 */

//$__classname__ = 'WLM_OTHER_INTEGRATION_CALLLOOP';
//$__optionname__ = 'callloop';
//$__methodname__ = 'Callloop';

if (!class_exists('WLM_OTHER_INTEGRATION_CALLLOOP')) {

	class WLM_OTHER_INTEGRATION_CALLLOOP {

		function Callloop($user_id, $wpm_id, $unsub = false) {
			global $WishListMemberInstance;
			
			
			if(strpos($wpm_id, "U-") !== false){
				return false;
			}
			
			 
			
			$callloop_settings = (array) $WishListMemberInstance->GetOption('callloop_settings');
			$callloopURL = $callloop_settings['URL'][$wpm_id];

			if ($callloopURL) {
				$user_id = (int) $user_id;
				$userCustomFields = $WishListMemberInstance->GetUserCustomFields($user_id);
				
				
				if (array_key_exists('phone', $userCustomFields)) {
					$phone = $userCustomFields['phone'];
					if (WLM_OTHER_INTEGRATION_CALLLOOP::ValidatePhoneNumber($phone)) {
						$callloop_autoresponder_id = str_replace("https://www.callloop.com/r/?", "", $callloop_settings['URL'][$wpm_id]);
						$arUnsub = ($callloop_settings['callloopUnsub'][$wpm_id] == 1 ? true : false);
						if (function_exists('curl_init')) {
							if ($callloop_autoresponder_id) {
								$user_data= $WishListMemberInstance->Get_UserData($user_id);
								$fName=$user_data->user_firstname;
								$lName=$user_data->user_lastname;
								
								if ( ($fName=='')||($lName=='') ){
									list($fName, $lName) = explode(" ", $user_data->display_name, 2);
								} 
								
								$emailAddress = $user_data->user_email;

								if ($unsub) {
									if ($arUnsub) {
										// remove  phone from call loop list
										$UnsubURL = "http://www.callloop.com/s/?{$callloop_autoresponder_id}&phone={$phone}";
										WLM_OTHER_INTEGRATION_CALLLOOP::NavigateURL($UnsubURL);
									}
								} else {
									// add phone to call loop list
									$subURL = "http://www.callloop.com/r/?{$callloop_autoresponder_id}&first={$fName}&last={$lName}&email={$emailAddress}&phone={$phone}";
									WLM_OTHER_INTEGRATION_CALLLOOP::NavigateURL($subURL);
								}
							} // end if  $autoresponderID exist 
						} // end if curl exist
					} // end if phone number is valed
				} // end if phone custom filed exist
			} // end if $arURL
		}

		function ValidatePhoneNumber($phone) {
			return TRUE;
		}

		function NavigateURL($url) {
			$ch = curl_init();
			// Set query data here with the URL
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_TIMEOUT, '60');
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			$content = trim(curl_exec($ch));
			curl_close($ch);
			//print $content;
		}
		
		// hooks
		function AddLevels($user_id, $levels) {
			foreach($levels AS $level) {
				WLM_OTHER_INTEGRATION_CALLLOOP::Callloop($user_id, $level);
			}
		}

		function RemoveLevels($user_id, $levels) {
			foreach($levels AS $level) {
				WLM_OTHER_INTEGRATION_CALLLOOP::Callloop($user_id, $level, true);
			}
		}

	}

	add_action('wishlistmember_remove_user_levels', array(WLM_OTHER_INTEGRATION_CALLLOOP, 'RemoveLevels'), 10, 2);
	add_action('wishlistmember_add_user_levels', array(WLM_OTHER_INTEGRATION_CALLLOOP, 'AddLevels'), 10, 2);
}
