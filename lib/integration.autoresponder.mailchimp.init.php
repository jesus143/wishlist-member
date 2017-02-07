<?php

/*
 * MailChimp Autoresponder Integration Init Functions
 * Original Author : Fel Jun Palawan
 * Version: $Id: integration.autoresponder.mailchimp.init.php 3151 2016-11-29 06:15:31Z mike $
 */
if ( ! class_exists('mcsdk') ) {
	include_once( $this->pluginDir . '/extlib/mailchimp/mcsdk.php' );
}
if (!class_exists('WLM_AUTORESPONDER_MAILCHIMP_INIT')) {

	class WLM_AUTORESPONDER_MAILCHIMP_INIT {
		/* This is the required function, this is being called by ARSubscibe, function name should be the same with $__methodname__ variable above */

		function mcProcessQueue($recnum = 10,$tries = 5){
			$WishlistAPIQueueInstance = new WishlistAPIQueue;
			$last_process = get_option("WLM_MailchimpAPI_LastProcess");
			$current_time = time();
			$tries = $tries > 1 ? (int)$tries:5;
			$error = false;
			//lets process every 10 seconds
			if ( ! $last_process || ( $current_time - $last_process ) > 10 || ( isset( $data['is_v3'] ) && $data['is_v3'] ) ){
				$queues = $WishlistAPIQueueInstance->get_queue("mailchimp",$recnum,$tries,"tries,name");
				foreach ( $queues as $queue ) {
					$data = maybe_unserialize( $queue->value );
					if ( $data['action'] == 'subscribe' ) {
						if ( isset( $data['is_v3'] ) && $data['is_v3'] ) {
							$res = $this->mc_list_subscribe( $data );
						} else {
							$res = $this->mcListSubscribe($data['apikey'], $data['listID'], $data['email'],$data['mergevars'],$data['optin'],$data['update_existing'],$data['replace_interests']);
						}
					}elseif($data['action'] == 'unsubscribe'){
						if ( isset( $data['is_v3'] ) && $data['is_v3'] ) {
							$res = $this->mc_list_unsubscribe( $data );
						} else {
							$res = $this->mcListUnsubscribe($data['apikey'], $data['listID'], $data['email'],$data['delete_member']);
						}
					}

					if ( isset( $res['error'] ) ) {
						$res['error'] = strip_tags($res['error']);
						$res['error'] = str_replace(array("\n", "\t", "\r"), '',$res['error']);
						$d = array(
							'notes'=> "{$res['code']}:{$res['error']}",
							'tries'=> $queue->tries + 1
							);
						$WishlistAPIQueueInstance->update_queue($queue->ID,$d);
						$error = true;
					}else{
						$WishlistAPIQueueInstance->delete_queue($queue->ID);
						$error = false;
					}
				}
				//save the last processing time when error has occured on last transaction
				if ( $error ) {
					$current_time = time();
					if ( $last_process ) {
						update_option( "WLM_MailchimpAPI_LastProcess", $current_time );
					} else {
						add_option( "WLM_MailchimpAPI_LastProcess", $current_time );
					}
				}
			}
		}

		/* Function for Subscribing Members */
		function mcListSubscribe($api_key, $id, $email_address, $merge_vars, $double_optin = true, $update_existing = true, $replace_interests = true) {
			//populate parameters for Subscribing a Member to Mailchimp subscription in array
			$params = array();
			$params["id"] = $id;
			$params["email_address"] = $email_address;
			$params["merge_vars"] = $merge_vars;
			$params["double_optin"] = $double_optin;
			$params["update_existing"] = $update_existing;
			$params["replace_interests"] = $replace_interests;
			return $this->mcCallServer("listSubscribe", $params, $api_key); // call the function of MAilChimp API for Subsccribing  a Member
		}

		/* Function for UnSubscribing Members */
		function mcListUnsubscribe($api_key, $id, $email_address, $delete_member = false, $send_goodbye = true, $send_notify = true) {
			//populate parameters for Subscribing a Member to Mailchimp Unsubscription in array
			$params = array();
			$params["id"] = $id;
			$params["email_address"] = $email_address;
			$params["delete_member"] = $delete_member;
			$params["send_goodbye"] = $send_goodbye;
			$params["send_notify"] = $send_notify;
			return $this->mcCallServer("listUnsubscribe", $params, $api_key); // call the function of MAilChimp API for UnSubscribing a Member
		}

		/* Function for Connecting to MailChimp API */
		function mcCallServer($method, $params, $api_key) {
			#moving to 1.3	
			$apiUrl = parse_url("http://api.mailchimp.com/1.3/?output=php");

			list($key, $dc) = explode("-", $api_key, 2);
			if (!$dc)
				$dc = "us1";
			$apiUrl["host"] = $dc . "." . $apiUrl["host"];
			$params["apikey"] = $api_key;

			$errorMessage = "";
			$post_vars = $this->mcHttpBuildQuery($params);

			$payload = "POST " . $apiUrl["path"] . "?" . $apiUrl["query"] . "&method=" . $method . " HTTP/1.0\r\n";
			$payload .= "Host: " . $apiUrl["host"] . "\r\n";
			$payload .= "User-Agent: MCAPI/1.2\r\n";
			$payload .= "Content-type: application/x-www-form-urlencoded\r\n";
			$payload .= "Content-length: " . strlen($post_vars) . "\r\n";
			$payload .= "Connection: close \r\n\r\n";
			$payload .= $post_vars;

			ob_start();
			$sock = fsockopen($apiUrl["host"], 80, $errno, $errstr, 300);
			if (!$sock) {
				$response = array("code" => $errno, "error" => "(-99) Could not connect. {$errstr}");
				ob_end_clean();
				return $response;
			}

			$response = "";
			fwrite($sock, $payload);
			while (!feof($sock)) {
				$response .= fread($sock, 8192);
			}
			fclose($sock);
			ob_end_clean();

			list($throw, $response) = explode("\r\n\r\n", $response, 2);

			if (ini_get("magic_quotes_runtime"))
				$response = stripslashes($response);

			$serial = unserialize($response);	
			if ($response && $serial === false) {
				$response = array("code" => "-99", "error" => "Bad Response.  Got This: {$response}");
			} else {
				$response = $serial;
			}
			return $response;
		}

		//create the variables to pass
		function mcHttpBuildQuery($params, $key = null) {
			$ret = array();
			foreach ((array) $params as $name => $val) {
				$name = urlencode($name);
				if ($key !== null) {
					$name = $key . "[" . $name . "]";
				}
				if (is_array($val) || is_object($val)) {
					$ret[] = $this->mcHttpBuildQuery($val, $name);
				} elseif ($val !== null) {
					$ret[] = $name . "=" . urlencode($val);
				}
			}
			return implode("&", $ret);
		}

		function mc_list_subscribe( $data ) {
			$response = array( "code" => 418, "error" => "Unknown Error.");
			$mc = new mcsdk( $data['apikey'] );
			if ( $mc->get_last_error() != "" ) return array( "code" => 418, "error" =>  $mc->get_last_error() );

			$list_id = isset( $data['listID'] ) ? $data['listID'] : '' ;
			$status = $data['optin'] ? 'pending' :'subscribed' ;

			$sub_id = $mc->get_subscriber_id( $data['email'] );
			$sub_details = $mc->get("lists/{$list_id}/members/{$sub_id}");

			$interests = array();
			foreach ( $data['interests'] as $key => $value ) {
				$interests[$value] = true;
			}

			if ( $sub_details ) {
				$sub_interests = isset( $sub_details['interests'] ) ? $sub_details['interests'] : array();
				if ( isset( $data['replace_interests'] ) && $data['replace_interests']  ) {
					foreach ( $sub_interests as $key => $value) {
						$sub_interests[ $key ] = false;
					}
				}
				$interests = array_merge( $sub_interests, $interests );

				$sub_data = array(
						'status_if_new' => $status,
						'interests' => $interests,
				);
				$ret = $mc->put("lists/{$list_id}/members/{$sub_id}", $sub_data );
				if ( ! $mc->is_success() ) {
					$response = array( "code" => 418, "error" => $mc->get_last_error() );
				} else {
					$response = true;
				}
			} else {
				$sub_data = array(
						'email_address' => $data['email'],
						'status' => $status,
						'merge_fields' => $data['mergevars'],
						'interests' => $interests,
				);
				$ret = $mc->post("lists/{$list_id}/members", $sub_data );
				if ( ! $mc->is_success() ) {
					$response = array( "code" => 418, "error" => $mc->get_last_error() );
				} else {
					$response = true;
				}
			}
			return $response;
		}

		function mc_list_unsubscribe( $data ) {
			$response = array( "code" => 418, "error" => "Unknown Error.");
			$mc = new mcsdk( $data['apikey'] );
			if ( $mc->get_last_error() != "" ) return array( "code" => 418, "error" =>  $mc->get_last_error() );

			$list_id = isset( $data['listID'] ) ? $data['listID'] : '' ;
			$status = 'unsubscribed' ;

			$sub_id = $mc->get_subscriber_id( $data['email'] );
			$sub_details = $mc->get("lists/{$list_id}/members/{$sub_id}");

			if ( $sub_details ) {
				$sub_data = array(
						'status' => $status,
				);
				$ret = $mc->put("lists/{$list_id}/members/{$sub_id}", $sub_data );
				if ( ! $mc->is_success() ) {
					$response = array( "code" => 418, "error" => $mc->get_last_error() );
				} else {
					$response = true;
				}
			} else {
				$response = true;
			}

			return $response;
		}

		function mc_get_lists( $api_key ) {
			$lists = array();
			$mc = new mcsdk( $api_key );
			if ( $mc->get_last_error() != "" ) return $lists;

			$lists = $lists2 = array();
			$rec_count = 100; //100 is the maximum number of lists to return with each call
			$lists = $mc->get('lists', array( "count"=>$rec_count ) );
			$start = floor ( $lists['total_items'] / $rec_count);
			$offset = 1;
			while ( $offset <= $start ) {
				$args = array( "count" => $rec_count, "offset" => $offset*$rec_count );
			    $lists2 = $mc->get('lists',$args);
			    if ( $lists2 ) $lists = array_merge_recursive( $lists, $lists2 );
			    $offset += 1;
			}

			if ( $lists && $lists['total_items'] > 0 ) {
				$lists = $lists['lists'];
			}
			return $lists;
		}

		function mc_get_lists_groups( $api_key, $list_id ) {
			$list_groups = array();
			$mc = new mcsdk( $api_key );
			if ( $mc->get_last_error() != "" ) return $list_groups;

			$interest_groups = $mc->get('lists/' . $list_id . '/interest-categories', array( "count"=>100 ) );
			if ( $interest_groups && $interest_groups['total_items'] > 0 ) {
				foreach ( $interest_groups['categories'] AS $group ) {
					$list_groups[ $group['id'] ] = array(
						'title' => $group['title'],
						'interests' => array(),
					);
					$interests = $mc->get('lists/' . $list_id . '/interest-categories/' .$group['id'] .'/interests', array( "count"=>100 ) );
					if ( $interests &&  $interests['total_items'] > 0 ) {
						foreach ( $interests['interests'] as $interest ) {
							$list_groups[ $group['id'] ]['interests'][ $interest['id'] ] = $interest['name'];
						}
					}
				}
			}
			return $list_groups;
		}

		function mc_admin_init() {
			if ( isset( $_POST['ar_action'] ) && $_POST['ar_action'] == 'get_list_interest_groups' ) {
				$list_id = $_POST['list_id'];
				$api_key = $_POST['api_key'];
				$lg = $this->mc_get_lists_groups( $api_key , $list_id );
				echo json_encode( $lg );
				exit(0);
			}
		}
		/* End of Functions */
	}
}

$WLM_AUTORESPONDER_MAILCHIMP_INIT = new WLM_AUTORESPONDER_MAILCHIMP_INIT;
add_action( 'admin_init', array( $WLM_AUTORESPONDER_MAILCHIMP_INIT, 'mc_admin_init') );