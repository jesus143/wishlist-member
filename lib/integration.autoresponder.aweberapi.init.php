<?php

/*
 * AWeber Autoresponder Integration Init Functions
 * Original Author : Fel Jun Palawan
 * Version: $Id: integration.autoresponder.aweberapi.php 2971 2016-01-20 17:08:07Z feljun $
 */

if ( ! class_exists('AWeberAPI') ) {
	include_once( $this->pluginDir . '/extlib/aweber_api/aweber_api.php' );
}

if (!class_exists('WLM_AUTORESPONDER_AWEBERAPI_INIT')) {

	class WLM_AUTORESPONDER_AWEBERAPI_INIT {

		private $app_id = '2d8307c8';
		private $api_ver = '1.0';
		private $api_key = '';
		private $api_secret = '';
		private $auth_key = "";
		private $debug = false;
		private $wlm;

		/**
		 *
		 * @param $access_tokens list containing access_token & access_token_secret
		 */

		private $access_tokens = '';

		function set_wlm($wlm) {
			$this->wlm = $wlm;
		}

		function set_auth_key( $auth_key ) {
			$this->auth_key = $auth_key;
		}

		function get_authkey_url() {
			return sprintf("https://auth.aweber.com/%s/oauth/authorize_app/%s", $this->api_ver, $this->app_id);
		}

		function parse_authkey( $key ) {
			if ( empty( $key ) ) {
				return array();
			}
			list( $api_key, $api_secret, $request_token, $token_secret, $auth_verifier ) = explode( '|', $key );
			$parsed = array(
				'api_key' => $api_key,
				'api_secret' => $api_secret,
				'request_token' => $request_token,
				'token_secret' => $token_secret,
				'auth_verifier' => $auth_verifier,
			);
			return $parsed;
		}

		function get_access_tokens() {
			$auth_key = $this->auth_key;

			if ( empty( $auth_key ) ) return false;

			$options = $this->wlm->GetOption('Autoresponders');
			$access_tokens = $options['aweberapi']['access_tokens'];
			if ( empty( $access_tokens ) ) {
				return false;
			}

			//test our access token
			$auth = $this->parse_authkey( $auth_key );

			$api = new AWeberAPI( $auth['api_key'], $auth['api_secret'] );
			$api->adapter->debug = $this->debug;
			$api->user->tokenSecret = $auth['token_secret'];
			$api->user->requestToken = $auth['request_token'];
			$api->user->verifier = $auth['auth_verifier'];

			list( $access_token, $access_token_secret ) = $access_tokens;
			try {
				$account = $api->getAccount( $access_token, $access_token_secret );
				return $account ?  $access_tokens : false;
			} catch (Exception $e) {
				return false;
			}
		}

		function renew_access_tokens() {
			$key = $this->auth_key;
			if ( empty( $key ) ) return false;
			$auth = $this->parse_authkey($key);
			$api = new AWeberAPI($auth['api_key'], $auth['api_secret']);
			$api->adapter->debug = $this->debug;
			$api->user->tokenSecret = $auth['token_secret'];
			$api->user->requestToken = $auth['request_token'];
			$api->user->verifier = $auth['auth_verifier'];
			try {
				$access_tokens = $api->getAccessToken();
				return $access_tokens;
			} catch (Exception $e) {
				return false;
			}
		}

		function AweberProcessQueue( $recnum = 10, $tries = 5 ){
			global $WishListMemberInstance;
			$WishlistAPIQueueInstance = new WishlistAPIQueue;
			$last_process = get_option("WLM_AweberAPI_LastProcess");
			$current_time = time();
			$tries = $tries > 1 ? (int)$tries:5;
			$recnum = $recnum > 1 ? (int)$recnum:10;
			$error = false;

			if ( ! isset( $WishListMemberInstance ) ) return false;
			$this->set_wlm( $WishListMemberInstance  );

			$options = $this->wlm->GetOption('Autoresponders');
			$auth_key = isset( $options['aweberapi']['auth_key'] ) ? trim( $options['aweberapi']['auth_key'] ) : "";
			if ( empty( $auth_key ) ) return false;
			$this->set_auth_key( $auth_key );

			//lets process every 10 seconds
			if ( ! $last_process || ( $current_time - $last_process ) > 10 ) {
				$queues = $WishlistAPIQueueInstance->get_queue("aweberapi",$recnum,$tries,"tries,name");

				foreach( $queues as $queue ) {
					$data = maybe_unserialize( $queue->value );
					if ( $data['action'] == 'subscribe') { //new and unsubcribing
						$res = $this->subscribe( $data );
					} elseif ( $data['action'] == 'unsubscribe') {//delete
						$res = $this->unsubscribe( $data );
					}

					if ( isset( $res['error'] ) ) {
						$res['error'] = strip_tags($res['error']);
						$res['error'] = str_replace(array("\n", "\t", "\r"), '',$res['error']);
						$d = array(
							'notes'=> $res['error'],
							'tries'=> $queue->tries + 1
							);
						$WishlistAPIQueueInstance->update_queue($queue->ID,$d);
						$error = true;
					} else {
						$WishlistAPIQueueInstance->delete_queue($queue->ID);
						$error = false;
					}
				}
				//save the last processing time when error has occured on last transaction
				if ( $error ) {
					$current_time = time();
					if ( $last_process) {
						update_option("WLM_AweberAPI_LastProcess",$current_time);
					} else {
						add_option("WLM_AweberAPI_LastProcess",$current_time);
					}
				}
			}
		}

		function get_lists() {
			$access_tokens = $this->get_access_tokens();
			if (empty($access_tokens)) {
				throw new Exception("Auth keys have already expired");
			}

			list($access_token, $access_token_secret) = $access_tokens;
			$key = $this->auth_key;
			$auth = $this->parse_authkey($key);
			$api = new AWeberAPI($auth['api_key'], $auth['api_secret']);
			$api->adapter->debug = $this->debug;
			$api->user->tokenSecret = $auth['token_secret'];
			$api->user->requestToken = $auth['request_token'];
			$api->user->verifier = $auth['auth_verifier'];

			try {
				$account = $api->getAccount($access_token, $access_token_secret);
				$lists = array();
				foreach ($account->lists as $l) {
					$lists[] = $l->attrs();
				}
				return $lists;
			} catch (Exception $e) {
				error_log("An error occured while getting list: " . $e->getMessage());
				return false;
			}
		}

		function unsubscribe( $params ) {
			$key = $this->auth_key;
			$auth = $this->parse_authkey($key);

			if ( empty( $auth ) ) {
				error_log("WishList Member Aweber API Error: Invalid Auth");
				return array('error' => "WishList Member Aweber API Error: Invalid Auth" );
			}

			$access_tokens = $this->get_access_tokens();
			if ( empty( $access_tokens ) ) {
				error_log("WishList Member Aweber API Error: Auth keys have already expired");
				return array('error' => "WishList Member Aweber API Error: Auth keys have already expired" );
			}

			list( $access_token, $access_token_secret ) = $access_tokens;
			$api = new AWeberAPI( $auth['api_key'], $auth['api_secret'] );
			$api->adapter->debug = $this->debug;
			$api->user->tokenSecret = $auth['token_secret'];
			$api->user->requestToken = $auth['request_token'];
			$api->user->verifier = $auth['auth_verifier'];

			if ( ! isset( $params['list_id'] ) || ! isset( $params['user_id'] ) ) return false;
			$list_id = $params['list_id'];
			$user_id = $params['user_id'];

			$aweber_uid = get_user_meta( $user_id, "aweberapi_{$list_id}_id", true );
			if ( ! $aweber_uid ) return false;

			try {
				$account = $api->getAccount( $access_token, $access_token_secret );
				$list = $account->lists->getById( $list_id );
				$subs = $list->subscribers;
				$sub = $subs->getById( $aweber_uid );
				$sub_details = $sub ? $sub->data : false;
				if ( $sub_details && $sub_details['status'] != "unconfirmed" ) { // we cannot delete unconfirmed
					$res = $sub->delete();
				}
				delete_user_meta( $user_id, "aweberapi_{$list_id}_id" );
				return true;
			} catch (Exception $e) {
				error_log("An error occured while deleting: " . $e->getMessage());
				return array('error' => $e->getMessage() );
			}
		}

		function subscribe( $params ) {
			$key = $this->auth_key;
			$auth = $this->parse_authkey($key);

			if ( empty( $auth ) ) {
				error_log("WishList Member Aweber API Error: Invalid Auth");
				return array('error' => "WishList Member Aweber API Error: Invalid Auth" );
			}

			$access_tokens = $this->get_access_tokens();
			if ( empty( $access_tokens ) ) {
				error_log("WishList Member Aweber API Error: Auth keys have already expired");
				return array('error' => "WishList Member Aweber API Error: Auth keys have already expired" );
			}

			list( $access_token, $access_token_secret ) = $access_tokens;
			$api = new AWeberAPI( $auth['api_key'], $auth['api_secret'] );
			$api->adapter->debug = $this->debug;
			$api->user->tokenSecret = $auth['token_secret'];
			$api->user->requestToken = $auth['request_token'];
			$api->user->verifier = $auth['auth_verifier'];

			if ( ! isset( $params['list_id'] ) || ! isset( $params['user_id'] ) ) return false;
			$list_id = $params['list_id'];
			$user_id = $params['user_id'];
			try {
				$account = $api->getAccount( $access_token, $access_token_secret );
				$list = $account->lists->getById( $list_id );
				$subs = $list->subscribers;

				$subscriber = false;
				$sub_details = false;
				$aweber_uid = get_user_meta( $user_id, "aweberapi_{$list_id}_id", true );
				if ( $aweber_uid ) {
					$subscriber = $subs->getById( $aweber_uid );
					$sub_details = $subscriber ? $subscriber->data : false;
				}

				//we can only update subscribed existing users
				if ( $sub_details && $sub_details['status'] == "unconfirmed" ) return true;

				$a_uid = false;
				if ( $sub_details ) { //update
					$subscriber->name = $params['name'];
					if ( isset( $params['level_tag']['apply'] ) && !empty( $params['level_tag']['apply'] ) ) {
						$params['level_tag']['apply'] = strtolower( $params['level_tag']['apply'] );
						$subscriber->tags['add'] = array_map('trim', explode( ',', $params['level_tag']['apply'] ) );
					}
					if ( isset( $params['level_tag']['remove'] ) && !empty( $params['level_tag']['remove'] ) ) {
						$params['level_tag']['remove'] = strtolower( $params['level_tag']['remove'] );
						$subscriber->tags['remove'] = array_map('trim', explode( ',', $params['level_tag']['remove'] ) );
					}
					if ( isset( $params['on_unsub'] ) && $params['on_unsub'] == "unsubscribe" ) {
						$subscriber->status = "unsubscribed";
					}
					$subscriber->save();
				} else { //add
					$ad_tracking = $params['ad_tracking'] ? substr( $params['ad_tracking'] , 0 ,20 ) : false;
					$data = array(
						'email' => $params['email'],
				        'name' => $params['name'],
				        'ip_address' => $params['ip_address'],
					);
					if ( $ad_tracking ) $data['ad_tracking'] = $ad_tracking;

					if ( isset( $params['level_tag']['apply'] ) && !empty( $params['level_tag']['apply'] ) ) {
						$params['level_tag']['apply'] = strtolower( $params['level_tag']['apply'] );
						$data['tags'] = array_map('trim', explode( ',', $params['level_tag']['apply'] ) );
					}
					$sub = $subs->create( $data );
					$attr = $sub->attrs();
					if ( !empty( $attr ) && is_array( $attr ) && isset( $attr['id'] ) ) {
						$a_uid = $attr['id'];
					}
				}

				if ( $a_uid ) update_user_meta( $params['user_id'], $aweber_uid, $a_uid );
				return true;

			} catch (Exception $e) {
				error_log("An error occured while subscribing: " . $e->getMessage());
				return array('error' => $e->getMessage() );
			}
		}

		function get_subscriber( $list_id, $id ) {
			$key = $this->auth_key;
			$auth = $this->parse_authkey($key);

			if (empty($auth)) {
				// throw new Exception("Invalid Auth");
				error_log("WishList Member Aweber API Error: Invalid Auth");
				return false;
			}

			$access_tokens = $this->get_access_tokens();
			if (empty($access_tokens)) {
				// throw new Exception("Auth keys have already expired");
				error_log("WishList Member Aweber API Error: Auth keys have already expired");
				return  false;
			}

			list( $access_token, $access_token_secret ) = $access_tokens;
			$api = new AWeberAPI($auth['api_key'], $auth['api_secret']);
			$api->adapter->debug = $this->debug;
			$api->user->tokenSecret = $auth['token_secret'];
			$api->user->requestToken = $auth['request_token'];
			$api->user->verifier = $auth['auth_verifier'];

			try {
				$account = $api->getAccount($access_token, $access_token_secret);
				$list = $account->lists->getById($list_id);
				$subs = $list->subscribers;
				// now create a new subscriber
				$sub = $subs->getById( $id );
				if ( $sub ) {
					return $sub->data;
				} else {
					return false;
				}
			} catch (Exception $e) {
				error_log("An error occured while getting subscriber: " . $e->getMessage());
				return false;
			}
		}

		function find_subscriber( $list_id, $email ) {
			$key = $this->auth_key;
			$auth = $this->parse_authkey($key);

			if (empty($auth)) {
				// throw new Exception("Invalid Auth");
				error_log("WishList Member Aweber API Error: Invalid Auth");
				return false;
			}

			$access_tokens = $this->get_access_tokens();
			if (empty($access_tokens)) {
				// throw new Exception("Auth keys have already expired");
				error_log("WishList Member Aweber API Error: Auth keys have already expired");
				return false;
			}

			list( $access_token, $access_token_secret ) = $access_tokens;
			$api = new AWeberAPI($auth['api_key'], $auth['api_secret']);
			$api->adapter->debug = $this->debug;
			$api->user->tokenSecret = $auth['token_secret'];
			$api->user->requestToken = $auth['request_token'];
			$api->user->verifier = $auth['auth_verifier'];

			try {
				$account = $api->getAccount($access_token, $access_token_secret);
				$list = $account->lists->getById($list_id);
				$subs = $list->subscribers;
				// now create a new subscriber
				$sub = $subs->find( array( "email"=>$email ) );
				if ( $sub ) {
					return $sub->data["entries"][0];
				} else {
					return false;
				}
			} catch (Exception $e) {
				error_log("An error occured while getting subscriber: " . $e->getMessage());
				return false;
			}
		}
	}

}