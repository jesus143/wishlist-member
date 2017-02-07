<?php

class WishListXhr {
	var $wlm;
	public function __construct($wlm) {
		$this->wlm = $wlm;


		//define ajax methods
		add_action('wp_ajax_wlm_form_membership_level', array($this, 'form_membership_level'));
		add_action('wp_ajax_wlm_del_membership_level', array($this, 'del_membership_level'));
		add_action('wp_ajax_wlm_set_protection', array($this, 'set_protection'));
		add_action('wp_ajax_wlm_set_membership_content', array($this, 'set_membership_content'));
		add_action('wp_ajax_wlm_reorder_membership_levels', array($this, 'reorder_membership_levels'));

	}
	public function reorder_membership_levels() {
		$sorted = $_POST['reorder'];
		$wpm_levels = $this->wlm->GetOption('wpm_levels');


		foreach($sorted as $lid => $i) {
			$wpm_levels[$lid]['levelOrder'] = $i;
		}

		$this->wlm->SortLevels($wpm_levels, 'a', 'levelOrder');
		$this->wlm->SaveOption('wpm_levels', $wpm_levels);
	}

	public function set_membership_content() {
		$this->wlm->SaveMembershipContent($data);
	}
	public function set_protection() {
		$id = $_POST['id'];

		$result = array();
		foreach($_POST['posts'] as $k => $val) {
			$status = $this->wlm->Protect($k, $val);
			$result[$k] = $status;
		}

		echo "(" . json_encode($result) . ")";
		die();
	}

	public function del_membership_level() {
		$id = $_POST['id'];
		$wpm_levels = $this->wlm->GetOption('wpm_levels');
		unset($wpm_levels[$id]);
		$this->wlm->SaveOption('wpm_levels', $wpm_levels);
	}
	public function form_membership_level($id) {
		ob_start();
		$id = $_POST['id'];
		$wpm_levels = $this->wlm->GetOption('wpm_levels');
		$level = $wpm_levels[$id];

		$pages = get_pages('exclude=' . implode(',', $this->wlm->ExcludePages(array(), true)));
		$pages_options = '';
		foreach ((array) $pages AS $page) {
			$pages_options.='<option value="' . $page->ID . '">' . $page->post_title . '</option>';
		}


		$roles = $GLOBALS['wp_roles']->roles;
		$caps = array();
		foreach ((array) $roles AS $key => $role) {
			if ($role['capabilities']['level_10'] || $role['capabilities']['level_9'] || $role['capabilities']['level_8']) {
				unset($roles[$key]);
			} else {
				list($roles[$key]) = explode('|', $role['name']);

				$caps[$key] = count($role['capabilities']);
			}
		}
		array_multisort($caps, SORT_ASC, $roles);

		include $this->wlm->pluginDir . '/resources/forms/edit_membership_level.php';
		$str = ob_get_clean();
		echo $str;
		//return $str;
		die();
	}
}