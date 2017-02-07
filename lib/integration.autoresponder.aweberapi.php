<?php

/*
 * Generic Autoresponder Integration Functions
 * Original Author : Erwin Atuli
 * Version: $Id: integration.autoresponder.aweberapi.php 3151 2016-11-29 06:15:31Z mike $
 */

if (!class_exists('WLM_AUTORESPONDER_AWEBERAPI')) {

	class WLM_AUTORESPONDER_AWEBERAPI {

		function AutoResponderAweberAPI( $that, $ar, $wpm_id, $email, $unsub = false ) {

			$autounsub = $ar['autounsub'][$wpm_id] == 'yes' ? 'unsubscribe' : $ar['autounsub'][$wpm_id];
			$ad_tracking = isset( $ar['ad_tracking'][$wpm_id] ) ? trim( $ar['ad_tracking'][$wpm_id] ) : '';
			$ad_tracking = $ad_tracking ? substr( $ad_tracking , 0 ,20 ) : $ad_tracking; //limit to 20 char
			$list_id = $ar['connections'][$wpm_id];
			$auth_key = isset( $ar['auth_key'] ) ? $ar['auth_key'] : "";

			if ( empty( $list_id ) || empty( $auth_key ) ) return; // exit if we don't have anything to sub/unsub to

			$WLM_AUTORESPONDER_AWEBERAPI_INIT = new WLM_AUTORESPONDER_AWEBERAPI_INIT;
			$WLM_AUTORESPONDER_AWEBERAPI_INIT->set_wlm( $that );
			$WLM_AUTORESPONDER_AWEBERAPI_INIT->set_auth_key( $ar['auth_key'] );

			$user = get_user_by( 'email', trim( $that->ARSender['email'] ) );
			if ( empty( $user ) ) return;

			$level_tag = array();
			$params = array();

			if ( $unsub === false ) {
				if ( isset( $ar['level_tag'][$wpm_id]['added'] ) ) {
					$level_tag = $ar['level_tag'][$wpm_id]['added'];
					$level_tag["apply"] = isset( $level_tag["apply"] ) ? trim($level_tag["apply"]) : "";
					$level_tag["remove"] = isset( $level_tag["remove"] ) ? trim($level_tag["remove"]) : "";
				}

				$params = array(
					"action"=>"subscribe",
					"list_id"=> $list_id,
					'update_existing' => 0,
					'email' => $that->ARSender['email'],
					'name' => $that->ARSender['name'],
					'ip_address' => $_SERVER['REMOTE_ADDR'],
					'level_tag' => $level_tag,
					'ad_tracking' => $ad_tracking,
					'on_unsub' => '',
					'user_id' => $user->ID
				);

				$aweber_uid = get_user_meta( $user->ID, "aweberapi_{$list_id}_id", true );
				if ( ! $aweber_uid ) {
					$sub = $WLM_AUTORESPONDER_AWEBERAPI_INIT->find_subscriber( $list_id, $that->ARSender['email'] ); //if no id, lets check if subcriber
					if ( $sub ) {
						$aweber_uid = isset( $sub['id'] ) ? $sub['id'] : false;
						if ( $aweber_uid ) add_user_meta( $user->ID, "aweberapi_{$list_id}_id", $aweber_uid );
					}
				}

			} else {

				if ( $autounsub == 'delete' ) {
					$aweber_uid = get_user_meta( $user->ID, "aweberapi_{$list_id}_id", true );
					if ( ! $aweber_uid ) {
						$sub = $WLM_AUTORESPONDER_AWEBERAPI_INIT->find_subscriber( $list_id, $that->ARSender['email'] ); //if no id, lets check if subcriber
						if ( $sub ) {
							$aweber_uid = isset( $sub['id'] ) ? $sub['id'] : false;
							if ( $aweber_uid ) add_user_meta( $user->ID, "aweberapi_{$list_id}_id", $aweber_uid );
						}
					}
					// we only unsubscribe people with records in aweber list
					if ( $aweber_uid ) {
						$params = array(
							"action"=>"unsubscribe",
							"list_id"=> $list_id,
							'update_existing' => 0,
							'email' => $that->ARSender['email'],
							'name' => $that->ARSender['name'],
							'ip_address' => $_SERVER['REMOTE_ADDR'],
							'level_tag' => $level_tag,
							'ad_tracking' => $ad_tracking,
							'on_unsub' => 'delete',
							'user_id' => $user->ID
						);
					} else {
						return;
					}
				} else {
					//get membership levels
					$user_levels = $that->GetMembershipLevels( $user->ID , false, false, true, true );
					$is_cancelled = in_array( $wpm_id, $user_levels ); //if user still have the level, its cancelled else its removed
					$level_tag_index = $is_cancelled ? "cancelled" : "removed";

					if ( isset( $ar['level_tag'][$wpm_id][$level_tag_index] ) ) {
						$level_tag = $ar['level_tag'][$wpm_id][$level_tag_index];
						$level_tag["apply"] = isset( $level_tag["apply"] ) ? trim($level_tag["apply"]) : "";
						$level_tag["remove"] = isset( $level_tag["remove"] ) ? trim($level_tag["remove"]) : "";
					}
					//if we dont need to apply or remove a tag, lets end
					if ( empty( $level_tag["apply"] ) && empty( $level_tag["remove"] ) && $autounsub != "unsubscribe"  ) return;

					$aweber_uid = get_user_meta( $user->ID, "aweberapi_{$list_id}_id", true );
					if ( ! $aweber_uid ) {
						$sub = $WLM_AUTORESPONDER_AWEBERAPI_INIT->find_subscriber( $list_id, $that->ARSender['email'] ); //if no id, lets check if subcriber
						if ( $sub ) {
							$aweber_uid = isset( $sub['id'] ) ? $sub['id'] : false;
							if ( $aweber_uid ) add_user_meta( $user->ID, "aweberapi_{$list_id}_id", $aweber_uid );
						}
					}

					if ( $aweber_uid ) {
						$params = array(
							"action"=>"subscribe",
							"list_id"=> $list_id,
							'update_existing' => 1,
							'email' => $that->ARSender['email'],
							'name' => $that->ARSender['name'],
							'ip_address' => $_SERVER['REMOTE_ADDR'],
							'level_tag' => $level_tag,
							'ad_tracking' => $ad_tracking,
							'on_unsub' => $autounsub,
							'user_id' => $user->ID
						);
					} else {
						return;
					}
				}
			}

			if ( !empty( $params ) ) {
				//add  to queue
				$WishlistAPIQueueInstance = new WishlistAPIQueue;
				$qname = "aweberapi_" .time();
				$params = maybe_serialize($params);
				$WishlistAPIQueueInstance->add_queue($qname,$params,"For Queueing");
				$WLM_AUTORESPONDER_AWEBERAPI_INIT->AweberProcessQueue();
			}
		}

	}

}