<?php

class WishlistDebug {

	public static function build_log_message($message) {
		$doing_cron = '(site)';
		if (defined('DOING_CRON') && DOING_CRON) {
			$doing_cron = '(cron)';
		}

		$pid = getmypid();

		$time = date('Y-m-d H:i:s');
		$log_msg = sprintf("\n[%s]%s(%s): %s", $time, $doing_cron, $pid, $message);
		return $log_msg;
	}

	public static function log($message) {
		$log_file = dirname(__FILE__) . '/../resources/logs/debug.log';
		$enabled = get_option('wishlist_enable_debug');
		$enabled = true;
		if (!$enabled) {
			return false;
		}

		$log_msg = self::build_log_message($message);
		$fp = @fopen($log_file, 'a+');
		if (!$fp) {
			//switch to  database logging            
			//going to be slow as log grows in size
			$log_msg = get_option('wishlist_debug_str') . $log_msg;
			update_option('wishlist_debug_str', $log_msg);
		} else {
			//file logging
			fwrite($fp, $log_msg);
			fclose($fp);
		}
	}

	public static function fetch_logs() {
		$log_file = dirname(__FILE__) . '/../resources/logs/debug.log';
		if (is_writable($log_file)) {
			return file_get_contents($log_file);
		}
		return get_option('wishlist_debug_str');
	}

	public static function clear_logs() {
		$log_file = dirname(__FILE__) . '/../resources/logs/debug.log';
		if (is_writable($log_file)) {
			$fp = fopen($log_file, 'w');
			fclose($fp);
		} else {
			update_option('wishlist_debug_str', null);
		}
	}

}