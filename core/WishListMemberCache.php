<?php

class WishListMemberCache {

	private $cache_loc;
	private $wishlist_tmp;
	private $plugin_tmp;
	private $dir_writeable = true;
	private $plugin_slug;
	private $stats = array();

	public function __construct($slug) {
		$sys_tmp = sys_get_temp_dir();

		// Check if open_basedir has value. Some servers have open_basedir restrictions so 
		// instead of using the default server tmp folder, we use the path open_basedir allows PHP to have access to.
		$open_basedir= ini_get('open_basedir');
		if(!empty($open_basedir)) {
			$basedirs = explode(":", $open_basedir);
			$sys_tmp = $basedirs[0].'tmp';
		}

//		$wishlist_tmp = sprintf("%s/wishlist-cache", $sys_tmp, rtrim($tmp));
		$wishlist_tmp = sprintf("%s/wishlist-cache", $sys_tmp);
		$plugin_tmp = sprintf("%s/%s", $wishlist_tmp, $slug);

		$status = false; // set default value of $status
		if (!is_dir($wishlist_tmp)) {
			$status = mkdir($wishlist_tmp);
		}

		if (!$status) {
			$this->dir_writeable = false;
		}

		if (!is_dir($plugin_tmp)) {
			$status = mkdir($plugin_tmp);
		}

		if (!$status) {
			$this->dir_writeable = false;
		}

		$this->wishlist_tmp = $wishlist_tmp;
		$this->plugin_tmp = $plugin_tmp;
		$this->plugin_slug = $slug;
		$this->stats['hit'] = 0;
		$this->stats['miss'] = 0;
	}

	public function set($key, $value, $ttl = 120) {
		$cache_key = sprintf("cache_%s_%s.cache", sha1($key), time() + $ttl);

		if ($this->cache_exists($key)) {
			$this->delete($key);
		}

		$fp = fopen($this->plugin_tmp . '/' . $cache_key, 'w');
		if (!$fp) {
			return false;
		}

		if (!is_serialized($value)) {
			$value = maybe_serialize($value);
		}

		$status = fwrite($fp, $value);
		if (!$status) {
			return false;
		}
		fclose($fp);
		return true;
	}

	public function delete($key) {
		$caches = glob($this->plugin_tmp . '/cache_' . sha1($key) . '*');
		foreach ($caches as $c) {
			unlink($c);
		}
	}

	public function get($key) {
		$value = $this->get_cache($key);

		if ($value === false) {
			return false;
		}

		if (empty($val)) {
			return $val;
		}
		return unserialize($value);
	}

	public function cache_exists($key) {
		$caches = glob($this->plugin_tmp . '/cache_' . sha1($key) . '*');
		if (!empty($caches)) {
			return true;
		}
		return false;
	}

	//underlying WishListMemberCache::get
	public function get_cache($key) {
		$caches = glob($this->plugin_tmp . '/cache_' . sha1($key) . '*');
		foreach ($caches as $i => $cache) {
			preg_match('/cache_(.*?)_(\d+)\.cache$/', $cache, $matches);
			list($tmp, $key, $expiry) = $matches;
			if ($expiry - time() <= 0) {
				//expired delete this
				unlink($cache);
				unset($caches[$i]);
			}
		}
		if (!empty($caches)) {
			return file_get_contents($caches[0]);
		}
		return false;
	}

	/**
	 * Cleans up cache dir by removing expired caches
	 */
	public function clean() {
		$caches = glob($this->plugin_tmp . '/cache*');
		foreach ($caches as $c) {
			unlink($c);
		}
	}

}