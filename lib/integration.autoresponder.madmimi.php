<?php

/*
 * MadMimi Autoresponder Integration Functions
 * Original Author : Erwin Atuli
 */

if (!class_exists('WPMadMimi')) {
	require_once dirname(__FILE__) . '/../extlib/madmimi/madmimi.php';
}


if (!class_exists('WLM_AUTORESPONDER_MADMIMI')) {

	class WLM_AUTORESPONDER_MADMIMI {

		public function madmimi_subscribe($that, $ar, $wpm_id, $email, $unsub = false) {
			$options = $that->GetOption('Autoresponders');
			$maps = $options['madmimi']['maps'][$wpm_id];
			if(empty($maps)) {
				return;
			}


			$username = $options['madmimi']['username'];
			$api_key = $options['madmimi']['api_key'];

			$mmm = new WPMadmimi($username, $api_key);

			try {
				if(!empty($maps)) {
					if($unsub && $options['madmimi'][$wpm_id]['autoremove']) {
						$mmm->remove_from_lists($maps, $email);
					}

					if(!$unsub) {
						
						list($fName, $lName) = explode(" ", $that->ARSender['name'], 2); //split the name into First and Last Name

						$mmm->add_to_lists($maps, $that->ARSender['email'], $fName,$lName);
					}

				}
			} catch (Exception $e) {
				error_log($e->getMessage());
			}

		}
	}
}