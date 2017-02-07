<?php

/**
 * Author: Erwin Atuli
 */
class WPMadMimi {
	private $username;
	private $endpoint;
	public function __construct($username, $api_key) {
		$this->username = $username;
		$this->api_key = $api_key;

	}
	public function request($action, $data=array()) {
		$actions = array(
			'/audience_lists/lists.json'			=> array('type' => 'GET'),
			'/audience_lists/{name_of_list}/add'	=> array('type' => 'POST'),
			'/audience_lists/{name_of_list}/remove?email={email_to_remove}' => array('type' => 'POST'),
		);

		$url  = 'http://api.madmimi.com';

		$defaults = array(
			'username'		=> $this->username,
			'api_key' 		=> $this->api_key,
		);

		if(!is_array($data)) {
			$data = array();
		}

		$method = $actions[$action]['type'];

		foreach($data as $k => $v) {
			if(stripos($action, sprintf('{%s}', $k)) !== false) {
				$action = str_replace(sprintf('{%s}', $k), rawurlencode ($v), $action);
			}
		}
		
		switch ($method) {
			case 'GET':
				$defaults = array_merge($defaults, $data);
				$url = $url . $action .'?'.http_build_query($defaults);
				$resp = wp_remote_get( $url, array('sslverify' => false, 'timeout' => 5));
				if(is_wp_error($resp)) {
					throw new Exception($resp->get_error_message());
				}
				$resp = $resp['body'];
				$resp = json_decode($resp);

				return $resp;
				break;
			case 'POST':
				$url .= $action;
				$data = array_merge($defaults, $data);
				$resp = wp_remote_post( $url, array('sslverify' => false, 'timeout' => 5, 'body' => http_build_query($data)));
				if(is_wp_error($resp)) {
					throw new Exception($resp->get_error_message());
				}
				$resp = $resp['body'];
				$resp = json_decode($resp);
				return $resp;
				break;
			default:
				# code...
				break;
		}

	}
	public function get_lists() {
		return $this->request('/audience_lists/lists.json');
	}
	public function add_to_lists($lists, $email, $fName, $lName) {
		foreach($lists as $l) {
			$this->add_to_list($l, $email, $fName, $lName);
		}
	}
	public function add_to_list($list, $email, $fName, $lName) {
		return $this->request('/audience_lists/{name_of_list}/add', array('name_of_list' => $list, 'email' => $email,'firstName' => $fName, 'lastName' => $lName));
	}
	public function remove_from_lists($lists, $email) {
		foreach($lists as $l) {
			$this->remove_from_list($l, $email);
		}

	}
	public function remove_from_list($list, $email) {
		return $this->request('/audience_lists/{name_of_list}/remove?email={email_to_remove}', array('name_of_list' => $list, 'email_to_remove' => $email));

	}

}