<?php
//$__classname__ = 'WishListMemberWebinarIntegrationEasyWebinar';
class WishListMemberWebinarIntegrationEasyWebinar {
	public function __construct() {
		$this->name = "Easy Webinar";
		$this->slug = "easywebinar";
	}
	public function init() {}
	public function subscribe($data) {
		if(!class_exists('webinar_db_interaction')) {
			return;
		}

		global $WishListMemberInstance;
		$webinars = $WishListMemberInstance->GetOption('webinar');
		$settings = $webinars[$this->slug];
		$webinar_id = $settings[$data['level']];

		if (empty($webinar_id)) {
			error_log('skipping, no webinar connected');
			return;
		}



		$webinar_db = new webinar_db_interaction();
		$webinar = $webinar_db->get_webinar_detail($webinar_id);
		$scheds = $webinar_db->get_scheduled_times_for_webinar($webinar_id);


		if(!empty($webinar)) {
			$webinar = current($webinar);
		}

		$tz = $webinar_db->get_timezone_detail($webinar->webinar_timezone_id_fk);
		if(!empty($tz)) {
			$tz = current($tz);
		}

		$num_slots = 1;
		$next_day = 1;
		$slot = $webinar_db->get_webinar_slots($num_slots,
										$next_day,
										$webinar_id,
										$webinar->max_number_of_attendees,
										$webinar->webinar_schedule_type_id_fk);
		$slot = current($slot);
		if(empty($slot)) {
			//no more slot avail
			return;
		}

		$everyday_session_detail = $webinar_db->get_registered_attendees_for_everyday($webinar_id, $slot);
		//$everyday_session_detail = current($everyday_session_detail);

		if(empty($everyday_session_detail)) {
			//just take the first avail slot
			$use_schedule = current($scheds);
		} else {
			foreach($everyday_session_detail as $session) {
				if($session->counts < $webinar->max_number_of_attendees) {
					$use_schedule = $webinar_db->get_webinar_schedule_detail($session->webinar_schedule_id_fk);
					$use_schedule = current($use_schedule);
					break;
				}
			}
		}



		$real_time = date('g:i a', strtotime($use_schedule->start_time));
		$real_time = $time . " " . $tz->name;

		$real_date = date('M d, Y', strtotime($slot));


		$args['webinar_id']				=	$webinar_id;
		$args['schedule_id']			=	$use_schedule->webinar_schedule_id_pk;
		$args['aten_name']				=	sprintf("%s %s", $data['first_name'], $data['last_name']);
		$args['aten_email']				=	base64_encode($data['email']);
		$args['webinar_date']			=	$slot;
		$args['webinar_start_date']		=	$slot;
		$args['max_attendee']			=	$webinar->max_attendee;
		$args['webinar_time']			=	$use_schedule->start_time;
		$args['after_webinar_enabled']	=	$webinar->notification_after_webinar_enabled;
		$args['after_webinar_hours']	=	$webinar->after_webinar_notification_hours;
		$args['video_length']			=	$webinar->webinar_video_length;
		$args['selected_timezone_id']	=	$webinar->webinar_timezone_id_fk;
		$args['webinar_real_time']		=	base64_encode($real_time);
		$args['webinar_real_date']		=	base64_encode($real_date);
		$args['attendee_local_timezone']=null;
		$args['easywebinaryloopflag']	= 1;

		$q = http_build_query($args);
		$url = WP_PLUGIN_URL.'/webinar_plugin/webinar-db-interaction/webinar-ajax-file.php?'.$q;

		$resp = wp_remote_post($url, array('body' => $args));

	}
	public function unsubscribe($data) {}
}
?>
