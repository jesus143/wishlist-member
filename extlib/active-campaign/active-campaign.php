<?php

class WpActiveCampaign {

	private $api_url;
	private $api_key;
	public function __construct($api_url, $api_key) {
		$this->api_url = $api_url;
		$this->api_key = $api_key;
	}
	public function request($action, $data = null) {
		$actions = array(
			'list_list' 			=> array('type' => 'GET'),
			'contact_view_email'	=> array('type' => 'GET'),
			'contact_add'			=> array('type' => 'POST'),
			'contact_edit'			=> array('type' => 'POST'),
		);

		$url  = $this->api_url . '/admin/api.php';

		$defaults = array(
			'api_key' 		=> $this->api_key,
			'api_action'	=> $action,
			'api_output'	=> 'json',
		);

		if(!is_array($data)) {
			$data = array();
		}

		switch ($actions[$action]['type']) {
			case 'GET':
				$defaults = array_merge($defaults, $data);
				$url .= '?'.http_build_query($defaults);
				$resp = wp_remote_get( $url, array('sslverify' => false, 'timeout' => 5));
				if(is_wp_error($resp)) {
					throw new Exception($resp->get_error_message());
				}
				$resp = $resp['body'];
				$resp = json_decode($resp);

				if($resp->result_code == 0) {
					throw new Exception($resp->result_message);
				}
				return $resp;
				break;
			case 'POST':
				$url .= '?'.http_build_query($defaults);
				$resp = wp_remote_post( $url, array('sslverify' => false, 'timeout' => 5, 'body' => http_build_query($data)));
				if(is_wp_error($resp)) {
					throw new Exception($resp->get_error_message());
				}
				$resp = $resp['body'];

				$resp = json_decode($resp);

				if($resp->result_code == 0) {
					throw new Exception($resp->result_message);
				}
				return $resp;
				break;
			default:
				# code...
				break;
		}
	}

	public function get_user_by_email($email) {
		try {
			$resp = $this->request('contact_view_email', array('email' => $email));
			return $resp;
		} catch (Exception $e) {
			return false;
		}

	}

	public function get_lists() {
		$resp = $this->request('list_list', array('ids' => 'all', 'full' => 0));
		$lists = array();
		foreach($resp as $l) {
			if(is_object($l)) {
				$lists[] = $l;
			}
		}
		return $lists;
	}

	public function add_to_lists($lists, $user_data) {
		$user = $this->get_user_by_email($user_data['email']);
		if($user) {
			$data = array(
				'id' => $user->id,
			);

			//build the previous list items
			foreach($user->lists as $list) {
				$data["p[{$list->listid}]"]			= $list->listid;
				$data["status[{$list->listid}]"]	= $list->status;
			}

			//override with our new ones
			foreach($lists as $lid) {
				$data["p[$lid]"]		= $lid;
				$data["status[$lid]"]	= 1;
			}

			$status = $this->request('contact_edit', $data);
		} else {
			//create
			$data = array(
				'first_name'		=> $user_data['first_name'],
				'last_name'			=> $user_data['last_name'],
				'email'				=> $user_data['email'],
				//misc
				"ip"				=> $_SERVER['REMOTE_ADDR']
			);

			//now add this to our lists
			foreach($lists as $lid) {
				$data["p[$lid]"]		= $lid;
				$data["status[$lid]"]	= 1;
			}

			$status = $this->request('contact_add', $data);
		}
	}
	public function remove_from_lists($lists, $email) {
		$user = $this->get_user_by_email($email);
		if(empty($user)) {
			return;
		}
		$data = array(
			'id' => $user->id,
		);
		//build the previous list items
		foreach($user->lists as $list) {
			$data["p[{$list->listid}]"]			= $list->listid;
			$data["status[{$list->listid}]"]	= $list->status;
		}
		//override with our new ones
		foreach($lists as $lid) {
			$data["p[$lid]"]		= $lid;
			$data["status[$lid]"]	= 2;
		}
		$status = $this->request('contact_edit', $data);
	}

}