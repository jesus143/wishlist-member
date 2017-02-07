<?php
//$__classname__ = 'WishListMemberWebinarIntegrationEverGreen';
class WishListMemberWebinarIntegrationEverGreen {
	public function __construct() {
		$this->name = "Evergreen Business System";
		$this->slug = "evergreen";
	}
	public function init() {}
	public function subscribe($data) {

		global $WishListMemberInstance;
		$webinars = $WishListMemberInstance->GetOption('webinar');
		$settings = $webinars[$this->slug];
		$settings = $settings[$data['level']];
		if (empty($settings)) {
			return;
		}

		$url = $settings;
		$urlparts = parse_url($url);
		parse_str($urlparts['query'], $args);
		$args['name'] = sprintf("%s %s", $data['first_name'], $data['last_name']);
		$args['email'] = $data['email'];

		//subscribe to next day
		$args['date'] =  date('Y-m-d',  time() + (3600 * 24));
		$args['timezone'] = 'UTC';

		$query = http_build_query($args);
		$urlparts['query'] = $query;

		$url = sprintf("%s://%s%s?%s", $urlparts['scheme'], $urlparts['host'], $urlparts['path'], $urlparts['query']);
		$WishListMemberInstance->ReadURL($url);
	}
	public function unsubscribe($data) {}
}
?>
