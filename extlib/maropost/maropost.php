<?php

/**
 * Author: Erwin Atuli
 */
class WPMaropost {
	private $account_id;
	private $endpoint;
	public function __construct($account_id, $auth_token) {
		$this->account_id = trim($account_id);
		$this->auth_token = trim($auth_token);

	}
	private function create_req_body_xml() {
		//implement
	}
	private function create_req_body($format, $data) {
		if($format == 'json') {
			return json_encode($data);
		} else if($format == 'xml') {
			return $this->create_req_body_xml();
		}
	}
	private function read_resp_xml($body) {
		//implement
	}
	private function read_resp($format, $body) {
		if($format == 'json') {
			return json_decode($body);
		} else if($format == 'xml') {
			return $this->read_resp_xml();
		}
	}
	public function request($method, $action, $params = array(), $data = array()) {
		$actions = array(
			'lists' =>  array(
				'resource' => '/accounts/{account_id}/lists{format}?auth_token={auth_token}&page={page}',
			),
			'contact' => array(
				'resource' => '/accounts/{account_id}/lists/{list_id}/contacts/{contact_id}{format}?auth_token={auth_token}',
			),
			'contacts' => array(
				'resource' => '/accounts/{account_id}/lists/{list_id}/contacts{format}?auth_token={auth_token}',
			)
		);

		$gateway  = 'http://api.maropost.com';
		$resource = $actions[$action]['resource'];
		$format   = 'json';

		$default_params = array(
			'{account_id}' => $this->account_id,
			'{format}'     => '.'.$format,
			'{auth_token}' => $this->auth_token,
		);

		$params = !is_array($params)? $default_params : array_merge($default_params, $params);

		foreach($params as $pkey => $pval) {
			$resource = str_replace($pkey, $pval, $resource);
		}

		if(!is_array($data)) {
			$data = array();
		}

		$body = $this->create_req_body($format, $data);

		switch ($method) {
			case 'GET':
				$gateway = $gateway . $resource;
				$resp = wp_remote_get( $gateway, array( 'timeout' => 15));
				if(is_wp_error($resp)) {
					throw new Exception($resp->get_error_message());
				}
				$resp = $resp['body'];
				$resp = $this->read_resp($format, $resp);
				return $resp;
				break;
			case 'POST':
				$headers['Content-type'] = 'application/json';
				$gateway = $gateway . $resource;

				$resp = wp_remote_post( $gateway, array('sslverify' => false, 'timeout' => 15, 'body' => $body, 'headers' => $headers));
				if(is_wp_error($resp)) {
					throw new Exception($resp->get_error_message());
				}
				$resp = $resp['body'];
				$resp = $this->read_resp($format, $resp);
				return $resp;
				break;
			default:
				$headers['Content-type'] = 'application/json';
				$gateway = $gateway . $resource;
				$resp = wp_remote_request( $gateway,
					array('method' => $method, 'timeout' => 15, 'body' => $body, 'headers' => $headers, 'body' => $body)
				);

				if(is_wp_error($resp)) {
					throw new Exception($resp->get_error_message());
				}
				$resp = $resp['body'];
				$resp = $this->read_resp($format, $resp);
				return $resp;
				# code...
				break;
		}

	}
	public function create_list($list) {
		$list = $this->request('POST', 'lists', array(), $list);
		if(empty($list->id)) {
			return false;
		}
		return $list;
	}
	public function get_lists() {
		$lists = array();

		$count = 0;
		while(true) {
			$list = $this->request('GET', 'lists', array('{page}' => ++$count));
			if(count($list) > 0) {
				$lists = array_merge($lists, $list);
			} else {
				break;
			}
		}

		return $lists;
	}
	public function add_to_list($list, $contact) {
		$obj = $this->request('POST', 'contacts', array('{list_id}' => $list), $contact);
		if(empty($obj->id)) {
			return false;
		}

		$contact['subscribe'] = true;
		$resp = $this->request('PUT', 'contact', array('{list_id}' => $list, '{contact_id}' => $obj->id), $contact);
		return $obj;
	}
	public function remove_from_list($list, $contact_id) {
		$contact = array();
		$contact['subscribe'] = false;
		$obj = $this->request('PUT', 'contact', array('{list_id}' => $list, '{contact_id}' => $contact_id), $contact);
		return $obj;
	}
}