<?php
//$__classname__ = 'WishListMemberWebinarIntegrationGotowebinar';
class WishListMemberWebinarIntegrationGotowebinar {
	public function __construct() {
		$this->name = "Gotowebinar";
		$this->slug = "gotomeeting";
	}
	public function init() {
	}
	public function subscribe($data) {
		
		$data2 = array();
		$data2['first_name'] = $data['first_name'];
		$data2['last_name'] = $data['last_name'];
		$data2['email'] = $data['email'];
		
		global $WishListMemberInstance;
		$webinars = $WishListMemberInstance->GetOption('webinar');
		$settings = $webinars[$this->slug];
		$settings = $settings[$data['level']];
		if (empty($settings)) {
			return;
		}
		$url = $settings;
		$baseurl = parse_url($url);
		if (!in_array($baseurl['scheme'], array('https', 'http')) OR empty($baseurl['host'])) {
			return;
		}
		$baseurl = $baseurl['scheme'] . '://' . $baseurl['host'];

		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$data = curl_exec($ch);


		preg_match('/<form.+?action="(.+?)".*?>/is', $data, $matches);
		$post_url = $baseurl . $matches[1];
		
		$vars = array();
		preg_match_all('/<input.+?name="(.+?)".*?>/is', $data, $matches);
		foreach ($matches[1] AS $key => $var) {
			preg_match('/<input.+?value="(.*?)".*?>/is', $matches[0][$key], $match);
			$vars[$var] = $match[1];
		}

		if (isset($vars['registrant.email'])) {
			// new interface 2013
			$vars['registrant.givenName'] = $data2['first_name'];
			$vars['registrant.surname'] = $data2['last_name'];
			$vars['registrant.email'] = $data2['email'];
		} else {
			// old interface
			$vars['Name_First'] = $data2['first_name'];
			$vars['Name_Last'] = $data2['last_name'];
			$vars['Email'] = $data2['email'];
		}
		
		// Check if this is a recurring webinar by checking if the Select Option for Webinar schedules is in the form
		if(preg_match_all('/<select name="webinar".+?name="(.+?)".*?>/is', $data, $select_match)) {
			preg_match_all('/<option.+?value="(.+?)".*?>/is', $select_match[0][0], $options_match);
			// Get the first result on the option as that normally is set as the SELECTED webinar
			$vars['webinar'] = $options_match[1][0];
		}

		$fields = http_build_query($vars);
		$ch = curl_init($post_url);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
		$ip = $_SERVER['REMOTE_ADDR'];
		if (preg_match('/\d\.\d\.\d\.\d/', $ip)) {
			curl_setopt($ch, CURLOPT_HTTPHEADER, array("REMOTE_ADDR: $ip", "HTTP_X_FORWARDED_FOR: $ip"));
		}
		$data = curl_exec($ch);
	}
	public function unsubscribe($data) {
		//
	}
}
?>
