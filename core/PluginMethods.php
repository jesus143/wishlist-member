<?php

/**
 * Plugin Methods Class for WishList Member
 * @author Mike Lopez <mjglopez@gmail.com>
 * @package wishlistmember
 *
 * @version $Rev: 3151 $
 * $LastChangedBy: mike $
 * $LastChangedDate: 2016-11-29 01:15:31 -0500 (Tue, 29 Nov 2016) $
 */
if (!defined('ABSPATH'))
	die();
if (!class_exists('WishListMemberPluginMethods')) {

	/**
	 * Plugin Methods WishList Member Class
	 * @package wishlistmember
	 * @subpackage classes
	 */
	class WishListMemberPluginMethods extends WishListMemberDBMethods {

		/**
		 * Save Membership Content
		 * @param array $data optional
		 * @param boolean $nohooks TRUE to disable custom hooks
		 * @return none
		 */
		function SaveMembershipContent($data = '', $nohooks = false) {
			global $wpdb;
			if ($data) {
				$msg = false;
				extract($data);
			} else {
				$msg = true;
				extract($_POST);
			}
			$Checked = (array) $Checked + (array) $ID;
			switch ($ContentType) {
				case 'folders':
					$content_type = '~FOLDER';
					break;
				case 'categories':
					$content_type = '~CATEGORY';
					break;
				case 'pages':
					$content_type = 'page';
					break;
				case 'posts':
					$content_type = 'post';
					break;
				case 'comments':
					$content_type = '~COMMENT';
					break;
				default:
					$content_type = $ContentType;
			}

			$content_ids = (array) $Checked + (array) $ID;
			$removed = $added = array();
			foreach ($content_ids AS $content_id => $status) {
				if ($status) {
					$result = $wpdb->query($x = $wpdb->prepare("INSERT IGNORE INTO `{$this->Tables->contentlevels}` (`content_id`, `level_id`, `type`) VALUES (%d,%s,%s)", $content_id, $Level, $content_type));
					if ($result) {
						$added[] = $content_id;
					}
				} else {
					$result = $wpdb->query($x = $wpdb->prepare("DELETE FROM `{$this->Tables->contentlevels}` WHERE `content_id`=%d AND `level_id`=%s AND `type`=%s", $content_id, $Level, $content_type));
					if ($result) {
						$removed[] = $content_id;
					}
				}
			}

			// Trigger Content Action Hooks Routine
			if (!$nohooks) {
				if (count($removed)) {
					foreach ((array) $removed AS $id) {
						$this->TriggerContentActionHooks($ContentType, $id, array($Level), array());
					}
				}
				if (count($added)) {
					foreach ((array) $added AS $id) {
						$this->TriggerContentActionHooks($ContentType, $id, array(), array($Level));
					}
				}
			}
			// End of Trigger Action Hooks Routine
			if ($msg) {
				if ($Level == 'Protection') {
					$_POST['msg'] = __('<b>Content Protection updated.</b>', 'wishlist-member');
				} elseif ($Level == 'PayPerPost') {
					$_POST['msg'] = __('<b>Pay Per Post access updated.</b>', 'wishlist-member');
				} else {
					$_POST['msg'] = __('<b>Membership Level access updated.</b>', 'wishlist-member');
				}
			}

			if(empty($this->SaveMembershipContent_do_not_pass_protection)) {
				$changed = array_unique( ( array ) $added + ( array ) $removed );
				foreach ( $changed AS $id ) {
					$this->pass_protection( $id, $ContentType == 'categories' );
				}
			}
			$this->SaveMembershipContent_do_not_pass_protection = false;
		}

		function SaveMembershipContentPayPerPost() {
			$this->SaveMembershipContent();
			$post = $_POST;
			$_POST['Checked'] = array_intersect($_POST['enable_free_payperpost'], array(1));
			$_POST['Level'] = 'Free_PayPerPost';
			$this->SaveMembershipContent();
			$_POST = $post;
		}

		/**
		 * Get Content for Membership Level
		 * @param <type> $ContentType
		 * @param <type> $Level
		 * @return <type>
		 */
		function GetMembershipContent($ContentType, $Level = '') {
			global $wpdb;
			$content_type = '';
			$post_type = '';
			$all_categories = $this->AllCategories;
			switch ($ContentType) {
				case 'folders':
					$content_type = '~FOLDER';
					break;
				case 'categories':
					$content_type = '~CATEGORY';
					if($this->taxonomyIds) {
						$all_categories = array_unique(array_merge( (array) $all_categories, (array) $this->taxonomyIds));
					}
					break;
				case 'pages':
					$content_type = 'page';
					$post_type = "'page'";
					break;
				case 'posts':
					$content_type = 'post';
					$post_type = "'post'";
					break;
				case 'comments':
					$content_type = '~COMMENT';
					$post_type = "'post'";
					break;
				default: // custom post type
					$content_type = $ContentType;
					$post_type = "'{$content_type}'";
			}
			$post_statuses = "'publish','pending','draft','private','future'";
			$all_query = "SELECT DISTINCT `ID` FROM `{$wpdb->posts}` WHERE `post_status` IN ({$post_statuses}) AND `post_type` IN ({$post_type})";

			$Content = array();
			$wpm_levels = $this->GetOption('wpm_levels');
			if ($Level && !is_array($Level)) {
				if (wlm_arrval($wpm_levels[$Level], 'all' . $ContentType)) {
					if ($post_type) {
						$Content = $wpdb->get_col($all_query);
					}
					if ($ContentType == 'categories') {
						$Content = $all_categories;
					}
				} else {
					if(strrpos($Level,"U-") !== false){ //for pay per post
						$Content = $this->GetUser_PayPerPost($Level,false,$content_type,true);
					}else{
						$Content = $wpdb->get_col($wpdb->prepare("SELECT `content_id` FROM `{$this->Tables->contentlevels}` WHERE type=%s AND level_id=%s GROUP BY content_id", $content_type, $Level));
					}
				}
			} elseif (is_array($Level)) {
				$xLevels = array();
				$xPPLevels = array();
				foreach ((array) $Level AS $L) {
					if ($wpm_levels[$L]['all' . $ContentType]) {
						if ($post_type) {
							$Content = $wpdb->get_col($all_query);
						}
						if ($ContentType == 'categories') {
							$Content = $all_categories;
						}
						$xLevels = array();
						break;
					} else {
						if ($L) {
							if(strrpos($L,"U-") !== false){
								$xPPLevels[] = esc_sql($L);
							}else{
								$xLevels[] = "'" . esc_sql($L) . "'";
							}
						}
					}
				}
				// if content is empty
				if (empty($Content)) {
					$level_content = $ppp_content = array();
					if(count($xLevels)){ //for levels
						$xLevels = implode(',', $xLevels);
						$level_content = $wpdb->get_col($wpdb->prepare("SELECT DISTINCT `content_id` FROM `{$this->Tables->contentlevels}` WHERE `type`=%s AND `level_id` IN ({$xLevels})", $content_type));
					}
					if(count($xPPLevels)){ // for pay per post
						$ppp_content = $this->GetUser_PayPerPost($xPPLevels,false,$content_type,true);
					}
					$Content = array_merge($ppp_content,$level_content);
				}
			} else {
				foreach (array_keys($wpm_levels) AS $level_id) {
					$Content[$level_id] = $this->GetMembershipContent($ContentType, $level_id);
				}
			}
			return $Content;
		}

		/**
		 * Clone Content Membership Level
		 * @param int $from Source Level
		 * @param int $to Destination Level
		 */
		function CloneMembershipContent($from, $to) {
			global $wpdb;
			$wpdb->query($wpdb->prepare("DELETE FROM `{$this->Tables->contentlevels}` WHERE `level_id`=%s", $to));
			$wpdb->query($x = $wpdb->prepare("INSERT INTO `{$this->Tables->contentlevels}` (`content_id`,`level_id`,`type`) SELECT `content_id`,%s,`type` FROM `{$this->Tables->contentlevels}` WHERE `level_id`=%s", $to, $from));
		}

		/**
		 * Set Membership Content Levels
		 * @param string $ContentType
		 * @param int $id Content ID
		 * @param array $levels Array of Level IDs
		 */
		function SetContentLevels( $ContentType, $id, $levels ) {
			$wpm_levels = $this->GetOption('wpm_levels');
			$this->ValidateLevels($levels);

			$current_levels = $this->GetContentLevels($ContentType, $id);
			$this->ArrayDiff($levels, $current_levels, $removed_levels, $new_levels);

			$oldpost = $_POST;
			$_POST = array(
				'ContentType' => $ContentType,
				'ID' => array($oldpost['post_ID'] => 0, $id => 0)
			);
			$level_keys = array_keys((array) $wpm_levels);
			while($key = array_shift($level_keys)) {
				if (in_array($key, $levels)) {
					$_POST['Checked'] = array($oldpost['post_ID'] => 1, $id => 1);
				} else {
					unset($_POST['Checked']);
				}
				$_POST['Level'] = $key;
				$this->SaveMembershipContent_do_not_pass_protection = count($level_keys) > 1;
				$this->SaveMembershipContent('', true);
			}
			$_POST = $oldpost;

			// trigger wordpress action hooks
			$this->TriggerContentActionHooks($ContentType, $id, $removed_levels, $new_levels);
		}

		//recursive
		function SetContentLevelsDeep($content_type, $id, $levels, $ascend=true) {
			/**
			 * How this is achieved
			 * 1. Find the topmost level
			 * 2. Find all the children
			 * 3. Filter all childrens  that "Inherit"s
			 * 4. Update those children
			 */

			switch ($content_type) {
				case 'pages':
				case 'page':
					//find topmost level
					$page = get_page($id);
					if(!$ascend) {
						if(!$this->SpecialContentLevel($id, 'Inherit')) {
							return;
						}
						$this->SetContentLevels($content_type, $page->ID, $levels);
						if(in_array('Protection', $levels)) {
							$this->Protect($id, 'Y');
						} else {
							$this->Protect($id, 'N');
						}
					} else {
						$root        = wlm_get_page_root($page->ID);
						$children    = wlm_get_page_children($root);

						if($root == $id) {
							if($this->Protect($id)) {
								$levels[] = 'Protection';
							}
						} else {
							$levels = $this->GetContentLevels($content_type, $root);
						}

						foreach($children as $c) {
							$this->SetContentLevelsDeep($content_type, $c, $levels, false);
						}
					}
					break;
				case 'category':
					//update the relevant category children
					$children_cat = wlm_get_category_children($id, 'category');
					foreach($children_cat as $c) {
						if($this->SpecialContentLevel($c, 'Inherit', null, '~CATEGORY')) {
							$this->SetContentLevels('categories', $c, $levels);
						}
					}
					/*
					 * Go through all posts and collect categories
					 */
					$children_post = wlm_get_category_children($id, 'post');

					// Collect all relevant categories
					$cats = array();
					foreach($children_post as $child) {
						$cats =  array_merge($cats, get_the_category($child->ID));
					}

					//filter & reformat for easy manipulation later
					$levels = array();
					foreach($cats as $c) {
						$levels[$c->term_id] = $this->GetContentLevels('categories', $c->term_id);
					}

					//now update the relevant posts
					// the final levels is set to the combined levels of
					// all parent categories
					foreach($children_post as $child) {
						if(!$this->SpecialContentLevel($child->ID, 'Inherit')) {
							continue;
						}
						$cats              = get_the_category($child->ID );
						$combined_settings = array();
						foreach($cats as $c) {
							$combined_settings = array_merge($combined_settings, $levels[$c->term_id]);
						}

						$_POST['post_ID'] = null;//apparently i need to clean this var up
						$this->SetContentLevels('posts', $child->ID, $combined_settings);
						if(in_array('Protection', $combined_settings)) {
							$this->Protect($child->ID, 'Y');
						} else {
							$this->Protect($child->ID, 'N');
						}
					}
					break;
				default:
					$roots   = wlm_get_post_root($id);
					$levels  = array();
					$protect = false;
					foreach($roots as $r) {
						$root_levels = $this->GetContentLevels('categories', $r);
						$this->SetContentLevelsDeep('category', $r, $root_levels);
					}
					break;

			}

		}

		/**
		 * Get Content Levels
		 * @param string $ContentType
		 * @param int $id Content ID
		 * @param boolean $names TRUE to return names instead of IDs
		 * @return array Levels
		 */
		function GetContentLevels($ContentType, $id, $names = null, $implode_names = null, &$immutable = 'nothing') {
			if(is_null($names)) {
				$names = false;
			}
			if(is_null($implode_names)) {
				$implode_names = false;
			}
			global $wpdb;
			switch ($ContentType) {
				case 'folders':
					$content_type = '~FOLDER';
					break;
				case 'categories':
					$content_type = '~CATEGORY';
					break;
				case 'pages':
					$content_type = 'page';
					break;
				case 'posts':
					$content_type = get_post_type($id);
					break;
				case 'comments':
					$content_type = '~COMMENT';
					break;
				default:
					$content_type = $ContentType;
			}

			$noppp = $names ? " `level_id` NOT LIKE 'U-%%' AND " : '';
			$levels = $wpdb->get_col($wpdb->prepare("SELECT DISTINCT `level_id` FROM `{$this->Tables->contentlevels}` WHERE {$noppp} `type`=%s AND `content_id`=%d", $content_type, $id));

			$wpm_levels = $this->GetOption('wpm_levels');
			if(in_array($content_type, array('page','post','~CATEGORY','~COMMENT')) && $immutable != 'nothing') {
				$immutable = array();
				foreach($wpm_levels AS $key => $val) {
					switch($content_type) {
						case '~CATEGORY':
							$allkey = 'allcategories';
						break;
						case '~COMMENT':
							$allkey = 'allcomments';
						break;
						case 'page':
							$allkey = 'allpages';
						break;
						case 'post':
							$allkey = 'allposts';
						break;
					}
					if($val[$allkey]) {
						$immutable[] = $key;
						array_unshift($levels, $key);
					}
				}	
				$levels = array_unique($levels);
			} else {
				$immutable = array();
			}


			if ($names) {
				$names = array();
				foreach ((array) $levels AS $level) {
					if(isset($wpm_levels[$level])) {
						$names[$level] = $wpm_levels[$level]['name'];
						if(is_array($immutable) && in_array($level, $immutable)) {
							$names[$level] = sprintf('<wlmimmutable>%s</wlmimmutable>', $names[$level]);
						}
					}
				}

				$levels = $implode_names ? implode(', ', $names) : $names;
			}
			return $levels;
		}

		/**
		 * Clone Protection
		 * @param int $orig Source Content ID
		 * @param int $clone Destination Content ID
		 * @param string $origtype Source Content Type
		 * @param string $clonetype (optional) Destination Content Type
		 */
		function CloneProtection($orig, $clone, $origtype = 'posts', $clonetype = null) {
			// first clone the levels
			if (is_null($clonetype))
				$clonetype = $origtype;
			$this->SetContentLevels($clonetype, $clone, $this->GetContentLevels($origtype, $orig));
			$protect = $this->Protect($orig) ? 'Y' : 'N';
			$this->Protect($clone, $protect);
		}

		/**
		 * Synchronize Content Levels
		 * @global object $wpdb
		 */
		function SyncContent() {
			global $wpdb;
			// fix all invalid post types
			$query = "UPDATE IGNORE `{$this->Tables->contentlevels}`,`{$wpdb->posts}` SET `{$this->Tables->contentlevels}`.`type`=`{$wpdb->posts}`.`post_type` WHERE `{$this->Tables->contentlevels}`.`content_id`=`{$wpdb->posts}`.`ID` AND `{$this->Tables->contentlevels}`.`type` NOT LIKE '~%%'";
			$wpdb->query($query);

			// remove all entries in wlm_contentlevels where type does not begin with ~ and no matching posts (any post type) in wp_posts
			$query = "DELETE `{$this->Tables->contentlevels}` FROM `{$this->Tables->contentlevels}` LEFT JOIN `{$wpdb->posts}` ON `{$this->Tables->contentlevels}`.`content_id`=`{$wpdb->posts}`.`ID` AND `{$this->Tables->contentlevels}`.`type`=`{$wpdb->posts}`.`post_type` WHERE `{$this->Tables->contentlevels}`.`type` NOT LIKE '~%%' AND `{$wpdb->posts}`.`ID` IS NULL";
			$wpdb->query($query);

			//remove all data from wlm_contentlevels if the membership level deleted.
			/* WishList Member Levels */
			$wpm_levels = $this->GetOption('wpm_levels');
			if (count($wpm_levels) > 0) {
				$in = "'" . implode("','", array_keys($wpm_levels)) . "'";
				$query = "DELETE FROM `{$this->Tables->contentlevels}` WHERE level_id NOT IN ({$in}) AND level_id NOT IN ('Protection','Free_PayPerPost','PayPerPost', 'Inherit', 'ForceDownload') AND level_ID NOT LIKE 'U-%'";
				$wpdb->query($query);
			}
		}
		function GetParentContents($id, $post_type=null) {
			if(empty($post_type)) {
				$post_type = get_post_type($id);
			}

			$parent_ids  = array();
			$parent_type = null;

			switch ($post_type) {
				case 'post':
					$parent_type = 'categories';
					$parent_ids  = wp_get_post_categories($id);
					break;
				case 'page':
					$parent_type = 'posts';
					if($post->post_parent) {
						$ancestors    = get_post_ancestors($id);
						$root         = count($ancestors) - 1;
						$parent_ids[] = $ancestors[$root];
					}
					break;
				case 'category':
					break;
				default:
					$parent_type = 'categories';
					$parent_ids  = wp_get_post_categories($id);
					return $parent_ids;
			}
			return array('type' => $parent_type, 'contents' => $parent_ids);
		}
		/**
		 * Get/Set Post/Page Protection
		 * @param int $id Post/Page ID
		 * @param char $status (optional) Y|N
		 * @return boolean
		 * Internally handles inheritance when protecting. So make sure you set the content's
		 * Inheritance status before protecting to be able to cascade protection the the content.
		 */
		function Protect( $id, $status = null ) {
			$current_status = $this->SpecialContentLevel( $id, 'Protection' );
			if ( ! is_null( $status ) ) {
				$new_status = $this->SpecialContentLevel( $id, 'Protection', $status );
				if ( $current_status != $new_status ) {
					$current_status = $new_status;
				}
			}
			return $current_status;
		}

		function SpecialContentLevel($id, $level, $status = null, $type = null) {
			global $wpdb;
			$id+=0;

			// if $id validates to false then return true (meaning protected)
			if ( ! $id ) {
				return true;
			}

			if(empty($type)) {
				$type = get_post_type($id);
				if (!$this->PostTypeEnabled($type)) {
					return false;
				}
			}

			if (!is_null($status)) {
				if(is_bool($status)) {
					$status = $status ? 'Y' : 'N';
				}
				switch (strtoupper($status)) {
					case 'Y':
						$query = $wpdb->prepare("INSERT IGNORE INTO `{$this->Tables->contentlevels}` (`content_id`,`level_id`,`type`) VALUES (%d,%s,%s)", $id, $level, $type);
						$wpdb->query($query);
						break;
					case 'N':
						$query = $wpdb->prepare("DELETE FROM `{$this->Tables->contentlevels}` WHERE `content_id`=%d AND `level_id`=%s AND `type`=%s", $id, $level, $type);
						$wpdb->query($query);
						break;
				}
				if($level == 'Protection' && (substr($type, 0, 1) != '~' || $type == '~CATEGORY')) {
					$this->pass_protection( $id, $type == '~CATEGORY' );
				}
			}

			$query = $wpdb->prepare("SELECT COUNT(*) FROM `{$this->Tables->contentlevels}` WHERE `content_id`=%d AND `level_id`=%s AND `type`=%s", $id, $level, $type);
			return (bool) $wpdb->get_var($query);
		}

		/**
		 * Save Members Data
		 * This function is called when updating information in Members tab
		 */
		function SaveMembersData() {

			extract($_POST);
			$force_sync = false;
			if(!$wpm_member_id) {
				$_POST['err'] = __('No users selected.', 'wishlist-member');
				return;
			}

			extract(array($_POST['wpm_action'] => 1));
			$wpm_levels = $this->GetOption('wpm_levels');
			if ($wpm_member_id) {
				if ((int) $wpm_membership_to) {
					//Set or Schedule a member to a certain level
					switch ($wpm_action) {
						case 'wpm_change_membership':
							$this->ScheduleToLevel($wpm_action, $wpm_membership_to, $wpm_member_id, $dp_move_level);
							$force_sync = true;
							break;
						case 'wpm_add_membership':
							$this->ScheduleToLevel($wpm_action, $wpm_membership_to, $wpm_member_id, $dp_add_level);
							$force_sync = true;
							break;
						case 'wpm_del_membership':
							$this->ScheduleToLevel($wpm_action, $wpm_membership_to, $wpm_member_id, $dp_remove_level);
							$force_sync = true;
							break;
						default:
							break;
					}
					// cancel/uncancel membership level
					if ($wpm_cancel_membership || $wpm_uncancel_membership) {
						$status = $wpm_cancel_membership ? true : false;
						$cancelled_or_not = $status ? __("Cancelled") : __("Uncancelled");
						$todays_date = strtotime(date("Y-m-d"));
						$cdate_array = explode('/', $_POST['cancel_date']);
						$cancel_date = gmmktime(gmdate('H'), gmdate('i'), gmdate('s'), (int) $cdate_array[0], (int) $cdate_array[1], (int) $cdate_array[2]);

						$_POST['msg'] = sprintf(__('<b>Selected members %1$s from %2$s.</b>', 'wishlist-member'), $cancelled_or_not, $wpm_levels[$wpm_membership_to]['name']);

						if ($cancel_date <= $todays_date && $cancelled_or_not == "Cancelled") {
							$this->LevelCancelled($wpm_membership_to, $wpm_member_id, $status);
						} else if ($cancelled_or_not == "Uncancelled") {
							$this->LevelCancelled($wpm_membership_to, $wpm_member_id, $status);
						} else if ($cancel_date > $todays_date && $cancelled_or_not == "Cancelled") {
							$this->ScheduleLevelDeactivation($wpm_membership_to, $wpm_member_id, $cancel_date );
							$_POST['msg'] = sprintf(__('<b>Selected members will be Cancelled from %1$s on %2$s.</b>', 'wishlist-member'), $wpm_levels[$wpm_membership_to]['name'], date('m/d/y', $cancel_date));
						}

					}

					// unconfirm/confirm membership level
					if ($wpm_unconfirm_membership || $wpm_confirm_membership) {
						$status = $wpm_unconfirm_membership ? true : false;
						$unconfirmed_or_not = $status ? __("Unconfirmed") : __("Confirmed");
						$this->LevelUnConfirmed($wpm_membership_to, $wpm_member_id, $status);
						$_POST['msg'] = sprintf(__('<b>Selected members %1$s for %2$s.</b>', 'wishlist-member'), $unconfirmed_or_not, $wpm_levels[$wpm_membership_to]['name']);
					}

					// unapprove/approve membership level
					if ($wpm_unapprove_membership || $wpm_approve_membership) {
						$status = $wpm_unapprove_membership ? true : false;
						$unapproved_or_not = $status ? __("Unapproved") : __("Approved");
						$approval = $this->LevelForApproval($wpm_membership_to, $wpm_member_id, $status);
						if ($wpm_approve_membership) {
							foreach($wpm_member_id as $m_id)
								$this->SendAdminApprovalNotification($m_id);
						}
						$_POST['msg'] = sprintf(__('<b>Selected members %1$s for %2$s.</b>', 'wishlist-member'), $unapproved_or_not, $wpm_levels[$wpm_membership_to]['name']);
					}
				}

				if($wpm_payperposts_to) {
					$post_type = get_post_type($wpm_payperposts_to);
					if($post_type) {
						if($wpm_add_payperposts || $wpm_del_payperposts){
							if($wpm_add_payperposts) {
								$this->AddPostUsers($post_type, $wpm_payperposts_to, $wpm_member_id);
								$_POST['msg'] = sprintf(__('<b>Post "%s" added to selected members</b>', 'wishlist-member'), get_the_title($wpm_payperposts_to));
							}else{
								$this->RemovePostUsers($post_type, $wpm_payperposts_to, $wpm_member_id);
								$_POST['msg'] = sprintf(__('<b>Post "%s" removed from selected members</b>', 'wishlist-member'), get_the_title($wpm_payperposts_to));
							}
						}
					}
				}

				// turn sequential upgrade on or off
				if ($wpm_disable_sequential || $wpm_enable_sequential) {
					$status = $wpm_enable_sequential ? true : false;
					$sequential_or_not = $status ? __("ENABLED") : __("DISABLED");
					$this->IsSequential($wpm_member_id, $status);
					$_POST['msg'] = sprintf(__('<b>Sequential Upgrade %s for selected members.</b>', 'wishlist-member'), $sequential_or_not);
				}

				if ($wpm_clear_scheduled) {
					foreach((array) $wpm_member_id AS $id) {
						$this->Delete_User_Scheduled_LevelsMeta($id);
					}
					$_POST['msg'] = __('<b>Scheduled Actions where cleared for the selected members.</b>', 'wishlist-member');
				}

				// delete selected members
				if ($wpm_delete_member) {
					foreach ((array) $wpm_member_id AS $id) {
						if ($id > 1) {
							$force_sync = true;
							wp_delete_user($id, 1);
						}
					}
					$_POST['msg'] = __('<b>Selected members DELETED.</b>', 'wishlist-member');
				}
			}
			if($force_sync) {
				$this->NODELETED_USER_HOOK = true;
			}
			$this->SyncMembership($force_sync);
		}

		/**
		 * Schedule a member to a certain level
		 * @param string action
		 * @param string level
		 * @param array member_ids
		 * @param string date
		 *
		 * @return false
		 */
		function ScheduleToLevel($action, $level, $member_ids = null, $date) {
			$wpm_levels = $this->GetOption('wpm_levels');
			$todays_date = strtotime(date("Y-m-d"));
			$sched_date = strtotime(date("Y-m-d", strtotime($date)));
			$message = '';
			$schedule_type = '';
			$meta_name = '';
			$is_current_level = false;

			if ($action == 'wpm_add_membership') {
				$message = __('ADDED to');
		        $schedule_type = 'add';
		        $meta_name = 'scheduled_add';
			} else if ($action == 'wpm_change_membership') {
				$message = __('MOVED to');
		        $schedule_type = 'move';
		        $meta_name = 'scheduled_move';
			} else {
				$message = __('REMOVED from');
		        $schedule_type = 'remove';
		        $meta_name = 'scheduled_remove';
			}

			foreach ((array) $member_ids as $id) {
				$levels = $this->GetMembershipLevels($id);

				if ($action == 'wpm_del_membership') {
					if($this->Get_UserLevelIndex($id, $level) && $sched_date) {
						$is_current_level = true;
					} else {
						$levels = array_diff($levels, (array) $level);
					}
				} else if ($action == 'wpm_change_membership' && !$sched_date) {
					unset($levels);
					$levels[] = $level;
				} else {
					$levels[] = $level;
				}
				$will_be = ' were ';
				$on_date = '';

		        if ($sched_date > $todays_date) {
					$cdate_array = explode('/', $date);
					$sched_date = gmmktime(gmdate('H'), gmdate('i'), gmdate('s'), (int) $cdate_array[0], (int) $cdate_array[1], (int) $cdate_array[2]);

					$schedule_data = array(
						'date' => gmdate('Y-m-d' . ' 00:00:00' , $sched_date),
						'type' => $schedule_type
					);

					$schedule_data['is_current_level'] = $is_current_level;

					if(!$this->Get_UserLevelIndex($id, $level)) {
						$xLevels = array('Levels' => array_unique($levels), 'Metas' => array());
						$xLevels['Metas'][$level] = array(array($meta_name, $schedule_data));
						$this->SetMembershipLevels($id, (object) $xLevels, true);
					}
					// $this->Update_UserLevelMeta($id, $level, $meta_name, $schedule_data);

					$will_be = ' will be ';
					$on_date = ' on ' . date('m/d/y', $sched_date);
		        } else {
					$this->SetMembershipLevels($id, array_unique($levels));
		        }
			}

			$_POST['msg'] = sprintf(__('<b>Selected members %1$s%2$s %3$s%4$s.</b>', 'wishlist-member'), $will_be, $message, $wpm_levels[$level]['name'], $on_date);
		}


		/**
		 * Count Non-members
		 * @global object $wpdb
		 * @return int
		 */
		function NonMemberCount() {
			global $wpdb;
			// total users in database
			$x = $wpdb->get_var("SELECT COUNT(*) FROM `{$wpdb->users}`");
			// total users as members
			$y = $this->MemberCount();
			return $x - $y;
		}

		/**
		 * Count Members
		 * @global object $wpdb
		 * @return int
		 */
		function MemberCount() {
			global $wpdb;
			return $wpdb->get_var($x = "SELECT COUNT(DISTINCT `user_id`) FROM `{$this->Tables->userlevels}`");
		}

		/**
		 * Get Member IDs
		 * @global object $wpdb
		 * @param array $levels (optional) Level IDs
		 * @param boolean $groupbylevel (optional) Whether to group the Member IDs by Level ID
		 * @param boolean $countonly (optional) True to return only the number of IDs found
		 * @return array
		 */
		function MemberIDs($levels = null, $groupbylevel = null, $countonly = null) {
			global $wpdb;
			if (is_null($groupbylevel))
				$groupbylevel = false;
			if (is_null($countonly))
				$countonly = false;
			if (!is_null($levels)) {
				$levels = (array) $levels;
				foreach ($levels AS $k => $v) {
					$levels[$k] = (int) $v;
				}
			} else {
				$levels = WishListMember_Level::GetAllLevels();
			}
			$levels_implode = "'" . implode("','", $levels) . "'";

			if ($groupbylevel == true) {
				$ids = array();
				foreach ($levels AS $level) {
					if ($countonly) {
						$query = $wpdb->prepare("SELECT COUNT(DISTINCT `user_id`) FROM `{$this->Tables->userlevels}` WHERE `level_id`=%d ORDER BY `user_id`", $level);
						$ids[$level] = $wpdb->get_var($query);
					} else {
						$query = $wpdb->prepare("SELECT DISTINCT `user_id` FROM `{$this->Tables->userlevels}` WHERE `level_id`=%d ORDER BY `user_id`", $level);
						$ids[$level] = $wpdb->get_col($query);
					}
				}
			} else {
				if ($countonly) {
					$query = "SELECT COUNT(DISTINCT `user_id`) FROM `{$this->Tables->userlevels}` WHERE `level_id` IN ($levels_implode) ORDER BY `user_id`";
					$ids = $wpdb->get_var($query);
				} else {
					$query = "SELECT DISTINCT `user_id` FROM `{$this->Tables->userlevels}` WHERE `level_id` IN ($levels_implode) ORDER BY `user_id`";
					$ids = $wpdb->get_col($query);
				}
			}
			return $ids;
		}

		/**
		 * Retrieve Member IDs by Status
		 * note: 'status=active' is only accurate for calls with single level
		 * @global object $wpdb
		 * @param string $status Any of cancelled, unconfirmed or forapproval
		 * @param array (optional) $levels Level IDs
		 * @param boolean $groupbylevel (optional) Whether to group the Member IDs by Level ID
		 * @param boolean $countonly (optional) True to return only the number of IDs found
		 * @return array
		 */
		function MemberIDsByStatus($status, $levels = null, $groupbylevel = null, $countonly = null) {
			global $wpdb;
			if (is_null($groupbylevel))
				$groupbylevel = false;
			if (is_null($countonly))
				$countonly = false;

			$status = trim(strtolower($status));
			if (!in_array($status, array('cancelled', 'unconfirmed', 'forapproval', 'active'))) {
				return false;
			}


			if (!is_null($levels)) {
				$levels = (array) $levels;
				foreach ($levels AS $k => $v) {
					$levels[$k] = (int) $v;
				}
			} else {
				$levels = WishListMember_Level::GetAllLevels();
			}

			$levels_implode = "'" . implode("','", $levels) . "'";

			$select = "DISTINCT `user_id`";
			if($countonly) {
				$select = "COUNT(DISTINCT `user_id`)";
			}

			// Special handling for active
			if($status == 'active') {
				$found = array();
				foreach(array('cancelled', 'unconfirmed', 'forapproval') as $s) {
					foreach($levels as $level) {
						$found = array_merge($found, $this->MemberIDsByStatus($s, $level, false, false));
					}
				}

				$expired = $this->ExpiredMembersID();
				foreach($expired as $l => $v) {
					if(in_array($l, $levels)) {
						$found = array_merge($found, $v);
					}
				}

				$found = implode(', ', array_unique($found));
				if(empty($found)) {
					$found = 0;
				}

				if($groupbylevel == true) {
					$ids = array();
					foreach($levels as $level) {
						// $query = $wpdb->prepare("SELECT $select FROM `{$this->Tables->userlevels}` `ul` WHERE `ul`.`user_id` NOT IN (".$found.") AND `ul`.`level_id` IN (".$level.")");
						$query = "SELECT $select FROM `{$this->Tables->userlevels}` `ul` WHERE `ul`.`user_id` NOT IN (".$found.") AND `ul`.`level_id` IN (".$level.")";
						$ids[$level] = $wpdb->get_col($query);
					}
				} else {
					$query = $wpdb->prepare("SELECT $select FROM `{$this->Tables->userlevels}` `ul` WHERE `ul`.`user_id` NOT IN (".$found.") AND `ul`.`level_id` IN (".implode(',', $levels).")");
					$ids = $wpdb->get_col($query);

				}

				return $ids;
			}

			//
			if ($groupbylevel == true) {
				$ids = array();
				foreach ($levels AS $level) {
					$query = $wpdb->prepare("SELECT $select FROM `{$this->Tables->userlevels}` `ul` LEFT JOIN `{$this->Tables->userlevel_options}` `ulm` ON `ul`.`ID`=`ulm`.`userlevel_id` WHERE `ul`.`level_id` = %d AND `ulm`.`option_name`='%s' AND `ulm`.`option_value`='1' ORDER BY `ul`.`user_id`", $level, $status);
					$ids[$level] = $wpdb->get_col($query);
				}
			} else {
				$query = $wpdb->prepare("SELECT $select FROM `{$this->Tables->userlevels}` `ul` LEFT JOIN `{$this->Tables->userlevel_options}` `ulm` ON `ul`.`ID`=`ulm`.`userlevel_id` WHERE `ul`.`level_id` IN ({$levels_implode}) AND `ulm`.`option_name`='%s' AND `ulm`.`option_value`='1' ORDER BY `ul`.`user_id`", $status);
				$ids = $wpdb->get_col($query);
			}
			return $ids;
		}

		/**
		 * Return Cancelled Member IDs
		 * @param array (optional) $levels Level IDs
		 * @param boolean $groupbylevel (optional) Whether to group the Member IDs by Level ID
		 * @param boolean $countonly (optional) True to return only the number of IDs found
		 * @return array
		 */
		function CancelledMemberIDs($levels = null, $groupbylevel = null, $countonly = null) {
			return $this->MemberIDsByStatus('cancelled', $levels, $groupbylevel, $countonly);
		}

		/**
		 * Return Unconfirmed Member IDs
		 * @param array (optional) $levels Level IDs
		 * @param boolean $groupbylevel (optional) Whether to group the Member IDs by Level ID
		 * @param boolean $countonly (optional) True to return only the number of IDs found
		 * @return array
		 */
		function UnConfirmedMemberIDs($levels = null, $groupbylevel = null, $countonly = null) {
			return $this->MemberIDsByStatus('unconfirmed', $levels, $groupbylevel, $countonly);
		}

		/**
		 * Return For Approval Member IDs
		 * @param array (optional) $levels Level IDs
		 * @param boolean $groupbylevel (optional) Whether to group the Member IDs by Level ID
		 * @param boolean $countonly (optional) True to return only the number of IDs found
		 * @return array
		 */
		function ForApprovalMemberIDs($levels = null, $groupbylevel = null, $countonly = null) {
			return $this->MemberIDsByStatus('forapproval', $levels, $groupbylevel, $countonly);
		}

		/**
		 * Synchronize Membership Data
		 * @global object $wpdb
		 */
		function SyncMembership($force_sync = false) {
			global $wpdb;

			$userlevelsTable = $this->Tables->userlevels;
			$userlevelsTableOptions = $this->Tables->userlevel_options;
			$userTableOptions = $this->Tables->user_options;

			if (!get_transient('WLM_delete') OR $force_sync){
				$deleted = 0;
				//$deleted += $wpdb->query("DELETE FROM `{$userlevelsTable}` WHERE `user_id` NOT IN (SELECT `ID` FROM {$wpdb->users})");
				$deleted += $wpdb->query("DELETE {$userlevelsTable} FROM `{$userlevelsTable}` LEFT JOIN `{$wpdb->users}` ON `{$userlevelsTable}`.`user_id` = `{$wpdb->users}`.`ID` WHERE `{$wpdb->users}`.`ID` IS NULL");
				//$deleted += $wpdb->query("DELETE FROM `{$userTableOptions}` WHERE `user_id` NOT IN (SELECT `ID` FROM {$wpdb->users})");
				$deleted += $wpdb->query("DELETE {$userTableOptions} FROM `{$userTableOptions}` LEFT JOIN `{$wpdb->users}` ON `{$userTableOptions}`.`user_id` = `{$wpdb->users}`.`ID` WHERE `{$wpdb->users}`.`ID` IS NULL");
				//$deleted += $wpdb->query("DELETE FROM `{$userlevelsTableOptions}` WHERE `userlevel_id` NOT IN (SELECT `ID` FROM {$userlevelsTable})");
				$deleted += $wpdb->query("DELETE {$userlevelsTableOptions} FROM `{$userlevelsTableOptions}` LEFT JOIN `{$userlevelsTable}` ON `{$userlevelsTableOptions}`.`userlevel_id` = `{$userlevelsTable}`.`ID` WHERE `{$userlevelsTable}`.`ID` IS NULL");

				set_transient('WLM_delete', 1, 60*60);

				wlm_cache_flush();
				WishListMember_Level::UpdateLevelsCount();
			}
		}

		/**
		 * Is Pending returns true if at least one of the user's levels is for admin approval and false otherwise
		 * @param integer $uid User ID
		 * @return boolean
		 */
		function IsPending($uid) {
			$user = new WishListMemberUser($uid);
			foreach ($user->Levels AS $level) {
				if ($level->Pending)
					return true;
			}
			return false;
		}

		/**
		 * Get/Set User Sequential Upgrade status
		 * @global object $wpdb
		 * @param array $uid User IDs
		 * @param int $status (optional) 0|1
		 * @return int 0|1
		 */
		function IsSequential($uid, $status = null) {
			global $wpdb;
			$uid = (array) $uid;
			if (!is_null($status)) {
				$status = (int) $status;
				foreach ($uid AS $id) {
					$this->Update_UserMeta((int) $id, 'sequential', $status);
				}
			}
			list($id) = $uid;
			return $this->Get_UserMeta($id, 'sequential');
		}

		/**
		 * Save Sequential Upgrade Configuration
		 */
		function SaveSequential() {
			$wpm_levels = $this->GetOption('wpm_levels');
			$err = array();
			$err_levels = array();
			$saved = array();
			$this->set_timezone_to_wp();
			foreach (array_keys($wpm_levels) AS $key) {
				$upgrade_on_date = strtotime($_POST['upgradeOnDate'][$key]);

				if ($_POST['upgradeMethod'][$key] == 'inactive') {
					$wpm_levels[$key]['upgradeMethod'] = '';
					$wpm_levels[$key]['upgradeTo'] = '';
					;

					$wpm_levels[$key]['upgradeSchedule'] = '';
					$wpm_levels[$key]['upgradeAfter'] = '';

					$wpm_levels[$key]['upgradeAfterPeriod'] = '';
					$wpm_levels[$key]['upgradeOnDate'] = '';

				} else {
					if (empty($_POST['upgradeMethod'][$key]) && !empty($_POST['upgradeTo'][$key])) {
						$err[] = sprintf(__('No "Method" was specified for Membership Level "%s"', 'wishlist-member'), $wpm_levels[$key]['name']);
						$err_levels[$key][] = 'wlm_sequential_error_upgrade_method';
						continue;
					}
					if (empty($_POST['upgradeTo'][$key]) && !empty($_POST['upgradeMethod'][$key])) {
						$err[] = sprintf(__('No Membership Level to "Upgrade To" was specified for Membership Level "%s"', 'wishlist-member'), $wpm_levels[$key]['name']);
						$err_levels[$key][] = 'wlm_sequential_error_upgrade_to';
						continue;
					}
					if ($_POST['upgradeMethod'][$key] == 'MOVE' && empty($_POST['upgradeSchedule'][$key]) && !((int) $_POST['upgradeAfter'][$key])) {
						$err[] = sprintf(__('Members cannot be Moved to another Level after 0 days. Please select a number of Days or change the Sequential Upgrade Method in Membership Level "%s".', 'wishlist-member'), $wpm_levels[$key]['name']);
						$err_levels[$key][] = 'wlm_sequential_error_upgrade_schedule';
						continue;
					}
					if ($_POST['upgradeSchedule'][$key] == 'ondate' && $upgrade_on_date < 1) {
						$err[] = sprintf(__('Invalid Date in Membership Level "%s".', 'wishlist-member'), $wpm_levels[$key]['name']);
						$err_levels[$key][] = 'wlm_sequential_error_upgrade_schedule';
						continue;
					}

					if ($_POST['upgradeSchedule'][$key] == 'ondate') {

						$date_now = strtotime(date("Y-m-d H:i:s"));

						if(empty($wpm_levels[$key]['upgradeOnDate'])) {
							// If there's no set ondate yet then only allow future dates
							if($upgrade_on_date < $date_now) {
								$err[] = sprintf(__('The selected date for the Sequential Upgrade has passed.  Please select an upcoming date for Membership Level "%s".', 'wishlist-member'), $wpm_levels[$key]['name']);
								$err_levels[$key][] = 'wlm_sequential_error_upgrade_schedule';
								continue;
							}
						} else {
							// If the saved ondate is in the past then we allow user to changed it up to the set date
							if($wpm_levels[$key]['upgradeOnDate'] < $date_now) { 
								if($upgrade_on_date < $wpm_levels[$key]['upgradeOnDate']) {
									$err[] = sprintf(__('The selected date for the Sequential Upgrade has passed for Membership Level "%s".', 'wishlist-member'), $wpm_levels[$key]['name']);
									$err_levels[$key][] = 'wlm_sequential_error_upgrade_schedule';
									continue;
								}
							} elseif($upgrade_on_date < $date_now) {
								$err[] = sprintf(__('The selected date for the Sequential Upgrade has passed for Membership Level "%s".', 'wishlist-member'), $wpm_levels[$key]['name']);
								$err_levels[$key][] = 'wlm_sequential_error_upgrade_schedule';
								continue;
							}
							
						}
					}

					if (empty($_POST['upgradeMethod'][$key]) OR empty($_POST['upgradeTo'][$key])) {
						continue;
					}

					$wpm_levels[$key]['upgradeMethod'] = $_POST['upgradeMethod'][$key];
					$wpm_levels[$key]['upgradeTo'] = $_POST['upgradeTo'][$key];

					$wpm_levels[$key]['upgradeSchedule'] = $_POST['upgradeSchedule'][$key];
					$wpm_levels[$key]['upgradeAfter'] = (int) $_POST['upgradeAfter'][$key];

					$wpm_levels[$key]['upgradeAfterPeriod'] = $wpm_levels[$key]['upgradeAfter'] ? $_POST['upgradeAfterPeriod'][$key] : '';
					$wpm_levels[$key]['upgradeOnDate'] = ($upgrade_on_date < 1) ? '' : $upgrade_on_date;
				}


				$saved[] = $wpm_levels[$key]['name'];
			}
			if (count($saved)) {
				$this->SaveOption('wpm_levels', $wpm_levels);
				$_POST['msg'] = sprintf(__('Sequential Upgrade settings saved for Membership Level%s %s', 'wishlist-member'), count($saved) > 1 ? 's' : '', '"' . implode('", "', $saved) . '"');
			}
			if ($err) {
				$_POST['sequential_err_msg'] = '<br>' . implode('<br>', $err);
			}
			$_POST['err_levels'] = $err_levels;
		}

		/**
		 * Get Active Levels of a Member
		 * @param integer $id User ID
		 * @return array
		 */
		function GetMemberActiveLevels($id) {
			return (array) $this->GetMembershipLevels($id, false, true);
		}

		/**
		 * Get Inactive Levels of a Member
		 * @param integer $id User ID
		 * @return array
		 */
		function GetMemberInactiveLevels($id) {
			$all = (array) $this->GetMembershipLevels($id, false);
			$active = $this->GetMemberActiveLevels($id);
			return array_diff($all, $active);
		}

		/**
		 * Return Member's Membership Levels
		 * @global object $wpdb
		 * @param int $id User ID
		 * @param boolean $names (optional) Default False. TRUE to return Level names instead of IDs
		 * @param boolean $activeOnly (optional) Default False. TRUE to return active levels only
		 * @param boolean $no_cache (optional) Default False. TRUE to skip cache data
		 * @param boolean $no_userlevels (optional) Default False. TRUE to exclude User Level (U-xxx)
		 * @param boolean $include_scheduled (optional) Default TRUE. FALSE to exclude scheduled levels
		 * @return array Levels
		 */
		function GetMembershipLevels($id, $names = null, $activeOnly = null, $no_cache = null, $no_userlevels = null, $include_scheduled = null) {
			global $wpdb;
			$names = is_null($names) ? false : $names;
			$activeOnly = is_null($activeOnly) ? false : $activeOnly;
			$no_cache = is_null($no_cache) ? false : $no_cache;
			$no_userlevels = is_null($no_userlevels) ? false : $no_userlevels;
			if (is_null($include_scheduled))
				$include_scheduled = true;

			$levels = ($no_cache === true) ? false : wlm_cache_get($id, $this->Tables->userlevels);

			#empty user == no membership levels
			if (empty($id)) {
				return array();
			}

			if ($levels === false) {
				if($include_scheduled) {
					$query = $wpdb->prepare("SELECT `level_id` FROM `{$this->Tables->userlevels}` WHERE `user_id`=%d", $id);
				} else {
					$query = $wpdb->prepare("SELECT `level_id`,MAX(`option_name`='scheduled_move' OR `option_name`='scheduled_add' OR `option_name`='scheduled_remove') `scheduled` FROM `{$this->Tables->userlevels}` `l` LEFT JOIN `{$this->Tables->userlevel_options}` `lo` ON `l`.`ID`=`lo`.`userlevel_id` WHERE `user_id`=%d GROUP BY `level_id` HAVING `scheduled`=0", $id);
				}
				$levels = $wpdb->get_col($query);
				wlm_cache_set($id, $levels, $this->Tables->userlevels);
			}


			if ($names) {
				$wpm_levels = $this->GetOption('wpm_levels');
				$names = array();
				foreach ((array) $levels AS $level) {
					$name = $wpm_levels[$level]['name'];
					if ($this->LevelCancelled($level, $id) OR $this->LevelForApproval($level, $id) OR $this->LevelUnConfirmed($level, $id) OR $this->LevelExpired($level, $id)
					) {

						$name = '<strike>' . $name . '</strike>';
					}
					$names[] = $name;
				}
				return implode(', ', $names);
			} else {
				if ($activeOnly) {

					foreach ((array) $levels AS $key => $level) {
						if ($this->IsLevelScheduled($level, $id) OR $this->LevelCancelled($level, $id) OR $this->LevelForApproval($level, $id) OR $this->LevelUnConfirmed($level, $id) OR $this->LevelExpired($level, $id)
						) {
							unset($levels[$key]);
						}
					}
					$levels = array_merge($levels, array());
				}
				if (!$no_userlevels) {
					// force individual user level
					$levels[] = 'U-' . $id;
				}
				return $levels;
			}
		}

		/**
		 * @global object $wpdb
		 */

		/**
		 * Set Member's Membership Levels
		 * @global object $wpdb
		 * @param int $id User ID
		 * @param array $levels Level IDs
		 * @param boolean $noautoresponder Set to TRUE to disable autoresponder
		 * @param boolean $timestamp_noset Set to TRUE to disable setting of timestamp
		 * @param boolean $transaction_id_noset Set to TRUE to disable setting of transaction ID
		 * @param boolean $nosync Set to TRUE to prevent calling SyncMembership
		 * @param boolean $nowebinar Set to TRUE to disable webinar
		 * @param type $pendingautoresponder
		 * @param type $keep_existing_levels Set to TRUE to keep existing levels not passed in $levels
		 * @return boolean
		 */
		function SetMembershipLevels($id, $levels, $noautoresponder = null, $timestamp_noset = null, $transaction_id_noset = null, $nosync = null, $nowebinar = null, $pendingautoresponder = null, $keep_existing_levels = null, $registration = false ) {
			global $wpdb;
			$id = (int) $id;

			if( empty($id) )
				return false;

			if(is_object($levels)) {
				$levels_metas      = (array) $levels->Metas;
				$to_removed_levels = isset( $levels->To_Removed_Levels ) ? (array) $levels->To_Removed_Levels : array();
				$levels            = (array) $levels->Levels;
			} else {
				$levels_metas      = array();
				$to_removed_levels = array();
				$levels            = (array) $levels;
			}

			$this->SetPayPerPost($id, $levels);
			if (is_null($noautoresponder))
				$noautoresponder = false;
			if (is_null($timestamp_noset))
				$timestamp_noset = false;
			if (is_null($transaction_id_noset))
				$transaction_id_noset = false;
			if (is_null($nosync))
				$nosync = false;
			if (is_null($nowebinar))
				$nowebinar = false;
			if (is_null($keep_existing_levels))
				$keep_existing_levels = false;

			// moved setting of $wpm_levels to top of method

			$wpm_levels = $this->GetOption('wpm_levels');
			if (count($levels)) {
				// we now use the ValidateLevels method to clear the $levels array of invalid Level IDs
				$validated = $this->ValidateLevels($levels, null, true, true);
				// at least one level was invalid so we stop
				if (!$validated) {
					return false;
				}
			}

			$current_levels = $this->GetMembershipLevels($id, null, null, true);
			if ($keep_existing_levels) {
				$levels = array_unique(array_merge($current_levels, $levels));
			}

			$removed_levels = $new_levels = array();
			$this->ArrayDiff($levels, $current_levels, $removed_levels, $new_levels);
			$removed_levels = array_unique( (array) $removed_levels );
			$new_levels     = array_unique( (array) $new_levels );

			// check for child levels, dont remove them
			// Added this line for "Add To" feature to work.
			// Without this, "Add To" wont work on Registrations that uses temp accounts.
			// because leves resets after merge, deleting add to levels. //fjpalawan
			if ( count( $new_levels ) <= 0 && $registration ) {
				foreach ( $current_levels as $key => $value ) {
					$parent = $this->LevelParent( $value, $id );
					$the_key = array_search( $value, $removed_levels );
					if( $parent && $the_key !== false && in_array( $parent, $levels ) ) {
						unset( $removed_levels[$the_key] ); // never removed the child of parent
					}
				}
				$removed_levels = array_unique( array_merge($to_removed_levels,$removed_levels) );
			}

			if (count($removed_levels)) {
				do_action('wishlistmember_pre_remove_user_levels', $id, $removed_levels);
				// remove from removed_levels
				$rlevels = "'" . implode("','", $removed_levels) . "'";
				$wpdb->query("DELETE FROM `{$this->Tables->userlevels}` WHERE `user_id`={$id} AND `level_id` IN ({$rlevels})");
			}

			// add to new levels
			foreach ((array) $new_levels AS $level) {
				$data = array(
					'user_id' => $id,
					'level_id' => $level
				);
				$wpdb->insert($this->Tables->userlevels, $data);
			}

			wlm_cache_delete($id, $this->Tables->userlevels);

			if (count($new_levels)) {
				/*
				 * update timestamps
				 */
				if ($timestamp_noset == false) {
					$ts = array_combine($new_levels, array_fill(0, count($new_levels), time()));
					$this->UserLevelTimestamps($id, $ts);
				}
				/*
				 * end timestamps update
				 */

				/*
				 * set initial transaction id
				 */
				if ($transaction_id_noset == false) {
					$txn = array_combine($new_levels, array_fill(0, count($new_levels), ''));
					$this->SetMembershipLevelTxnIDs($id, $txn);
				}
				/*
				 * end setting initial transaction id
				 */

				foreach($new_levels AS $new_level) {
					if(isset($levels_metas[$new_level])) {
						foreach((array) $levels_metas[$new_level] AS $level_meta) {
							if(is_array($level_meta) && count($level_meta) == 2) {
								list($meta, $value) = $level_meta;
								$this->Update_UserLevelMeta($id, $new_level, $meta, $value);
							}
						}
					}
				}
			}


			// autoresponder
			if (!$noautoresponder) {
				$usr = $this->Get_UserData($id);
				if ($usr->ID) {
					// unsubscribe from autoresponder
					foreach ((array) $removed_levels AS $rl) {
						$this->ARUnsubscribe($usr->first_name, $usr->last_name, $usr->user_email, $rl);
					}

					//if no flags we're set, add the member to AR list
					if (empty($pendingautoresponder)) {
						// subscribe to autoresponder
						foreach ((array) $new_levels AS $nl) {
							if (!$this->LevelCancelled($nl, $id)) {
								$this->ARSubscribe($usr->first_name, $usr->last_name, $usr->user_email, $nl);
							}
						}
					} else {
						foreach ($pendingautoresponder as $value) {
							$this->Add_UserLevelMeta($id, $level, $value, 1);
						}
					}
				}
			}
			// we now also set autoresponder on the temp account
			else {
				foreach ((array) $pendingautoresponder as $value) {
					$this->Add_UserLevelMeta($id, $level, $value, 1);
				}
			}

			if (!$nowebinar) {
				// do webinar stuff;
				foreach ((array) $new_levels AS $nl) {
					$this->WebinarSubscribe($usr->first_name, $usr->last_name, $usr->user_email, $nl);
				}
			}

			// trigger remove_user_levels action if a user is removed from at least one level
			if (count($removed_levels)) {
				do_action('wishlistmember_remove_user_levels', $id, $removed_levels, $new_levels);
			}
			// trigger add_user_levels action if a user is added to at least one level
			if (count($new_levels)) {
				do_action('wishlistmember_add_user_levels', $id, $new_levels, $removed_levels);
			}

			wlm_cache_delete($id, $this->Tables->userlevels);

			if (!$nosync) {
				$this->SyncMembership();
			}

			return array('added' => $new_levels, 'removed' => $removed_levels);
		}

		/**
		 * Get / Set User Level Timestamp
		 * @param int $id User ID
		 * @param int $level Level ID
		 * @param int $timestamp (optional)
		 * @return int Timestamp
		 */
		function UserLevelTimestamp($id, $level, $timestamp = null, $adjust_user_registration_date = null) {
			static $uid, $ureg;

			$id = (int) $id;
			if ($uid != $id) {
				$ureg = $this->Get_UserData($id);
				$ureg = $this->UserRegistered($ureg, false);
				$uid = $id;
			}
			//moving this outside the if statement above and making $ulevels non static because it causes issue on seq upgrade build 1263
			$ulevels = $this->GetMembershipLevels($id, false);

			if (!in_array($level, $ulevels)) {
				return false;
			}

			if (is_numeric($timestamp)) {
				if ($timestamp < $ureg) {
					if ($adjust_user_registration_date) {
						$ureg = $timestamp;
						wp_update_user(array(
							'ID' => $id,
							'user_registered' => gmdate('Y-m-d H:i:s', $timestamp)
						));
					} else {
						$timestamp = $ureg;
					}
				}
				$fraction = $timestamp - (int) $timestamp;
				$timestamp = (int) $timestamp;
				$this->Update_UserLevelMeta($id, $level, 'registration_date', gmdate('Y-m-d H:i:s#' . $fraction, $timestamp));
			}

			list($date, $fraction) = explode('#', $this->Get_UserLevelMeta($id, $level, 'registration_date'));
			if (empty($date)) {
				$ts = $ureg;
			} else {
				list($year, $month, $day, $hour, $minute, $second) = preg_split('/[- :]/', $date);
				$ts = gmmktime($hour, $minute, $second, $month, $day, $year) + $fraction;
				if ($ts < $ureg) {
					$ts = $ureg;
				}
			}
			return $ts;
		}

		/**
		 * Get/Set Timestamps for a Member's Levels
		 * @param int $id User ID
		 * @param array $levels Associative array (LevelID=>Timestamp). If parameter passed is not an array then method will not set anything
		 * @return array Associative array (LevelID=>Timestamp)
		 */
		function UserLevelTimestamps($id, $levels = null) {
			if (is_array($levels)) {
				foreach ($levels AS $level_id => $timestamp) {
					$this->UserLevelTimestamp($id, $level_id, $timestamp);
				}
			}
			$levels = $this->GetMembershipLevels($id);
			$levels = array_flip($levels);
			foreach (array_keys($levels) AS $level) {
				$ts = $this->UserLevelTimestamp($id, $level);
				$levels[$level] = $ts;
			}
			asort($levels);
			return $levels;
		}

		/**
		 * Move/Add Users from one Level to another
		 */
		function MoveMembership() {
			global $wpdb;
			extract($_POST);
			$wpm_levels = $this->GetOption('wpm_levels');
			if ($wpm_from == 'NONMEMBERS'){
				$ids = $wpdb->get_col("SELECT `ID` FROM `{$wpdb->users}` WHERE `ID` NOT IN (SELECT DISTINCT `user_id` FROM `{$this->Tables->userlevels}`)");
			}else{
				$ids = $this->MemberIDs($wpm_from);
			}
			if ($wpm_move) {
				foreach ($ids AS $id) {
					$this->SetMembershipLevels($id, $wpm_to, true);
					echo "<!-- {$id} -->";
				}
			} elseif ($wpm_add) {
				foreach ($ids AS $id) {
					$levels = $this->GetMembershipLevels($id);
					$levels[] = $wpm_to;
					$this->SetMembershipLevels($id, $levels, true);
					echo "<!-- {$id} -->";
				}
			}
			if ($wpm_move || $wpm_add) {
                $force_sync = true;
				$this->SyncMembership($force_sync);
				$_POST['msg'] = __('<b>Membership level access updated.</b>', 'wishlist-member');
			}
		}

		/**
		 * reCaptcha Response
		 * @return boolean
		 */
		function reCaptchaResponse() {
			/* recaptcha */
			$recaptcha = true;
			if (isset($_POST['g-recaptcha-response'])) {
				$recaptcha_public = $this->GetOption('recaptcha_public_key');
				$recaptcha_private = $this->GetOption('recaptcha_private_key');
				if ($recaptcha_public && $recaptcha_private) {
					if (!function_exists('recaptcha_verify')) {
						require_once($this->pluginDir . '/extlib/recaptchalib.php');
					}
					$recaptcha = recaptcha_verify($recaptcha_private, $_SERVER["REMOTE_ADDR"], $_POST["g-recaptcha-response"]);

					if ($recaptcha->is_valid) {
						$recaptcha = true;
					} else {
						$recaptcha = false;
					}
				}
			}
			return $recaptcha;
			/* end recaptcha */
		}

		/**
		 * WPMRegister
		 * Registers new users to WordPress and
		 * assigns the correct membership level
		 *
		 * @global object $wpdb WordPress database object
		 * @param array $data User data array
		 * @param string $wpm_errmsg Passed by reference, we save the error message here
		 * @param boolean $send_welcome_email True to send registration email or not
		 * @param boolean $notifyadmin True to notify admin via email of this registration
		 * @param integer $passmin Minimum password length. Defaults to user specified length in settings section
		 * @return integer|boolean User ID on success or false on error
		 */
		function WPMRegister( $data, &$wpm_errmsg, $send_welcome_email = true, $notifyadmin = true, $passmin = null, $pendingstatus = null ) {
			global $wpdb;

			/* include the required wordpress functions */
			require_once(ABSPATH . WPINC . '/pluggable.php');
			require_once(ABSPATH . WPINC . '/registration.php');

			$registered_by_admin = true === wlm_admin_in_admin();

			// Fix for the issue where Wordpress MU doesn't allow Uppercase Usernames
			remove_filter( 'sanitize_user', 'strtolower' );

			$custom_fields = array();
			if ( ! empty( $_POST['custom_fields'] ) ) {
				$custom_fields = explode( ',', $_POST['custom_fields'] );
			}


			$required_fields = array();
			if ( ! empty( $_POST['required_fields'] ) ) {
				$required_fields = explode( ',', $_POST['required_fields'] );
			}

			$custom_form = isset( $_POST['custom_fields'] ) && isset( $_POST['required_fields'] );

			$required_error  = false;
			$required_fields = array_intersect( $required_fields, $custom_fields );
			foreach ( $required_fields AS $required_field ) {
				
			 	$value=trim($_POST[$required_field]);
				if ( (empty( $value )) && ( $value!='0') ) {
					$required_error = true;
					break;
				}
			}
			
			/* remove fields that go into the wp profile */
			$custom_fields = array_diff( $custom_fields, array( 'website', 'aim', 'yim', 'jabber', 'biography', 'nickname', 'firstname', 'lastname' ) );
			/* remove fields that go into wpm_useraddress */
			$custom_fields = array_diff( $custom_fields, array( 'company', 'address1', 'address2', 'city', 'state', 'zip', 'country' ) );

			/* determine the minimum password length */
			if ( is_null( $passmin ) )
				$passmin = $this->GetOption( 'min_passlength' );
			$passmin+=0;
			if ( ! $passmin )
				$passmin = 8;

			/*
			 * are we merging? if so, load $mergewith with
			 * data of user to merge with. $mergewith is used
			 * to merge temp accounts generated by shopping
			 * cart registrations to the user info provided
			 * by the user when he completes the registration
			 */
			if ( $data['mergewith'] )
				$mergewith = $this->Get_UserData( $data['mergewith'] );

			/* is this a temp account? */
			$tempacct = $data['email'] == 'temp_' . md5( $data['orig_email'] );

			/* load membership levels */
			$wpm_levels = $this->GetOption( 'wpm_levels' );

			/* load blacklist data */
			$blacklist = $this->CheckBlackList( $data['email'] );

			/* Check if for approval registration */
			$is_forapproval = $this->IsForApprovalRegistration( $data['wpm_id'] );
			if ( $is_forapproval ) {
				$pendingstatus = "Registered For Approval";
				$wpm_newid     = time();
				sleep( 1 );
				if ( $is_forapproval['name'] == "PinPayments" ) {
					$data['sctxnid'] = "SP-" . $data['wpm_id'] . "-" . $wpm_newid;
					$pendingstatus   = "Pin Payments Confirmation";
				}
				$data['wpm_id']      = $is_forapproval["level"];
				$registered_by_admin = false;
			}

			/* blacklist checking */
			if ( $blacklist ) {
				switch ( $blacklist ) {
					case 1:
						$wpm_errmsg = $this->GetOption( 'blacklist_email_message' );
						break;
					case 2:
						$wpm_errmsg = $this->GetOption( 'blacklist_ip_message' );
						break;
					case 3:
						$wpm_errmsg = $this->GetOption( 'blacklist_email_ip_message' );
						break;
				}
				return false;
			}
			/* validate username */
			if ( ! trim( $data['username'] ) OR ! validate_username( $data['username'] ) ) {
				$wpm_errmsg = __( 'Please enter a username', 'wishlist-member' );
				return false;
			}
			/* check username length - cannot be more than 50 characters */
			if ( strlen($data['username']) > 50 ) {
				$wpm_errmsg = __( 'Username cannot be more than 50 characters in length. Please enter a shorter username.', 'wishlist-member' );
				return false;
			}
			/* check if username already exists */
			if ( username_exists( $data['username'] ) ) {
				$wpm_errmsg = __( 'The username you chose already exists.  Please try another one.', 'wishlist-member' );
				if ( wlm_arrval( $_GET, 'reg' ) && empty( $wpm_levels[wlm_arrval( $_GET, 'reg' )]['disableexistinglink'] ) ) {
					if ( $this->GetOption( 'FormVersion' ) == 'improved' ) {
						$wpm_errmsg.='<br /><br />' . __( 'If you are already a member and are upgrading your membership access, please select the "I have an existing account" option below.', 'wishlist-member' );
					} else {
						$wpm_errmsg.='<br /><br />' . __( 'If you are already a member and are upgrading your membership access, please click the "Existing Members" link below.', 'wishlist-member' );
					}
				}
				return false;
			}
			/* check for firstname and lastname */
			if ( ! (trim( $data['firstname'] ) AND trim( $data['lastname'] )) AND ! $custom_form AND ! $registered_by_admin ) {
				$wpm_errmsg = __( 'Please enter your first name and your last name.', 'wishlist-member' );
				return false;
			}
			/* validate email */
			if ( ! wlm_is_email( trim( $data['email'] ) ) AND ! (wlm_is_email( $data['orig_email'] ) AND $tempacct) ) {
				$wpm_errmsg = __( 'Please enter a valid email address.', 'wishlist-member' );
				return false;
			}
			/* check if email already exists */
			if ( email_exists( $data['email'] ) AND $mergewith->user_email != $data['email'] ) {
				$wpm_errmsg = __( 'The email you entered is already in our database.', 'wishlist-member' );
				return false;
			}
			/* check email length - cannot be more than 100 characters */
			if ( strlen($data['email']) > 100 ) {
				$wpm_errmsg = __( 'Email address cannot be more than 100 characters in length. Please enter a shorter email address.', 'wishlist-member' );
				return false;
			}
			/* validate password length */
			if ( strlen( trim( $data['password1'] ) ) < $passmin ) {
				$wpm_errmsg = sprintf( __( 'Password has to be at least %d characters long and must not contain spaces.', 'wishlist-member' ), $passmin );
				return false;
			}
			/* validate password strength (if enabled) */
			if ( $this->GetOption('strongpassword') && !wlm_check_password_strength($data['password1']) && !$tempacct ) {
				$wpm_errmsg = __( 'Please provide a strong password. Password must contain at least one uppercase letter, one lowercase letter, one number and one special character.', 'wishlist-member' );
				return false;
			}
			/* check if password1 and password2 matches */
			if ( $data['password1'] != $data['password2'] ) {
				$wpm_errmsg = __( 'The passwords you entered do not match.', 'wishlist-member' );
				return false;
			}
			/* validate reCaptcha */
			if ( ! $this->reCaptchaResponse() ) {
				$wpm_errmsg = __( 'The reCAPTCHA wasn\'t entered correctly. Go back and try it again', 'wishlist-member' );
				return false;
			}
			if ( $required_error ) {
				$wpm_errmsg = __( 'All required fields must be filled-in.', 'wishlist-member' );
				return false;
			}

			/* sanitize the lastname, firstname and email */
			$data['firstname'] = $this->CleanInput( $data['firstname'] );
			$data['lastname']  = $this->CleanInput( $data['lastname'] );
			$data['email']     = $this->CleanInput( $data['email'] );
			$data['reg_date']  = $this->CleanInput( $data['reg_date'] );

			$nickname = trim( empty( $data['nickname'] ) ? $data['firstname'] : $data['nickname'] );

			/* generate userdata */
			$userdata = array(
				'user_pass'       => trim( $data['password1'] ),
				'user_login'      => trim( $data['username'] ),
				'user_email'      => trim( $data['email'] ),
				'user_registered' => trim( $data['reg_date'] ),
				'nickname'        => $nickname,
				'first_name'      => trim( $data['firstname'] ),
				'last_name'       => trim( $data['lastname'] ),
				'display_name'    => trim( $data['firstname'] ) . ' ' . trim( $data['lastname'] ),
				'user_url'        => trim( $data['website'] ),
				'aim'             => trim( $data['aim'] ),
				'yim'             => trim( $data['yim'] ),
				'jabber'          => trim( $data['jabber'] ),
				'description'     => trim( $data['biography'] )
			);

			/* wpm_useraddress */
			$wpm_useraddress = array_intersect_key( $data, array_flip( array( 'company', 'address1', 'address2', 'city', 'state', 'zip', 'country' ) ) );

			/* set role for user */
			if ( $wpm_levels[$data['wpm_id']]['role'] ) {
				$userdata['role'] = $wpm_levels[$data['wpm_id']]['role'];
			}

			/*
			 * create the user
			 * if $mergewith->ID is set then we are merging with
			 * a temp account generated by one of the shopping cart
			 * registrations. we merge the info passed by the user
			 * with the temp account using wp_update_user
			 *
			 * if we're not merging then we create the user using
			 * the wordpress wp_insert_user function
			 */
			if ( $mergewith->ID ) {
				//error_log('updating user');
				$userdata['ID']            = $mergewith->ID;
				$userdata['user_nicename'] = '';
				$id                        = wp_update_user( $userdata );
			} else {
				//creating user
				//error_log('creating user');
				$id = wp_insert_user( $userdata );

				//if password hinting is enabled, add the password hint to members user options table
				if ( $this->GetOption( 'password_hinting' ) ) {
					$this->Update_UserMeta( $id, 'wlm_password_hint', trim( $_POST['passwordhint'] ) );
				}
			}

			// No more id=0 check. We simply bail at this point if WP failed
			if ( ! $id || is_wp_error( $id ) ) {
				$wpm_errmsg = __( 'There was an error with the registration and an account could not be created.  Please try again or contact the site administrator for more information.' );
				return false;
			}

			/*
			 * we repeat the update to make sure we have the password
			 * updated because for some reason, wp_update_user does
			 * not correctly save the password for new users...
			 *
			 * I'm no longer sure if this is still needed but no harm
			 * done if we just re-update the user with the same info
			 * anyway.
			 *
			 * The story for this goes a long way back to the time when
			 * we first added the functionality of allowing users to
			 * assign their own usernames and passwords when they go
			 * through one of our shopping cart integrations
			 */
			
			add_filter( 'send_password_change_email', create_function('', 'return false;') ); // added to prevent WP from sending the password change email (since WP 4.3)
			$userdata['ID'] = $id;
			$id             = wp_update_user( $userdata );

			/* do fixes if we're doing a merge */
			if ( $mergewith->ID ) {
				/*
				 * fix the username because temp account's username
				 * is in the form of temp_(md5 hash here)
				 */
				$wpdb->query( $wpdb->prepare( "UPDATE `{$wpdb->users}` SET `user_login`=%s WHERE `ID`=%d", $userdata['user_login'], $id ) );
				wp_update_user( $userdata ); // another update to refresh things
			}

			/*
			 * we save registration post and get data if
			 * we are not doing a merge
			 */
			if ( ! $mergewith->ID ) {
				/* save registration post */
				$this->Update_UserMeta( $id, 'wlm_reg_post', $this->WLMEncrypt( $this->OrigPost ) );
				/* save registration get */
				$this->Update_UserMeta( $id, 'wlm_reg_get', $this->WLMEncrypt( $this->OrigGet ) );
			}

			/*
			 * we save additional_levels if it's set
			 */
			if ( wlm_arrval( $_POST, 'additional_levels' ) ) {
				$this->Update_UserMeta( $id, 'additional_levels', $_POST['additional_levels'] );
			}


			/*
			 * save custom registration fields
			 */
			foreach ( $custom_fields AS $custom_field ) {
				$name  = 'custom_' . $custom_field;
				$value = $_POST[$custom_field];
				$this->Update_UserMeta( $id, $name, $value );
			}


			if ( $id < 1 ) {
				$wpm_errmsg = __( 'An unknown error occured.  Please try again.', 'wishlist-member' );
				return false;
			}

			/* save orig_email to usermeta (shopping cart stuff) */
			if ( $data['orig_email'] ) {
				$this->Update_UserMeta( $id, 'wlm_origemail', $data['orig_email'] );
			}
			/* if its a temporary account, set notification count which also servers as marker for incomplete registrants */
			if ( $tempacct && ! $mergewith->ID ) {
				//initialize data
				$wlm_incregnotification = array( "count" => 0, "lastsend" => time() );
				add_user_meta( $id, 'wlm_incregnotification', $wlm_incregnotification );
			}
			/* if its not a temporary account and is merging delete notification count we set for templorary users */
			if ( ! $tempacct && $mergewith->ID ) {
				delete_user_meta( $id, 'wlm_incregnotification' );
			}
			/* save registration IP */
			$this->Update_UserMeta( $id, 'wpm_registration_ip', $_SERVER['REMOTE_ADDR'] );


			/* prepare stuff for email merge-codes */
			$macros = array(
				'[memberlevel]' => trim( $wpm_levels[$data['wpm_id']]['name'] ),
				'[password]'    => trim( $data['password1'] ),
				'[confirmurl]'  => get_bloginfo( 'url' ) . '/index.php?wlmconfirm=' . $id . '/' . md5( trim( $data['email'] ) . '__' . trim( $data['username'] ) . '__' . $data['wpm_id'] . '__' . $this->GetAPIKey() ),
			);

			/*
			 *  we check if there's a "need for admin approval" or "email confirmation"
			 *  in the level settings, if yes, then add a flag that will delay member from being added to AR
			 *  until both all flags are cleared
			 */
			$pendingautoresponder = array();

			$isshoppingcartpending = $this->IsPendingShoppingCartApproval( $data['wpm_id'], $id );
			if ( $isshoppingcartpending && ! $is_forapproval ) { //make sure this is not SC Integration that use approval status
				$pendingstatus = $isshoppingcartpending;
			}

			/*
			 * check if we need to set admin approval
			 */
			// $level_for_approval = array(
			// 	( bool ) ( $wpm_levels[$data['wpm_id']]['requireadminapproval'] AND ! $mergewith->ID AND ! $tempacct ),
			// 	( bool ) ( $wpm_levels[$data['wpm_id']]['requireadminapproval_integrations'] AND ! $mergewith->ID AND $tempacct),
			// 	( bool ) ( $wpm_levels[$data['wpm_id']]['requireadminapproval'] AND $this->GetOption( 'admin_approval_shoppingcart_reg' ) AND ! $mergewith->ID AND $tempacct),
			// 	( bool ) ( ! is_null( $pendingstatus ) ),
			// );
			//just commented the first one needed mikes verification :)
			$level_for_approval = array(
				( bool ) ( $wpm_levels[$data['wpm_id']]['requireadminapproval'] AND ! $mergewith->ID AND ! $tempacct AND ! $is_forapproval ),
				( bool ) ( $wpm_levels[$data['wpm_id']]['requireadminapproval_integrations'] AND $this->GetOption( 'admin_approval_shoppingcart_reg' ) AND ! $mergewith->ID AND $tempacct ),
				( bool ) ( ! is_null( $pendingstatus ) AND ! $is_forapproval ),
			);


			//admin approval for pending shoppingcart transactions (Autoresponder Pending)
			if ( in_array( true, $level_for_approval ) AND ! $registered_by_admin ) {
				$pendingautoresponder[] = 'autoresponder_add_pending_admin_approval';
			}

			//require email confirmation (Autoresponder Pending)
			if ( (($wpm_levels[$data['wpm_id']]['requireemailconfirmation'] && ! $registered_by_admin)) || (($wpm_levels[$data['wpm_id']]['requireemailconfirmation'] && $tempacct)) ) {
				$pendingautoresponder[] = 'autoresponder_add_pending_email_confirmation';
			}

			/* We're now using the levels first assigned in the temp account
			 * merging? remove user from all levels first
			 *
			 */
			if ( $mergewith->ID ) {
				if ( $this->IsPPPLevel( $data['wpm_id'] ) ) {
					$data['sctxnid'] = $this->Get_ContentLevelMeta( 'U-' . $id, substr( $data['wpm_id'], 11 ), 'transaction_id' );
				} else {
					$data['sctxnid'] = $this->GetMembershipLevelsTxnID( $id, $data['wpm_id'] );
				}

				//Adding this here cause when merging, ARsubscribe aren't be called anymore as we are just using the
				//membership levels first assigned in the temp account
				if ( empty( $pendingautoresponder ) ) {
					$this->ARSubscribe( trim( $data['firstname'] ), trim( $data['lastname'] ), trim( $data['email'] ), $data['wpm_id'] );
				}

				// Also adding this here, same reason as with ARSubscribe above.
				$this->WebinarSubscribe(trim($data['firstname']), trim($data['lastname']), trim($data['email']), $data['wpm_id']);
			}


			/* add new member to right level */
			$this->SetMembershipLevels( $id, $data['wpm_id'], $tempacct, null, null, null, $tempacct, $pendingautoresponder, true );

			/* turn on user's sequential upgrade */
			$this->IsSequential( $id, true );

			/* save sctxnid */
			if ( $data['sctxnid'] ) {
				if ( $this->IsPPPLevel( $data['wpm_id'] ) ) {
					$this->AddUserPostTransactionID( $id, substr( $data['wpm_id'], 11 ), $data['sctxnid'] );
				} else {
					$this->SetMembershipLevelTxnID( $id, $data['wpm_id'], $data['sctxnid'] );
				}
			}

			/* let's also save the user's wpm_useraddress if it's specified */
			if ( ! empty( $_POST['wpm_useraddress'] ) || ! empty( $wpm_useraddress ) ) { // we only save the address if it's specified
				$wpm_useraddress = array_merge( ( array ) $_POST['wpm_useraddress'], ( array ) $wpm_useraddress );
				$this->Update_UserMeta( $id, 'wpm_useraddress', $wpm_useraddress );
			}

			/* update level count */
			if ( $wpm_levels[$data['wpm_id']] ) {
				$wpm_levels[$data['wpm_id']]['count'] ++;
			}
			$this->SaveOption( 'wpm_levels', $wpm_levels );

			/* set for approval and send approval email (if so configured) */
			if ( in_array( true, $level_for_approval ) AND ! $registered_by_admin ) {

				$this->LevelForApproval( $data['wpm_id'], $id, $pendingstatus ? $pendingstatus : true  );

				if ( ! $tempacct ) {
					//Send require admin approval email
					$this->send_email_template( 'require_admin_approval', $id, $macros );
					$send_welcome_email = false;
				}
			} else if ( $is_forapproval ) { // for shopping carts that uses for approval status eg. PinPayments, thats the purpose of $is_forapproval
				$this->LevelForApproval( $data['wpm_id'], $id, $pendingstatus ? $pendingstatus : true  );
			}

			/* set for email confirmation and send confirmation email (if so configured) */
			if ( $wpm_levels[$data['wpm_id']]['requireemailconfirmation'] && ! $registered_by_admin) {
				$this->LevelUnConfirmed( $data['wpm_id'], $id, true );

				if ( ! $tempacct ) {
					$this->send_email_template( 'email_confirmation', $id, $macros );
					$send_welcome_email = false;
				}
			}

			/* send the welcome email */
			if ( $send_welcome_email && ! $tempacct) {
				$this->send_email_template( 'registration', $id, $macros );
			}

			/* notify the admin via e-amil */
			if ( $notifyadmin ) {
				if ( $this->GetOption( 'notify_admin_of_newuser' ) ) {
					$admin_macros = $macros;
					if ( $this->GetOption( 'mask_passwords_in_emails' ) ) {
						$admin_macros['[password]'] = '********';
					}
					$this->send_email_template( 'admin_new_member_notice', $id, $admin_macros, $this->GetOption( 'newmembernotice_email_recipient' ) );
				}
			}

			/* delete the registration page security cookie */
			$this->RegistrationCookie( 'x', $dummy );

			/*
			 * auto login
			 */
			if ( false === wlm_admin_in_admin() AND ! $tempacct ) {
				$this->WPMAutoLogin( $id );
			}

			/*
			 * delete the wpmu cookie
			 * mu means "Merge User"
			 */
			if ( ! headers_sent() ) {
				wlm_setcookie( 'wpmu', '', time() - 3600, '/' );
			}

			/**
			 * Is Transient IP specified?
			 */
			if ( isset( $_POST['transient_hash'] ) ) {
				$this->SetTransientHash( $_POST['transient_hash'], $data['orig_email'] );
			}

			$this->SyncMembership();
			$this->SyncContent( 'posts' );

			/* Hook triggere when new user is added */
			do_action( 'wishlistmember_user_registered', $id, $data );
			/* finally, now we can return the new user's ID */
			return $id;
		}

		/**
		 * WPMRegisterExisting
		 * Registers existing user to a membership level
		 * @param array $data User data array
		 * @param string $wpm_errmsg Passed by reference, we save the error message here
		 * @param boolean $send_welcome_email True to send registration email or not
		 * @param boolean $notifyadmin True to notify admin via email of this registration
		 * @return integer|boolean User ID on success or false on error
		 */
		function WPMRegisterExisting( $data, &$wpm_errmsg, $send_welcome_email = true, $notifyadmin = true, $special_bypass = false ) {
			/* include the required WordPress functions */
			require_once(ABSPATH . 'wp-admin/includes/user.php');

			/* load the membership levels */
			$wpm_levels = $this->GetOption( 'wpm_levels' );

			/* set blacklist to zero */
			$blacklist = 0;


			/* Check if for approval registration */
			$is_forapproval = $this->IsForApprovalRegistration( $data['wpm_id'] );
			if ( $is_forapproval ) {
				$wpm_newid     = time();
				sleep( 1 );
				$pendingstatus = "Registered For Approval";
				if ( $is_forapproval['name'] == "PinPayments" ) {
					$data['sctxnid'] = "SP-" . $data['wpm_id'] . "-" . $wpm_newid;
					$pendingstatus   = "Pin Payments Confirmation";
				}
				$data['wpm_id']      = $is_forapproval["level"];
				$registered_by_admin = false;
			}

			/* check if the user is valid */
			if ( true === wlm_admin_in_admin() || true === $special_bypass ) {
				$validuser = username_exists( $data['username'] );

				if ( ! $validuser ) {
					$validuser        = email_exists( $data['email'] );
					$user_info        = get_userdata( $validuser );
					$data['username'] = $user_info->user_login;
				}

				$data['password'] = __( 'Already assigned', 'wishlist-member' );
			} else {
				$validuser = wp_login( $data['username'], $data['password'] );
			}
			if ( $validuser ) {
				$user      = $this->Get_UserData( 0, $data['username'] );
				/* check for blacklist status */
				$blacklist = $this->CheckBlackList( $user->user_email );

				/* load user's Membership Levels */
				$levels = $this->GetMembershipLevels( $user->ID );

				/* check if the member is already registered to the level */
				$inlevel = in_array( $data['wpm_id'], $levels );

				/*
				 * if member is already in level, check if he's expired and if so,
				 * check if level is configured to reset registration for expired
				 * level re-registration
				 */
				if ( $inlevel ) {
					$expired      = $this->LevelExpired( $data['wpm_id'], $user->ID );
					$resetexpired = $wpm_levels[$data['wpm_id']]['registrationdatereset'] == 1;
					/* if expired and level allows re-registration then set inlevel to false */
					if ( $expired && $resetexpired ) {
						$inlevel = false;
					}

					$cancelled      = $this->LevelCancelled( $data['wpm_id'], $user->ID );
					$resetcancelled = $wpm_levels[$data['wpm_id']]['uncancelonregistration'] == 1;
					/* if expired and level allows re-registration then set inlevel to false */
					if ( $cancelled && $resetcancelled ) {
						$inlevel = false;
					}

					$repeat_registration = false;
					if ( defined( 'WLM_ALLOW_REPEAT_REGISTRATION' ) ) {
						$inlevel             = false;
						$repeat_registration = true;
					}
				}
			}

			/* validate if not blacklisted */
			if ( $blacklist ) {
				switch ( $blacklist ) {
					case 1:
						$wpm_errmsg = $this->GetOption( 'blacklist_email_message' );
						break;
					case 2:
						$wpm_errmsg = $this->GetOption( 'blacklist_ip_message' );
						break;
					case 3:
						$wpm_errmsg = $this->GetOption( 'blacklist_email_ip_message' );
						break;
				}
				return false;
			}
			/* validate if a valid user */
			if ( ! $validuser ) {
				$wpm_errmsg = __( 'Invalid username and/or password.', 'wishlist-member' );
				return false;
			}
			/* validate if not in level */
			if ( $inlevel ) {
				$wpm_errmsg = __( 'You are already registered to this level.', 'wishlist-member' );
				return false;
			}
			/* validate if reCaptcha is OK */
			if ( ! $this->reCaptchaResponse() ) {
				$wpm_errmsg = __( 'The reCAPTCHA wasn\'t entered correctly. Go back and try it again', 'wishlist-member' );
				return false;
			}


			/*
			 *  we check if there's a "need for admin approval" or "email confirmation"
			 *  in the level settings, if yes, then add a flag that will delay member from being added to AR
			 *  until all these flags are cleared
			 */
			$pendingautoresponder   = array();
			if ( $wpm_levels[$data['wpm_id']]['requireadminapproval'] && ! $registered_by_admin && ! $is_forapproval  ) {
				$pendingautoresponder[] = 'autoresponder_add_pending_admin_approval';
			}

			if ( $wpm_levels[$data['wpm_id']]['requireemailconfirmation'] && ! $registered_by_admin ) {
				$pendingautoresponder[] = 'autoresponder_add_pending_email_confirmation';
			}


			/* set membership levels */
			$levels[] = $data['wpm_id'];
			$this->SetMembershipLevels( $user->ID, $levels, $null, $null, $null, $null, $null, $pendingautoresponder , true );


			/* attach transaction_id to user and delete mergewith temporary user */
			if ( $data['mergewith'] ) {
				$mw = $this->Get_UserData( $data['mergewith'] );
				if ( $mw->data->additional_levels ) {
					$this->Update_UserMeta( $user->ID, 'additional_levels', $mw->data->additional_levels );
				}
				if ( $this->IsPPPLevel( $data['wpm_id'] ) ) {
					$clcntnt = substr( $data['wpm_id'], 11 );
					$clmeta  = $this->Get_AllContentLevelMeta( 'U-' . $mw->ID, substr( $data['wpm_id'], 11 ) );
					if ( $clmeta ) {
						foreach ( $clmeta AS $k => $v ) {
							if ( ! $this->Add_ContentLevelMeta( 'U-' . $user->ID, $content_id, $k, $v ) ) {
								$this->Update_ContentLevelMeta( 'U-' . $user->ID, $content_id, $k, $v );
							}
						}
					}
				} else {
					foreach ( ( array ) $this->GetMembershipLevelsTxnIDs( $mw->ID ) AS $key => $val ) {
						$this->SetMembershipLevelTxnID( $user->ID, $key, $val );
					}
					$this->LevelCancelled( $data['wpm_id'], $user->ID, false );
				}
				
				// Fix for issue where WLM can't delete temp accounts on WP MU.
				// Should also fix issue where incomplete reg emails still being sent even with users already completing it.
				if(is_multisite()) {
					require_once(ABSPATH . 'wp-admin/includes/ms.php');
					wpmu_delete_user( $data['mergewith'] );
				} else {
					//unset($mw);
					wp_delete_user( $data['mergewith'] );
				}

			} else {
				if ( $this->IsPPPLevel( $data['wpm_id'] ) ) {
					$this->AddUserPostTransactionID( $user->ID, substr( $data['wpm_id'], 11 ), $data['sctxnid'] );
				} else {
					if ( ! $repeat_registration ) {
						$this->SetMembershipLevelTxnID( $user->ID, $data['wpm_id'], $data['sctxnid'] );
					}
				}
			}

			/* if expired and level allows re-registration, then reset timestamp */
			if ( $expired && $resetexpired ) {
				$this->UserLevelTimestamp( $user->ID, $data['wpm_id'], time() );
			}

			/* if cancelled and level is set to uncancel on re-registration, then uncancel */
			if ( $cancelled && $resetcancelled ) {
				$txnid = $this->GetMembershipLevelsTxnID( $user->ID, $data['wpm_id'] );
				foreach ( ( array ) $this->GetMembershipLevelsTxnIDs( $user->ID, $txnid ) AS $level => $txnid ) {
					$this->LevelCancelled( $level, $user->ID, false );
				}
			}

			/* prepare email mergecodes */
			$macros = array(
				'[memberlevel]' => trim( $wpm_levels[$data['wpm_id']]['name'] ),
				'[password]'    => $data['password'],
				'[confirmurl]'  => get_bloginfo( 'url' ) . '/index.php?wlmconfirm=' . $user->ID . '/' . md5( $user->user_email . '__' . $user->user_login . '__' . $data['wpm_id'] . '__' . $this->GetAPIKey() ),
			);

			/*
			 * doing a manual registration so we also
			 * set the level's For Approval status if
			 * the level is configured as such
			 */

			$level_for_approval = array(
				(bool) ( $wpm_levels[$data['wpm_id']]['requireadminapproval'] AND ! $registered_by_admin AND ! $data['mergewith'] AND ! $is_forapproval ),
				(bool) ( $wpm_levels[$data['wpm_id']]['requireadminapproval_integrations'] AND $data['mergewith']  ),
			);

			/* set for approval and send approval email (if so configured) */
			if ( in_array( true, $level_for_approval ) AND ! $registered_by_admin ) {

				$this->LevelForApproval( $data['wpm_id'], $user->ID, $pendingstatus ? $pendingstatus : true  );

				//Send require admin approval email
				$this->send_email_template( 'require_admin_approval', $user->ID, $macros );
				$send_welcome_email = false;
			} else if ( $is_forapproval ) { // for shopping carts that uses for approval status eg. PinPayments, thats the purpose of $is_forapproval
				$this->LevelForApproval( $data['wpm_id'], $user->ID, $pendingstatus ? $pendingstatus : true  );
			}
			if ( $GLOBALS['wlm_cookies']->wishlist_reg_cookie_manual ) {
				// send confirmation email (if so configured)
				if ( $wpm_levels[$data['wpm_id']]['requireemailconfirmation'] ) {
					$this->LevelUnConfirmed( $data['wpm_id'], $user->ID, true );

					$this->send_email_template( 'email_confirmation', $user->ID, $macros );
					$send_welcome_email = false;
				}
			}

			/* add password */
			$macros['[password]'] = $data['password'];

			/* and send the mail */
			if ( $send_welcome_email ) {
				$this->send_email_template( 'registration', $user->ID, $macros );
			}
			if ( $notifyadmin ) {
				if ( $this->GetOption( 'notify_admin_of_newuser' ) ) {
					$admin_macros = $macros;
					if ( $this->GetOption( 'mask_passwords_in_emails' ) ) {
						$admin_macros['[password]'] = '********';
					}
					$this->send_email_template( 'admin_new_member_notice', $user->ID, $admin_macros, $this->GetOption( 'newmembernotice_email_recipient' ) );
				}
			}

			// make sure sequential upgrade is enabled
			// $this->IsSequential( $user->ID, true );

			// delete the registration page security cookie
			$this->RegistrationCookie( 'x', $dummy );

			// wp multisite stuff
			if(function_exists('add_user_to_blog')) {
				if(!is_user_member_of_blog($user->ID)) {
					add_user_to_blog($GLOBALS['blog_id'], $user->ID, $wpm_levels[$data['wpm_id']]['role']);
				}
			}

			if ( false === wlm_admin_in_admin() ) {
				/*
				 * we no longer save the password since WLM 2.8
				 */
				// $this->SaveOption('xxxssapxxx-' . $user->ID, $data['password'], true);
				$this->WPMAutoLogin( $user->ID );
			}
			/* we're done */
			do_action( 'wishlistmember_user_registered', $user->ID, $data, $mw );
			return $user->ID;
		}

		/**
		 * Auto Login a User
		 * @param int $id User ID
		 */
		function WPMAutoLogin($id) {
			// clear auth cookies
			if (is_user_logged_in()) // we now only clear cookies if a user is logged in. dunno why but this fixes ticket #364404
				wp_clear_auth_cookie();

			// pull user info
			wp_set_auth_cookie($id);

			// save login IP
			$this->Update_UserMeta($id, 'wpm_login_ip', $_SERVER['REMOTE_ADDR']);
			// $this->Update_UserMeta($id,'wpm_login_date',time()-get_option('gmt_offset')*3600);
			$this->Update_UserMeta($id, 'wpm_login_date', time());
		}

		/**
		 * Saves User ID based on Hash as an
		 * 8-hour Transient option in WP
		 * @param string $ip IP Address
		 * @param integer $trans Unique identifier
		 */
		function SetTransientHash($hash, $trans) {
			$name = $this->GetTempDir() . '/wlm_th_' . $hash;
			//set_transient($name, $trans, 60 * 60 * 8);
			$f = fopen($name, 'w');
			fwrite($f, $trans);
			fclose($f);
		}

		/**
		 * Retrieves User ID based on Transient Hash
		 * @param <type> $ip
		 * @return string
		 */
		function GetTransientHash() {
			$ckname = md5('wlm_transient_hash');
			$hashes = (array) $GLOBALS['wlm_cookies']->$ckname;
			foreach ($hashes as $hash) {
				$name = $this->GetTempDir() . '/wlm_th_' . $hash;
				//$trans = get_transient($name);
				if (file_exists($name)) {
					$trans = trim(file_get_contents($name));
					if ($trans) {
						return $trans;
					}
				}
			}
			return '';
		}

		/**
		 * Deletes the Transient Hash from WP Database
		 * and clears the Transient Hash Cookie
		 */
		function DeleteTransientHash() {
			$ckname = md5('wlm_transient_hash');
			$hashes = (array) $GLOBALS['wlm_cookies']->$ckname;
			foreach ($hashes as $hash) {
				$name = $this->GetTempDir() . '/wlm_th_' . $hash;
				if (file_exists($name)) {
					unlink($name);
				}
			}
			wlm_setcookie(md5('wlm_transient_hash'), '', time() - 3600, '/');
		}

		function GetMatchingLevels($thefile, $mlevel) {
			@ini_set('memory_limit', '256M');
			$auto_detect_line_endings = ini_get('auto_detect_line_endings');
			@ini_set('auto_detect_line_endings', 1);
			wlm_set_time_limit(3600);
			$wpm_levels = $this->GetOption('wpm_levels');
			$f = fopen($thefile, 'r');
			$row = 0;

			while (($data = fgetcsv($f, 10000)) !== false) {
				$row++;
				echo str_pad(' ', 2048);
				flush();
				list($uname, $fname, $lname, $email, $password, $m_level, $txn_id, $registration_date) = $data;
				$_POST['m_level'] = $m_level;
				$m_level = explode(',', $m_level);
				foreach ($m_level as $k => $vl) {
					if ($vl != 'level')
						$all_level[] = $vl;
				}
			}
			$all_level = array_unique($all_level);
			foreach ($all_level as $id => $v) {
				foreach ($wpm_levels as $k => $vl) {
					if ($v == $vl['name']) {
						$matchingname[] = $v;
						$all_level_match[] = $k;
					}
				}
			}

			if (count($matchingname) > 0) {
				$nonmatching = array_diff($all_level, $matchingname);
			} else {
				$nonmatching = $all_level;
			}

			fclose($f);
			@ini_set('auto_detect_line_endings', $auto_detect_line_endings);
			if ($mlevel == 'match')
				return $nonmatching;
			else
				return $all_level_match;
		}

		/**
		 * Import Members from CSV file
		 */
		function ImportMembers() {
			global $wpdb;
			ignore_user_abort(true);
			$wpm_levels = $this->GetOption('wpm_levels');

			/* flags */
			$import_membership_levels = !empty($_POST['importmlevels']);
			$duplicate_handling = wlm_arrval($_POST, 'duplicates');
			$default_password = trim($_POST['password']);
			$email_notification = !empty($_POST['notify']);
			$membership_levels = wlm_arrval($_POST, 'wpm_to');
			$process_autoresponders = !empty($_POST['process_autoresponders']);
			$process_webinars = !empty($_POST['process_webinars']);
			$use_regdate = !empty($_POST['use_regdate']);
			$require_lastname = !empty($_POST['require_lastname']);
			$require_firstname = !empty($_POST['require_firstname']);

			if (!$import_membership_levels && empty($membership_levels)) {
				$_POST['err'] = __('Membership level(s) not specified.', 'wishlist-member');
				return;
			}

			if (is_uploaded_file($_FILES['File']['tmp_name'])) {
				@ini_set('auto_detect_line_endings', 1);
				wlm_set_time_limit(3600);

				$file = fopen($_FILES['File']['tmp_name'], 'r');
				$allowed_headers = $this->ImportExportColumnNames(
						array(
							'with_password' => true,
							'with_date_added_to_level' => true,
							'with_level' => true,
							'with_transaction_id' => true,
							'with_level_status' => true,
							'with_subscription_status' => true,
							'with_address' => true
						)
				);

				/* check headers */
				$headers = fgetcsv($file);
				foreach ($headers AS &$column) {
					$column = trim(str_replace('(optional)', '', $column));
				}
				unset($column);

				if (count($headers) != count(array_unique($headers))) {
					$_POST['err'] = __('Duplicate column headers detected.', 'wishlist-member');
					return;
				}

				$main_headers = $headers;
				$custom_fields_marker = array_search('__CUSTOM_FIELDS_MARKER__', $headers);
				if ($custom_fields_marker !== false) {
					$custom_fields_headers = array_diff(array_slice($headers, $custom_fields_marker + 1), array(''));
					$has_custom_fields = count($custom_fields_headers) > 0;
					$main_headers = array_slice($headers, 0, $custom_fields_marker);
				}

				if (array_search('', $headers) !== false) {
					$_POST['err'] = __('Empty column headers detected.', 'wishlist-member');
					return;
				}

				$invalid_headers = array_diff($main_headers, $allowed_headers);
				if (count($invalid_headers)) {
					$_POST['err'] = __('Invalid column header(s) detected.<ol><li>', 'wishlist-member') . implode('</li><li>', $invalid_headers) . '</li></ol>';
					return;
				}

				if (!in_array('username', $main_headers)) {
					$_POST['err'] = __('Required <b>username</b> column not found.', 'wishlist-member');
					return;
				}

				if (!in_array('email', $main_headers)) {
					$_POST['err'] = __('Required <b>email</b> column not found.', 'wishlist-member');
					return;
				}

				if (!in_array('firstname', $main_headers)) {
					$_POST['err'] = __('Required <b>firstname</b> column not found.', 'wishlist-member');
					return;
				}

				if (!in_array('lastname', $main_headers)) {
					$_POST['err'] = __('Required <b>lastname</b> column not found.', 'wishlist-member');
					return;
				}

				$index = array_flip($headers);

				/* first pass - validate import file */
				$row_count = 0;
				$valid_level_names = $this->GetOption('wpm_levels');
				foreach ($valid_level_names AS &$level) {
					$level = trim(strtoupper($level['name']));
				}
				unset($level);

				while ($row = fgetcsv($file)) {
					$row_count++;

					if (!trim($row[$index['username']])) {
						$_POST['err'] = sprintf(__('No <b>username</b> detected in row #%d.', 'wishlist-member'), $row_count);
						return;
					}
					if (!trim($row[$index['email']])) {
						$_POST['err'] = sprintf(__('No <b>email</b> detected in row #%d.', 'wishlist-member'), $row_count);
						return;
					}
					if ($require_firstname){
						if (!trim($row[$index['firstname']])) {
							$_POST['err'] = sprintf(__('No <b>firstname</b> detected in row #%d.', 'wishlist-member'), $row_count);
							return;
						}
					}
					if ($require_lastname){
						if (!trim($row[$index['lastname']])) {
							$_POST['err'] = sprintf(__('No <b>lastname</b> detected in row #%d.', 'wishlist-member'), $row_count);
							return;
						}
					}

					if ($import_membership_levels) {
						if (!trim($row[$index['level']])) {
							$_POST['err'] = __('You chose to auto-detect levels from the import file but not all rows in your import file have levels.', 'wishlist-member');
							return;
						}
						$levels = preg_split('/[,\r\n\t]/', preg_replace('/\s*,\s*/', ',', strtoupper($row[$index['level']])));
						$invalid_levels = array_diff($levels, $valid_level_names);
						if ($invalid_levels) {
							$_POST['err'] = sprintf(__('Invalid level(s) detected in row #%d.', 'wishlist-member'), $row_count) . '<ol><li>' . implode('</li><li>', $invalid_levels) . '</li></ol>';
							return;
						}
					}
				}

				/* validation done - let's go back to the first row and reset our row counter */
				rewind($file);
				fgetcsv($file); // skip header row
				$row_count = 0;

				$logfile = tmpfile();

				$duplicates = 0;
				$skipped_admin = 0;
				$successful_inserts = 0;
				$replaced_users = 0;
				$updated_users = 0;
				$replaced_levels = 0;
				$updated_levels = 0;
				$insert_errors = 0;

				while ($row = fgetcsv($file)) {
					$row_count++;
					$password_is_encrypted = false;
					$firstname = trim($row[$index['firstname']]);
					$lastname = trim($row[$index['lastname']]);
					$username = trim($row[$index['username']]);
					$email = trim($row[$index['email']]);
					$password = trim($row[$index['password']]);
					$random_password = false;
					if (empty($password)) {
						if ($default_password) {
							$password = $default_password;
						} else {
							$password = $this->PassGen();
							$random_password = true;
						}
					}

					/* step 1: add or get user */

					$username_exists = username_exists($username);
					$email_exists = email_exists($email);

					$new_user = false;
					$user = false;
					$replace_id = 0;

					if ($username_exists OR $email_exists) {
						switch ($duplicate_handling) {
							case 'update': // update meta and levels
							case 'update_levels': // update levels
							case 'replace_levels': // replace levels
							case 'replace': // replace all information
								if ($email_exists) {
									$user = get_user_by('email', $email);
								} else {
									$user = get_user_by('login', $username);
								}

								if ($duplicate_handling == 'replace') {
									if(user_can($user->ID, 'manage_options')) {
										$logmsg = sprintf(__('Admin User not Replaced: Row %d - %s / %s', 'wishlist-member'), $row_count, $username, $email);
										fwrite($logfile, $logmsg . "\n");
										$skipped_admin++;
										continue 2;
									} else {
										$replace_id = $user->ID;
										wp_delete_user($user->ID);
										$this->SyncMembership();
										$user = false;
										$replaced_users++;
									}
								}

								break;
							default: // skip duplicates
								$logmsg = sprintf(__('Duplicate Skipped: Row %d - %s / %s', 'wishlist-member'), $row_count, $username, $email);
								fwrite($logfile, $logmsg . "\n");
								$duplicates++;
								continue 2;
								break;
						}
					}

					if (empty($user)) {
						$user = wp_insert_user(array(
							'user_login' => $username,
							'user_email' => $email,
							'first_name' => $firstname,
							'last_name' => $lastname,
							'user_pass' => $password
						));
						if (is_wp_error($user)) {
							$logmsg = sprintf(__('Import Error: Row %d - %s / %s', 'wishlist-member'), $row_count, $username, $email);
							$error_details .= 'Import Error on row <b>'.$row_count . '</b> : '. $email .'<br />';
							fwrite($logfile, $logmsg . "\n");
							$insert_errors++;
							continue;
						}

						// is password already encrypted? if so we update the password
						if (preg_match('/^___ENCPASS___(.+)?___ENCPASS___$/', $password, $match)) {
							$password_is_encrypted = true;
							$wpdb->query($wpdb->prepare("UPDATE `{$wpdb->users}` SET `user_pass`='%s' WHERE `ID`=%d", $match[1], $user));
						}

						// are we replacing a user?
						if (!empty($replace_id)) {
							$wpdb->query($wpdb->prepare("UPDATE `{$wpdb->users}` SET `ID`=%d WHERE `ID`=%d", $replace_id, $user));
							$user = $replace_id;
						}

						$new_user = true;
						$user = get_user_by('id', $user);
					}

					/* by this point, we already have $user */

					/*
					 * step 2: update user meta information if new or update
					 */

					if ($new_user OR $duplicate_handling == 'update') {
						if (!$new_user) {
							$updated_users++;
						}
						// first name and last name
						wp_update_user(array(
							'ID' => $user->ID,
							'first_name' => $firstname,
							'last_name' => $lastname
						));
						// address
						$address = $this->Get_UserMeta($user->ID, 'wpm_useraddress');
						$address_changed = false;
						foreach (array('company', 'address1', 'address2', 'city', 'state', 'zip', 'country') AS $address_field) {
							if (trim($row[$index[$address_field]])) {
								$address[$address_field] = trim($row[$index[$address_field]]);
								$address_changed = true;
							}
						}

						if ($address_changed) {
							$this->Update_UserMeta($user->ID, 'wpm_useraddress', $address);
						}

						//subscrption status
						$subscribed = wlm_boolean_value($row[$index['subscribed']], true);
						if ($subscribed) {
							$this->Delete_UserMeta($user->ID, 'wlm_unsubscribe');
						} else {
							$this->Update_UserMeta($user->ID, 'wlm_unsubscribe', 1);
						}

						//sequential status
						$sequential = wlm_boolean_value($row[$index['sequential']], true);
						$this->IsSequential($user->ID, $sequential);

						//custom fields
						if ($has_custom_fields) {
							foreach ($custom_fields_headers AS $custom_field) {
								$custom_field = trim($custom_field);
								$this->Update_UserMeta($user->ID, 'custom_' . $custom_field, trim(wlm_arrval($row, $index[$custom_field])));
							}
						}
					}

					/*
					 * step 3: add / update / replace membership levels
					 * also apply proper status flags, transaction ids and
					 * registration dates if specified in import file
					 */

					$keep_existing_levels = true;
					if (!$new_user) {
						switch ($duplicate_handling) {
							case 'replace_levels':
								$replaced_levels++;
								$keep_existing_levels = false;
								break;
							case 'update_levels':
								$updated_levels++;
								break;
						}
					}

					if ($import_membership_levels || (!$import_membership_levels && $use_regdate)) {
						if ($import_membership_levels){
						$membership_levels = preg_split('/[,\r\n\t]/', $row[$index['level']]);
							foreach ($membership_levels AS &$level) {
								$level = trim(strtoupper($level));
								$level = array_search($level, $valid_level_names);
							}
                        }
						unset($level);
						$transaction_ids = preg_split('/[,\r\n\t]/', $row[$index['transaction_id']]);
						$timestamps = preg_split('/[,\r\n\t]/', $row[$index['date_added_to_level']]);
						$cancelled = preg_split('/[,\r\n\t]/', $row[$index['cancelled']]);
					}

					// Setting the $no_autoresponder to false if the option is checked.
					$no_autoresponder = ($process_autoresponders) ? 0 : 1;

					// Setting the $no_webinar to false if the option is checked.
					$process_webinars = ($process_webinars) ? 0 : 1;

					$changed_levels = $this->SetMembershipLevels($user->ID, $membership_levels, $no_autoresponder, null, null, true, $process_webinars, null, $keep_existing_levels);

					// set transaction IDs and timestamps if we're importing levels from file
					if ($import_membership_levels || (!$import_membership_levels && $use_regdate)) {
						foreach ($membership_levels AS $key => $level) {
							$txnid = trim(wlm_arrval($transaction_ids, $key));
							if (!empty($txnid)) {
								$this->SetMembershipLevelTxnID($user->ID, $level, $txnid);
							}
							$ts = strtotime(trim(wlm_arrval($timestamps, $key)));
							if ($ts > 0) {
								$this->UserLevelTimestamp($user->ID, $level, $ts, true);
							}

							if (wlm_boolean_value(wlm_arrval($cancelled, $key), false)) {
								$this->LevelCancelled($level, $user->ID, true);
							}
						}
					}

					/*
					 * step 4: send email notifications if needed
					 */
					if ($email_notification OR $random_password) {
						$member_levels = array();
						foreach ($changed_levels['added'] AS $level) {
							$member_levels[] = $wpm_levels[$level]['name'];
						}

						$macros = array(
							'[memberlevel]' => implode(', ', $member_levels),
							'[password]' => $password_is_encrypted ? '********' : $password
						);

						$this->send_email_template('registration', $user->ID, $macros);
					}

					$successful_inserts++;
					$logmsg = '%d - %s / %s';
					if (empty($new_user) OR !empty($replace_id)) {
						switch ($duplicate_handling) {
							case 'update': // update meta and levels
								$logmsg = __('Updated User Info and Levels: Row ', 'wishlist-member') . $logmsg;
								break;
							case 'replace': // replace all information
								$logmsg = __('Replaced User: Row ', 'wishlist-member') . $logmsg;
								break;
							case 'update_levels': // update levels
								$logmsg = __('Updated User Levels: Row ', 'wishlist-member') . $logmsg;
								break;
							case 'replace_levels': // replace levels
								$logmsg = __('Replaced User Levels: Row ', 'wishlist-member') . $logmsg;
								break;
						}
						if (substr($logmsg, 0, 3) != '%d') {
							fwrite($logfile, sprintf($logmsg, $row_count, $username, $email) . "\n");
						}
					} else {
						fwrite($logfile, sprintf(__('Imported User: Row ', 'wishlist-member') . $logmsg, $row_count, $username, $email) . "\n");
					}
				}
			}

			$_POST['msg'] = '';
			$_POST['err'] = '';

			if ($successful_inserts) {
				$_POST['msg'] .= sprintf(__('<p>Successfully imported %d %s</p>', 'wishlist-member'), $successful_inserts, $successful_inserts != 1 ? 'users' : 'user');
			}
			if (!empty($duplicates)) {
				$_POST['msg'] .= sprintf(__('<p>Skipped %d duplicate %s</p>', 'wishlist-member'), $duplicates, $duplicates != 1 ? 'entries' : 'entry');
			}
			if (!empty($skipped_admin)) {
				$_POST['msg'] .= sprintf(__('<p>Did not replace %d %s</p>', 'wishlist-member'), $skipped_admin, $skipped_admin != 1 ? 'administrators' : 'administrator');
			}
			if (!empty($insert_errors)) {
				$_POST['err'] .= sprintf(__('<p>Error importing %d %s</p>', 'wishlist-member'), $insert_errors, $insert_errors != 1 ? 'users' : 'user');
				$_POST['err'] .= sprintf(__('<br /> <p><b>Error Details:</b> <br /><br />  %s</p>', 'wishlist-member'), $error_details);
			}

			$this->SyncMembership();
			/*
			  rewind($logfile);
			  while ($log = fread($logfile, 10000)) {
			  echo nl2br($log);
			  }
			 */
			fclose($logfile);
			return;
		}

		function ExportMembersChunked() {
			global $wpdb;

			@ini_set('memory_limit', '256M');

			$wpm_to = (array) wlm_arrval($_POST, 'wpm_to');
			$full_data_export = wlm_arrval($_POST, 'full_data_export') == 1;
			$include_password = wlm_arrval($_POST, 'include_password') == 1;
			$include_inactive = wlm_arrval($_POST, 'include_inactive') == 1;
			$per_page = wlm_arrval($_POST, 'per_page');
			$current_page = wlm_arrval($_POST, 'current_page');

			$fname = 'members_' . date('Ymd_His') . '.csv';

			$search_results_count = 0;
			$search_results = array();

			$include_nonmembers = in_array('nonmember', $wpm_to);
			$wpm_to = array_diff($wpm_to, array('nonmember'));

			if ($wpm_to) {
				$ids = $this->MemberIDs($wpm_to);
				$search_results_count += count($ids);
				$search_results = array_merge($search_results, $ids);
			}
			if ($include_nonmembers) {
				$ids = $wpdb->get_col("SELECT `ID` FROM `{$wpdb->users}` WHERE `ID` NOT IN (SELECT DISTINCT `user_id` FROM `{$this->Tables->userlevels}`)");
				$search_results_count += count($ids);
				$search_results = array_merge($search_results, $ids);
			}

			if($search_results_count <= 0) {
				echo json_encode( array('error' => __("Nothing to export")));
				die();
			}


			$total          = count($search_results);
			$current_page   = (int) $current_page;
			$per_page       = (int) $per_page;
			$nonce          = wlm_arrval($_POST, 'nonce');
			$search_results = array_slice($search_results, $current_page * $per_page, $per_page);
			$has_more       = $current_page+1 < $total/$per_page;
			$exported       = count($search_results);
			$tmpname        = wlm_arrval($_POST, 'tempname');


			if (count($search_results)) {
				$f = fopen($tmpname, 'a');

				/* prepare column headers */

				$column_header_settings = array();
				if ($include_password) {
					$column_header_settings['with_password'] = true;
				}
				$column_header_settings['with_level'] = true;

				if ($full_data_export) {
					$column_header_settings['with_transaction_id'] = true;
					$column_header_settings['with_date_added_to_level'] = true;
					$column_header_settings['with_level_status'] = true;
					$column_header_settings['with_subscription_status'] = true;
					$column_header_settings['with_address'] = true;
					$column_header_settings['with_custom_fields'] = true;
				}

				$column_headers = $this->ImportExportColumnNames($column_header_settings);

				if ($full_data_export) {
					$custom_fields = array_search('__CUSTOM_FIELDS_MARKER__', $column_headers);
					if ($custom_fields !== false) {
						$custom_fields = array_slice($column_headers, $custom_fields + 1);
					}
				}

				if($current_page == 0) {
					fputcsv($f, $column_headers, ',', '"');
				}


				$data_template = array_combine($column_headers, array_fill(0, count($column_headers), ''));

				foreach ((array) $search_results AS $uid) {
					$data = $data_template;

					$wlm_user = new WishListMemberUser($uid, null, true);
					$user = $this->Get_UserData($uid);
					$wlm_ulevelactive = false;
					$wpm_ulevel = array();

					foreach ($wpm_to as $k => $wlm_to) {
						if ($include_inactive || (!$include_inactive && $wlm_user->Levels[$wlm_to]->Active)) {
							$wpm_ulevel[] = $wlm_user->Levels[$wlm_to]->Name;
							$wlm_ulevelactive = true;
						}
					}
					$wlm_ulevel = implode("\n", array_filter($wpm_ulevel));
					unset($wpm_ulevel);
					if ($include_inactive || $wlm_ulevelactive || $include_nonmembers) {
						$data['username'] = $user->user_login;
						$data['firstname'] = $user->first_name;
						$data['lastname'] = $user->last_name;
						$data['email'] = $user->user_email;
						$data['level'] = $wlm_ulevel;

						if ($include_password) {
							$data['password'] = '___ENCPASS___' . $user->user_pass . '___ENCPASS___';
						}

						if ($full_data_export) {

							$wlm_txnID = array();
							$wlm_gmdate = array();
							$wlm_active = array();
							$wlm_active = array();
							$wlm_pending = array();
							$wlm_cancelled = array();
							$wlm_unconfirmed = array();
							$wlm_expired = array();
							$wlm_expirydate = array();

							foreach ($wpm_to as $k => $wlm_to) {
								if ($include_inactive || (!$include_inactive && $wlm_user->Levels[$wlm_to]->Active)) {
									if (isset($wlm_user->Levels[$wlm_to])) {
										$wlm_txnID[] = $wlm_user->Levels[$wlm_to]->TxnID;
										$wlm_gmdate[] = gmdate('m/d/Y h:i:s a', $wlm_user->Levels[$wlm_to]->Timestamp);
										$wlm_active[] = $wlm_user->Levels[$wlm_to]->Active ? 'Y' : 'N';
										$wlm_pending[] = $wlm_user->Levels[$wlm_to]->Pending ? 'Y' : 'N';
										$wlm_cancelled[] = $wlm_user->Levels[$wlm_to]->Cancelled ? 'Y' : 'N';
										$wlm_unconfirmed[] = $wlm_user->Levels[$wlm_to]->UnConfirmed ? 'Y' : 'N';
										$wlm_expired[] = $wlm_user->Levels[$wlm_to]->Expired ? 'Y' : 'N';
										$wlm_expirydate[] = $wlm_user->Levels[$wlm_to]->ExpiryDate ? gmdate('m/d/Y h:i:s a', $wlm_user->Levels[$wlm_to]->ExpiryDate) : '';
									}
								}
							}

							$data['transaction_id'] = implode("\n", array_filter($wlm_txnID));
							$data['date_added_to_level'] = implode("\n", array_filter($wlm_gmdate));
							$data['active'] = implode("\n", array_filter($wlm_active));
							$data['cancelled'] = implode("\n", array_filter($wlm_cancelled));
							$data['forapproval'] = implode("\n", array_filter($wlm_pending));
							$data['forconfirmation'] = implode("\n", array_filter($wlm_unconfirmed));
							$data['expired'] = implode("\n", array_filter($wlm_expired));
							$data['expiry'] = implode("\n", array_filter($wlm_expirydate));
							$data['company'] = $user->wpm_useraddress['company'];
							$data['address1'] = $user->wpm_useraddress['address1'];
							$data['address2'] = $user->wpm_useraddress['address2'];
							$data['city'] = $user->wpm_useraddress['city'];
							$data['state'] = $user->wpm_useraddress['state'];
							$data['zip'] = $user->wpm_useraddress['zip'];
							$data['country'] = $user->wpm_useraddress['country'] == 'Select Country' ? '' : $user->wpm_useraddress['country'];
							$data['subscribed'] = $user->wlm_unsubscribe ? 'N' : 'Y';

							foreach ($custom_fields AS $custom_field) {
								$fld = "custom_" . $custom_field;
								$data[$custom_field] = $user->$fld;

                                                                if ( is_array( $data[$custom_field] ) ) {
                                                                    $data[$custom_field] = implode( ',', $data[$custom_field] );
                                                                }
							}
						}

						unset($wlm_txnID, $wlm_gmdate, $wlm_active, $wlm_active, $wlm_pending, $wlm_cancelled, $wlm_unconfirmed, $wlm_expired, $wlm_expirydate);

						fputcsv($f, $data, ',', '"');
					}
				}
				fclose($f);
				echo json_encode( compact('total', 'current_page', 'per_page', 'nonce', 'has_more', 'exported', 'tmpname') );
			} else {
				header("Content-type:text/csv");
				header("Content-disposition: attachment; filename=" . $fname);
				$handle = fopen($tmpname, 'r');
				while (!feof($handle)) {
  					echo fread($handle, 1024);
				}
				fclose($handle);
				flush();

				//clear the contents
				$handle = fopen($tmpname, 'w' );
				fclose($handle);
			}
			exit;
		}
		/**
		 * Export Members to CSV file
		 */
		function ExportMembers() {
			global $wpdb;

			@ini_set('memory_limit', '256M');

			$wpm_to = (array) wlm_arrval($_POST, 'wpm_to');
			$full_data_export = wlm_arrval($_POST, 'full_data_export') == 1;
			$include_password = wlm_arrval($_POST, 'include_password') == 1;
			$include_inactive = wlm_arrval($_POST, 'include_inactive') == 1;

			$fname = 'members_' . date('Ymd_His') . '.csv';

			$search_results_count = 0;
			$search_results = array();

			$include_nonmembers = in_array('nonmember', $wpm_to);
			$wpm_to = array_diff($wpm_to, array('nonmember'));

			if ($wpm_to) {
				$ids = $this->MemberIDs($wpm_to);
				$search_results_count += count($ids);
				$search_results = array_merge($search_results, $ids);
			}
			if ($include_nonmembers) {
				$ids = $wpdb->get_col("SELECT `ID` FROM `{$wpdb->users}` WHERE `ID` NOT IN (SELECT DISTINCT `user_id` FROM `{$this->Tables->userlevels}`)");
				$search_results_count += count($ids);
				$search_results = array_merge($search_results, $ids);
			}

			header("Content-type:text/csv");
			header("Content-disposition: attachment; filename=" . $fname);
			flush();

			if ($search_results_count) {
				$f = fopen('php://output', 'w');

				/* prepare column headers */

				$column_header_settings = array();
				if ($include_password) {
					$column_header_settings['with_password'] = true;
				}
				$column_header_settings['with_level'] = true;

				if ($full_data_export) {
					$column_header_settings['with_transaction_id'] = true;
					$column_header_settings['with_date_added_to_level'] = true;
					$column_header_settings['with_level_status'] = true;
					$column_header_settings['with_subscription_status'] = true;
					$column_header_settings['with_address'] = true;
					$column_header_settings['with_custom_fields'] = true;
				}

				$column_headers = $this->ImportExportColumnNames($column_header_settings);

				if ($full_data_export) {
					$custom_fields = array_search('__CUSTOM_FIELDS_MARKER__', $column_headers);
					if ($custom_fields !== false) {
						$custom_fields = array_slice($column_headers, $custom_fields + 1);
					}
				}

				fputcsv($f, $column_headers, ',', '"');

				$data_template = array_combine($column_headers, array_fill(0, count($column_headers), ''));

				foreach ((array) $search_results AS $uid) {
					$data = $data_template;

					$wlm_user = new WishListMemberUser($uid, null, true);
					$user = $this->Get_UserData($uid);
					$wlm_ulevelactive = false;
					$wpm_ulevel = array();

					foreach ($wpm_to as $k => $wlm_to) {
						if ($include_inactive || (!$include_inactive && $wlm_user->Levels[$wlm_to]->Active)) {
							$wpm_ulevel[] = $wlm_user->Levels[$wlm_to]->Name;
							$wlm_ulevelactive = true;
						}
					}
					$wlm_ulevel = implode("\n", array_filter($wpm_ulevel));
					unset($wpm_ulevel);
					if ($include_inactive || $wlm_ulevelactive || $include_nonmembers) {
						$data['username'] = $user->user_login;
						$data['firstname'] = $user->first_name;
						$data['lastname'] = $user->last_name;
						$data['email'] = $user->user_email;
						$data['level'] = $wlm_ulevel;

						if ($include_password) {
							$data['password'] = '___ENCPASS___' . $user->user_pass . '___ENCPASS___';
						}

						if ($full_data_export) {

							$wlm_txnID = array();
							$wlm_gmdate = array();
							$wlm_active = array();
							$wlm_active = array();
							$wlm_pending = array();
							$wlm_cancelled = array();
							$wlm_unconfirmed = array();
							$wlm_expired = array();
							$wlm_expirydate = array();

							foreach ($wpm_to as $k => $wlm_to) {
								if ($include_inactive || (!$include_inactive && $wlm_user->Levels[$wlm_to]->Active)) {
									if (isset($wlm_user->Levels[$wlm_to])) {
										$wlm_txnID[] = $wlm_user->Levels[$wlm_to]->TxnID;
										$wlm_gmdate[] = gmdate('m/d/Y h:i:s a', $wlm_user->Levels[$wlm_to]->Timestamp);
										$wlm_active[] = $wlm_user->Levels[$wlm_to]->Active ? 'Y' : 'N';
										$wlm_pending[] = $wlm_user->Levels[$wlm_to]->Pending ? 'Y' : 'N';
										$wlm_cancelled[] = $wlm_user->Levels[$wlm_to]->Cancelled ? 'Y' : 'N';
										$wlm_unconfirmed[] = $wlm_user->Levels[$wlm_to]->UnConfirmed ? 'Y' : 'N';
										$wlm_expired[] = $wlm_user->Levels[$wlm_to]->Expired ? 'Y' : 'N';
										$wlm_expirydate[] = $wlm_user->Levels[$wlm_to]->ExpiryDate ? gmdate('m/d/Y h:i:s a', $wlm_user->Levels[$wlm_to]->ExpiryDate) : '';
									}
								}
							}

							$data['transaction_id'] = implode("\n", array_filter($wlm_txnID));
							$data['date_added_to_level'] = implode("\n", array_filter($wlm_gmdate));
							$data['active'] = implode("\n", array_filter($wlm_active));
							$data['cancelled'] = implode("\n", array_filter($wlm_cancelled));
							$data['forapproval'] = implode("\n", array_filter($wlm_pending));
							$data['forconfirmation'] = implode("\n", array_filter($wlm_unconfirmed));
							$data['expired'] = implode("\n", array_filter($wlm_expired));
							$data['expiry'] = implode("\n", array_filter($wlm_expirydate));
							$data['company'] = $user->wpm_useraddress['company'];
							$data['address1'] = $user->wpm_useraddress['address1'];
							$data['address2'] = $user->wpm_useraddress['address2'];
							$data['city'] = $user->wpm_useraddress['city'];
							$data['state'] = $user->wpm_useraddress['state'];
							$data['zip'] = $user->wpm_useraddress['zip'];
							$data['country'] = $user->wpm_useraddress['country'] == 'Select Country' ? '' : $user->wpm_useraddress['country'];
							$data['subscribed'] = $user->wlm_unsubscribe ? 'N' : 'Y';

							foreach ($custom_fields AS $custom_field) {
								$fld = "custom_" . $custom_field;
								$data[$custom_field] = $user->$fld;

                                                                if ( is_array( $data[$custom_field] ) ) {
                                                                    $data[$custom_field] = implode( ',', $data[$custom_field] );
                                                                }
							}
						}

						unset($wlm_txnID, $wlm_gmdate, $wlm_active, $wlm_active, $wlm_pending, $wlm_cancelled, $wlm_unconfirmed, $wlm_expired, $wlm_expirydate);

						fputcsv($f, $data, ',', '"');
					}
				}
				fclose($f);
			}
			exit;
		}

		/**
		 * Download Sample Import CSV
		 */
		function SampleImportCSV() {
			header("Content-type:text/csv");
			header("Content-disposition: attachment; filename=import_file_template.csv");
			$f = fopen('php://output', 'w');

			$headers = array(
				'with_password' => true,
				'with_date_added_to_level' => true,
				'with_level' => true,
				'with_transaction_id' => true,
				'with_level_status' => true,
				'with_subscription_status' => true,
				'with_address' => true,
				'with_custom_fields' => true
			);

			fputcsv($f, $this->ImportExportColumnNames($headers, true), ',', '"');
			fclose($f);
			exit;
		}

		/**
		 * Generate column headers for member import/export file
		 * @param array $column_header_settings array specifying which extra column headers to include. Keys are: with_password, with_date_added_to_level,with_level,with_transaction_id,with_level_status,with_subscription_status,with_address and with_custom_fields
		 * @param bool $for_sample_data (optional) default false
		 * @return array
		 */
		function ImportExportColumnNames($column_header_settings = array(), $for_sample_data = null) {

			$for_sample_data = (bool) $for_sample_data;

			$with_password = !empty($column_header_settings['with_password']);
			$with_date_added_to_level = !empty($column_header_settings['with_date_added_to_level']);
			$with_level = !empty($column_header_settings['with_level']);
			$with_transaction_id = !empty($column_header_settings['with_transaction_id']);
			$with_level_status = !empty($column_header_settings['with_level_status']);
			$with_subscription_status = !empty($column_header_settings['with_subscription_status']);
			$with_address = !empty($column_header_settings['with_address']);
			$with_custom_fields = !empty($column_header_settings['with_custom_fields']);

			$columns = array('username', 'firstname', 'lastname', 'email');
			if ((bool) $with_password) {
				$columns[] = 'password' . ($for_sample_data ? ' (optional)' : '');
			}
			if ((bool) $with_date_added_to_level) {
				$columns[] = 'date_added_to_level' . ($for_sample_data ? ' (optional)' : '');
			}
			if ((bool) $with_level) {
				$columns[] = 'level' . ($for_sample_data ? ' (optional)' : '');
			}
			if ((bool) $with_transaction_id) {
				$columns[] = 'transaction_id' . ($for_sample_data ? ' (optional)' : '');
			}
			if ((bool) $with_level_status) {
				if (!$for_sample_data) {
					$columns[] = 'active';
				}
				$columns[] = 'cancelled' . ($for_sample_data ? ' (optional)' : '');
				if (!$for_sample_data) {
					$columns[] = 'forapproval' . ($for_sample_data ? ' (optional)' : '');
					$columns[] = 'forconfirmation' . ($for_sample_data ? ' (optional)' : '');
					$columns[] = 'expired';
					$columns[] = 'expiry';
				}
			}
			if ((bool) $with_address) {
				$columns[] = 'company' . ($for_sample_data ? ' (optional)' : '');
				$columns[] = 'address1' . ($for_sample_data ? ' (optional)' : '');
				$columns[] = 'address2' . ($for_sample_data ? ' (optional)' : '');
				$columns[] = 'city' . ($for_sample_data ? ' (optional)' : '');
				$columns[] = 'state' . ($for_sample_data ? ' (optional)' : '');
				$columns[] = 'zip' . ($for_sample_data ? ' (optional)' : '');
				$columns[] = 'country' . ($for_sample_data ? ' (optional)' : '');
			}
			if ((bool) $with_subscription_status) {
				$columns[] = 'subscribed' . ($for_sample_data ? ' (optional)' : '');
			}
			if ((bool) $with_custom_fields) {
				$columns[] = '__CUSTOM_FIELDS_MARKER__';
				if ($for_sample_data) {
					$columns[] = 'custom_field_name_1';
					$columns[] = 'custom_field_name_2';
					$columns[] = 'custom_field_name_3';
				} else {
					$custom_fields = array_keys((array) $this->GetCustomRegFields());
					$columns = array_merge($columns, $custom_fields);
				}
			}
			return $columns;
		}

		/**
		 * Send email immediately or queue it in database for sending later.
		 * This function uses the WordPress wp_mail function to send the actual email.
		 * @param string $recipient Email address of recipient
		 * @param string $subject Email subject
		 * @param string $body Body of email
		 * @param array $data Associative array of merge codes
		 * @param mixed $queue FALSE to send immediately or timestamp to queue
		 * @param boolean $html TRUE to send as HTML or FALSE to send as Plain Text
		 * @param int $record_id id of the email queued
		 */
		function SendTheMail($recipient, $subject, $body, $data, $queue = false, $html = false, $record_id = null, $charset = null) {

			/*
			 * always return true when trying to send to temp account email
			 */
			if (preg_match('/temp_[a-f0-9]{32}/', $recipient)) {
				return true;
			}

			$this->SendingMail = true; // tell our hook that it's WishList Member sending the mail.

			/*
			 * $queue should be either a timestamp or FALSE
			 * if for some reason, we receive a value of TRUE
			 * then we replace its value with the current time
			 */
			if ($queue === true)
				$queue = time();

			// we add loginurl to the merge codes
			$data['loginurl'] = wp_login_url();

			// html or plain text?

			$header = ($html === false) ? 'text/plain' : 'text/html';
			if (empty($charset))
				$charset = $this->BlogCharset;
			$header = "Content-Type: {$header}; charset={$charset}\n";

			// the merge codes
			$search = array_keys((array) $data);
			foreach ((array) $search AS $k => $v) {
				if (substr(trim($v), 0, 1) == '[' && substr(trim($v), -1) == ']') {
					$search[$k] = $v;
				} else {
					$search[$k] = '[' . $v . ']';
				}
			}

			// run merge codes on subject
			$subject = str_replace($search, $data, $subject);
			// run merge codes on body
			$body = str_replace($search, $data, $body);
			$mailed = false;
			// queue or not?
			if ($queue) {
				// Queue...
				// Step 1 - Put all data in an array
				$x = array($recipient, $subject, $body, $header);
				// Step 2 - Create the variable name
				$name = $record_id . 'wlmember_email_queue_' . ((string) $queue) . '_' . md5(serialize($x));
				// Step 3 - Save it to wp_options
				$mailed = add_option($name, $x, '', 'no');
			} else {
				// Send now...
				$tries = 3; // <- number of tries before we surrender
				// Send the email
				while ($tries-- && !$mailed)
					$mailed = wp_mail($recipient, $subject, $body, $header);
			}
			$this->SendingMail = false; // done sending mail.

			return $mailed; // return the result
		}

		/**
		 * Send Email as HTML
		 * @param string $recipient Email address of recipient
		 * @param string $subject Email subject
		 * @param string $body Body of email
		 * @param array $data Associative array of merge codes
		 * @param mixed $queue FALSE to send immediately or timestamp to queue
		 * @param int $record_id id of the email queued
		 */
		function SendHTMLMail($recipient, $subject, $body, $data, $queue = false, $record_id = null, $charset = null) {
			return $this->SendTheMail($recipient, $subject, $body, $data, $queue, true, $record_id, $charset);
		}

		/**
		 * Send Email as Plain Text
		 * @param string $recipient Email address of recipient
		 * @param string $subject Email subject
		 * @param string $body Body of email
		 * @param array $data Associative array of merge codes
		 * @param mixed $queue FALSE to send immediately or timestamp to queue
		 * @param int $record_id id of the email queued
		 */
		function SendMail($recipient, $subject, $body, $data, $queue = false, $record_id = null, $charset = null) {
			return $this->SendTheMail($recipient, $subject, $body, $data, $queue, false, $record_id, $charset);
		}

		// -----------------------------------------
		// Form Values Functions
		/**
		 * Outputs checked="true" if $value1 == $value2 or
		 * if $value1 is in $value2
		 * @param string $value1
		 * @param string|array $value2
		 */
		function Checked($value1, $value2) {
			$string = ' checked="true" ';
			if (is_array($value2)) {
				if (in_array($value1, $value2))
					echo $string;
			}else {
				if ($value1 == $value2)
					echo $string;
			}
		}

		/**
		 * Outputs selected="true" if $value1 == $value2 or
		 * if $value1 is in $value2
		 * @param string $value1
		 * @param string|array $value2
		 * @param boolean $strict TRUE if $value1 must be an exact of $value2
		 */
		function Selected($value1, $value2, $strict = false) {
			$string = ' selected="true" ';
			if (is_array($value2)) {
				if (in_array($value1, $value2, $strict))
					echo $string;
			}else {
				if ($strict) {
					if ($value1 === $value2)
						echo $string;
				}else {
					if ($value1 == $value2)
						echo $string;
				}
			}
		}

		/**
		 * Outputs $value if it's not empty or $default if $value is empty
		 * @param <type> $value
		 * @param <type> $default
		 */
		function Value($value, $default) {
			if (!$value)
				$value = $default;
			echo htmlentities($value, ENT_QUOTES);
		}

		/**
		 * Sort Memership Levels according to a given field
		 * @param array $wpm_levels Membership Levels
		 * @param string $sortorder d|a  d for desc and a for asc
		 * @param string $sort_field the field to be userd for sorting .. name|id are
		 * the only supported values for now
		 */
		function SortLevels(&$wpm_levels, $sortorder, $sort_field = 'name') {
			//do this so that we can use id as the sort field
			//we'll remove it afterwards
			foreach ($wpm_levels as $i => &$item) {
				$item['id'] = $i;
			}
			//make sure to do this!! look at the manual
			unset($item);

			$sort_fn = 'asort';
			if ($sortorder == 'd') {
				$sort_fn = 'arsort';
			}

			//well use regular sorting except for the id
			//which is numeric
			$sort_type = SORT_REGULAR;
			if ($sort_field == 'id') {
				$sort_type = SORT_NUMERIC;
			}

			$sort_field_tmp = array();
			foreach ($wpm_levels as $item) {
				//lowercase for case-insensitive sorting
				$sort_field_tmp[] = strtolower($item[$sort_field]);
			}

			//now sort
			$sort_fn($sort_field_tmp);

			//build the sorted array
			//we are performance freaks :)
			$orig_tmp = $wpm_levels;
			$sorted_arr = array();
			foreach ($sort_field_tmp as $v) {
				foreach ($orig_tmp as $i => $item) {
					if (strtolower($item[$sort_field]) == $v) {
						$sorted_arr[$i] = $item;
						// remove this item, so we have lesser loops later
						unset($orig_tmp[$i]);
					}
				}
			}
			$wpm_levels = $sorted_arr;
			//remove the id member variable
			//so that we are consistent
			foreach ($wpm_levels as $i => &$item) {
				unset($item['id']);
			}
			unset($i);
		}

		/**
		 * Retrieves the WishList Member page.
		 * It also generates the page if it does not exist
		 * @param boolean $link TRUE to return the link or FALSE to just return the page ID
		 * @return mixed URL | Page ID
		 */
		function MagicPage($link = true) {
			/*
			 * This method has been totally recoded
			 * to make it work with the new WP 2.9
			 * Trash feature.
			 *
			 * It also takes care of the bug wherein
			 * WishList Member generates hundreds to
			 * thousands of "WishList Member" magic
			 * pages when conflicting plugins are
			 * installed.
			 */
			global $wpdb;

			$content = '<p>This page is auto-generated by the WishList Member Plugin. This status of this page must be set to Published. Do not delete this page or put it to Trash.</p>';
			$date = '2000-01-01 00:00:00'; // this will act as our "marker".
			$title = 'WishList Member';

			$wpmpageQuery = "SELECT `ID` FROM `{$wpdb->posts}` WHERE `post_date`='{$date}' AND `post_status`='publish' AND `post_type`='page' ORDER BY `ID` DESC LIMIT 1";

			$wpmpage = $this->GetOption('magic_page');
			$page_data = (object) get_page($wpmpage);

			if ($page_data->post_status != 'publish')
				$wpmpage = $wpdb->get_var($wpmpageQuery);

			$page_data = (object) get_page($wpmpage);
			if ($page_data->post_status != 'publish') {
				$toinsert = array(
					'post_title' => $title, // title
					'post_content' => $content, // content
					'post_status' => 'publish', // published
					'post_author' => '1', // author is admin
					'ping_status' => 'closed', // no pings
					'comment_status' => 'closed', // no comments
					'post_type' => 'page', // type page
					'post_date' => $date // post date
				);

				$wpmpage = wp_insert_post($toinsert);
				if (!$wpmpage) {
					/*
					 * something bad happened. our post was not added somehow. it should
					 * be added in normal situations and the only reason why an apparent
					 * failure appears is due to conflicting plugins.
					 *
					 * some conflicting plugins are installed mess up with the return
					 * value of wp_insert_post which evaluates to false
					 *
					 * our solution is to attempt to get the magic page's ID by searching
					 * for it straight in the database.  obviously, this is just a
					 * workaround and may not work sometimes.
					 *
					 * our database search criteria are as follows:
					 * - post_date = 2000-01-01 00:00:00 (marker)
					 * - post_status = publish
					 * - post_type = page
					 */
					$wpmpage = $wpdb->get_var($wpmpageQuery);
				}
			}
			$this->SaveOption('magic_page', $wpmpage);
			//wlm_cache_flush(); // taken out for semiologic's cause... let's see what happens.
			if ($link) {
				return get_permalink($wpmpage);
			} else {
				return $wpmpage;
			}
		}

		/**
		 * Get User Feed Key
		 * @param int $id User ID
		 * @return string
		 */
		function FeedKey($id = null, $no_verify = false) {
			$public = '';
			$user = new stdClass();
			if (is_null($id)) {
				$user = wp_get_current_user();
			} else {
				if ($no_verify) {
					$user->ID = $id;
				} else {
					$user = $this->Get_UserData($id);
				}
			}
			if ($user->ID) {
				$sk = $this->GetOption('rss_secret_key');
				// this messes up the rss feeds when a user sequentially upgrades because the key changes.
				// $public=$user->ID.';'.md5($user->ID.';'.implode(',',$this->GetMembershipLevels($user->ID)).';'.$sk);
				// this is the right way to do it! the key never changes for the user id.
				$public = $user->ID . ';' . md5($user->ID . ';' . md5($sk) . ';' . $sk);
			}
			return $public;
		}

		/**
		 * Check for Category Protection Status
		 * @param int $id Category ID
		 * @param char $status (optional) Y|N
		 * @return boolean
		 */
		function CatProtected( $id, $status = null ) {
			global $wpdb;
			$id+=0;
			$current_status = $this->SpecialContentLevel($id, 'Protection', null, '~CATEGORY');
			if ( ! is_null( $status ) ) {
				$new_status = $this->SpecialContentLevel($id, 'Protection', $status, '~CATEGORY');
				if ( $current_status != $new_status ) {
					$current_status = $new_status;
				}
			}
			return $current_status;
		}
		
		/**
		 * Set Folder Protection
		 * @param integer $folder_id
		 * @param bool|string $status Boolean value or Y / N
		 */
		function FolderProtected($folder_id, $status = null) {
			if(!is_null($status)) {
				$this->folder_protection_htaccess(true);
				$this->SpecialContentLevel($folder_id, 'Protection', $status, '~FOLDER');
				
			}
			return $this->SpecialContentLevel($folder_id, 'Protection', null, '~FOLDER');
		}
		
		function FolderForceDownload($folder_id, $status = null) {
			if(!is_null($status)) {
				$this->SpecialContentLevel($folder_id, 'ForceDownload', $status, '~FOLDER');
			}
			return $this->SpecialContentLevel($folder_id, 'ForceDownload', null, '~FOLDER');
		}
		
		/**
		 * Returns relative path of folder protection parent folder
		 * @param string $folder_name
		 * @return string
		 */
		function folder_protection_relative_path($folder_name) {
			return preg_replace(array('/^\/*/', '/\/*$/'), '', array_pop(explode(ABSPATH, $folder_name)));
		}
		
		/**
		 * Returns full path of folder protection parent folder
		 * @param string $folder_name
		 * @return string
		 */
		function folder_protection_full_path($folder_name) {
			return ABSPATH . $this->folder_protection_relative_path($folder_name);
		}
		
		/**
		 * Computes unsigned crc32 of base folder name and returns it as ID
		 * @param string $folder_name
		 * @return integer
		 */
		function FolderID($folder_name) {
			return crc32(basename($folder_name));
		}

		/**
		 * Generate Password
		 * @param int $length (optional) default=min_passlength setting
		 * @return string Random password
		 */
        function PassGen($length = null) {
            // if $length is empty then we set to $length to our `min_passlength` setting
            if(empty($length)) {
                $length = $this->GetOption( 'min_passlength' ) + 0;
            }
            // if $length is still empty then we set it to 8
            if(empty($length)) {
                $length = 8;
            }
            return implode('', array_rand(array_flip(array_merge(range('A', 'Z'), range('a', 'z'), range(0, 9))), $length));
        }

		/**
		 * Get Unsubscribe Confirmation URL
		 * @param string $default (optional)
		 * @return string
		 */
		function UnsubscribeURL() {
			$url = $this->GetOption('unsubscribe_internal');
			$url = $url ? get_permalink($url) : $this->GetOption('unsubscribe');
			return $url;
		}

		/**
		 * Get Non-Members URL
		 * @param string $default (optional)
		 * @return string
		 */
		function NonMembersURL() {
			global $post;
			$url = $this->GetOption('non_members_error_page_internal');
			$url = $url ? get_permalink($url) : $this->GetOption('non_members_error_page');

			if (isset($post->ID) || $post->ID > 0) {
				$url2 = $this->GetOption('non_members_error_page_internal_' . $post->ID);
				$url2 = $url2 ? get_permalink($url2) : $this->GetOption('non_members_error_page_' . $post->ID);
				$url = $url2 ? $url2 : $url;
			}

			$url = !$url ? get_bloginfo('url') : $this->AppendCurrentURI($url);
			return $url;
		}

		/**
		 * Get Cancelled Level URL
		 * @param string $default (optional)
		 * @return string
		 */
		function CancelledURL() {
			global $post;
			$url = $this->GetOption('membership_cancelled_internal');
			$url = $url ? get_permalink($url) : $this->GetOption('membership_cancelled');

			if (isset($post->ID) || $post->ID > 0) {
				$url2 = $this->GetOption('membership_cancelled_internal_' . $post->ID);
				$url2 = $url2 ? get_permalink($url2) : $this->GetOption('membership_cancelled_' . $post->ID);
				$url = $url2 ? $url2 : $url;
			}

			$url = !$url ? get_bloginfo('url') : $this->AppendCurrentURI($url);
			return $url;
		}

		/**
		 * Get Wrong Level URL
		 * @param string $default (optional)
		 * @return string
		 */
		function WrongLevelURL($default = '') {
			global $post;
			if (!$default)
				$default = get_bloginfo('url');
			$url = $this->GetOption('wrong_level_error_page_internal');
			$url = $url ? get_permalink($url) : $this->GetOption('wrong_level_error_page');

			if (isset($post->ID) || $post->ID > 0) {
				$url2 = $this->GetOption('wrong_level_error_page_internal_' . $post->ID);
				$url2 = $url2 ? get_permalink($url2) : $this->GetOption('wrong_level_error_page_' . $post->ID);
				$url = $url2 ? $url2 : $url;
			}

			$url = !$url ? $default : $this->AppendCurrentURI($url);
			return $url;
		}

		/**
		 * Get For Approval URL
		 * @param string $default (optional)
		 * @return string
		 */
		function ForApprovalURL($default = '') {
			global $post;
			if (!$default)
				$default = get_bloginfo('url');
			$url = $this->GetOption('membership_forapproval_internal');
			$url = $url ? get_permalink($url) : $this->GetOption('membership_forapproval');

			if (isset($post->ID) || $post->ID > 0) {
				$url2 = $this->GetOption('membership_forapproval_internal_' . $post->ID);
				$url2 = $url2 ? get_permalink($url2) : $this->GetOption('membership_forapproval_' . $post->ID);
				$url = $url2 ? $url2 : $url;
			}

			$url = !$url ? $default : $this->AppendCurrentURI($url);
			return $url;
		}

		/**
		 * Get For Confirmation URL
		 * @param string $default (optional)
		 * @return string
		 */
		function ForConfirmationURL($default = '') {
			global $post;
			if (!$default)
				$default = get_bloginfo('url');
			$url = $this->GetOption('membership_forconfirmation_internal');
			$url = $url ? get_permalink($url) : $this->GetOption('membership_forconfirmation');

			if (isset($post->ID) || $post->ID > 0) {
				if (!$this->GetOption('membership_forconfirmation_internal_' . $post->ID) == '') {
					$url = $this->GetOption('membership_forconfirmation_internal_' . $post->ID);
					$url = $url ? get_permalink($url) : $this->GetOption('membership_forconfirmation_' . $post->ID);
				}
			}

			$url = !$url ? $default : $this->AppendCurrentURI($url);
			return $url;
		}

		/**
		 * Get Expired URL
		 * @param string $default (optional)
		 * @return string
		 */
		function ExpiredURL($default = '') {
			global $post;
			if (!$default)
				$default = get_bloginfo('url');
			$url = $this->GetOption('membership_expired_internal');
			$url = $url ? get_permalink($url) : $this->GetOption('membership_expired');

			if (isset($post->ID) || $post->ID > 0) {
				$url2 = $this->GetOption('membership_expired_internal_' . $post->ID);
				$url2 = $url2 ? get_permalink($url2) : $this->GetOption('membership_expired_' . $post->ID);
				$url = $url2 ? $url2 : $url;
			}

			$url = !$url ? $default : $this->AppendCurrentURI($url);
			return $url;
		}

		function DuplicatePostURL($default = '') {
			if (!$default)
				$default = get_bloginfo('url');
			$url = $this->GetOption('duplicate_post_error_page_internal');
			$url = $url ? get_permalink($url) : $this->GetOption('duplicate_post_error_page');
			$url = !$url ? $default : $this->AppendCurrentURI($url);
			return $url;
		}

		/**
		 * Append the Current Request URI to the passed URL
		 * @param string $url
		 * @return string
		 */
		function AppendCurrentURI($url) {
			$qs = (strpos($url, '?') === false) ? '?' : '&';
			$url.=$qs . 'wlfrom=' . urlencode($_SERVER['REQUEST_URI']);
			return $url;
		}

		/**
		 * Get Expired IDs of User Level
		 * @return array
		 */
		function ExpiredMembersID() {
			global $wpdb;
			$wpm_levels = $this->GetOption('wpm_levels');
			$ids = array();
			foreach ((array) $wpm_levels as $levelid => $thelevel) {
				$ids[$levelid] = array();

				if (!$thelevel['noexpire']) {
					$usrlvltbl = $this->Tables->userlevels;
					$usrlvlopttbl = $this->Tables->userlevel_options;
					$calendar = strtoupper(substr($thelevel['calendar'], 0, -1));

					$query = "SELECT lvl.user_id FROM `{$usrlvltbl}` as lvl INNER JOIN `{$usrlvlopttbl}` as lvlop ON lvl.id=lvlop.userlevel_id WHERE lvl.level_id ='{$levelid}' AND lvlop.option_name = 'registration_date' AND date_add(SUBSTRING_INDEX(lvlop.option_value, '#', 1), INTERVAL {$thelevel['expire']} {$calendar})<date_add(NOW(), INTERVAL 0 {$calendar})";
					$user_ids = $wpdb->get_results($query);

					foreach ($user_ids as $user_id) {
						$ids[$levelid][] = $user_id->user_id;
					}
				}
			}
			return $ids;
		}

		function GetExpiringMembers() {
			global $wpdb;
			$wpm_levels = $this->GetOption('wpm_levels');
			$ids = array();
			$days = $this->GetOption('expiring_notification_days');

			foreach ($wpm_levels as $levelid => $thelevel) {
				$usrlvltbl = $this->Tables->userlevels;
				$usrlvlopttbl = $this->Tables->userlevel_options;
				$calendar = strtoupper(substr($thelevel['calendar'], 0, -1));

				if (!empty($thelevel['expire']) && !empty($calendar)) {
					$query = "SELECT lvl.user_id, lvl.level_id FROM `{$usrlvltbl}` as lvl INNER JOIN `{$usrlvlopttbl}` as lvlop ON lvl.id=lvlop.userlevel_id";
					$query .= " WHERE lvl.level_id ='{$levelid}' AND lvlop.option_name = 'registration_date'";
					$query .= " AND datediff(date_add(SUBSTRING_INDEX(lvlop.option_value, '#', 1), interval {$thelevel['expire']} {$calendar}), CURDATE()) <= {$days}";
					$query .= " AND datediff(date_add(SUBSTRING_INDEX(lvlop.option_value, '#', 1), interval {$thelevel['expire']} {$calendar}), CURDATE()) > 0";

					$user_ids = $wpdb->get_results($query);
					foreach ($user_ids as $user_id) {
						$ids[] = array(
							'user_id' => $user_id->user_id,
							'level_id' => $user_id->level_id
						);
					}
				}
			}
			return $ids;
		}

		/**
		 * Get User Level is Expired Status
		 * @param int $level Level ID
		 * @param int $uid User ID
		 * @return boolean
		 */
		function LevelExpired($level, $uid) {
			$expire = $this->LevelExpireDate($level, $uid);
			$expire = apply_filters('wishlistmember_user_expires', $expire, $uid, $level);
			if ($expire === false) {
				return false;
			} else {
				if ($expire < time()) {
					return true;
				} else {
					return false;
				}
			}
		}

		/**
		 * Retrieve User Level Expiry Date
		 * @param int $level Level ID
		 * @param int $uid User ID
		 * @return int Expiry Date Timestamp
		 */
		function LevelExpireDate($level, $uid) {
			$wpm_levels = $this->GetOption('wpm_levels');
			$thelevel = $wpm_levels[$level];
			$start = $this->UserLevelTimestamp($uid, $level);
			$exp_date = false;
			if ($thelevel['noexpire'] OR $start === false) {
				$exp_date = false;
			} else {
				$exp_date = strtotime('+' . $thelevel['expire'] . ' ' . $thelevel['calendar'], $start);
			}
			$exp_date = apply_filters('wishlistmember_user_expire_date', $exp_date, $uid, $level);
			return $exp_date;
		}


		/**
		 * @param int $level Level ID
		 * @param int $uid User ID
		 * @return mixed false | $meta_data
		 */
    function IsLevelScheduled($level, $uid) {
      $schedule_types= array('scheduled_add', 'scheduled_remove', 'scheduled_move');
      foreach($schedule_types as $type) {
        $meta_data = maybe_unserialize(unserialize($this->Get_UserLevelMeta($uid, $level, $type)));
        if($meta_data){
          break;
        }
      }
      if($meta_data['type'] === 'remove' && $meta_data['is_current_level']){
        return false;
      } else if($meta_data) {
      	return $meta_data;
      } else {
        return;
      }
    }

		/**
		 * Checks if User Level has a shoppingcart pending status
		 * @param int $level Level ID
		 * @param array $uid User IDs
		 * @return string (pending reason)
		 */
		function IsPendingShoppingCartApproval($level, $uid) {
			$uid = (array) $uid;
			list($id) = $uid;
			return $this->Get_UserLevelMeta($id, $level, 'forapproval');
		}

		/**
		 * Get/Set User Level For Approval Status
		 * @param int $level Level ID
		 * @param array $uid User IDs
		 * @param boolean $status (optional)
		 * @param int $time (optional)
		 * @return int
		 */
		function LevelForApproval($level, $uid, $status = null, $time = null) {
			$uid = (array) $uid;
			if (!is_null($status)) {
				if (is_null($time)) {
					$time = time();
				}
				$time = gmdate('Y-m-d H:i:s', $time);
				if ($status) {
					foreach ($uid AS $id) {
						if (!$this->LevelForApproval($level, $id)) {
							$this->Update_UserLevelMeta($id, $level, 'forapproval', $status);
							$this->Update_UserLevelMeta($id, $level, 'forapproval_date', $time);

							do_action('wishlistmember_unapprove_user_levels', $id, (array) $level);
						}
					}
				} else {
					foreach ($uid AS $id) {
						if ($this->LevelForApproval($level, $id)) {
							$this->Update_UserLevelMeta($id, $level, 'forapproval', 0);
							$this->Update_UserLevelMeta($id, $level, 'forapproval_date', $time);

							$this->UserLevelTimestamp($id, $level, $time);

							do_action('wishlistmember_approve_user_levels', $id, (array) $level, 'autoresponder_add_pending_admin_approval');
						}
					}
				}
			}
			list($id) = $uid;
			return $this->Get_UserLevelMeta($id, $level, 'forapproval');
		}

		/**
		 * Wrapper for LevelForApproval
		 */
		function LevelPending($level, $uid, $status = null, $time = null) {
			return $this->LevelForApproval($level, $uid, $status, $time);
		}

		/**
		 * Get / Set User Level UnConfirmed Status
		 * @param int $level Level ID
		 * @param array $uid User IDs
		 * @param boolean $status (optional)
		 * @param int $time (optional)
		 * @return int
		 */
		function LevelUnConfirmed($level, $uid, $status = null, $time = null) {
			$uid = (array) $uid;
			if (!is_null($status)) {
				if (is_null($time))
					$time = time();
				$time = gmdate('Y-m-d H:i:s', $time);
				if ($status) {
					foreach ($uid AS $id) {
						if (!$this->LevelUnConfirmed($level, $id)) {
							$this->Update_UserLevelMeta($id, $level, 'unconfirmed', 1);
							$this->Update_UserLevelMeta($id, $level, 'unconfirmed_date', $time);

							do_action('wishlistmember_unconfirm_user_levels', $id, (array) $level);
						}
					}
				} else {
					foreach ($uid AS $id) {
						if ($this->LevelUnConfirmed($level, $id)) {
							$this->Update_UserLevelMeta($id, $level, 'unconfirmed', 0);
							$this->Update_UserLevelMeta($id, $level, 'unconfirmed_date', $time);

							do_action('wishlistmember_confirm_user_levels', $id, (array) $level, 'autoresponder_add_pending_email_confirmation');
						}
					}
				}
			}
			list($id) = $uid;
			return $this->Get_UserLevelMeta($id, $level, 'unconfirmed');
		}

		/**
		 * Get/Set User Leval Cancellation Status
		 * @param int $level Level ID
		 * @param array $uid User IDs
		 * @param boolean $status (optional)
		 * @param int $time (optional)
		 * @return int
		 */
		function LevelCancelled($level, $uid, $status = null, $time = null) {
			$uid = (array) $uid;
			if (!is_null($status)) {
				if (is_null($time))
					$time = time();
				$time = gmdate('Y-m-d H:i:s', $time);
				if ($status) {
					foreach ($uid AS $id) {
						if (!$this->LevelCancelled($level, $id)) {
							$this->Update_UserLevelMeta($id, $level, 'cancelled', 1);
							$this->Update_UserLevelMeta($id, $level, 'cancelled_date', $time);

							$usr = $this->Get_UserData($id);
							if ($usr->ID) {
								$this->ARUnsubscribe($usr->first_name, $usr->last_name, $usr->user_email, $level);
							}
							do_action('wishlistmember_cancel_user_levels', $id, (array) $level);
						}
					}
				} else {
					foreach ($uid AS $id) {
						if ($this->LevelCancelled($level, $id)) {
							$this->Update_UserLevelMeta($id, $level, 'cancelled', 0);
							$this->Update_UserLevelMeta($id, $level, 'cancelled_date', $time);

							$usr = $this->Get_UserData($id);
							if ($usr->ID) {
								$this->ARSubscribe($usr->first_name, $usr->last_name, $usr->user_email, $level);
							}
							do_action('wishlistmember_uncancel_user_levels', $id, (array) $level);
						}

						if ($this->LevelSequentialCancelled($level, $id)) {
                            // Also uncancel sequential_cancelled when level is manuall uncancelled or a rebill has been processed
                            $this->LevelSequentialCancelled($level, $id, false);
                    	}
					}
				}
				foreach ($uid AS $id) {
					$this->Delete_UserLevelMeta($id, $level, 'wlm_schedule_level_cancel');
				}
			}
			list($id) = $uid;
			return $this->Get_UserLevelMeta($id, $level, 'cancelled');
		}

		function LevelCancelDate($level, $uid) {
			$date = $this->Get_UserLevelMeta($uid, $level, 'wlm_schedule_level_cancel');
			if (empty($date))
				$date = false;
			if (!is_numeric($date)) {
				$date = strtotime($date);
			}
			return $date;
		}

		/**
		 * Get/Set User Level Parent
		 * @param int $level Level ID
		 * @param array $uid User IDs
		 * @param int $parent Level ID
		 * @return Level ID
		 */
		function LevelParent($level, $uid, $parent = null) {
			$uid = (array) $uid;
			$wpm_levels = $this->GetOption('wpm_levels');
			if ( ! is_null( $parent ) ) {
				if ( isset( $wpm_levels[$parent] ) ) {
					foreach ($uid AS $id) {
						$this->Update_UserLevelMeta($id, $level, 'parent_level', $parent);
					}
				} else if ( $parent == '' ) {
					foreach ($uid AS $id) {
						$this->Update_UserLevelMeta($id, $level, 'parent_level', null);
					}
				}
			}
			list($id) = $uid;
			$p = $this->Get_UserLevelMeta($id, $level, 'parent_level');
			return isset( $wpm_levels[$p] ) ? $p : false;
		}


		/**
		 * Get Child Levels of Parent
		 * @param int $uid User ID
		 * @param array $parent Level ID
		 * @return array Level IDs
		 */
		function GetChildLevels( $uid, $parent_levels ) {
			global $wpdb;
			$levels = (array) $parent_levels;
			foreach ($levels as $key => $lvl ) {
				if( strpos($lvl, 'U-') !== false ) {
					unset( $levels[$key] );
				}
			}
			if ( count($levels) <= 0 ) return array();

			$usrlvltbl = $this->Tables->userlevels;
			$usrlvlopttbl = $this->Tables->userlevel_options;
			$level = "'" .implode( "','", $levels ) ."'";
			$query = "SELECT lvl.level_id FROM `{$usrlvltbl}` as lvl INNER JOIN `{$usrlvlopttbl}` as lvlop ON lvl.id=lvlop.userlevel_id";
			$query .= " WHERE lvlop.option_name='parent_level' AND lvlop.option_value IN ({$level}) AND lvl.user_id={$uid}";
			$level_ids = $wpdb->get_col($query);
			if( count($level_ids) <= 0 ) return array();
			$level_ids = array_unique( $level_ids );
			return $level_ids;
		}

		/**
		 * Removes Child Levels of the Parent
		 * @param int $uid User IDs
		 * @param array $parent_levels Level ID
		 * @return Level ID
		 */
		function RemoveChildLevels( $uid, $parent_levels ) {
			$level_ids = $this->GetChildLevels( $uid, $parent_levels );
			if( count($level_ids) <= 0 ) return;
			$wlmuser        = new WishListMemberUser($uid);
			$current_levels = array_keys($wlmuser->Levels);
			$levels = array_unique( array_diff( $current_levels, $level_ids ) );
			// we update the levels
			$this->SetMembershipLevels( $uid, $levels);
		}

		/**
		 * Updates Child status when Parent Status Change
		 * @param int $uid User IDs
		 * @param array $parent_levels Level ID
		 * @return Level ID
		 */
		function UpdateChildStatus( $uid, $parent_levels ) {
			$level_ids = array();
			foreach( $parent_levels as $p ) {
				$clvls = $this->GetChildLevels( $uid, $p );
				if( count($clvls) > 0 ) {
					$level_ids[$p] = $clvls;
				}
			}
			if( count($level_ids) <= 0 )  return; //nothing to do here

			$wlmuser        = new WishListMemberUser($uid);
			foreach( $level_ids as $plvl=>$clvls ) {
				if( ! isset($wlmuser->Levels[$plvl]) ) continue; //nothing to do here
				$active = ! ( $wlmuser->Levels[$plvl]->Cancelled || (bool) $wlmuser->Levels[$plvl]->Pending || $wlmuser->Levels[$plvl]->UnConfirmed ) ;
				if( $active ) {
					foreach( $clvls as $clvl ) {
						if( ! $wlmuser->Levels[$clvl]->Active ) {
							$this->Delete_UserLevelMeta($uid, $clvl, 'forapproval');
							$this->Delete_UserLevelMeta($uid, $clvl, 'forapproval_date');

							$this->Delete_UserLevelMeta($uid, $clvl, 'unconfirmed');
							$this->Delete_UserLevelMeta($uid, $clvl, 'unconfirmed_date');

							$this->Delete_UserLevelMeta($uid, $clvl, 'cancelled');
							$this->Delete_UserLevelMeta($uid, $clvl, 'cancelled_date');
						}
					}
				} else {
					foreach( $clvls as $clvl ) {
						if ( $wlmuser->Levels[$plvl]->Cancelled ) {
							$this->LevelCancelled($clvl, $uid, true );
						}
						if ( $wlmuser->Levels[$plvl]->Pending ) {
							$this->LevelForApproval($clvl, $uid, true );
						}
						if ( $wlmuser->Levels[$plvl]->UnConfirmed ) {
							$this->LevelUnConfirmed($clvl, $uid, true );
						}
					}
				}
			}
		}
		/**
		 * Get/Set User Leval Sequential Cancellation Status
		 * @param int $level Level ID
		 * @param array $uid User IDs
		 * @param boolean $status (optional)
		 * @param int $time (optional)
		 * @return int
		 */
		function LevelSequentialCancelled($level, $uid, $status = null, $time = null) {
			$uid = (array) $uid;
			if (!is_null($status)) {
				if (is_null($time))
					$time = time();
				$time = gmdate('Y-m-d H:i:s', $time);
				if ($status) {
					foreach ($uid AS $id) {
						if (!$this->LevelSequentialCancelled($level, $id)) {
							$this->Update_UserLevelMeta($id, $level, 'sequential_cancelled', 1);
							$this->Update_UserLevelMeta($id, $level, 'sequential_cancelled_date', $time);
						}
					}
				} else {
					foreach ($uid AS $id) {
						if ($this->LevelSequentialCancelled($level, $id)) {
							$this->Update_UserLevelMeta($id, $level, 'sequential_cancelled', 0);
							$this->Update_UserLevelMeta($id, $level, 'sequential_cancelled_date', $time);
						}
					}
				}
			}
			list($id) = $uid;
			return $this->Get_UserLevelMeta($id, $level, 'sequential_cancelled');
		}

		# function to check and see if the transaction id exists. Added 5/21/2010 Glen Barnhardt

		function CheckMemberTransID($txid) {
			$txns = $this->Get_UserID_From_UserLevelsMeta('transaction_id', $txid);
			if (empty($txns)) {
				return false;
			} else {
				return true;
			}
		}

		/**
		 * Shopping Cart Registration
		 * @param boolean $temp (optional) TRUE if temporary account
		 * @param boolean $redir (optional) TRUE to redirect to regisrtation form
		 */
		function ShoppingCartRegistration($temp = null, $redir = null, $pendingstatus = null) {
			if (is_null($temp))
				$temp = true;
			if (is_null($redir))
				$redir = true;

			// expects values in $_POST
			$_POST['orig_email'] = $_POST['email'];

			if ($temp) {
				// set temporary email because we will change things later...
				$_POST['username'] = $_POST['email'] = 'temp_' . md5($_POST['email']);
				// we don't want any emails sent for temporary accounts
				$sendmail = false;
				$notifyadmin = false;
			} else {
				// send emails because this is not a temporary account
				$sendmail = true;
				$notifyadmin = true;
			}

			$wpm_levels = $this->GetOption('wpm_levels');
			$existing = false;
			$email_exists = "";
			$payperpost = $this->IsPPPLevel(wlm_arrval($_POST, 'wpm_id'));
			if (isset($wpm_levels[wlm_arrval($_POST, 'wpm_id')]) || $payperpost) {
				$wpm_errmsg = '';
				$email_exists = email_exists(wlm_arrval($_POST, 'orig_email'));
				$registered = $this->WPMRegister($_POST, $wpm_errmsg, $sendmail, $notifyadmin, null, $pendingstatus);
				if (!$registered && $temp) {
					$u = new WP_User(wlm_arrval($_POST, 'username'));
					/**
					 * Do not fail registration if
					 * 1. This is a temporary account and
					 * 2. It failed registration because the same
					 * tmp account
					 * --Reuse the tmp account instead so that the user may be able
					 * to complete it.
					 */
					if (!$u) {
						return $wpm_errmsg;
					}
					$registered = true;
					if ($redir) {
						$location = $this->GetContinueRegistrationURL(wlm_arrval($_POST, 'orig_email'));
						if ($email_exists && $this->GetOption('redirect_existing_member')) {
							$location .= "&existing=1";
						}
						if ($location) {
							header('Location:' . $location);
							exit;
						}
					}
				}

				if ($registered) {
					do_action('wishlistmember_shoppingcart_register', $this);
				} else {
					$xid = email_exists(wlm_arrval($_POST, 'email'));
					if (!$xid)
						$xid = username_exists(wlm_arrval($_POST, 'username'));
					if ($xid) {
						$this->WPMRegisterExisting($_POST, $wpm_errmsg, $sendmail, $notifyadmin, true);

						$registered = true;
						$existing = true;
					}
				}

				if ($registered && $existing) {
					// uncancel "cancelled" members when they "re-pay"
					$this->ShoppingCartReactivate();
				}

				if ($registered && !$temp) {
					do_action('wishlistmember_after_registration');
				}

				if ($redir) {
					if (!$existing && $temp) {
						@wlm_setcookie('wpmu', $_POST['email'], 0, '/');
						$location = $this->GetRegistrationURL($_POST['wpm_id'], true, $dummy);
						if ($email_exists && $this->GetOption('redirect_existing_member')) {
							$location .= "&existing=1";
						}
						header('Location:' . $location);
						exit;
					}

					// redirect to "processing" page
					$location = $this->GetRegistrationURL($_POST['wpm_id'], false, $dummy) . '&registered=1';
					header('Location:' . $location);
					exit;
				}
			} else {
				// we got an invalid membership level ID
				header("Location:" . get_bloginfo('url'));
				exit;
			}
		}

		/**
		 * Shopping Cart Membership De-activation
		 * @return boolean TRUE on success
		 */
		function ShoppingCartDeactivate() {
			// expects values in $_POST
			// add member to level's cancelled list
			$wpm_levels = $this->GetOption('wpm_levels');

			// we search for the user who has wlm_sctxns set to the posted transaction ID
			$user = $this->GetUserIDFromTxnID(wlm_arrval($_POST, 'sctxnid'));
			if ($user)
				$user = $this->Get_UserData($user);

			// load user posts from transaction id
			$userposts = $this->GetUserPostsFromTxnID(wlm_arrval($_POST, 'sctxnid'));

			// no user still?  then load one from the posted username
			if (!$user->ID)
				$user = $this->Get_UserData(0, $_POST['username']);
			if ($user->ID || $userposts) {
				$levels = array_intersect(array_keys((array) $this->GetMembershipLevelsTxnIDs($user->ID, $_POST['sctxnid'])), $this->GetMembershipLevels($user->ID));
				foreach ((array) $levels AS $level) {
					if (!$wpm_levels[$level]['isfree']) {
						$this->LevelCancelled($level, $user->ID, true);
					} else {
						$this->LevelSequentialCancelled($level, $user->ID, true);
					}
				}


				if ($userposts) {
					foreach ($userposts AS $userpost) {
						$this->RemovePostUsers($userpost->type, $userpost->content_id, $userpost->level_id);
					}
				}

				do_action('wishlistmember_shoppingcart_deactivate', $this);
				return true;
			} else {
				$this->CartIntegrationTerminate();
			}
		}

		/**
		 * Shopping Cart Membership Re-activation
		 * @return boolean TRUE on success
		 */
		function ShoppingCartReactivate($processpending = null) {
			// expects values in $_POST
			// remove member from level's cancelled list
			// we search for the user who has wlm_sctxns set to the posted transaction ID
			$user = $this->GetUserIDFromTxnID(wlm_arrval($_POST, 'sctxnid'));
			if ($user)
				$user = $this->Get_UserData($user);

			// no user still?  then load one from the posted username
			if (!$user->ID)
				$user = $this->Get_UserData(0, $_POST['username']);
			if ($user->ID) {
				$levels = array_intersect(array_keys((array) $this->GetMembershipLevelsTxnIDs($user->ID, $_POST['sctxnid'])), $this->GetMembershipLevels($user->ID));
				foreach ((array) $levels AS $level) {
					if (!is_null($processpending))
						$this->LevelForApproval($level, $user->ID, false);
					else
						$this->LevelCancelled($level, $user->ID, false);
				}
				do_action('wishlistmember_shoppingcart_reactivate', $this);
				return true;
			} else {
				$this->CartIntegrationTerminate();
			}
		}

		# Shopping cart deactivation will set a meta_key of deactivate_date for a membership level. Glen Barnhardt 4/15/2010

		function ScheduleCartDeactivation() {
			global $wpdb;
			// expects values in $_POST
			// add member to level's scheduled for cancel.
			$wpm_levels = $this->GetOption('wpm_levels');

			// we search for the user who has wlm_sctxns set to the posted transaction ID
			$user = $this->GetUserIDFromTxnID(wlm_arrval($_POST, 'sctxnid'));
			if ($user)
				$user = $this->Get_UserData($user);

			// no user still?  then load one from the posted username
			if (!$user->ID)
				$user = $this->Get_UserData(0, $_POST['username']);
			if ($user->ID) {
				$levels = array_intersect(array_keys((array) $this->GetMembershipLevelsTxnIDs($user->ID, $_POST['sctxnid'])), $this->GetMembershipLevels($user->ID));
				# first check to see if the array has been set.
				$cancel_array = $this->Get_UserMeta($user->ID, 'wlm_schedule_member_cancel');
				foreach ((array) $levels AS $level) {
					if (!$wpm_levels[$level]['isfree']) {
						# if the array has been set see if the value being set is in the array.
						if (!empty($cancel_array[$level])) {
							$cancel_array[$level] = $_POST['ddate'];
						} else {
							$cancel_array[$level] = $_POST['ddate'];
						}
					}
				}
				$update_status = $this->Update_UserMeta($user->ID, 'wlm_schedule_member_cancel', $cancel_array);
				return true;
			}
			header("Location:" . get_bloginfo('url'));
			exit;
		}

		// Cacellation date function. This function stores the data to cancel a date later.
		function ScheduleLevelDeactivation($wpm_membership_to, $wpm_member_id, $cancel_date ) {
			global $wpdb;
			$good = $bad = 0;
			foreach ($wpm_member_id as $user_id) {
				$time = gmdate('Y-m-d H:i:s', $cancel_date);
				$remove_date = $this->Get_UserLeveLMeta($user_id, $wpm_membership_to, 'scheduled_remove');
				if($remove_date && $remove_date['date'] < $time) {
					$bad++;
				} else {
					$good++;
					$this->Update_UserLevelMeta($user_id, $wpm_membership_to, 'wlm_schedule_level_cancel', $time);
				}
			}
			if($bad) {
				$_POST['notice'] = sprintf(__('Scheduled cancellation were not processed for %d user%s because a scheduled removal with an earlier date is already in place.', 'wishlist-member'), $bad, ($bad != '1' ? 's' : ''));
			}
			return true;
		}

		// Run by cron to Move,Remove and Add user to certain level.
		function RunScheduledUserLevels() {
			$users = $this->MemberIDs();
			$levels = false;
			$option_names = array(
				'scheduled_add',
				'scheduled_move',
				'scheduled_remove'
			);
			if (!empty($users)) {
				foreach ($users as $user) {
					foreach ($option_names as $name) {
						$this->VerifyScheduledLevels($user, $name);
					}
				}
			}
		}

		/**
		 * Handles all the query for scheduled user level
		 *
		 * @param type $uid
		 * @param type $option_name
		 */
		function VerifyScheduledLevels($uid, $option_name) {		
			$current_date =  gmdate('Y-m-d'). " 23:59:59";
			$levels = $this->Get_Levels_From_UserLevelsMeta($uid, $option_name);
			if(empty($levels)) {
				return;
			}
            $usr = $this->Get_UserData($uid);
			$current_levels = $this->GetMembershipLevels($uid);

			foreach($levels AS $level) {
				$meta = maybe_unserialize(maybe_unserialize($this->Get_UserLeveLMeta($uid, $level, $option_name)));

				if($meta['date'] <= $current_date) {
					
					switch($meta['type']) {
						case 'move':
							$current_levels = array_intersect($current_levels, array($level));
							$this->SetMembershipLevels($uid, $current_levels);
							// continue to 'add' for adding timestamp and txn id
						case 'add':
							$this->Delete_UserLevelMeta($uid, $level, $option_name);
							$this->UserLevelTimestamp($uid, $level, time());
							$this->SetMembershipLevelTxnID($uid, $level, '');
                            $this->ARSubscribe($usr->first_name, $usr->last_name, $usr->user_email, $level);
						break;
						case 'remove':
							$current_levels = array_diff($current_levels, array($level));
							$this->SetMembershipLevels($uid, array_unique($current_levels));
						break;
					}
				}
			}
		}

		// Cancel scheduled cancellations called by wp_cron. Cancels the scheduled cancellations. Glen Barnhardt 4-16-2010
		function CancelScheduledLevels() {
			global $wpdb;
			$today = gmdate('Y-m-d H:i:s');
			$users = $this->MemberIDs();
			if (!empty($users)) {
				foreach ($users as $user) {
					$levels = $this->Get_Levels_From_UserLevelsMeta($user, 'wlm_schedule_level_cancel');
					if (!empty($levels)) {
						foreach ($levels as $level) {
							$cancel_date = $this->Get_UserLevelMeta($user, $level, 'wlm_schedule_level_cancel');
							if (!empty($cancel_date)) {
								if ($cancel_date <= $today) {
									$this->LevelCancelled($level, $user, true);
									$this->Delete_UserLevelMeta($user, $level, 'wlm_schedule_level_cancel');
								}
							}
						}
					}
				}
			}
		}

		// Get users with incomplete registration: Fel Jun
		function GetIncompleteRegistrations() {
			global $wpdb;
			$ret = array();
			$users = $wpdb->get_results("SELECT DISTINCT user_id,meta_value FROM {$wpdb->usermeta} WHERE meta_key='wlm_incregnotification'");
			if (count($users) > 0) {
				foreach ($users as $user) {
					$user_orig_email = $this->Get_UserMeta($user->user_id, 'wlm_origemail');
					if ($user_orig_email != "") {
						$ret[$user->user_id]["email"] = $user_orig_email;
						$ret[$user->user_id]["wlm_incregnotification"] = maybe_unserialize($user->meta_value);
					}
				}
			}
			return $ret;
		}

		// Cancel scheduled cancellations called by wp_cron. Cancels the scheduled cancellations. Glen Barnhardt 4-16-2010
		function CancelScheduledCancelations() {
			global $wpdb;
			$today = date("Y-m-d");
			$users = $wpdb->get_results("SELECT `user_id` FROM `{$wpdb->usermeta}` WHERE `meta_key`='wlm_schedule_member_cancel'");
			if (!empty($users)) {
				foreach ($users as $user) {
					$userID = $user->user_id;
					$cancel_array = $this->Get_UserMeta($userID, 'wlm_schedule_member_cancel');
					if (!empty($cancel_array)) {
						foreach ($cancel_array as $level => $cancel_date) {
							if ($cancel_date <= $today) {
								$this->LevelCancelled($level, $userID, true);
								$this->RemoveCancelledSchedule($level, $userID);
							}
						}
					}
				}
			}
		}

		// Remove Cancellation Schedules. Glen Barnhardt 4-16-2010
		function RemoveCancelledSchedule($level, $userID) {
			$cancel_array = $this->Get_UserMeta($userID, 'wlm_schedule_member_cancel');
			if (!empty($cancel_array)) {
				foreach ($cancel_array as $key => $value) {
					if ($key == $level) {
						#skip this cause we are removing the level from the schedule
					} else {
						$new_array[$key] = $value;
					}
				}

				if (!empty($new_array)) {
					$this->Update_UserMeta($user->ID, 'wlm_schedule_member_cancel', $new_array);
				} else {
					$this->Delete_UserMeta($userID, 'wlm_schedule_member_cancel');
				}
			}
		}

		/**
		 * Get User ID from Transaction ID
		 * @global object $wpdb
		 * @param string $txnid
		 * @return int User ID
		 */
		function GetUserIDFromTxnID($txnid) {
			global $wpdb;
			$user = $this->Get_UserID_From_UserLevelsMeta('transaction_id', $txnid);
			return $user;
		}

		/*  --------------------- Import/Export Settings Functions ------------------------ */

		/**
		 * Used to export/import configuration settings
		 * @param string|null $restore_data
		 * @return strin|array $out
		 */
		function ExportConfigurations($restore_data = null) {
			global $wpdb;
			//set array for internal pages
			$arr_pages = array(
				'non_members_error_page_internal',
				'wrong_level_error_page_internal',
				'membership_cancelled_internal',
				'membership_forapproval_internal',
				'membership_forconfirmation_internal',
				'after_registration_internal',
				'after_login_internal',
				'after_logout_internal',
				'unsubscribe_internal',
				'duplicate_post_error_page_internal',
			);
			//set array for other configuration settings
			$arr = array(
				'non_members_error_page',
				'wrong_level_error_page',
				'membership_cancelled',
				'membership_forapproval',
				'membership_forconfirmation',
				'after_registration',
				'after_login',
				'after_logout',
				'unsubscribe',
				'recaptcha_public_key',
				'recaptcha_private_key',
				'min_passlength',
				'only_show_content_for_level',
				'hide_from_search',
				'protect_after_more',
				'auto_insert_more',
				'auto_insert_more_at',
				'exclude_pages',
				'default_protect',
				'folder_protection',
				'file_protection',
				'file_protection_ignore',
				'private_tag_protect_msg',
				'login_limit',
				'login_limit_error',
				'login_limit_notify',
				'notify_admin_of_newuser',
				'PreventDuplicatePosts',
				'members_can_update_info',
				'show_linkback',
				'affiliate_id'
			);

			//if restore data contains a value, we will restore it
			if ($restore_data != null) {
				$data = maybe_unserialize($restore_data);
				$data_keys = array_keys($data);
				$config_options = array_merge($arr_pages, $arr);
				//restore the settings of internal pages for configuration tab
				foreach ((array) $data_keys AS $option_name) {
					if (array_search($option_name, $arr_pages) !== false) { //check if the key is in the array we set above
						$id = $this->RestorePageData($data[$option_name]); //create a page from the to be used for the option
						if ($id > 0 || $id === "") { //if the page is created succesfully
							$this->SaveOption($option_name, $id);
							if ($id > 0) {
								$page_data = get_page($id);
								$out .= "<span style='color:green'>Page Created[" . $id . "]: </span> '<i>" . $page_data->post_title . "</i>' for [" . $option_name . "].<br />";
							}
						} else { //if the page was not created and it should be created
							$out .= "<span style='color:red'>Warning </span>[" . $id . "]: Cannot create post for '" . $option_name . "'.<br />";
						}
					} else { //if the key is different from the option's we had
						if (array_search($option_name, $config_options) === false) {
							$out .= "<span style='color:red'>Warning: </span>'" . $option_name . "' is Invalid.<br />";
						}
					}
				}

				//restore the rest of the options for configuration tab
				foreach ((array) $data_keys AS $option_name) {
					if (array_search($option_name, $arr) !== false) { //check if the key is in the array we set above
						$this->SaveOption($option_name, $data[$option_name]);
					} else { //if the key is different from the option's we had
						if (array_search($option_name, $config_options) === false) {
							$out .= "<span style='color:red'>Warning: </span>'" . $option_name . "' is Invalid.<br />";
						}
					}
				}
				$out = "<span style='color:green'>Complete</span><br /><blockquote>" . $out . "</blockquote>";
			} else {
				//getting the pages
				foreach ((array) $arr_pages AS $option_name) {
					$data = $this->GetPageData($this->GetOption($option_name)); // get the page data based on the id passed
					$out[$option_name] = $data;
				}
				//getting the rest of the options
				foreach ((array) $arr AS $option_name) {
					$out[$option_name] = $this->GetOption($option_name);
				}
			}
			return maybe_serialize($out);
		}

		/**
		 * Used to export/import Email Settings Tab
		 * @param string|null $restore_data
		 * @return strin|array $out
		 */
		function ExportEmailSettings($restore_data = null) {
			//set array for other configuration settings
			$arr = array(
				'email_sender_name',
				'email_sender_address',
				'email_per_hour',
				'register_email_subject',
				'register_email_body',
				'lostinfo_email_subject',
				'lostinfo_email_message',
				'newmembernotice_email_recipient',
				'newmembernotice_email_subject',
				'newmembernotice_email_message',
				'confirm_email_subject',
				'confirm_email_message',
				'requireadminapproval_email_subject',
				'requireadminapproval_email_message'
			);
			//if restore data contains a value, we will restore it
			if ($restore_data != null) {
				$data = maybe_unserialize($restore_data);
				$data_keys = array_keys($data);
				foreach ((array) $data_keys AS $option_name) {
					if (array_search($option_name, $arr) !== false) {//check if the key is in the array we set above
						$this->SaveOption($option_name, $data[$option_name]);
					} else {
						$out .= "<span style='color:red'>Warning: </span>'" . $option_name . "' is Invalid.<br />";
					}
				}
				$out = "<span style='color:green'>Complete</span><br /><blockquote>" . $out . "</blockquote>";
			} else { //else generate the settings array to be saved
				foreach ((array) $arr AS $option_name) {
					$out[$option_name] = $this->GetOption($option_name);
				}
			}
			return maybe_serialize($out);
		}

		/**
		 * Used to export/import Advance Settings Tab
		 * @param string|null $restore_data
		 * @return strin|array $out
		 */
		function ExportAdvanceSettings($restore_data = null) {
			//set array for other configuration settings
			$arr = array(
				'sidebar_widget_css',
				'login_mergecode_css',
				'reg_form_css',
				'reg_instructions_new',
				'reg_instructions_new_noexisting',
				'reg_instructions_existing'
			);
			//if restore data contains a value, we will restore it
			if ($restore_data != null) {
				$data = maybe_unserialize($restore_data);
				$data_keys = array_keys($data);
				foreach ((array) $data_keys AS $option_name) {
					if (array_search($option_name, $arr) !== false) {//check if the key is in the array we set above
						$this->SaveOption($option_name, $data[$option_name]);
					} else {
						$out .= "<span style='color:red'>Warning: </span>'" . $option_name . "' is Invalid.<br />";
					}
				}
				$out = "<span style='color:green'>Complete</span><br /><blockquote>" . $out . "</blockquote>";
			} else { //else generate the settings array to be saved
				foreach ((array) $arr AS $option_name) {
					$out[$option_name] = $this->GetOption($option_name);
				}
			}
			return maybe_serialize($out);
		}

		/**
		 * Used to export/import Membership Levels
		 * @param string|null $restore_data
		 * @return strin|array $out
		 */
		function ExportMembershipLevels($restore_data = null) {
			global $wpdb;
			//set array for other configuration settings
			$arr = array(
				'wpm_levels',
				'regpage_before',
				'regpage_after',
			);
			//if restore data contains a value, we will restore it
			if ($restore_data != null) {
				$data = maybe_unserialize($restore_data);
				$data_keys = array_keys($data);
				foreach ((array) $data_keys AS $option_name) {
					if (array_search($option_name, $arr) !== false) {//check if the key is in the array we set above
						$this->SaveOption($option_name, $data[$option_name]);
					} else {
						$out .= "<span style='color:red'>Warning: </span>'" . $option_name . "' is Invalid.<br />";
					}
				}
				$out = "<span style='color:green'>Complete</span><br /><blockquote>" . $out . "</blockquote>";
			} else {//else generate the settings array to be saved
				foreach ((array) $arr AS $option_name) {
					if (wlm_arrval($_POST, 'export_registrationpage') == 1 || $option_name == "wpm_levels") { // if including the before/after text on registration page
						$out[$option_name] = $this->GetOption($option_name);
					}
				}
			}
			return maybe_serialize($out);
		}

		/**
		 * Used to get the page data for Exporting the settings
		 * @param int $id
		 * @return strin $out
		 */
		function GetPageData($id) {
			global $wpdb;
			$row = $wpdb->get_row("SELECT * FROM $wpdb->posts WHERE ID =" . $id, ARRAY_A);
			if (empty($row)) {
				return "";
			}
			unset($out['ID']); //take out ID field
			return maybe_serialize($out);
		}

		/**
		 * Used to restore the page data for importing the settings
		 * @param string $data
		 * @return int $id
		 */
		function RestorePageData($data) {
			global $wpdb;
			$table_data = maybe_unserialize($data);
			if (!is_array($table_data))
				return "";
			foreach ((array) $table_data as $key => $value) { //create the post data
				$post_data[$key] = $value;
			}
			return wp_insert_post($post_data); //create the post as page and return the id
		}

		/**
		 * Used to export the settings to file
		 */
		function ExportSettingsToFile() {
			@ini_set('memory_limit', '256M');
			if (wlm_arrval($_POST, 'export_configurations') == 1)
				$arr_settings['export_configurations'] = $this->ExportConfigurations();
			if (wlm_arrval($_POST, 'export_emailsettings') == 1)
				$arr_settings['export_emailsettings'] = $this->ExportEmailSettings();
			if (wlm_arrval($_POST, 'export_advancesettings') == 1)
				$arr_settings['export_advancesettings'] = $this->ExportAdvanceSettings();
			if (wlm_arrval($_POST, 'export_membershiplevels') == 1)
				$arr_settings['export_membershiplevels'] = $this->ExportMembershipLevels();
			if (count($arr_settings) > 0) {
				$filename = "settings_" . gmdate('YmdHis') . ".wlm"; //add date to  the filename
				$settings_str = maybe_serialize($arr_settings); //obfuscate the settings
				$settings['data'] = $settings_str;
				$settings['md5'] = md5($settings_str);
				$settings_data = base64_encode(maybe_serialize($settings));
				header("Content-type:text/plain");
				header("Content-disposition: attachment; filename=" . $filename);
				flush();
				echo $settings_data;
				flush();
				exit;
			}
		}

		/**
		 * Used to restore the settings from file
		 */
		function RestoreSettingsFromFile() {
			$Settingsfile = $_FILES['Settingsfile'];
			$size = $Settingsfile['size'];
			$tmp_name = $Settingsfile['tmp_name'];
			$type = $Settingsfile['type'];
			echo "Reading File ..."; // ============== Read  the File =============
			$handle = fopen($tmp_name, "rb");
			$contents = fread($handle, $size);
			fclose($handle);
			$settings = maybe_unserialize(base64_decode(trim($contents))); //decoding obfuscation
			if (array_key_exists('md5', $settings) && array_key_exists('data', $settings)) {
				if ($settings['md5'] == md5($settings['data'])) {
					$arr_settings = maybe_unserialize($settings['data']);
					if (!empty($arr_settings)) {
						echo " <span style='color:green'>Complete</span><br />";
						if (array_key_exists('export_configurations', $arr_settings) && $arr_settings['export_configurations'] != "") {
							// ============== Restoring  the Configuration Settings =============
							echo "<br />Restoring  the Configuration Settings ...";
							$export_configurations = $arr_settings['export_configurations'];
							echo $this->ExportConfigurations($export_configurations);
						}
						if (array_key_exists('export_emailsettings', $arr_settings) && $arr_settings['export_emailsettings'] != "") {
							// ============== Restoring  the Email Settings =============
							echo "<br />Restoring  the Email Settings ...";
							$export_emailsettings = $arr_settings['export_emailsettings'];
							echo $this->ExportEmailSettings($export_emailsettings);
						}
						if (array_key_exists('export_advancesettings', $arr_settings) && $arr_settings['export_advancesettings'] != "") {
							// ============== Restoring  the Advance Settings =============
							echo "<br />Restoring  the Advance Settings ...";
							$export_advancesettings = $arr_settings['export_advancesettings'];
							echo $this->ExportAdvanceSettings($export_advancesettings);
						}
						if (array_key_exists('export_membershiplevels', $arr_settings) && $arr_settings['export_membershiplevels'] != "") {
							// ============== Restoring  the Membership Levels =============
							echo "<br />Restoring  the Membership Levels ...";
							$export_membershiplevels = $arr_settings['export_membershiplevels'];
							echo $this->ExportMembershipLevels($export_membershiplevels);
                                                        echo "<br /> <b>The Settings Import Has Been Completed Successfully.</b>";
						}
						if (array_key_exists('export_scsettings', $arr_settings) && $arr_settings['export_scsettings'] != "") {
							// ============== Restoring  the Shopping Cart Integration Settings =============
							echo "<br />Restoring  the Shopping Cart Integration Settings ...";
							$export_scsettings = $arr_settings['export_scsettings'];
							echo $this->ExportSCSettings($export_scsettings);
						}
						if (array_key_exists('export_arsettings', $arr_settings) && $arr_settings['export_arsettings'] != "") {
							// ============== Restoring  the Autoresponder Integration Settings =============
							echo "<br />Restoring  the Autoresponder Integration Settings ...";
							$export_arsettings = $arr_settings['export_arsettings'];
							echo $this->ExportARSettings($export_scsettings);
						}
					} else { //if the file is empty or no file is selected yet
						echo "<span style='color:red'> Empty File!</span> Please choose another one.";
					}
				} else {
					echo "<span style='color:red'> Corrupted File!</span> Contents of the file has been changed.";
				}
			} else {
				echo "<span style='color:red'> Cannot Read File.</span> Contents of the file has been changed.";
			}
		}

		/**
		 * Sets Registration Security Cookie
		 * @param boolean|string $set
		 * @param string $hash
		 * @return boolean
		 */
		function RegistrationCookie($set = null, &$hash = null, $level = null) {
			if (is_null($set))
				$set = true;
			if ($set == 'manual') {
				$set = true;
				$manual = true;
			} else {
				$manual = false;
			}
			$level = is_null($level) ? "" : ("_" . $level);

			if ($set === true) {
				$x = time();
				$x = serialize(array(md5(WLMREGCOOKIESECRET . '_' . $x . $level), $x));
				$hash = $x;
				if (!headers_sent()) {
					@wlm_setcookie('wishlist_reg_cookie', $x, 0, '/');

					if ($manual) {
						@wlm_setcookie('wishlist_reg_cookie_manual', 1, 0, '/');
					} else {
						@wlm_setcookie('wishlist_reg_cookie_manual', '', time() - 3600, '/');
					}
				}
				$return = true;
			} elseif ($set === false) {
				$x = maybe_unserialize(stripslashes($GLOBALS['wlm_cookies']->wishlist_reg_cookie));
				if (empty($x) OR !is_array($x))
					return false;
				$timeout = $x[1] + WLMREGCOOKIETIMEOUT;
				$return = (md5(WLMREGCOOKIESECRET . '_' . $x[1] . $level) == $x[0] && time() < $timeout);
			} else {
				if (!headers_sent()) {
					// non-boolean parameter deletes the cookie
					@wlm_setcookie('wishlist_reg_cookie', '', time() - 3600, '/');
					@wlm_setcookie('wishlist_reg_cookie_manual', '', time() - 3600, '/');
				}
				$return = false;
			}
			return $return;
		}

		/**
		 * Redirects to the correct Level Registration URL
		 * @return string Shopping Cart Reg URL?
		 */
		function RegistrationURL() {
			$levels = $this->GetOption('wpm_levels');
			$reg = explode('/register/', $_SERVER['REQUEST_URI']);
			$reg = preg_split('/[\?&\/]/', wlm_arrval($reg, 1));
			$post_id = wlm_arrval($reg, 1);
			$reg = wlm_arrval($reg, 0);

			$fallback = false;
			if ($reg == 'fallback') {
				$url = $this->GetRegistrationURL($_GET['h'] . '/fallback', true);
				header('Location:' . $url);
				exit;
			}

			// > Shopping Cart Generic API
			$continue = false;
			if ($reg == 'continue') {
				$continue = true;
				$reg = '';
				// get the secret key
				$secret = $this->GetOption('genericsecret');

				//this is a short url version
				if (isset($_GET['to'])) {
					$longurl = $this->GetContinueRegistrationURLFromShort($_GET['to'], false);
					wp_redirect(WLMREGISTERURL . $longurl);
					die();
				}

				// generate the hash
				$h = urlencode(md5($_GET['e'] . '__' . $secret));
				$h2 = urlencode(md5($_GET['e'] . '__' . $this->GetAPIKey()));

				if ($h == $_GET['h'] || $h2 == $_GET['h']) {
					$counter = 0;
					do {
						if ($counter > 0)
							sleep(2);
						$user = $this->Get_UserData(0, $e = 'temp_' . md5($_GET['e']));
					} while (!$user && $counter++ < 5);

					if ($user->ID) {
						$level = $this->GetMembershipLevels($user->ID);
						if ( count($level) > 1 ) { //if levels is more than one
							foreach( $level as $k=>$lvl ) { // lets remove child levels
								if ( $this->LevelParent( $lvl, $user->ID) ) {
									unset($level[$k]);
								}
							}
						}
						$level = array_values($level); //reset level index so its start with 0 again.
						$userlevel = 'U-' . $user->ID;
						$level = array_diff($level, array($userlevel));
						if (!count($level)) {
							$post_id = $this->GetMembershipContent('posts', $userlevel);
							if (empty($post_id)) {
								$post_id = $this->GetMembershipContent('pages', $userlevel);
							}
							if (!empty($post_id)) {
								list($post_id) = $post_id;
								$reg = 'payperpost-' . $post_id;
							}
						} else {
							list($level) = $level;
							$reg = $levels[$level]['url'];
						}
						if ($reg && !headers_sent()) {
							@wlm_setcookie('wpmu', $e, 0, '/');
						}
					}
				} else {
					$reg = '';
				}
			}
			// Shopping Cart Generic API <

			if (is_array($post_id)) {
				list($post_id) = $post_id;
			}

			$post_id+=0;
			if ($post_id && get_post($post_id)) {
				if ($continue || $this->Free_PayPerPost($post_id)) {
					$this->RegistrationCookie('manual', $dummy, 'payperpost-' . $post_id);
					$redir = $this->GetRegistrationURL('payperpost-' . $post_id, false, $dummy);
					header("Location:" . $redir);
					exit;
				} else {
					header("Location:" . get_bloginfo('url'));
					exit;
				}
			}

			foreach ((array) $levels AS $id => $level) {
				if ($level['url'] == $reg && $level['url'] != '') {
					$this->RegistrationCookie('manual', $dummy, $id);
					$redir = $this->GetRegistrationURL($id, false, $dummy);
					header("Location:" . $redir);
					exit;
				}
			}

			//check for approval registrations
			$for_approval_registration = $this->GetOption('wlm_for_approval_registration');
			if ($for_approval_registration) {
				$for_approval_registration = unserialize($for_approval_registration);

				foreach ((array) $for_approval_registration AS $id => $title) {
					if ($id == $reg && $title != '') {
						$this->RegistrationCookie('manual', $dummy, $id);
						$redir = $this->GetRegistrationURL($id, false, $dummy);
						header("Location:" . $redir);
						exit;
					}
				}
			}

			// not one of our registration URLs.  Possible shopping cart thank you URL.
			return $reg;
		}

		/**
		 * Get Registration URL
		 * @param int $id Level ID
		 * @param boolean $setcookie (optional)
		 * @param string $hash
		 * @return string
		 */
		function GetRegistrationURL($id, $setcookie = false, &$hash = null) {
			if ($setcookie) {
				$this->RegistrationCookie(true, $hash, $id);
			}
			$redir = $this->MagicPage();
			$qe = strpos($redir, '?') === false ? '?' : '&';
			$redir.=$qe . 'reg=' . $id;

			if (wlm_arrval($_GET, 'existing') == '1') {
				$redir.='&existing=1';
			}
			if (isset($_GET['wlm_reg_msg'])) {
				$redir.='&wlm_reg_msg=' . urlencode(wlm_arrval($_GET, 'wlm_reg_msg'));
			}
			$getdata = array_diff($_GET, array(''));
			unset($getdata['existing']);
			unset($getdata['wlm_reg_msg']);
			if ($getdata) {
				$getdata = base64_encode(http_build_query($getdata));
				$redir.='&wlm_rgd=' . $getdata;
			}
			return $redir;
		}

		/**
		 *
		 */
		function GetMembersIDByDateRange($meta, $from, $to) {
			global $wpdb;

			$query = "SELECT DISTINCT `ul`.`user_id`, `ulm`.`option_value` AS `date` FROM `{$this->Tables->userlevel_options}` AS `ulm`
			  LEFT JOIN `{$this->Tables->userlevels}` AS `ul` ON `ulm`.`userlevel_id`=`ul`.`ID`
			  WHERE `ulm`.`option_name`='%s'";

			$query = $wpdb->prepare($query, $meta);
			$results = $wpdb->get_results($query);
			$ids = array();
			foreach ($results as $value) {
				$data = explode('#', $value->date);
				$date_value = date('Y-m-d', strtotime($data[0]));
				$from = date('Y-m-d', strtotime($from));
				$to = strtotime($to);
				if($to <= 0) {
					$to = time();
				}
				$to = date('Y-m-d', $to);
				if ($date_value >= $from && $date_value <= $to) {
					array_push($ids, $value->user_id);
				}
			}
			return $ids;
		}

		/**
		 * Registration Form
		 * @param int $levelID (optional)
		 * @param boolean $returnForm (optional)
		 * @return string
		 */
		function RegContent($levelID = null, $returnForm = null) {
			$regID = is_null($levelID) ? $_GET['reg'] : $levelID;

			if (is_null($returnForm)) {
				$returnForm = false;
			}

			remove_filter('the_content', 'wptexturize');
			remove_filter('the_content', 'wpautop');

			$wpm_levels = $this->GetOption('wpm_levels');
			$wpm_level_id = $regID;

			$formVersion = $this->GetOption('FormVersion');

			if ($this->IsPPPLevel($wpm_level_id)) {
				$this->InjectPPPSettings($wpm_levels, $wpm_level_id);
			}

			$is_forapproval = $this->IsForApprovalRegistration($wpm_level_id);
			if ($is_forapproval) {
				$this->InjectForApprovalSettings($wpm_levels, $wpm_level_id);
			}

			$wpm_level = $wpm_levels[$wpm_level_id];
			$formAction = str_replace('&existing=1', '', htmlentities($this->GetRegistrationURL($wpm_level_id, true, $hash)));

			$hash = htmlentities($hash, ENT_QUOTES);
			$captcha_html = $this->GenerateRecaptchaHTML($wpm_level_id);

			$wpm_errmsg = '';
			$mergewith = '';

			$_GET['u'] = $_POST['mergewith'] ? $_POST['mergewith'] : $GLOBALS['wlm_cookies']->wpmu; // i used to pass this variable in the query string which is why i added this line. :)
			if (wlm_arrval($_GET, 'u')) {
				$theU = $this->Get_UserData(wlm_arrval($_GET, 'u'));
				if ($theU->ID) {
					$firstname = $theU->first_name;
					$lastname = $theU->last_name;
					$email = $theU->wlm_origemail;
					$mergewith = $theU->ID;
				}
			}

			/*
			 * don't process registration if we just
			 * want to return the registration form
			 *
			 * doing so prevents multiple registration attempts
			 * as well as sending multiple email notifications
			 * when fulfilling an incomplete registration
			 */
			$registered = false;
			if (!$returnForm) {
				$registration_called = false;
				if (wlm_arrval($_POST, 'action') == 'wpm_register') {
					$registered = $this->WPMRegister($_POST, $wpm_errmsg);
					$registration_called = true;
				} elseif (wlm_arrval($_POST, 'action') == 'wpm_register_existing') {
					$registered = $this->WPMRegisterExisting($_POST, $wpm_errmsg);
					$registration_called = true;
				}

				if ($registration_called && !$registered) {
					$username = $_POST['username'];
					$firstname = $_POST['firstname'];
					$lastname = $_POST['lastname'];
					$email = $_POST['email'];
				}
			}

			// Get after registration page
			if ($wpm_level['afterregredirect'] == '---') { // default after registration page
				$afterreg = $this->GetOption('after_registration_internal');
				if ($afterreg) {
					$afterreg = get_permalink($afterreg);
				} else {
					$afterreg = trim($this->GetOption('after_registration'));
				}
			} elseif ($wpm_level['afterregredirect'] == '') { // after registration is homepage
				$afterreg = get_bloginfo('url');
			} elseif ($this->IsPPPLevel($wpm_level_id) && $wpm_level['afterregredirect'] == 'backtopost') { // PPP + back to post
				$afterreg = get_permalink(substr($wpm_level_id, 11));
			} elseif ($is_forapproval && isset($wpm_level['afterregredirect']) && $wpm_level['afterregredirect'] != '') {
				$afterreg = $wpm_level['afterregredirect'];
			} else { // per level after reg page
				$afterreg = get_permalink($wpm_level['afterregredirect']);
			}

			// Check if level require email confirmation to show email confirm page after registration.
			if ($wpm_level['requireemailconfirmation']) {
				$afterreg = $this->GetOption('membership_forconfirmation_internal');
				if ($afterreg) {
					$afterreg = get_permalink($afterreg);
				} else {
					$afterreg = trim($this->GetOption('membership_forconfirmation'));
				}
			}

			// if no after registration url specified then set it to homepage
			if (!$afterreg) {
				$afterreg = get_bloginfo('url');
			}

			if (get_option('permalink_structure') === '') {
				$existinglink = $this->MagicPage($link = true) . '&reg=' . $regID . '&existing=1';
				$newlink = $this->MagicPage($link = true) . '&reg=' . $regID;
			} else {
				$existinglink = $this->MagicPage($link = true) . '?reg=' . $regID . '&existing=1';
				$newlink = $this->MagicPage($link = true) . '?reg=' . $regID;
			};

			$reglevel = $wpm_level['name'];

			$regBefore = $this->GetOption('regpage_before');
			$regAfter = $this->GetOption('regpage_after');

			if ($is_forapproval) {
				$regBefore = $regBefore[$is_forapproval["level"]];
				$regAfter = $regAfter[$is_forapproval["level"]];
			} else {
				$regBefore = $regBefore[$this->IsPPPLevel($wpm_level_id) ? 'payperpost' : $wpm_level_id];
				$regAfter = $regAfter[$this->IsPPPLevel($wpm_level_id) ? 'payperpost' : $wpm_level_id];
			}

			if (wlm_arrval($_GET, 'existing')) {
				$registration_instructions = str_replace(array('[level]', '[newlink]', '[existinglink]'), array($reglevel, $newlink, $existinglink), $this->GetOption('reg_instructions_existing'));
				$registration_header = __('Existing Member Login', 'wishlist-member');

				$formBody = $this->GetLevelExistingRegistrationForm($wpm_level_id, $formAction, $hash, $mergewith, $captcha_html);
			} else {
				$registration_instructions_no_existing = str_replace(array('[level]', '[newlink]', '[existinglink]'), array($reglevel, $newlink, $existinglink), $this->GetOption('reg_instructions_new_noexisting'));
				$registration_instructions_has_existing = str_replace(array('[level]', '[newlink]', '[existinglink]'), array($reglevel, $newlink, $existinglink), $this->GetOption('reg_instructions_new'));
				$registration_header = __('New Member Registration', 'wishlist-member');

				if ($wpm_level['disableexistinglink']) {
					$registration_instructions = $registration_instructions_no_existing;
				} else {
					$registration_instructions = $registration_instructions_has_existing;
				}

				$formBody = $this->GetLevelRegistrationForm($wpm_level_id, $formAction, $hash, $username, $firstname, $lastname, $email, $mergewith, $captcha_html);
			}

			if (trim($wpm_errmsg)) {
				$form_error = <<<STRING
   				<p class="wpm_err">{$wpm_errmsg}</p>
STRING;
			} else {
				$form_error = '';
			}

			if (trim(wlm_arrval($_GET, 'wlm_reg_msg'))) {
				$wlm_reg_msg_external = sprintf('<p class="wlm_reg_msg_external">%s</p>', trim(wlm_arrval($_GET, 'wlm_reg_msg')));
			} else {
				$wlm_reg_msg_external = '';
			}

			$form_instructions = <<<STRING
				<div id="wlmreginstructions">
					{$registration_instructions}
				</div>
				<h3 style="margin:0">{$registration_header}</h3>
				<br />
STRING;

			if ($formVersion == 'improved') {
				if (wlm_arrval($_GET, 'existing')) {
					$formBody = $this->GetLevelRegistrationForm($wpm_level_id, $formAction, $hash, $username, $firstname, $lastname, $email, $mergewith, $captcha_html) . $formBody;
				} else {
					$formBody .= $this->GetLevelExistingRegistrationForm($wpm_level_id, $formAction, $hash, $mergewith, $captcha_html);
				}
//				$form_style = sprintf('<link rel="stylesheet" href="%s">', $this->pluginURL . '/css/registration_form_frontend_v2.8.css');
//				$form_js = sprintf('<script src="%s"></script>', $this->pluginURL . '/js/registration_form_frontend_v2.8.js');
				$checked_existing = $_GET['existing'] ? ' checked="checked"' : '';
				$checked_new = !wlm_arrval($_GET, 'existing') ? ' checked="checked"' : '';
				$existing_account_option_label = __('I have an existing account', 'wishlist-member');
				$new_account_option_label = __('I am a new user', 'wishlist-member');
				if (!$wpm_level['disableexistinglink'] OR wlm_arrval($_GET, 'existing')) {
					$form_toggle = <<<STRING
						<form onsubmit="return false" class="wlm_regform_toggle">
						<label><input type="radio" name="regtype" value="wlm_show_existing_regform"{$checked_existing}> {$existing_account_option_label}</label>
						<label><input type="radio" name="regtype" value="wlm_show_new_regform"{$checked_new}> {$new_account_option_label}</label>
					</form>
STRING;
				}

				$regform_show_class = $_GET['existing'] ? 'wlm_show_existing_regform' : 'wlm_show_new_regform';
				$formBody = '<div class="wlm_regform_improved ' . $regform_show_class . '">' . $form_error . $wlm_reg_msg_external . $form_toggle . $formBody . '</div>';

				$formBefore = $regBefore;
				$formAfter = $regAfter;
			} else {
				$formBefore = $form_error . $form_instructions . $wlm_reg_msg_external;
				if (!isset($_GET['existing'])) {
					$formBefore = $regBefore . $formBefore;
					$formAfter = $formAfter . $regAfter;
				}
			}

			$formBody = str_replace(array("\r", "\n", "\t"), '', $formBody);

			$redirectcount = 3;
			$registration_please_wait = __('Please wait while we process your submission and kindly do not click your browser\'s back or refresh button.', 'wishlist-member');
			$click_to_redirect = sprintf(__('<a href="%1$s">Click here</a> if you are not redirected in %2$d seconds.', 'wishlist-member'), $afterreg, $redirectcount);

			if ($registered || $_GET['registered'] || $_POST['WLMRegHookIDs']) {
				$welcome = <<<STRING
				<meta http-equiv="refresh" content="{$redirectcount};url={$afterreg}">
				<script type="text/javascript">
					function wlmredirect(){
						document.location='{$afterreg}';
					}
					window.setTimeout(wlmredirect,{$redirectcount}000)
				</script>
				<p>{$registration_please_wait}</p>
				<p>{$click_to_redirect}</p>
STRING;

				$text = apply_filters('wishlistmember_after_registration_page', $welcome, $this); // we no longer pass $this by reference. might break PHP4 setups
				do_action('wishlistmember_after_registration', $this);
				if ($text != $welcome) {
					// our text was filtered so we set the registration cookie again
					$this->RegistrationCookie(true, $hash, $wpm_level_id);
				} else {
					// no more hooks playing around so we delete our cookie
					$this->RegistrationCookie('DELETE', $dummy);
				}
			} else {
				$text = $formBefore . $formBody . $formAfter;
			}

			$csscode = str_replace(array("\r", "\n"), '', $this->GetOption('reg_form_css'));
			$css = <<<STRING
			<style type="text/css">
			{$csscode}
			</style>
STRING;
			if ($returnForm) {
				return $formBody;
			} else {
				$text = apply_filters('wishlistmember_registration_page', $css . $text, $this);
				return $text;
			}
		}

		/**
		 * Return the appropriate existing members registration form
		 *
		 * @param type $level_id
		 * @param type $form_action
		 * @param type $hash
		 * @param type $mergewith
		 * @param type $captcha_code
		 * @return string
		 */
		function GetLevelExistingRegistrationForm($level_id, $form_action, $hash, $mergewith = '', $captcha_code = '') {
			if ($this->GetOption('FormVersion') == 'improved') {
				$formBody = $this->get_improved_existing_registration_form($captcha_code);
			} else {
				$formBody = $this->get_legacy_existing_registration_form($captcha_code);
			}

			$form_action = str_replace(array('&existing=1', '&amp;existing=1'), '', $form_action);
			$mergewithinput = $mergewith == '' ? '' : "<input type='hidden' name='mergewith' value='{$mergewith}' />";


			$additional_levels_form = "";
			if($mergewith != ''){
				$mw = $this->Get_UserData($mergewith);
				if ($mw->data->additional_levels) {
					$additional_levels_form = $this->RegFormAdditionalLevelsList($mw->data->additional_levels);
				}
			}

			$form = <<<STRING
				{$additional_levels_form}
				<form method="post" action="{$form_action}&existing=1">
					<input type="hidden" name="action" value="wpm_register_existing" />
					<input type="hidden" name="cookiehash" value="{$hash}" />
					<input type="hidden" name="wpm_id" value="{$level_id}" />
					{$mergewithinput}
					<div class="wlm_regform_container wlm_regform_existing_user">
						{$formBody}
					</div>
				</form>
STRING;

			return str_replace(array("\n", "\r", "\t"), '', $form);
		}

		/**
		 * Return improved existing members registration form
		 * @param string $captcha_code
		 * @return string
		 */
		function get_improved_existing_registration_form($captcha_code = '') {
			$txt_username = __('Username', 'wishlist-member');
			$txt_password = __('Password', 'wishlist-member');
			$txt_login = __('Login', 'wishlist-member');
			$url_forgot_password = esc_url(wp_lostpassword_url());
			$txt_forgot_password = __('Forgot Password?', 'wishlist-member');

			$captcha_code = trim($captcha_code);
			if ($captcha_code) {
				$captcha_code = <<<STRING
					<div class="wlm_form_group captcha_html">
						{$captcha_code}
					</div>
STRING;
			}

			$formBody = <<<STRING
				<div class="wlm_regform_div wlm_registration wlm_regform_2col wlm_regform_improved">
					<div class="wlm_form_group">
						<label for="wlm_username_field" class="wlm_form_label wlm_required_field" id="wlm_username_label">
							<span class="wlm_label_text" id="wlm_username_text">{$txt_username}:</span>
						</label>
						<input class="fld wlm_input_text" id="wlm_username_field" name="username" type="text">
						<p class="wlm_field_description"></p>
					</div>
					<div class="wlm_form_group">
						<label for="wlm_password_field" class="wlm_form_label wlm_required_field" id="wlm_password_label">
							<span class="wlm_label_text" id="wlm_password_text">{$txt_password}:</span>
						</label>
						<input class="fld wlm_input_text" id="wlm_password_field" name="password" type="password">
					</div>
					{$captcha_code}
					<p class="forgotpassword">
						<a href="{$url_forgot_password}" target="_blank">{$txt_forgot_password}</a>
					</p>
					<p class="submit">
						<input class="submit" id="wlm_submit_button" type="submit" value="{$txt_login}" />
					</p>
				</div>
STRING;
			return $formBody;
		}

		/**
		 * Return legacy existing members registration form
		 *
		 * @param string $captcha_code
		 * @return string
		 */
		function get_legacy_existing_registration_form($captcha_code = '') {

			$txt_username = __('Username', 'wishlist-member');
			$txt_password = __('Password', 'wishlist-member');
			$txt_login = __('Login', 'wishlist-member');
			$url_forgot_password = esc_url(wp_lostpassword_url());
			$txt_forgot_password = __('Forgot Password?', 'wishlist-member');

			$captcha_code = trim($captcha_code);
			if ($captcha_code) {
				$captcha_code = <<<STRING
					<tr class="li_fld captcha_html">
						<td class="label">&nbsp;</td>
						<td class="fld_div">{$captcha_code}</td>
					</tr>
STRING;
			}

			$formBody = <<<STRING
				<table class="wpm_existing wpm_regform_table">
					<tr valign="top" class="li_fld">
						<td class="label"><b>{$txt_username}:</b>&nbsp;</td>
						<td class="fld_div"><input type="text" name="username" class="fld" value="{$username}" size="25" /></td>
					</tr>
					<tr valign="top" class="li_fld">
						<td class="label"><b>{$txt_password}:</b>&nbsp;</td>
						<td class="fld_div"><input type="password" name="password" class="fld" size="25" /></td>
					</tr>
					{$captcha_code}
					<tr valign="top" class="li_submit">
						<td></td>
						<td class="fld_div"><input type="submit" class="button" value="{$txt_login}" /></td>
					</tr>
					<tr>
						<td></td>
						<td class="forgotpassword">
							<a href="{$url_forgot_password}" target="_blank">{$txt_forgot_password}</a>
						</td>
					</tr>
				</table>
STRING;
			return $formBody;
		}

		/**
		 * Get the registration form for the membership level
		 * @param int $level_id Level ID
		 * @param string $form_action Value to put in the form's "action" attribute
		 * @param int $mergewith User to ID to mergewith
		 * @param string $hash Security hash
		 * @param string $captcha_code Captcha HTML code
		 * @param string $username Username to pre-fill
		 * @param string $firstname First name to pre-fill
		 * @param string $lastname Last name to pre-fill
		 * @param string $email Email to pre-fill
		 * @return string HTML code for the registration form
		 */
		function GetLevelRegistrationForm($level_id, $form_action, $hash, $username = '', $firstname = '', $lastname = '', $email = '', $mergewith = '', $captcha_code = '') {

			wlm_xss_sanitize($username);
			wlm_xss_sanitize($firstname);
			wlm_xss_sanitize($lastname);
			wlm_xss_sanitize($email);

			$form_action = str_replace(array('&existing=1', '&amp;existing=1'), '', $form_action);

			$mergewithinput = $mergewith == '' ? '' : "<input type='hidden' name='mergewith' value='{$mergewith}' />";

			$wpm_levels = $this->GetOption('wpm_levels');
			$regpage_form_id = '';
			$is_forapproval = $this->IsForApprovalRegistration($level_id);

			if (!empty($level_id) && (isset($wpm_levels[$level_id]) || $this->IsPPPLevel($level_id) || $is_forapproval)) {
				$regpage_form_id = $this->GetOption('regpage_form');
				if ($this->IsPPPLevel($level_id)) {
					$regpage_form_id = trim($regpage_form_id['payperpost']);
				} elseif ($is_forapproval) {
					$regpage_form_id = trim($regpage_form_id[$is_forapproval['level']]);
				} else {
					$regpage_form_id = trim($regpage_form_id[$level_id]);
				}
			}

			$reg_post = array();
			$wpm_useraddress = array();
			if ($mergewith) {
				$reg_post = $this->WLMDecrypt($this->Get_UserMeta($mergewith + 0, 'wlm_reg_post'));
				$wpm_useraddress = $this->Get_UserMeta($mergewith + 0, 'wpm_useraddress');
			}
			$function_passed_data = array(
				'username' => $username,
				'firstname' => $firstname,
				'lastname' => $lastname,
				'email' => $email
			);

			if (wlm_arrval($_GET, 'wlm_rgd')) {
				parse_str(base64_decode(wlm_arrval($_GET, 'wlm_rgd')), $getdata);
			}
			$this->RegPageFormData = array_merge($function_passed_data, (array) $reg_post, (array) $wpm_useraddress, (array) $getdata, $_GET, $_POST);

			if ($this->GetOption('FormVersion') == 'improved') {
				$regpage_form = $this->get_improved_registration_form($regpage_form_id, $captcha_code, '', $wpm_levels[$level_id]['disableprefilledinfo']);
			} else {
				$regpage_form = $this->get_legacy_registration_form($regpage_form_id, $captcha_code, '',  $wpm_levels[$level_id]['disableprefilledinfo']);
			}

			$additional_levels_form = "";
			if($mergewith != ''){
				$mw = $this->Get_UserData($mergewith);
				if ($mw->data->additional_levels) {
					$additional_levels_form = $this->RegFormAdditionalLevelsList($mw->data->additional_levels);
				}
			}

			$formBody = <<<STRING
				{$additional_levels_form}
				<form method="post" action="{$form_action}">
					<input type="hidden" name="action" value="wpm_register" />
					<input type="hidden" name="wpm_id" value="{$level_id}" />{$mergewithinput}
					<input type="hidden" name="cookiehash" value="{$hash}" />
					<input type="hidden" name="orig_firstname" value="{$firstname}" />
					<input type="hidden" name="orig_lastname" value="{$lastname}" />
					<input type="hidden" name="orig_email" value="{$email}" />
						{$regpage_form}
				</form>
STRING;
			return '<div class="wlm_regform_container wlm_regform_new_user">' . $formBody . '</div>';
		}

		function RegFormAdditionalLevelsList( $add_levels ) {
			$wpm_levels = $this->GetOption('wpm_levels');
			$level_list ="";
			$level_caption = "";
			$additional_levels_form = "";
			if(count($add_levels) == 1) {
				$add_level = trim($add_levels[0]);
				$level_list = "&nbsp;<span class='additional-level'>{$wpm_levels[$add_level]["name"]}</span>";
				$level_caption ="level";
			}else{
				foreach ($add_levels as $add_level) {
					$add_level = trim($add_level);
					if(isset($wpm_levels[$add_level])){
						$level_list .= "<li class='additional-level'>{$wpm_levels[$add_level]["name"]}</li>\n";
					}
				}
				if($level_list != ""){
					$level_list = "<br /><ul class='additional-levels-list'>{$level_list}</ul>";
				}
				$level_caption ="levels";
			}

			if($level_list != ""){
				$additional_levels_form = "<span>" .__("You are also registering for the following {$level_caption}:", 'wishlist-member') ."</span>\n";
				$additional_levels_form .= $level_list;
			}
			return $additional_levels_form;
		}

		/**
		 * Email Broadcast Routine
		 */
		function EmailBroadcast(){
			global $wpdb;
			//Save Email Broadcast
			if( wlm_arrval($_POST,'EmailBroadcastAction') == "Save" ){
				$save = true;
				$subject = stripslashes(wlm_arrval($_POST, 'subject'));
				$msg = trim(wlm_arrval($_POST, 'message'));
				$sent_as = trim( wlm_arrval( $_POST, 'sent_as' ) );
				$send_to = wlm_arrval($_POST,'send_to');
				$otheroptions = isset($_POST['otheroptions']) ? (array) $_POST['otheroptions']:array();
				$otheroptions = implode("#", $otheroptions);
				$mlevel = array();
				$error = "none";

				if ( $send_to == "send_mlevels" ) {
					$mlevel = (array) $_POST['send_mlevels'];
				} else if ( $send_to == "send_search" ) {
					$mlevel = (array) $_POST['save_searches'];
				} else {
					$save = false;
					$error = __("Invalid Levels: Neither Levels or Save Searches was given", 'wishlist-member');
				}

				$mlevel = implode("#", $mlevel);

				$signature = trim(wlm_arrval($_POST, 'signature'));
				// save the signature and can spam address info
				$broadcast = array();
				foreach ((array) $_POST AS $k => $v) {
					if (substr($k, 0, 7) == 'canspam') {
						$broadcast[$k] = $v;
					}
				}
				$broadcast['signature'] = $signature;
				$this->SaveOption('broadcast', $broadcast);

				// create can spam
				$canspamaddress = trim( wlm_arrval( $_POST, 'canspamaddr1' ) ) . "\n";
				if ( trim( wlm_arrval( $_POST, 'canspamaddr2' ) ) ){
					$canspamaddress .= trim( wlm_arrval( $_POST, 'canspamaddr2' ) ) . "\n";
				}
				$canspamaddress .= trim( wlm_arrval( $_POST, 'canspamcity' ) ) . ", ";
				$canspamaddress .= trim( wlm_arrval( $_POST, 'canspamstate' ) ) . "\n";
				$canspamaddress .= trim( wlm_arrval( $_POST, 'canspamzip' ) ) . "\n";
				$canspamaddress .= trim( wlm_arrval( $_POST, 'canspamcountry' ) );

				$footer = "\n\n" .$signature . "\n\n" . $canspamaddress;

				if( $save ){
					$record_id = $this->SaveEmailBroadcast ( $subject, $msg, $footer, $send_to, $mlevel, $sent_as, $otheroptions );
					if ( ! $record_id ) {
						$error = __("An error occured while saving the broadcast.", 'wishlist-member') .$wpdb->last_error;
					}
				}else{
					$record_id = $save;
				}

				$log = "#NEW EMAIL BROADCAST#=> " .$record_id ." #Error: {$error}";
				$ret = $this->LogEmailBroadcastActivity($log);

				$_POST["broadcast_id"]    = $record_id;
				$_POST["broadcast_error"] = $error;

			} else if( wlm_arrval($_POST,'EmailBroadcastAction') == "ProcessBroadcast" ) {

				$broadcast_id = wlm_arrval($_POST,'BroadcastID');
				$emailbroadcast = $this->GetEmailBroadcast( $broadcast_id );
				if( !$emailbroadcast ) exit(0);
				//if( $emailbroadcast->status != "Queueing") exit(0);

				$mlimit = $this->GetOption('email_memory_allocation');
				$mlimit = ($mlimit == "" ? "128M" : $mlimit);
				ignore_user_abort(true);
				@ini_set('memory_limit', $mlimit);
				wlm_set_time_limit(86400); //limit this script to run for 1 day only, I think its enough
				$mlevel = explode("#", $emailbroadcast->mlevel);
				$otheroptions = explode("#", $emailbroadcast->otheroptions);
				$recipients = array();
				if($emailbroadcast->send_to == "send_mlevels"){
					$include_pending = in_array('p', $otheroptions);
					$include_cancelled = in_array('c', $otheroptions);

					$members = $this->MemberIDs(null, true);
					$cancelled = $this->CancelledMemberIDs(null, true);
					$pending = $this->ForApprovalMemberIDs(null, true);
					$expiredmembers = $this->ExpiredMembersID();

					foreach ($mlevel AS $level) {
						$xmembers = $members[$level];
						$members_cnt += count($members[$level]);
						// exclude cancelled levels unless specified otherwise
						$cancelled_cnt += count($cancelled[$level]);
						if (!$include_cancelled) {
							$xmembers = array_diff($xmembers, $cancelled[$level]);
						}
						// exclude pending members unless specified otherwise
						$pending_cnt += count($pending[$level]);
						if (!$include_pending) {
							$xmembers = array_diff($xmembers, $pending[$level]);
						}
						// exclude Expired Members
						$xmembers = array_diff($xmembers, $expiredmembers[$level]);
						$expired_cnt += count($expiredmembers[$level]);
						
						if( is_array($xmembers) )
							$recipients = array_merge($recipients, $xmembers);
					}
				}else if($emailbroadcast->send_to == "send_search"){
					require_once($this->pluginDir . '/core/UserSearch.php');
					$save_searches = $this->GetSavedSearch($mlevel[0]);
					if ( $save_searches ) {
						$save_searches = $save_searches[0];
						$usersearch = isset( $save_searches["search_term"] ) ? $save_searches["search_term"] : '';
						$usersearch = isset( $save_searches["usersearch"] ) ? $save_searches["usersearch"] : $usersearch;
						$wp_user_search = new WishListMemberUserSearch( $usersearch, '', '', '', '', '', 99999999, $save_searches );
						$recipients = $wp_user_search->results;
					} else {
						$recipients = array();
					}
				}
				//remove unsubscribed users
				$unsubscribed_users = $this->GetUnsubscribedUsers();
				$recipients = array_diff($recipients, $unsubscribed_users);
				//get unique recipients
				$recipients = array_diff(array_unique($recipients), array(0));
				$total_queued = 0;
				foreach ((array) $recipients AS $id) {
					if($this->AddEmailBroadcastQueue( $broadcast_id, $id )){
						$total_queued++;
					}
				}
				$data = array( "status"=> "Queued", "total_queued"=>$total_queued);
				$this->UpdateEmailBroadcast($broadcast_id, $data);
				echo $total_queued;
				exit(0);
			}else if( wlm_arrval($_POST,'EmailBroadcastAction') == "GetEmailQueue" ) {
				$email_queue = $this->GetEmailBroadcastQueue(null,false,false,0);
				$data = array();
				foreach($email_queue as $e){
					$data[] = $e->id;
				}
				echo json_encode($data);
				exit(0);
			}else if( wlm_arrval($_POST,'EmailBroadcastAction') == "GetFailedEmails" ) {
				$broadcastid = wlm_arrval($_POST,'BroadcastID');
				$broadcastid = $broadcastid ? $broadcastid : 0;

				$email_queue = $this->GetFailedQueue( $broadcastid );
				$data = array();
				foreach($email_queue as $e){
					$data[$e->id] = $e->user_email;
				}
				echo json_encode($data);
				exit(0);
			}else if( wlm_arrval($_POST,'EmailBroadcastAction') == "RequeueFailedEmails" ) {
				$failedids = wlm_arrval($_POST,'FailedIDs');
				$failedids = $failedids ? explode( ",", $failedids ) : array();
				if ( count( $failedids ) > 0 ) {
					if ( ! $this->FailEmailBroadcastQueue( $failedids, 0 ) ) {
						echo "A database error occured while queueing the selected emails. Please try again.";
					}
				} else {
					echo "No emails to queue. Please try again.";
				}
				exit(0);
			}else if( wlm_arrval($_POST,'EmailBroadcastAction') == "SendEmailQueue" ) {
				echo json_encode($this->SendEmailQueue(wlm_arrval($_POST,'QueueID')));
				exit(0);
			}
		}

		function SendEmailQueue ( $id ){

			$mail = $this->GetEmailQueue($id);
			$mailed =  false;
			if($mail){
				$user = $this->Get_UserData($mail->userid);
				if ( $user ) { //if user exists
					$subject = $mail->subject;
					$message = $mail->text_body;
					$footer = $mail->footer;
					$sent_as = $mail->sent_as;

					//add unsubcribe and user details link
					$footer .= "\n\n" . sprintf(WLMCANSPAM, $user->ID . '/' . substr(md5($user->ID . WLMUNSUBKEY), 0, 10));
					//process shortcodes
					$shortcode_data = $this->wlmshortcode->manual_process($user->ID, $message, true);
					//lets make sure that it is an array
					if (!is_array($shortcode_data)) {
						$shortcode_data = array();
					}
					/* strip tags for membership levels */
					if ($shortcode_data['wlm_memberlevel']) {
						$shortcode_data['wlm_memberlevel'] = strip_tags($shortcode_data['wlm_memberlevel']);
					}
					if ($shortcode_data['wlmmemberlevel']) {
						$shortcode_data['wlmmemberlevel'] = strip_tags($shortcode_data['wlmmemberlevel']);
					}
					if ($shortcode_data['memberlevel']) {
						$shortcode_data['memberlevel'] = strip_tags($shortcode_data['memberlevel']);
					}

					if ( trim( $user->user_email ) != "" ) {
						if ($sent_as == "html") {
							$fullmsg = $message . nl2br(wordwrap($footer));
							$mailed = $this->SendHTMLMail($user->user_email, $subject, stripslashes($fullmsg), $shortcode_data, false, null, 'UTF-8');
						} else {
							$fullmsg = $message . $footer;
							$fullmsg = wordwrap($fullmsg);
							$mailed = $this->SendMail($user->user_email, $subject, stripslashes($fullmsg), $shortcode_data, false, null, 'UTF-8');
						}
					} else {
						//this will skip users with no email address
						//and remove them from queue
						$mailed = true;
					}
				}
				// update total count of emails processed
				if ($mailed) { // if sent
					$this->DeleteEmailBroadcastQueue($mail->id);
				} else { // if failed
					$this->FailEmailBroadcastQueue($mail->id);
				}

				// save last send date
				$date_sent = date("F j, Y, h:i:s A");
				$this->SaveOption('WLM_Last_Queue_Sent', $date_sent);
			}
			return $mailed;
		}

		function RequeueEmail ( $broadcastid ) {
			return $this->emailbroadcast->requeue_email( $broadcastid );
		}
		/**
		 * Fail/Unfail Email Broadcast Queue
		 * @param int $id
		 * @param array $value
		 */
		function FailEmailBroadcastQueue ( $ids, $value=1 ) {

			return $this->emailbroadcast->fail_email_queue( $ids, $value );
		}

		/**
		 * Delete Email Broadcast Queue
		 * @param array $id
		 * @return boolean
		 */
		function DeleteEmailBroadcastQueue ( $ids ) {

			return $this->emailbroadcast->delete_email_queue( $ids );
		}

		/**
		 * Delete Email Broadcast Queue
		 * @param array $id
		 * @return boolean
		 */
		function PurgeBroadcastQueue ( $broadcastid ) {

			return $this->emailbroadcast->purge_broadcast_queues( $broadcastid );
		}

		/**
		 * Get Failed Email Queue
		 * @global object $wpdb
		 * @param int $broadcastid
		 * @param boolean $count
		 */
		function GetFailedQueue ( $broadcastid = null, $count = false ) {
			if( $count ){
				return $this->emailbroadcast->count_failed_queue( $broadcastid );
			} else {
				return $this->emailbroadcast->get_failed_queue( $broadcastid );
			}
		}

		/**
		 * Get Email Queue
		 * @global object $wpdb
		 * @param int $broadcastid
		 * @param boolean $include_fail
		 * @param boolean $count
		 */
		function GetEmailBroadcastQueue ( $broadcastid = null, $include_fail = false, $include_paused = false, $limit = 0, $count = false ) {
			if( $count ){
				return $this->emailbroadcast->count_email_queue( $broadcastid, $include_fail, $include_paused );
			} else {
				return $this->emailbroadcast->get_email_queue( $broadcastid, $include_fail, $include_paused, $limit );
			}
		}

		/**
		 * Get Email Queue by ID
		 * @param int $id
		 */
		function GetEmailQueue( $id ){
			return $this->emailbroadcast->get_email_queue_by_id( $id );
		}

		/**
		 * Add To Email Queue
		 * @global object $wpdb
		 * @param int $broadcastid
		 * @param int $userid
		 * @return boolean
		 */
		function AddEmailBroadcastQueue ( $broadcastid, $userid ) {

			return $this->emailbroadcast->add_email_queue( $broadcastid, $userid );
		}

		/**
		 * Get Email Broadcast
		 * @param int $id
		 * @param array $data
		 */
		function UpdateEmailBroadcast($id, $data){

			return $this->emailbroadcast->update_broadcast( $id, $data );
		}

		/**
		 * Get Email Broadcast
		 * @param int $id
		 */
		function GetEmailBroadcast ( $id ) {

			return $this->emailbroadcast->get_broadcast( $id );
		}

		/**
		 * Get All Email Broadcast
		 * @global object $wpdb
		 * @param boolean $count
		 * @param string $start
		 * @param string $per_page
		 * @param string $order
		 */
		function GetALLEmailbroadcast ( $count = false, $start = "", $per_page = "", $order = "" ) {

			if(!$count){
				return $this->emailbroadcast->get_all_broadcast ( $start, $per_page, $order );
			}else{
				return $this->emailbroadcast->count_broadcast();
			}
		}

		/**
		 * Save Email Broadcast
		 * @param string $subject
		 * @param string $msg
		 * @param string $recipients
		 * @param string $mlevel
		 * @param string $sent_as
		 */
		function SaveEmailBroadcast ( $subject, $msg, $footer, $send_to, $mlevel, $sent_as, $otheroptions ) {

			return $this->emailbroadcast->save_broadcast( $subject, $msg, $footer, $send_to, $mlevel, $sent_as, $otheroptions );
		}

		/**
		 * Delete Email Broadcasts
		 * @param array $id
		 * @return boolean
		 */
		function DeleteEmailBroadcast ( $ids ) {

			return $this->emailbroadcast->delete_broadcast( $ids );
		}

		/**
		 * Check if email broadcast stats missing
		 */
		function IsEmailBroadcastMissingStats () {
			return $this->emailbroadcast->check_stats_missing();
		}

		/**
		 * Check if email broadcast stats missing
		 */
		function EmailBroadcastSyncStat () {
			$res = $this->emailbroadcast->get_unsync_broadcast();
			if ( $res ) {
				foreach ( $res as $eb ) {

					//update total queue
					$r_count = 0;
					if ( $eb->recipients != "" && $eb->total_queued <= 0 ) {
						$recipients = explode( ",", $eb->recipients );
						if ( is_array( $recipients ) ) {
							$r_count = count($recipients);
						}
					}
					if( $r_count > 0 ) {
						$this->emailbroadcast->update_broadcast ( $eb->id, array( "recipients"=>"","total_queued"=>$r_count ) );
					}

					$f_address = array();
					if ( !is_null( $eb->failed_address ) && $eb->failed_address != "" ) {
						$f_address = explode( ",", $eb->failed_address );
					}

					//update the failed
					$failed_insert = array();
					if ( is_array( $f_address ) ) {
						foreach( $f_address as $email_add ) {
							$user = get_user_by( 'email', $email_add );
							if ( $user && isset( $user->ID ) ) {
								$failed_insert[] = array("broadcastid"=>$eb->id,"userid"=>$user->ID,"failed"=>1);
							}
						}
					}

					if ( count( $failed_insert ) ) {
						$ret = $this->emailbroadcast->bulk_add_email_queue( array("broadcastid","userid","failed"), $failed_insert );
						if( $ret ) {
							$this->emailbroadcast->update_broadcast ( $eb->id, array( "failed_address"=>"" ) );
						}
					}
				}
			}
		}

		/**
		 * Log Broadcast Email
		 * @param string $txt
		 * @param boolean $clear
		 * @return boolean
		 */
		function LogEmailBroadcastActivity($txt, $clear = false) {
			if ($this->GetOption('WLM_BroadcastLog') == 1) {
				$date = date("F j, Y, h:i:s A");
				$logfolder = WLM_BACKUP_PATH;
				$logfile = $logfolder . "broadcast.txt";
				if (!file_exists($logfolder))
					@mkdir($logfolder, 0755, true);
				if (!file_exists($logfile))
					return false;
				if ($clear) {
					$logfilehandler = fopen($logfile, 'w');
				} else {
					$logfilehandler = fopen($logfile, 'a');
				}
				if (!$logfilehandler) {
					return false;
				}
				$log = '[' . $date . '] ' . $txt . "\n------------------------------------------------------------\n";
				fwrite($logfilehandler, $log);
				fclose($logfilehandler);
			}
			return true;
		}

		/**
		 * Get Unsubscribe User IDs
		 * @global $wpdb
		 * @return array
		 */
		function GetUnsubscribedUsers(){
			global $wpdb;
			$query = $wpdb->prepare("SELECT DISTINCT `user_id` FROM `{$this->Tables->user_options}` WHERE `option_name`='wlm_unsubscribe' AND `option_value`='1'", $user->ID);
			return $wpdb->get_col($query);
		}
		/**
		 * Blacklist Check
		 * @param string $email
		 * @return int
		 */
		function CheckBlackList($email) {
			if (true === wlm_admin_in_admin()){
				return 0;
			}
			$emails = trim($this->GetOption('blacklist_email'));
			$ips = trim($this->GetOption('blacklist_ip'));
			$return = 0;
			if ($emails) {
				$emails = explode("\n", $emails);
				foreach ((array) $emails AS $p) {
					$p = '/^' . str_replace('\*', '.*?', preg_quote(trim($p), '/')) . '$/i';
					if (preg_match($p, $email)) {
						$return+=1;
						break;
					}
				}
			}
			if ($ips) {
				$ips = explode("\n", $ips);
				foreach ((array) $ips AS $p) {
					$p = '/^' . str_replace('\*', '.*?', preg_quote(trim($p), '/')) . '$/i';
					if (preg_match($p, $_SERVER['REMOTE_ADDR'])) {
						$return+=2;
						break;
					}
				}
			}
			return $return;
		}

		/**
		 * Checks how many times a user has logged in and redirects to an error page
		 * if user has exceeded the set limit or returns TRUE otherwise
		 * @global object $wpdb WordPress DB Object
		 * @param object $user WP User Object
		 * @return boolean TRUE if User has not exceeded the daily limit
		 */
		function LoginCounter($user) {
			global $wpdb;
			$id = $user->ID;
			if ($user->caps['administrator'])
				return true;
			$counter = $this->Get_UserMeta($id, 'wpm_login_counter');
			if (!is_array($counter))
				$counter = array();

			// remove counts for the previous day
			$now = date('Ymd');
			foreach ((array) $counter AS $ip => $d) {
				if ($d < $now)
					unset($counter[$ip]);
			}

			// get user limit
			$limit = $this->Get_UserMeta($id, 'wpm_login_limit') + 0;
			if ($limit < 0)
				return true; // <- no login limits

			if (!$limit) {
				$limit = $this->GetOption('login_limit') + 0;
			}

			if (count($counter) >= $limit && $limit > 0 && !isset($counter[$_SERVER['REMOTE_ADDR']])) {
				if ($this->GetOption("login_limit_notify")) {
					/* we send notification to admin about the exceeded login */
					$adminemail = $this->GetOption('email_sender_address');
					wp_mail($adminemail, 'Login Limit Exceeded', "Login limit exceeded.\n\nUsername:{$user->user_login}\nEmail:{$user->user_email}\nIP:{$_SERVER[REMOTE_ADDR]}");
				}
				$this->NoLogoutRedirect = true;
				wp_logout();
				header("Location:" . wp_login_url() . '?loginlimit=1');
				exit;
				return false;
			}

			if (!in_array($_SERVER['REMOTE_ADDR'], array_keys((array) $counter))) {
				$counter[$_SERVER['REMOTE_ADDR']] = $now;
			}

			$this->Update_UserMeta($id, 'wpm_login_counter', $counter);
			return true;
		}

		/**
		 * Registration Hook IDs
		 * Used by Extensions that wish to integrate with after registration process
		 * @param int $id
		 * @return string
		 */
		function AfterRegHookID($id) {
			$return = '';
			if (is_array(wlm_arrval($_POST, 'WLMRegHookIDs'))) {
				foreach ((array) $_POST['WLMRegHookIDs'] AS $RHI) {
					$return.='<input type="hidden" name="WLMRegHookIDs[]" value="' . $RHI . '" />';
				}
			}
			$return.='<input type="hidden" name="WLMRegHookIDs[]" value="' . $id . '" />';
			return $return;
		}

		/**
		 * Subscribe to AutoResponder
		 * @param string $fname
		 * @param string $lname
		 * @param string $email
		 * @param int $wpm_id Level ID
		 */
		function ARSubscribe($fname, $lname, $email, $wpm_id) {
			// Autoresponder subscription
			$this->SendingMail = true; // we add this to trigger our hook
			$this->ARSender = array('name' => "{$fname} {$lname}", 'email' => "{$email}", "first_name" => $fname, "last_name" => $lname);
			$ar = $this->GetOption('Autoresponders');
			$arp = $ar['ARProvider'];
			$ar = $ar[$arp];

			// retrieve the method to call
			$ar_integration_info = $this->ARIntegrationMethods[$arp];
			// and call it
			if ($ar_integration_info) {
				if (!class_exists($ar_integration_info['class'])) {
					include_once($this->pluginDir . '/lib/' . $ar_integration_info['file']);
					$this->RegisterClass($ar_integration_info['class']);
				}
				call_user_func_array(array(&$this, $ar_integration_info['method']), array($ar, $wpm_id, $email, false));
			}

			$this->ARSender = '';
			$this->SendingMail = false;
		}

		/**
		 * Unsubscribe from AutoResponder
		 * @param string $fname
		 * @param string $lname
		 * @param string $email
		 * @param int $wpm_id Level ID
		 */
		function ARUnsubscribe($fname, $lname, $email, $wpm_id) {
			$this->SendingMail = true; // we add this to trigger our hook
			$this->ARSender = array('name' => "{$fname} {$lname}", 'email' => "{$email}");
			$ar = $this->GetOption('Autoresponders');
			$arp = $ar['ARProvider'];
			$ar = $ar[$arp];

			// retrieve the method to call
			$ar_integration_info = $this->ARIntegrationMethods[$arp];
			// and call it
			if ($ar_integration_info) {
				if (!class_exists($ar_integration_info['class'])) {
					include_once($this->pluginDir . '/lib/' . $ar_integration_info['file']);
					$this->RegisterClass($ar_integration_info['class']);
				}
				call_user_func_array(array(&$this, $ar_integration_info['method']), array($ar, $wpm_id, $email, true));
			}

			$this->ARSender = '';
			$this->SendingMail = false;
		}

		function WebinarSubscribe($fname, $lname, $email, $level) {
			$data = array(
				'first_name' => $fname,
				'last_name' => $lname,
				'email' => $email,
				'level' => $level
			);

			$active_webinar = $this->GetOption('WebinarProvider');

			foreach ($this->WebinarIntegrations AS $webinar => $webinar_data) {
				if ($active_webinar == $webinar) {
					include_once($this->pluginDir . '/lib/' . $webinar_data['file']);
					$w = new $webinar_data['class'];
					$w->init();
					$w->subscribe($data);
				}
			}
		}

		/**
		 * Set Transaction ID of a Single Membership Level
		 * @param int $uid User ID
		 * @param int $level Level ID
		 * @param string $txnid Transaction ID
		 */
		function SetMembershipLevelTxnID($uid, $level, $txnid) {
			if (empty($txnid))
				$txnid = "WL-{$uid}-{$level}";
			$this->Update_UserLevelMeta($uid, $level, 'transaction_id', $txnid);
		}

		/**
		 * Set Transaction IDs of Multiple Membership Levels
		 * @param int $uid User ID
		 * @param array $levels Associative array levelID=>transactionID
		 */
		function SetMembershipLevelTxnIDs($uid, $levels) {
			foreach ((array) $levels AS $level => $txnid) {
				$this->SetMembershipLevelTxnID($uid, $level, $txnid);
			}
		}

		/**
		 * Get Transaction IDs
		 * @param int $uid User ID
		 * @param string $txnid (optional) Transaction ID
		 * @return array Associative array levelID=>txnID
		 */
		function GetMembershipLevelsTxnIDs($uid, $txnid = '') {
			$levels = $this->GetMembershipLevels($uid);
			$txns = array();
			foreach ($levels AS $level_id) {
				$txns[$level_id] = $this->Get_UserLevelMeta($uid, $level_id, 'transaction_id');
			}
			if ($txnid) {
				$txns = array_intersect($txns, (array) $txnid);
			}
			return $txns;
		}

		/**
		 * Get Transaction ID of a single Membership Level
		 * @param integer $uid User ID
		 * @param integer $level Membership Level
		 * @return string Transaction ID
		 */
		function GetMembershipLevelsTxnID($uid, $level) {
			return $this->Get_UserLevelMeta($uid, $level, 'transaction_id');
		}

		/**
		 * Processes the Private Tags
		 * @param string $content Data to filter
		 * @param array $regtags Passed by reference, loaded with the tags
		 * @return string filtered Data
		 */
		function PrivateTags($content, &$regtags) {

			global $wp_query;

			$is_userpost = false;

			$wpm_current_user = $GLOBALS['current_user'];
			$wpm_levels = (array) $this->GetOption('wpm_levels');

			// generate tags
			$tags = $regtags = array();
			foreach ((array) $wpm_levels AS $id => $level) {
				$tags[$id] = '(wlm_|wlm|)private_' . preg_quote(strtolower($level['name']), '/');
				$regtags[$id] = '(wlm_|wlm|)register_' . preg_quote(strtolower($level['name']), '/');
			}
			$alltags = $tags;

			// pick our tags
			$thelevels = $this->GetMembershipLevels($wpm_current_user->ID, false, true);

			//ignore non-standard membership levels (ppp levels)
			foreach ($thelevels as $key => $lvl) {
				if (preg_match('/U-\d+/', $lvl)) {
					unset($thelevels[$key]);
				}
			}
			$mytags = $mylevels = array();

			foreach ((array) $thelevels AS $thelevelid) {
				$mytags[] = $tags[$thelevelid];
				unset($tags[$thelevelid]);
				$mylevels[$thelevelid] = strtolower($wpm_levels[$thelevelid]['name']);
			}

			// strip private tags for unprotected posts and if user is admin
			// if(!$this->Protect($GLOBALS['post']->ID) || $wpm_current_user->caps['administrator']){
			// just strip private tags for admins and not for unprotected posts so that private tags still work on unprotected posts
			if (wlm_arrval($wpm_current_user->caps, 'administrator')) {
				$content = preg_replace('/\[\/{0,1}(wlm_|wlm|)private_.+?\]/i', '', $content);
				$content = preg_replace('/\[\/{0,1}ismember\]/i', '', $content);
				$content = preg_replace('/\[\/{0,1}nonmember\]/i', '', $content);
			}
			/* remove all private tags inside user's private blocks */
			foreach ((array) $mytags AS $mytag) {
				$myblocks = preg_match_all('/\[' . $tag . '\](.*?)\[\/' . $mytag . '\]/is', $content, $matches);
				foreach ((array) $matches[1] AS $match) {
					$content = str_replace($match, preg_replace('/\[\/{0,1}(wlm_|wlm|)private_.+?\]/i', '', $match), $content);
				}
			}

			/* fix tag nesting */
			$xtags = $alltags;
			$prevtags = array();
			foreach ((array) $tags AS $id => $tag) {
				unset($xtags[$id]);
				preg_match_all('/\[(' . $tag . ')\].*?\[\/' . $tag . '\]/is', $content, $matches, PREG_SET_ORDER);
				foreach ((array) $matches AS $match_set) {
					$match = $match_set[0];
					$tag = $match_set[1];
					$xmatch = preg_replace('/\[\/{0,1}' . $tag . '\]/i', '', $match);
					foreach ((array) $xtags AS $xtag) {
						$xmatch = preg_replace('/\[' . $xtag . '\]/i', '[/' . $tag . ']\0', $xmatch);
						$xmatch = preg_replace('/\[\/' . $xtag . '\]/i', '\0[' . $tag . ']', $xmatch);
					}
					foreach ((array) $prevtags AS $prevtag) {
						$xmatch = preg_replace('/\[\/{0,1}' . $prevtag . '\]/i', '', $xmatch);
					}
					$content = stripslashes(str_replace($match, '[' . $tag . ']' . $xmatch . '[/' . $tag . ']', $content));
					$prevtags[] = $tag;
				}
			}

			/* remove tags with whitespace only and empty tags */
			foreach ((array) $alltags AS $tag) {
				$content = preg_replace('/\[' . $tag . '\]\[\/' . $tag . '\]/is', '', $content);
				$content = preg_replace('/\[' . $tag . '\](<\/p>\s<p>)\[\/' . $tag . '\]/is', '\1', $content);
			}

			/* remove blocks enclosed in private tags that don't belong to the user */
			$protectmsg = $this->GetOption('private_tag_protect_msg');
			foreach ((array) $tags AS $id => $tag) {
				$pmsg = str_replace('[level]', ucwords(strtolower($wpm_levels[$id]['name'])), $protectmsg);
				$content = preg_replace('/\[' . $tag . '\].+?\[\/' . $tag . '\]/is', $pmsg, $content);
			}

			/* multiple private tag - multiple levels [private level1|level2|level3|...] */
			while (preg_match_all('/\[private ([^\]]+)?\](.*?)\[\/private\]?/is', $content, $privates)) {
				foreach ((array) $privates[0] AS $key => $private) {
					$private_levels = explode('|', strtolower(trim($privates[1][$key])));
					if (count(array_intersect($private_levels, $mylevels))) {
						$content = str_replace($privates[0][$key], $privates[2][$key], $content);
					} else {
						$pmsg = str_replace('[level]', ucwords(strtolower(implode(', ', $private_levels))), $protectmsg);
						$content = str_replace($privates[0][$key], $pmsg, $content);
					}
				}
			}

			if ($this->GetOption('payperpost_ismember')) {
				$wpm_current_user = wp_get_current_user();
				$is_userpost = in_array($wp_query->post->ID, $this->GetMembershipContent($wp_query->post->post_type, 'U-' . $wpm_current_user->ID));
			}

			/* private all, ismember and nonmember */
			if (!count($mylevels) && ($is_userpost == false)) {
				$lnames = array();
				foreach ((array) $wpm_levels AS $level)
					$lnames[] = $level['name'];
				$pmsg = str_replace('[level]', ucwords(strtolower(implode(', ', $lnames))), $protectmsg);
				$content = preg_replace('/\[private all\].+?\[\/private\]/is', $pmsg, $content);

				// not a member of any level - strip out ismember
				$content = preg_replace('/\[ismember\].+?\[\/ismember\]/is', '', $content);
			} else {
				// member of at least one level - strip out nonmember
				$content = preg_replace('/\[nonmember\].+?\[\/nonmember\]/is', '', $content);
			}

			/* cleanup remaining private tags if any */
			$content = preg_replace('/\[\/{0,1}private[_ ]{0,1}[^\]]*?\]/i', '', $content);
			$content = preg_replace('/\[\/{0,1}ismember\]/i', '', $content);
			$content = preg_replace('/\[\/{0,1}nonmember\]/i', '', $content);

			return $content;
		}

		/**
		 * Return IDs of protected posts and pages as array
		 * @return array
		 */
		function ProtectedIds() {
			global $wpdb;
			static $protected;
			if ($protected)
				return $protected;

			$post_types = get_post_types(array('_builtin' => false));
			$enabled_types = (array) $this->GetOption('protected_custom_post_types');
			$remove_types = array_diff($post_types, $enabled_types);
			if ($remove_types) {
				foreach ($remove_types AS $k => $v) {
					$remove_types[$k] = esc_sql($v);
				}
				$remove_types = "'" . implode("','", $remove_types) . "'";
				$remove_types = " AND `content_id` NOT IN (SELECT `ID` FROM `{$wpdb->posts}` WHERE `post_type` IN ({$remove_types})) ";
			} else {
				$remove_types = '';
			}

			$protected = $wpdb->get_col("SELECT `content_id` FROM `{$this->Tables->contentlevels}` WHERE `level_id`='Protection' {$remove_types} AND `type` NOT LIKE '~%'");

			return $protected;
		}

		/**
		 * Returns the user registration date as a timestamp
		 * @param object $user The user object
		 * @param boolean $add_wp_timezone (optional) True to add WP Timezone
		 * @return int Timestamp for current Timezone
		 */
		function UserRegistered($user, $add_wp_timezone = true) {
			// compute timezone difference
			if (is_int($user)) {
				$user = $this->Get_UserData($user);
			}
			list($year, $month, $day, $hour, $minute, $second) = preg_split('/[-: ]/', $user->user_registered);
			$reg = gmmktime($hour, $minute, $second, $month, $day, $year);
			if ($add_wp_timezone) {
				$reg += $this->GMT;
			}
			return $reg;
		}

		/**
		 * Removes invalid Level IDs from the passed array
		 * @param array $levels Array of Level IDs (passed by reference)
		 * @param int $uid (optional) User ID. If specified, then validate against user's levels as well
		 * @param boolean $terminate_on_error (optional) Default False. TRUE to stop validation, reset $levels to an empty array and return FALSE if at least one level does not validate.
		 */
		function ValidateLevels(&$levels, $uid = null, $terminate_on_error = null, $include_user_posts = null, $include_pay_per_posts = null) {
			$wpm_levels = $this->GetOption('wpm_levels', null, true);
			if (is_null($terminate_on_error)) {
				$terminate_on_error = false;
			}
			if (is_null($include_user_posts)) {
				$include_user_posts = false;
			}
			if (is_null($include_pay_per_posts)) {
				$include_pay_per_posts = false;
			}

			$levels = array_unique((array) $levels);
			foreach ((array) $levels AS $levelkey => $level) {
				if (!$wpm_levels[$level]) {
					if ($include_user_posts) {
						if ($this->IsUserLevel($level, $include_user_posts == 'STRICT'))
							continue;
					}
					if ($include_pay_per_posts) {
						if ($this->IsPPPLevel($level))
							continue;
					}
					if ($terminate_on_error) {
						$levels = array();
						return false;
					}
					unset($levels[$levelkey]);
				}
			}

			if (!is_null($uid)) {
				$ulevels = $this->GetMembershipLevels($uid);
				$levels = array_intersect($levels, $ulevels);
				if ($ulevels != $levels && $terminate_on_error) {
					$levels = array();
					return false;
				}
			}

			sort($levels);
			return true;
		}

		function IsUserLevel($level, $strict = false) {
			$level = explode('-', $level, 2);
			if ($level[0] != 'U') {
				return false;
			}
			if ($strict == true) {
				return get_userdata($level[1]) ? true : false;
			} else {
				return ((int) $level[1]) ? true : false;
			}
		}

		/**
		 * Determines what array members have been removed and added
		 * @param array $new_array New Array
		 * @param array $old_array Old Array
		 * @param array $removed_members This variable will contain the levels that were removed (passed by reference)
		 * @param array $new_members This variable will contain the levels that were added (passed by reference)
		 */
		function ArrayDiff($new_array, $old_array, &$removed_members, &$new_members) {
			$removed_members = array_diff((array) $old_array, (array) $new_array);
			$new_members = array_diff((array) $new_array, (array) $old_array);
		}

		/**
		 * Triggers the correct hook when a content changes levels
		 * @param string $ContentType The content type. Can be categories, posts, pages, or comments
		 * @param int $ContentID Unique ID of the content
		 * @param array $removed_levels Array of levels that were removed
		 * @param array $new_levels Array of levels that were added
		 */
		function TriggerContentActionHooks($ContentType, $ContentID, $removed_levels, $new_levels) {
			// trigger remove_***content***_levels action if a content is removed from at least one level
			if (count($removed_levels)) {
				do_action('wishlistmember_remove_' . $ContentType . '_levels', $ContentID, $removed_levels);
			}
			// trigger add_***content***_levels action if content is added to at least one level
			if (count($new_levels)) {
				do_action('wishlistmember_add_' . $ContentType . '_levels', $ContentID, $new_levels);
			}
		}

		/**
		 * Registers a WishList Member Extensions
		 * @param string $Name Extension name
		 * @param string $URL Extension Website
		 * @param string $Version Extension version
		 * @param string $Description Extension description
		 * @param string $Author Extension's author
		 * @param string $AuthorURL Extension author's URL
		 * @param string $File Extension's filename
		 */
		function RegisterExtension($Name, $URL, $Version, $Description, $Author, $AuthorURL, $File) {
			$File = basename($File);
			if ($File) {
				$this->loadedExtensions[$File] = array(
					'Name' => $Name,
					'URL' => $URL,
					'Version' => $Version,
					'Description' => $Description,
					'Author' => $Author,
					'AuthorURL' => $AuthorURL,
					'File' => $File
				);
			}
		}

		/**
		 * Unregisters an extension
		 * @param string $File Extension's filename
		 */
		function UnregisterExtension($File) {
			unset($this->loadedExtensions[$File]);
		}

		/**
		 * Returns an array of loaded extensions
		 * @return array Loaded extensions
		 */
		function GetRegisteredExtensions() {
			return $this->loadedExtensions;
		}

		/**
		 * Get Page IDs used for Specific System Pages
		 * @param type $sysPage
		 * @return page IDs
		 */
		function GetSpecificSystemPagesID() {
			global $wpdb;
			$query = "SELECT option_value FROM `{$this->Tables->options}` WHERE option_name LIKE  'non_members_error_page_internal_%'";
			$query .= " OR option_name LIKE  'wrong_level_error_page_internal_%'";
			$query .= " OR option_name LIKE  'membership_cancelled_internal_%'";
			$query .= " OR option_name LIKE  'membership_expired_internal%_'";
			$query .= " OR option_name LIKE  'membership_forapproval_internal_%'";
			$query .= " OR option_name LIKE  'membership_forconfirmation_internal_%'";
			$page_ids = $wpdb->get_results($query);
			$x = array();
			foreach ($page_ids as $page_id) {
				if ($page_id->option_value > 0)//get only page id exlcude external links
					$x[] = $page_id->option_value;
			}
			return $x;
		}

		/**
		 * GetMimeType
		 *
		 * Retrieves the correct mime type of a file
		 * This function is based on Chris Jean's recommendations:
		 * http://chrisjean.com/2009/02/14/generating-mime-type-in-php-is-not-magic/
		 *
		 * @param string $filename path to file
		 * @return string Mime type (or an empty string if it failed)
		 */
		function GetMimeType($filename) {
                        
                        if(file_exists($filename) ){
                            /* first, let's see if we can get the mime type using finfo functions */
                            if (function_exists('finfo_open') && function_exists('finfo_file') && function_exists('finfo_close')) {

                                    $finfo = finfo_open(FILEINFO_MIME);
                                    $mime = finfo_file($finfo, $filename);
                                    finfo_close($finfo);
                                    if (!empty($mime))
                                            return $mime;
                            }
                        }
			

			/* next, let's try to retrieve the mime type from our array */
			$mime_types = array(
				'ai' => 'application/postscript',
				'aif' => 'audio/x-aiff',
				'aifc' => 'audio/x-aiff',
				'aiff' => 'audio/x-aiff',
				'asc' => 'text/plain',
				'asf' => 'video/x-ms-asf',
				'asx' => 'video/x-ms-asf',
				'au' => 'audio/basic',
				'avi' => 'video/x-msvideo',
				'bcpio' => 'application/x-bcpio',
				'bin' => 'application/octet-stream',
				'bmp' => 'image/bmp',
				'bz2' => 'application/x-bzip2',
				'cdf' => 'application/x-netcdf',
				'chrt' => 'application/x-kchart',
				'class' => 'application/octet-stream',
				'cpio' => 'application/x-cpio',
				'cpt' => 'application/mac-compactpro',
				'csh' => 'application/x-csh',
				'css' => 'text/css',
				'dcr' => 'application/x-director',
				'dir' => 'application/x-director',
				'djv' => 'image/vnd.djvu',
				'djvu' => 'image/vnd.djvu',
				'dll' => 'application/octet-stream',
				'dms' => 'application/octet-stream',
				'doc' => 'application/msword',
				'dvi' => 'application/x-dvi',
				'dxr' => 'application/x-director',
				'eps' => 'application/postscript',
				'etx' => 'text/x-setext',
				'exe' => 'application/octet-stream',
				'dmg' => 'application/octet-stream',
				'msi' => 'application/octet-stream',
				'ez' => 'application/andrew-inset',
				'flv' => 'video/x-flv',
				'gif' => 'image/gif',
				'gtar' => 'application/x-gtar',
				'gz' => 'application/x-gzip',
				'hdf' => 'application/x-hdf',
				'hqx' => 'application/mac-binhex40',
				'htm' => 'text/html',
				'html' => 'text/html',
				'ice' => 'x-conference/x-cooltalk',
				'ief' => 'image/ief',
				'iges' => 'model/iges',
				'igs' => 'model/iges',
				'img' => 'application/octet-stream',
				'iso' => 'application/octet-stream',
				'jad' => 'text/vnd.sun.j2me.app-descriptor',
				'jar' => 'application/x-java-archive',
				'jnlp' => 'application/x-java-jnlp-file',
				'jpe' => 'image/jpeg',
				'jpeg' => 'image/jpeg',
				'jpg' => 'image/jpeg',
				'js' => 'application/x-javascript',
				'kar' => 'audio/midi',
				'kil' => 'application/x-killustrator',
				'kpr' => 'application/x-kpresenter',
				'kpt' => 'application/x-kpresenter',
				'ksp' => 'application/x-kspread',
				'kwd' => 'application/x-kword',
				'kwt' => 'application/x-kword',
				'latex' => 'application/x-latex',
				'lha' => 'application/octet-stream',
				'lzh' => 'application/octet-stream',
				'm3u' => 'audio/x-mpegurl',
				'man' => 'application/x-troff-man',
				'me' => 'application/x-troff-me',
				'mesh' => 'model/mesh',
				'mid' => 'audio/midi',
				'midi' => 'audio/midi',
				'mif' => 'application/vnd.mif',
				'mov' => 'video/quicktime',
				'movie' => 'video/x-sgi-movie',
				'mp2' => 'audio/mpeg',
				'mp3' => 'audio/mpeg',
				'mp4' => 'video/mp4',
				'mpe' => 'video/mpeg',
				'mpeg' => 'video/mpeg',
				'mpg' => 'video/mpeg',
				'mpga' => 'audio/mpeg',
				'ms' => 'application/x-troff-ms',
				'msh' => 'model/mesh',
				'mxu' => 'video/vnd.mpegurl',
				'nc' => 'application/x-netcdf',
				'odb' => 'application/vnd.oasis.opendocument.database',
				'odc' => 'application/vnd.oasis.opendocument.chart',
				'odf' => 'application/vnd.oasis.opendocument.formula',
				'odg' => 'application/vnd.oasis.opendocument.graphics',
				'odi' => 'application/vnd.oasis.opendocument.image',
				'odm' => 'application/vnd.oasis.opendocument.text-master',
				'odp' => 'application/vnd.oasis.opendocument.presentation',
				'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
				'odt' => 'application/vnd.oasis.opendocument.text',
				'oga' => 'audio/ogg',
				'ogg' => 'application/ogg',
				'ogv' => 'video/ogg',
				'otg' => 'application/vnd.oasis.opendocument.graphics-template',
				'oth' => 'application/vnd.oasis.opendocument.text-web',
				'otp' => 'application/vnd.oasis.opendocument.presentation-template',
				'ots' => 'application/vnd.oasis.opendocument.spreadsheet-template',
				'ott' => 'application/vnd.oasis.opendocument.text-template',
				'pbm' => 'image/x-portable-bitmap',
				'pdb' => 'chemical/x-pdb',
				'pdf' => 'application/pdf',
				'pgm' => 'image/x-portable-graymap',
				'pgn' => 'application/x-chess-pgn',
				'png' => 'image/png',
				'pnm' => 'image/x-portable-anymap',
				'ppm' => 'image/x-portable-pixmap',
				'ppt' => 'application/vnd.ms-powerpoint',
				'ps' => 'application/postscript',
				'qt' => 'video/quicktime',
				'ra' => 'audio/x-realaudio',
				'ram' => 'audio/x-pn-realaudio',
				'ras' => 'image/x-cmu-raster',
				'rgb' => 'image/x-rgb',
				'rm' => 'audio/x-pn-realaudio',
				'roff' => 'application/x-troff',
				'rpm' => 'application/x-rpm',
				'rtf' => 'text/rtf',
				'rtx' => 'text/richtext',
				'sgm' => 'text/sgml',
				'sgml' => 'text/sgml',
				'sh' => 'application/x-sh',
				'shar' => 'application/x-shar',
				'silo' => 'model/mesh',
				'sis' => 'application/vnd.symbian.install',
				'sit' => 'application/x-stuffit',
				'skd' => 'application/x-koan',
				'skm' => 'application/x-koan',
				'skp' => 'application/x-koan',
				'skt' => 'application/x-koan',
				'smi' => 'application/smil',
				'smil' => 'application/smil',
				'snd' => 'audio/basic',
				'so' => 'application/octet-stream',
				'spl' => 'application/x-futuresplash',
				'src' => 'application/x-wais-source',
				'stc' => 'application/vnd.sun.xml.calc.template',
				'std' => 'application/vnd.sun.xml.draw.template',
				'sti' => 'application/vnd.sun.xml.impress.template',
				'stw' => 'application/vnd.sun.xml.writer.template',
				'sv4cpio' => 'application/x-sv4cpio',
				'sv4crc' => 'application/x-sv4crc',
				'swf' => 'application/x-shockwave-flash',
				'sxc' => 'application/vnd.sun.xml.calc',
				'sxd' => 'application/vnd.sun.xml.draw',
				'sxg' => 'application/vnd.sun.xml.writer.global',
				'sxi' => 'application/vnd.sun.xml.impress',
				'sxm' => 'application/vnd.sun.xml.math',
				'sxw' => 'application/vnd.sun.xml.writer',
				't' => 'application/x-troff',
				'tar' => 'application/x-tar',
				'tcl' => 'application/x-tcl',
				'tex' => 'application/x-tex',
				'texi' => 'application/x-texinfo',
				'texinfo' => 'application/x-texinfo',
				'tgz' => 'application/x-gzip',
				'tif' => 'image/tiff',
				'tiff' => 'image/tiff',
				'torrent' => 'application/x-bittorrent',
				'tr' => 'application/x-troff',
				'tsv' => 'text/tab-separated-values',
				'txt' => 'text/plain',
				'ustar' => 'application/x-ustar',
				'vcd' => 'application/x-cdlink',
				'vrml' => 'model/vrml',
				'wav' => 'audio/x-wav',
				'wax' => 'audio/x-ms-wax',
				'webm' => 'video/webm',
				'wbmp' => 'image/vnd.wap.wbmp',
				'wbxml' => 'application/vnd.wap.wbxml',
				'wm' => 'video/x-ms-wm',
				'wma' => 'audio/x-ms-wma',
				'wml' => 'text/vnd.wap.wml',
				'wmlc' => 'application/vnd.wap.wmlc',
				'wmls' => 'text/vnd.wap.wmlscript',
				'wmlsc' => 'application/vnd.wap.wmlscriptc',
				'wmv' => 'video/x-ms-wmv',
				'wmx' => 'video/x-ms-wmx',
				'wrl' => 'model/vrml',
				'wvx' => 'video/x-ms-wvx',
				'xbm' => 'image/x-xbitmap',
				'xht' => 'application/xhtml+xml',
				'xhtml' => 'application/xhtml+xml',
				'xls' => 'application/vnd.ms-excel',
				'xml' => 'text/xml',
				'xpm' => 'image/x-xpixmap',
				'xsl' => 'text/xml',
				'xwd' => 'image/x-xwindowdump',
				'xyz' => 'chemical/x-xyz',
				'zip' => 'application/zip',
				'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
				'xltx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.template',
				'potx' => 'application/vnd.openxmlformats-officedocument.presentationml.template',
				'ppsx' => 'application/vnd.openxmlformats-officedocument.presentationml.slideshow',
				'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
				'sldx' => 'application/vnd.openxmlformats-officedocument.presentationml.slide',
				'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
				'dotx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.template',
				'xlam' => 'application/vnd.ms-excel.addin.macroEnabled.12',
				'xlsb' => 'application/vnd.ms-excel.sheet.binary.macroEnabled.12'
			);

			$ext = explode('.', $filename);
			$ext = strtolower(array_pop($ext));
			if (!empty($mime_types[$ext]))
				return $mime_types[$ext];

			/*
			 * last, we try to retrieve it using mime_content_type
			 * Why is this last??? Because it's unreliable...
			 */

			if (function_exists('mime_content_type')) {
				$mime = mime_content_type($filename);
				if (!empty($mime))
					return $mime;
			}

			/* still nothing? we return an empty string */
			return '';
		}

		/**
		 * Processes file protection
		 * @param string $wlmfile File to process (relative to the WP Upload Folder)
		 */
		function FileProtect($wlmfile) {
						
			$attachments = $this->FileAttachments();

			// There's an issue where /wp-content/uploads/ are also being returned in NGINX web servers
			// So we check if the $wlmfile have those and trim them out
			if (strpos($wlmfile, '/wp-content/uploads/') !== false) 
				$wlmfile = str_replace('/wp-content/uploads/', '', $wlmfile);
				
			// fis for when WP is installed at subfolder but home url is main domain.
			$upload_dir = wp_upload_dir();
			$pathtouploads_file = $upload_dir['baseurl'] . '/'.$wlmfile;


			if ( isset($attachments[$wlmfile]) || isset($attachments[$pathtouploads_file])   )  {
				$finfo = $attachments[$wlmfile];
				if ($finfo=='')  $finfo = $attachments[$pathtouploads_file];

				$redirect = false;

				$levels    = $this->GetContentLevels( 'attachment', $finfo->ID );
				$protected = array_search( 'Protection', $levels );
				if ($protected !== false) { // we're protected
					unset($levels[$protected]);
					$user = wp_get_current_user();
					if (!$user->caps['administrator']) {
						if ($user->ID) {
							$ulevels = $this->GetMembershipLevels($user->ID);
							$levels = array_intersect($levels, $ulevels);
							if (count($levels)) {
								// check if any of the levels are for confirmation
								foreach ((array) $levels AS $key => $level) {
									if ($this->LevelUnConfirmed($level, $user->ID)) {
										$unconfirmedlevels[] = $level;
									}
								}
								if ($unconfirmedlevels) {
									$redirect = $this->ForConfirmationURL();
								}
								// check if any of the levels are cancelled
								foreach ((array) $levels AS $key => $level) {
									if ($this->LevelCancelled($level, $user->ID)) {
										unset($levels[$key]);
									}
								}
								// no more levels left for this member? if so, redirect to cancelled page
								if (!count($levels)) {
									$redirect = $this->CancelledURL();
								}
							} else {
								$redirect = $this->WrongLevelURL();
							}
						} else {
							$redirect = $this->NonMembersURL();
						}
					}
				}

				// no access rights, we redirect
				if ($redirect) {
					header("Location:{$redirect}");
					exit;
				}
			}

			$file = $this->wp_upload_path . '/' . $wlmfile;
			// load the correct mime type
			$mime = $this->GetMimeType($file);
			if (empty($mime)) {
				$mime = $finfo->mime;
			}

			if (file_exists($file)) {
				$this->download($file);
			} else {
				// file does not exist
				header("HTTP/1.0 404 Not Found");
				print('404 - File Not Found');
			}
			exit;
		}

		/**
		 * Adds/Removes htaccess code to the upload folder's htaccess file
		 * @param boolean $remove (optional) Default False.  True removes the code
		 */
		function FileProtectHtaccess($remove = false) {
			if (file_exists($this->wp_upload_path)) {
				$htaccess_start = '# BEGIN WishList Member Attachment Protection';
				$htaccess_end = '# END WishList Member Attachment Protection';
				$htaccess_file = $this->wp_upload_path . '/.htaccess';
				$nginx_file = $this->wp_upload_path . '/wlm_file_protect_nginx.conf';

				// read it
				$htaccess = (string) @file_get_contents($htaccess_file);

				// remove it
				list($start) = explode($htaccess_start, $htaccess);
				list($x, $end) = explode($htaccess_end, $htaccess);
				$htaccess = trim($start) . "\n" . trim($end);

				if (!$remove) {

					$ignorelist = trim($this->GetOption('file_protection_ignore'));
					if (empty($ignorelist))
						$ignorelist = 'jpg,jpeg,png,gif,bmp,css,js';

					$ignorelist = explode(',', $ignorelist);
					foreach ($ignorelist AS $i => $ext) {
						$ext = preg_replace('/[^A-Za-z0-9]/', '', trim($ext));
						$ignorelist[$i] = $ext;
					}
					$this->SaveOption('file_protection_ignore', implode(', ', $ignorelist));
					$ignorelist = implode('|', $ignorelist);

					// .htaccess rules
					$siteurl = parse_url(home_url('/index.php'), PHP_URL_PATH);
					$htaccess.="\n{$htaccess_start}\nRewriteEngine on\nRewriteCond %{REQUEST_URI} !\.({$ignorelist})\$\nRewriteRule ^(.*)\$ {$siteurl}?wlmfile=\$1 [L]\n{$htaccess_end}";

					// nginx rules - saved to wlm_file_protect_nginx.conf
					$upload_dir = wp_upload_dir();
					$upload_dir = parse_url($upload_dir['baseurl'].'/', PHP_URL_PATH);
					$nginx = sprintf("# Include this file in your site configuration's server {} block\n%4\$s\n\nlocation ~ %1\$s.+?\.(%2\$s)\$ {}\nlocation %1\$s {\n\trewrite ^(.*)\$ %3\$s/index.php?wlmfile=\$1 break;\n}\n%5\$s", $upload_dir, $ignorelist, home_url(), $htaccess_start, $htaccess_end);
				}

				// write it
				// .htaccess
				$f = fopen($htaccess_file, 'w');
				fwrite($f, trim($htaccess));
				fclose($f);
				// nginx
				$f = fopen($nginx_file, 'w');
				fwrite($f, trim($nginx));
				fclose($f);
			}
		}

		function Edit_Attachment($attachment_id) {
			$this->Add_Attachment($attachment_id, true);
		}
		function Add_Attachment($attachment_id, $editing = false) {
			$pathtouploads = get_bloginfo('url') . '/' . $this->wp_upload_path_relative . '/';
			$obj = get_post($attachment_id);


			$attachments = $this->GetOption('AttachmentsData');
			$sizes = array('thumbnail', 'medium', 'large', 'full');

			$attachments[str_replace($pathtouploads, '', wp_get_attachment_url($obj->ID))] = (object) array('ID' => $obj->ID, 'post_parent' => $obj->post_parent, 'mime' => $obj->post_mime_type);
			foreach ((array) $sizes AS $size) {
				list($x) = wp_get_attachment_image_src($obj->ID, $size);
				if ($x)
					$attachments[str_replace($pathtouploads, '', $x)] = (object) array('ID' => $obj->ID, 'post_parent' => $obj->post_parent, 'mime' => $obj->post_mime_type);
			}
			$this->SaveOption('AttachmentsHash', '');
			$this->SaveOption('AttachmentsData', $attachments);

			if(!$editing || ($editing && $this->SpecialContentLevel( $attachment_id, 'Inherit' ))) {
				$this->inherit_protection($attachment_id);
			}
		}

		function Delete_Attachment($attachment_id) {



			$pathtouploads = get_bloginfo('url') . '/' . $this->wp_upload_path_relative . '/';
			$obj = get_post($attachment_id);

			$attachments = $this->GetOption('AttachmentsData');
			$sizes = array('thumbnail', 'medium', 'large', 'full');

			unset($attachments[str_replace($pathtouploads, '', wp_get_attachment_url($obj->ID))]);

			foreach ((array) $sizes AS $size) {
				list($x) = wp_get_attachment_image_src($obj->ID, $size);
				if ($x)
					unset($attachments[str_replace($pathtouploads, '', $x)]);
			}

			$this->SaveOption('AttachmentsHash', '');
			$this->SaveOption('AttachmentsData', $attachments);

		}

		/**
		 * Loads all attachments from the database
		 * and saves it using FileAttachments method
		 */
		function FileProtectLoadAttachments() {
			// bug fix.  get_posts do not return all posts for none admin user
			if (current_user_can('manage_options')) {
				// admin user
				$pathtouploads = get_bloginfo('url') . '/' . $this->wp_upload_path_relative . '/';
				$objs = get_posts('post_type=attachment&post_status=inherit&numberposts=1000000&suppress_filters=> false ');
				$objmd5 = md5(serialize($objs));
				$chk_attachments = $this->GetOption('AttachmentsData');

				if (empty($chk_attachments)) {
					$rebuild = "YES";
				}
				if ($objmd5 != $this->GetOption('AttachmentsHash') | $rebuild == 'YES') {
					$attachments = array();
					$sizes = array('thumbnail', 'medium', 'large', 'full');
					foreach ((array) $objs AS $obj) {
						$attachments[str_replace($pathtouploads, '', wp_get_attachment_url($obj->ID))] = (object) array('ID' => $obj->ID, 'post_parent' => $obj->post_parent, 'mime' => $obj->post_mime_type);

						foreach ((array) $sizes AS $size) {
							list($x) = wp_get_attachment_image_src($obj->ID, $size);
							if ($x)
								$attachments[str_replace($pathtouploads, '', $x)] = (object) array('ID' => $obj->ID, 'post_parent' => $obj->post_parent, 'mime' => $obj->post_mime_type);
						}
					}
					$this->SaveOption('AttachmentsHash', $objmd5);
					$this->SaveOption('AttachmentsData', $attachments);

					//$log.="attachments=" . print_r($attachments, 1);
				}else{
					//  admin user. nothing is changed.
					$attachments = $this->GetOption('AttachmentsData');
				}
				
			}else {
				// none admin user.
				$attachments = $this->GetOption('AttachmentsData');
				//$log.="attachments=" . print_r($attachments, 1);
			}

			// attachments
			$this->FileAttachments($attachments);
		}
		/**
		 * Saves and Returns File Attachments
		 * @staticvar array $a Array of saved attachments
		 * @param array $attachments Array of attachments to save
		 * @return array Array of attachments
		 */
		function FileAttachments($attachments = null) {
			static $a = array();
			if (!is_null($attachments))
				$a = $attachments;
			return $a;
		}

		/**
		 * Get Site Info
		 * @param array $info Array of info to return
		 * @return array Array of info
		 */
		function GetSiteInfo($info = null) {
			$data = array();
			if (is_null($info))
				return $data;

			if (isset($info["send_wlmversion"]))
				$data["wlmversion"] = $this->Version;
			else
				$data["wlmversion"] = null;

			if (isset($info["send_phpversion"]))
				$data["phpversion"] = phpversion();
			else
				$data["phpversion"] = null;

			if (isset($info["send_apachemod"]))
				$data["apachemod"] = php_sapi_name();
			else
				$data["apachemod"] = null;

			if (isset($info["send_webserver"]))
				$data["webserver"] = $_SERVER["SERVER_SOFTWARE"];
			else
				$data["webserver"] = null;

			if (isset($info["send_language"]))
				$data["language"] = get_bloginfo("language");
			else
				$data["language"] = null;

			if (isset($info["send_apiused"])) {
				$api_used = $this->GetOption('WLMAPIUsed');
				if ($api_used) {
					$api_used = (array) maybe_unserialize($api_used);
				} else {
					$api_used = array();
				}
				$data["apiused"] = $api_used;
			} else {
				$data["apiused"] = null;
			}


			if (isset($info["send_payment"])) {
				$shoppingcart_used = $this->GetOption("WLMShoppinCartUsed");
				if ($shoppingcart_used) {
					$shoppingcart_used = (array) maybe_unserialize($shoppingcart_used);
				} else {
					$shoppingcart_used = array();
				}
				$data["payment"] = $shoppingcart_used;
			} else {
				$data["payment"] = null;
			}

			if (isset($info["send_autoresponder"])) {
				$autoresponder_used = $this->GetOption('Autoresponders');

				if ($autoresponder_used && isset($autoresponder_used["ARProvider"]) && $autoresponder_used["ARProvider"] != "") {
					$data["autoresponder"] = $autoresponder_used["ARProvider"];
				} else {
					$data["autoresponder"] = "None";
				}
			} else {
				$data["autoresponder"] = null;
			}

			if (isset($info["send_webinar"])) {
				$webinars = $this->GetOption('webinar');
				if ($webinars) {
					$data["webinar"] = implode(",", array_keys((array) $webinars));
				} else {
					$data["webinar"] = "None";
				}
			} else {
				$data["webinar"] = null;
			}


			if (isset($info["send_nlevels"])) {
				$wpm_levels = (array) $this->GetOption('wpm_levels');
				$data["nlevels"] = count($wpm_levels);
			} else {
				$data["nlevels"] = null;
			}

			if (isset($info["send_nmembers"]))
				$data["nmembers"] = count($this->MemberIDs());
			else
				$data["nmembers"] = null;


			if (isset($info["send_sequential"])) {
				$wpm_levels = (array) $this->GetOption('wpm_levels');
				$is_seq = false;
				foreach ($wpm_levels as $level) {
					if ($level["upgradeTo"] && strlen($level["upgradeTo"]) > 3) {
						$is_seq = true;
						break;
					}
				}
				if ($is_seq)
					$data["sequential"] = 1;
				else
					$data["sequential"] = 0;
			}else {
				$data["sequential"] = null;
			}

			if (isset($info["send_customreg"])) {
				$forms = $this->GetCustomRegForms();
				$data["customreg"] = count($forms);
			} else {
				$data["customreg"] = null;
			}

			return $data;
		}

		/**
		 * Send Anonymous Data to server
		 */
		function ReturnAnonymousData() {
			$info_to_send = $this->GetOption('WLMSiteTracking');
			if ($info_to_send) {
				$info_to_send = maybe_unserialize($info_to_send);
				$site_info = $this->GetSiteInfo($info_to_send);
				if (!empty($site_info)) {
					return $site_info;
				}
			}
			return '';
		}

		/**
		 * validate the request for anonymous data
		 * @param int $time
		 * @param string $hash sha1 hash
		 * @return boolean
		 */
		function ValidateRequestForAnonData($time, $hash) {
			$t = time();
			$minus = $t - 10;
			$plus = $t + 10;
			if ($time >= $minus && $time <= $plus) {
				$license = $this->GetOption('LicenseKey');
				if (preg_match('/[0-9a-f]{32}/', $license)) {
					$myhash = sha1($time . $license);
					return $myhash == $hash;
				}
			}
			return false;
		}

		/**
		 * Loads the init file for the integration
		 */
		public function LoadInitFile($file) {
			global $WishListMemberInstance;
			$init_file = str_replace('.php', '.init.php', $file);
			$init_file = $this->pluginDir . '/lib/' . $init_file;
			if (file_exists($init_file)) {
				include_once($init_file);
			}
		}

		/**
		 * Register a Shopping Cart Integration Function
		 * @param string $uri URI prefix
		 * @param string $methodname Method Name to call
		 */
		function RegisterSCIntegration($uri, $filename, $classname, $methodname) {
			if (!isset($this->SCIntegrationURIs))
				$this->SCIntegrationURIs = array();

			$this->SCIntegrationURIs[$uri] = array(
				'file' => $filename,
				'class' => $classname,
				'method' => $methodname
			);
		}

		/**
		 * Register an Autoresponder Integration Function
		 * @param string $ar Autoresponder Option Name
		 * @param string $methodname Method Name to call
		 */
		function RegisterARIntegration($ar, $filename, $classname, $methodname) {
			if (!isset($this->ARIntegrationMethods))
				$this->ARIntegrationMethods = array();

			$this->ARIntegrationMethods[$ar] = array(
				'file' => $filename,
				'class' => $classname,
				'method' => $methodname
			);
		}

		function RegisterWebinarIntegration($webinar, $filename, $classname) {
			if (!isset($this->WebinarIntegrations))
				$this->WebinarIntegrations = array();

			$this->WebinarIntegrations[$webinar] = array(
				'file' => $filename,
				'class' => $classname
			);
		}

		/**
		 * Checks if a Registration URL suffix is already in use
		 * @param string $suffix Registration URL suffix
		 * @param $excludeLevels = null array of Membership Level IDs to exclude
		 * @param $excludeSCs = null array of Shopping Cart Thank you URL option names to exclude
		 * @return boolean
		 */
		function RegURLExists($suffix, $excludeLevels = null, $excludeSCs = null) {
			$suffix = trim($suffix);
			$suffixes = array();

			// stuff that we remove from our check
			$excludeLevels = (array) $excludeLevels;
			$excludeSCs = (array) $excludeSCs;

			$keys = array_keys((array) $this->SCIntegrationURIs);
			foreach ((array) $keys AS $key) {
				if (!in_array($key, $excludeSCs)) {
					$suffixes[] = trim($this->GetOption($key));
				}
			}

			$wpm_levels = $this->GetOption('wpm_levels');
			foreach ((array) $wpm_levels AS $key => $level) {
				if (!in_array($key, $excludeLevels)) {
					$suffixes[] = trim($level['url']);
				}
			}

			// remove empty entries and the 2nd function parameter
			$suffixes = array_diff($suffixes, array(''));

			return in_array($suffix, $suffixes);
		}

		/**
		 * Generates a Registration / Thank You URL Suffix
		 * @param integer $length = 6 Length of the suffix to return
		 * @return string Registration/Thank You URL Suffix
		 */
		function MakeRegURL($length = 6) {
			$array = array_flip(array_merge(range('A', 'Z'), range('a', 'z'), range(0, 9)));
			do {
				$url = implode('', array_rand($array, $length));
			} while ($this->RegURLExists($url));
			return $url;
		}

		/**
		 * Saves Membership Levels (Used only by the Membership Levels tab)
		 * @return boolean
		 */
		function SaveMembershipLevels() {
			$wpm_levels = $this->GetOption('wpm_levels');

			foreach ((array) $_POST['wpm_levels'] AS $key => $level) {

				$_POST['wpm_levels'][$key]['name'] = trim($level['name']);

				if ($this->RegURLExists($level['url'], $key)) {
					$_POST['err'] = 'URL for ' . $level['name'] . ' (' . $level['url'] . ') is already in use by another Membership Level or a Shopping Cart integration.  Please try a different one.';
					return false;
				}

				$_POST['wpm_levels'][$key]['removeFromLevel'] = empty($level['removeFromLevel']) ? array() : array_fill_keys($level['removeFromLevel'], 1);
				$_POST['wpm_levels'][$key]['addToLevel'] = empty($level['addToLevel']) ? array() : array_fill_keys($level['addToLevel'], 1);

			}

			foreach ($_POST['wpm_levels'] as $key => $val) {
				$wpm_levels[$key] = $val;
			}



			$this->SortLevels($wpm_levels, 'a', 'levelOrder');
			$_POST['wpm_levels'] = $wpm_levels;
			$this->SaveOptions();
			// do we clone?

			if (wlm_arrval($_POST, 'doclone')) {
				$this->CloneMembershipContent($_POST['clonefrom'], $_POST['doclone']);
			}
			return true;
		}

		/**
		 * Return an array of countries
		 * @return array
		 */
		function Countries() {
			return array('Select Country', 'Afghanistan', 'Aland Islands', 'Albania', 'Algeria', 'American Samoa', 'Andorra', 'Angola', 'Anguilla', 'Antarctica', 'Antigua And Barbuda', 'Argentina', 'Armenia', 'Aruba', 'Australia', 'Austria', 'Azerbaijan', 'Bahamas', 'Bahrain', 'Bangladesh', 'Barbados', 'Belarus', 'Belgium', 'Belize', 'Benin', 'Bermuda', 'Bhutan', 'Bolivia', 'Bosnia And Herzegovina', 'Botswana', 'Bouvet Island', 'Brazil', 'British Indian Ocean Territory', 'Brunei Darussalam', 'Bulgaria', 'Burkina Faso', 'Burundi', 'Cambodia', 'Cameroon', 'Canada', 'Cape Verde', 'Cayman Islands', 'Central African Republic', 'Chad', 'Channel Islands', 'Chile', 'China', 'Christmas Island', 'Cocos (Keeling) Islands', 'Colombia', 'Comoros', 'Congo', 'Congo The Dem. Rep. Of The', 'Cook Islands', 'Costa Rica', 'Cote Divoire', 'Croatia', 'Cuba', 'Cyprus', 'Czech Republic', 'Denmark', 'Djibouti', 'Dominica', 'Dominican Republic', 'East Timor', 'Ecuador', 'Egypt', 'El Salvador', 'Equatorial Guinea', 'Eritrea', 'Estonia', 'Ethiopia', 'Falkland Islands (Malvinas)', 'Faroe Islands', 'Fiji', 'Finland', 'France', 'French Guiana', 'French Polynesia', 'French Southern Territories', 'Gabon', 'Gambia', 'Georgia', 'Germany', 'Ghana', 'Gibraltar', 'Greece', 'Greenland', 'Grenada', 'Guadeloupe', 'Guam', 'Guatemala', 'Guersney', 'Guinea', 'Guinea-Bissau', 'Guyana', 'Haiti', 'Heard Island And Mcdonald Islands', 'Holy See (Vatican City State)', 'Honduras', 'Hong Kong', 'Hungary', 'Iceland', 'India', 'Indonesia', 'Iran Islamic Republic Of', 'Iraq', 'Ireland', 'Isle of Man', 'Israel', 'Italy', 'Jamaica', 'Japan', 'Jersey', 'Jordan', 'Kazakstan', 'Kenya', 'Kiribati', 'Korea Democratic Peoples Republic', 'Kuwait', 'Kyrgyzstan', 'Lao Peoples Democratic Republic', 'Latvia', 'Lebanon', 'Lesotho', 'Liberia', 'Libya', 'Libyan Arab Jamahiriya', 'Liechtenstein', 'Lithuania', 'Luxembourg', 'Macau', 'Macedonia', 'Madagascar', 'Malawi', 'Malaysia', 'Maldives', 'Mali', 'Malta', 'Marshall Islands', 'Martinique', 'Mauritania', 'Mauritius', 'Mayotte', 'Mexico', 'Micronesia Federated States Of', 'Moldova Republic Of', 'Monaco', 'Mongolia', 'Montenegro', 'Montserrat', 'Morocco', 'Mozambique', 'Myanmar', 'Namibia', 'Nauru', 'Nepal', 'Netherlands', 'Netherlands Antilles', 'New Caledonia', 'New Zealand', 'Nicaragua', 'Niger', 'Nigeria', 'Niue', 'Norfolk Island', 'Northern Mariana Islands', 'Norway', 'Oman', 'Pakistan', 'Palau', 'Palestinian Territory Occupied', 'Panama', 'Papua New Guinea', 'Paraguay', 'Peru', 'Philippines', 'Pitcairn', 'Poland', 'Portugal', 'Puerto Rico', 'Qatar', 'Reunion', 'Romania', 'Russia', 'Russian Federation', 'Rwanda', 'Saint Helena', 'Saint Kitts And Nevis', 'Saint Lucia', 'Saint Pierre And Miquelon', 'Saint Vincent And The Grenadines', 'Samoa', 'San Marino', 'Sao Tome And Principe', 'Saudi Arabia', 'Senegal', 'Serbia', 'Serbia &amp; Montenegro', 'Seychelles', 'Sierra Leone', 'Singapore', 'Slovakia', 'Slovenia', 'Solomon Islands', 'Somalia', 'South Africa', 'South Georgia / South Sandwich Islands', 'South Korea', 'Spain', 'Sri Lanka', 'Sudan', 'Suriname', 'Svalbard And Jan Mayen', 'Swaziland', 'Sweden', 'Switzerland', 'Syrian Arab Republic', 'Taiwan', 'Tajikistan', 'Tanzania United Republic Of', 'Thailand', 'Timor-Leste', 'Togo', 'Tokelau', 'Tonga', 'Trinidad And Tobago', 'Tunisia', 'Turkey', 'Turkmenistan', 'Turks And Caicos Islands', 'Tuvalu', 'Uganda', 'Ukraine', 'United Arab Emirates', 'United Kingdom', 'United States', 'United States Minor Outlying Islands', 'Uruguay', 'Uzbekistan', 'Vanuatu', 'Venezuela', 'Vietnam', 'Virgin Islands British', 'Virgin Islands U.S.', 'Wallis And Futuna', 'Western Sahara', 'Yemen', 'Yugoslavia', 'Zambia', 'Zimbabwe');
		}

		/**
		 * Backs up WishList Member Data
		 * @return mixed FALSE on failure OR Date and Time of the Backup formatted as yyyymmddhhmmss on success
		 */
		function Backup_Generate() {
			ignore_user_abort(true);
			global $wpdb;
			wlm_set_time_limit(60 * 60 * 24);

			$tables = array_values((array) $this->Tables);
			$up = array();
			if (wlm_arrval($_POST, 'WishListMemberAction') == 'BackupSettings') {
				if (wlm_arrval($_POST, 'backup_include_users') == 1) {
					$this->SaveOption('backup_include_users', 1);
					array_unshift($tables, $wpdb->users, $wpdb->usermeta);
					//$up[]='u';
				} else {
					$this->SaveOption('backup_include_users', 0);
				}
				if (wlm_arrval($_POST, 'backup_include_posts') == 1) {
					$this->SaveOption('backup_include_posts', 1);
					array_unshift($tables, $wpdb->posts, $wpdb->postmeta, $wpdb->comments, $wpdb->commentmeta);
					//$up[]='p';
				} else {
					$this->SaveOption('backup_include_posts', 0);
				}
			}
			if ($this->GetOption('backup_include_users') == 1) {
				array_unshift($tables, $wpdb->users, $wpdb->usermeta);
				$up[] = 'u';
			}
			if ($this->GetOption('backup_include_posts') == 1) {
				array_unshift($tables, $wpdb->posts, $wpdb->postmeta, $wpdb->comments, $wpdb->commentmeta);
				$up[] = 'p';
			}
			$up = count($up) ? '-' . implode('', $up) : '';

			$date = gmdate('YmdHis');

			$backupname = 'wlmbackup' . $up . '_' . $date . '_' . str_replace('.', '-', $this->Version);
			$sqlname = $backupname . '.sql';
			$tmpname = $backupname . '.tmp';

			//$backupfolder = ABSPATH . WLM_BACKUP_PATH;
                        // fix, some host add the ABSPATH to the WLM_BACKUP_PATH, following remove the equal part.
                        $backupfolder = ABSPATH . substr(WLM_BACKUP_PATH, strpos(WLM_BACKUP_PATH, basename(ABSPATH)) + strlen(basename(ABSPATH)));
                        
			$outfile = $backupfolder . $tmpname;
			$httfile = $backupfolder . ".htaccess";
			@mkdir($backupfolder, 0755, true);

			$httfilehandler = fopen($httfile, 'w');
			if (!$httfilehandler) {
				$_POST['err'] = sprintf(__("ERROR: Cannot create backup file. Please check file permissions for <b>%s</b>", 'wishlist-member'), WLM_BACKUP_PATH);
				return false;
			}
			fwrite($httfilehandler, "<Limit GET POST>\n");
			fwrite($httfilehandler, "deny from all\n");
			fwrite($httfilehandler, "</Limit>\n");
			fclose($httfilehandler);

			$f = fopen($outfile, 'w');

			if (!$f) {
				$_POST['err'] = sprintf(__("ERROR: Cannot create backup file. Please check file permissions for <b>%s</b>", 'wishlist-member'), WLM_BACKUP_PATH);
				return false;
			}

			/* write file description */
			fwrite($f, "# WishList Member Backup\n");
			$date = $this->FormatDate($date);
			fwrite($f, "# Generated on {$date}\n");
			fwrite($f, "# Includes: WishList Member Settings\n");
			if (strpos($up, 'u') !== false)
				fwrite($f, "# Includes: Users\n");
			if (strpos($up, 'p') !== false)
				fwrite($f, "# Includes: Content\n");
			fwrite($f, "\n# ----------------------\n\n");

			foreach ($tables AS $table) {
				fwrite($f, "# Table {$table}\n");
				fwrite($f, "DROP TABLE IF EXISTS `{$table}`;\n");
				$create = $wpdb->get_row($x = "SHOW CREATE TABLE `{$table}`", ARRAY_A);
				$create = str_replace(array("\r", "\n"), ' ', $create['Create Table']);
				fwrite($f, $create . ";\n");

				$query = "SELECT * FROM `{$table}`";

				// WP uses mysqli from v3.9 onwards so we check for it
				if($wpdb->use_mysqli) {
					$r = mysqli_query($wpdb->dbh, $query);
					$fetch_function = 'mysqli_fetch_assoc';
				} else {
					$r = mysql_query($query, $wpdb->dbh);
					$fetch_function = 'mysql_fetch_assoc';
				}

				while ($out = $fetch_function($r)) {
					$cols = '`' . implode('`,`', array_keys($out)) . '`';
					$query = "INSERT INTO `{$table}` ({$cols}) VALUES ";
					$placeholders = array_fill(0, count($out), '%s');
					$placeholders = "('" . implode("','", $placeholders) . "')";
					$out = $wpdb->prepare($query . $placeholders, $out);
					fwrite($f, $out . ";\n");
				}
				fwrite($f, "\n");
			}
			fwrite($f, "\n# --- END OF BACKUP FILE {$backupname} ---\n");
			fclose($f);
			rename($outfile, $backupfolder . $sqlname);

			$result = $this->Backup_Details($sqlname);
			$_POST['msg'] = sprintf(__("WishList Member successfully backed-up on %s.", 'wishlist-member'), $this->FormatDate($result['date']));

			return $result;
		}

		/**
		 * Download Backup
		 * @param string $backupName
		 * @return boolean FALSE on Error
		 */
		function Backup_Download($backupName) {
                        $folderpath = ABSPATH . substr(WLM_BACKUP_PATH, strpos(WLM_BACKUP_PATH, basename(ABSPATH)) + strlen(basename(ABSPATH)));
			$file = $folderpath . $backupName . '.sql';
			if (!file_exists($file)) {
				$_POST['err'] = __('Backup file not found.', 'wishlist-member');
				return false;
			}

			$fname = basename($file);
			header('Content-type: text/plain');
			header('Conent-length: ' . filesize($file));
			header('Content-disposition: attachment; filename="' . $fname . '"');
			readfile($file);
			exit;
		}

		/**
		 * Restore a WishList Member Backup
		 *
		 * Returns:
		 * FALSE on failure or if the backup date does not exist
		 * TRUE on success and $backupCurrent is FALSE
		 * Date of the new Backup on success and $backupCurrent is TRUE
		 *
		 * @param string $backupname
		 * @param boolean $backupCurrent (optional) TRUE to backup current database first before restoration
		 * @return array Backup Details
		 */
		function Backup_Restore($backupname, $backupCurrent = true) {
			$result = $this->Backup_Import($backupCurrent, $backupname);
			if ($result) {
				$_POST['msg'] = sprintf(__("WishList Member Settings successfully restored to %s.", 'wishlist-member'), $this->FormatDate($result['date']));
				return true;
			} else {
				$_POST['err'] = __("An error occured while trying to restore WishList Member Settings", 'wishlist-member');
				return false;
			}
		}

		/**
		 * Backup Details
		 * @param string $backupname
		 * @return array
		 */
		function Backup_Details($backupname) {

			if (substr($backupname, -4) == '.sql')
				$backupname = substr($backupname, 0, -4);

			$ar = explode('_', $backupname);
			list($name, $up) = explode('-', $ar[0]);
			if ($up) {
				$users = strpos($up, 'u') !== false;
				$posts = strpos($up, 'p') !== false;
			}
			$date = $ar[1];
			$ver = str_replace('-', '.', $ar[2]);
			$full = $backupname;

			$backup = array(
				'name' => $name,
				'date' => $date,
				'ver' => $ver,
				'full' => $full,
				'users' => $users,
				'posts' => $posts
			);

			return $backup;
		}

		/**
		 * Deletes a WishList Member Backup
		 * @param string $date Backup date to delete (yyyymmddhhmmss)
		 */
		function Backup_Delete($backupname) {
                    
                        $folderpath = ABSPATH . substr(WLM_BACKUP_PATH, strpos(WLM_BACKUP_PATH, basename(ABSPATH)) + strlen(basename(ABSPATH)));
			unlink($folderpath . $backupname . '.sql');
			
                        $result = $this->Backup_Details($backupname);
			$_POST['msg'] = sprintf(__("WishList Member Settings \"%s\" deleted.", 'wishlist-member'), $this->FormatDate($result['date']));
			return $result;
		}

		/**
		 * Lists all WishList Member Backups
		 * @return array of Backup Codes (yyyymmddhhmmss)
		 */
		function Backup_ListAll() {
			global $wpdb;
			//$folderpath = ABSPATH . WLM_BACKUP_PATH;
                        $folderpath = ABSPATH . substr(WLM_BACKUP_PATH, strpos(WLM_BACKUP_PATH, basename(ABSPATH)) + strlen(basename(ABSPATH)));
                        
			$results = glob($folderpath . '*.sql');
			foreach ($results AS $k => $v) {
				$results[$k] = substr(basename($v), 0, -4);
			}
			//$results = $wpdb->get_results("SELECT `option_name` FROM {$wpdb->options} WHERE `option_name` LIKE '{$this->PluginOptionName}-%'");
			$backups = array();
			foreach ($results AS $result) {
				$backup = $this->Backup_Details($result);
				$backups[$backup['date']] = $backup;
			}
			krsort($backups);
			return $backups;
		}

		/**
		 * Import Settings
		 * @return boolean FALSE on failure
		 */
		function Backup_Import($backupCurrent = true, $backupName = null) {
			ignore_user_abort(true);
			global $wpdb;
			if ($backupCurrent) {
				$this->Backup_Generate();
			}
			if (is_null($backupName)) {
				$showmsg = true;
				$fileName = $_FILES['ImportSettingsfile']['name'];
				$backupFile = $_FILES['ImportSettingsfile']['tmp_name'];
				$fileSize = $_FILES['ImportSettingsfile']['size'];
				$fileType = $_FILES['ImportSettingsfile']['type'];
				$err_num = isset( $_FILES['ImportSettingsfile']['error'] ) ? $_FILES['ImportSettingsfile']['error'] : 0;
			} else {
				$showmsg = false;
                $folderpath = ABSPATH . substr(WLM_BACKUP_PATH, strpos(WLM_BACKUP_PATH, basename(ABSPATH)) + strlen(basename(ABSPATH)));
				$backupFile = $folderpath . $backupName . '.sql';
			}

			if ( $err_num && $showmsg ) {
					$file_errors = array(
				        1=>"The uploaded file exceeds the upload_max_filesize directive in php.ini",
				        2=>"The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form",
				        3=>"The uploaded file was only partially uploaded",
				        4=>"No file was uploaded",
				        6=>"Missing a temporary folder",
					);
					if ( isset( $file_errors[$err_num] ) ) {
						$_POST['err'] = $file_errors[$err_num];
					} else {
						$_POST['err'] = __("An unknown error number occured while trying to import file", 'wishlist-member');
					}
					return false;
			}

			$f = fopen($backupFile, 'r');
			if (!$f) {
				if ($showmsg) {
					$_POST['err'] = __("An error occured while trying to import file", 'wishlist-member');
				}
				return false;
			}

			while (!feof($f)) {
				$line = trim(fgets($f, 1000000));
				$first_char = substr($line, 0, 1);
				if ($line != '' && $first_char != '#' && $first_char != ';') {
					$query = $line;
					if ($wpdb->query($query) === false) {
						$_POST['err'] = __("An SQL error occured while trying to import file.", 'wishlist-member');
						//return false;
					}
				}
			}

			if ($showmsg) {
				$_POST['msg'] = __("WishList Member Settings successfully imported.", 'wishlist-member');
			}
			return $this->Backup_Details($backupName);
		}


		function ResetSettings() {
			global $wpdb;
			if (wlm_arrval($_POST, 'resetSettingConfirm')) {
				if ($this->Backup_Generate()) {
					foreach ($this->Tables AS $table) {
						$wpdb->query("TRUNCATE `{$table}`");
						//$wpdb->query("DROP TABLE `{$table}`");
					}
					$this->Activate();
					$_POST['msg'] = __('WishList Member reset to default settings', 'wishlist-member');
				} else {
					$_POST['err'] = __('Reset Aborted. Failed to backup current settings.', 'wishlist-member');
					return false;
				}
			}
		}

		/**
		 * Format date to "D, d M Y h:i:s a"
		 * @return string ( Ex: Thu, January 21th, 2010 10:16:18 am)
		 */
		function FormatDate($date) {
			return date_i18n('D, d M Y h:i:s a', strtotime($date) + $this->GMT);
		}

		/**
		 * This function returns a 200 OK Response Header and
		 * Displays the text WishList Member and a link to the WP homepage
		 */
		function CartIntegrationTerminate() {
			global $wlm_no_cartintegrationterminate;
			if(!empty($wlm_no_cartintegrationterminate)) return;
			if (!headers_sent()) {
					header($_SERVER['SERVER_PROTOCOL'] . " 200 OK");
				}
			$url = get_bloginfo('url');
			echo "WishList Member<br /><a href='{$url}'>Click here to view homepage</a>";
			exit;
		}

		/**
		 * Generate Recaptcha HTML for level
		 * @param int $levelID
		 * @return string
		 */
		function GenerateRecaptchaHTML($levelID) {
			$wpm_levels = $this->GetOption('wpm_levels');
			if ($this->IsPPPLevel($levelID)) {
				$this->InjectPPPSettings($wpm_levels, $levelID);
			}
			$captcha_html = '';
			if ($wpm_levels[$levelID]['requirecaptcha']) {
				$recaptcha_public = $this->GetOption('recaptcha_public_key');
				$recaptcha_private = $this->GetOption('recaptcha_private_key');
				if ($recaptcha_public && $recaptcha_private) {
					if (!function_exists('recaptcha_get_html')) {
						require_once($this->pluginDir . '/extlib/recaptchalib.php');
					}
					$captcha_html = recaptcha_get_html($recaptcha_public, $error, is_ssl());
				}
			}
			return $captcha_html;
		}

		function Plugin_Download_Url() {
			static $url;
			if ($this->GetOption('LicenseStatus') != 1) {
				return false;
			}
			if (!$url) {
				$url = 'http://wishlistproducts.com/download/' . $this->GetOption('LicenseKey') . '/==' . base64_encode(pack('i', $this->ProductSKU));
			}
			return $url;
		}

		function Plugin_Update_Url() {
			return wp_nonce_url('update.php?action=upgrade-plugin&plugin=' . $this->PluginFile, 'upgrade-plugin_' . $this->PluginFile);
		}

		function Plugin_Latest_Version() {
			static $latest_wpm_ver;
			$varname = 'WishListMember_Latest_Plugin_Version';
			if (empty($latest_wpm_ver) OR isset($_GET['checkversion'])) {
				$latest_wpm_ver = get_transient($varname);
				if (empty($latest_wpm_ver) OR isset($_GET['checkversion'])) {
					$latest_wpm_ver = $this->ReadURL(
							array(
						'http://wishlistproducts.com/download/ver.php?wlm/' . $this->Version,
						'http://wishlistactivation.com/versioncheck/?wlm/' . $this->Version
							)
							, 3);
					if (empty($latest_wpm_ver)) {
						//we failed, set the latest version to this one so that we won't keep checking again for today
						$latest_wpm_ver = $this->Version;
					}
					//even if we fail never try again for this day
					set_transient($varname, $latest_wpm_ver, 60 * 60 * 24);
				}
			}
			return $latest_wpm_ver;
		}

		function Plugin_Is_Latest() {
			$latest_ver = $this->Plugin_Latest_Version();
			$ver = $this->Version;
			if (preg_match('/^(\d+\.\d+)\.{' . 'GLOBALREV}/', $this->Version, $match)) {
				$ver = $match[1];
				preg_match('/^(\d+\.\d+)\.[^\.]*/', $latest_ver, $match);
				$latest_ver = $match[1];
			}
			return version_compare($latest_ver, $ver, '<=');
		}
		
		function Get_Announcement() {
			
			$announcement = $this->GetOption('hq_announcement');

			$hq_announcement = get_transient('wlm_hq_announcement');
			if($hq_announcement === false) {
				$hq_announcement = $this->ReadURL('http://hq.wishlistproducts.com/announcement/?wlm');
				set_transient( 'wlm_hq_announcement', $hq_announcement, 86400 );
			}
			
			//if 
			if ((empty($announcement) && !empty($hq_announcement)) || ($announcement != $hq_announcement)) {
				global $current_user;
				$user_id = $current_user->ID;
				$announcement = $hq_announcement;	
				$this->SaveOption('hq_announcement', $announcement);
				delete_user_meta($user_id, 'dismiss_hq_notice');
			}
			return $announcement;
		}

		// start folder protection method

		/**
		 * Adds/removes .htaccess in protected folders
		 * @param boolean $install
		 */
		function folder_protection_htaccess($install = true) {
			$parent_folder = $this->folder_protection_full_path($this->GetOption('rootOfFolders'));
			if(!is_dir($parent_folder)) {
				return;
			}
			
			$folders = glob($parent_folder . '/*', GLOB_ONLYDIR);
			if(empty($folders)) {
				return;
			}
			foreach($folders AS $folder) {
				$folder_id = $this->FolderID( $folder );
				//if($this->FolderProtected( $folder_id )){
					$this->FolderProtectHtaccess($parent_folder . '/' . basename($folder), $install);
				//}
			}
		}
		
		/**
		 * Adds .htaccess to protected folders
		 */
		function AddHtaccessToProtectedFolders() {
			$this->folder_protection_htaccess(true);
		}

		function RemoveAllHtaccessFromProtectedFolders() {
			$this->folder_protection_htaccess(false);
		}

		/**
		 * Retrieve Individual Folder's Protection Status
		 * @param string $folder Folder Path
		 * @return boolean
		 */
		function GetFolderProtectForceDownload($folder) {
			return $this->FolderForceDownload($this->FolderID($folder));
		}

		/**
		 * Retrieve all levels of a folder in an array
		 * @param string $folder folderpath
		 * @return array Membership Levels
		 */
		function GetFolderLevels($folder) {
			return $this->GetContentLevels('folders', $this->FolderID($folder), false, false);
		}

		/**
		 * Processes Folder protection
		 * @param string $wlm_folder Folder to process (relative to the Root of folders option)
		 */
		function FolderProtect($wlm_folder, $wlm_file) {

			$folder_id = $this->FolderID($wlm_folder);
			
			$wlm_file = $this->GetOption('rootOfFolders') . '/' . $wlm_folder . '/' . $wlm_file;
			if(!file_exists($wlm_file)) {
				// file does not exist
				header("HTTP/1.0 404 Not Found");
				print('404 - File Not Found');
				exit;
			}
			
			$forceDownload = $this->FolderForceDownload($folder_id);
			$user = wp_get_current_user();
			
			if(!$this->FolderProtected($folder_id) OR $user->caps['administrator']) {
				// folder not protected or user is admin
				$this->download($wlm_file, $forceDownload);
				exit;
			}
			
			$redirect = false;
			
			if(!$user->ID) {
				// not logged in
				header(sprintf('Location:%s', $this->NonMembersURL()));
				exit;
			}
			
			$ulevels = $this->GetMembershipLevels($user->ID, null, null, null, true);
			$levels = array_intersect($this->GetFolderLevels($wlm_folder), $ulevels);
			
			if(!count($levels)) {
				// no valid levels
				header(sprintf('Location:%s', $this->WrongLevelURL()));
				exit;
			}

			// remove expired levels
			foreach ((array) $levels AS $key => $level) {
				if ($this->LevelExpired($level, $user->ID)) {
					unset($levels[$key]);
				}
			}
			if(!count($levels)) {
				header(sprintf('Location:%s', $this->ExpiredURL()));
				exit;
			}

			// remove unconfirmed levels
			foreach ((array) $levels AS $key => $level) {
				if ($this->LevelUnConfirmed($level, $user->ID)) {
					unset($levels[$key]);
				}
			}
			if(!count($levels)) {
				header(sprintf('Location:%s', $this->ForConfirmationURL()));
				exit;
			}
			
			// remove forapproval levels
			foreach ((array) $levels AS $key => $level) {
				if ($this->LevelForApproval($level, $user->ID)) {
					unset($levels[$key]);
				}
			}
			if(!count($levels)) {
				header(sprintf('Location:%s', $this->ForApprovalURL()));
				exit;
			}
			
			// remove cancelled levels
			foreach ((array) $levels AS $key => $level) {
				if ($this->LevelCancelled($level, $user->ID)) {
					unset($levels[$key]);
				}
			}
			if(!count($levels)) {
				header(sprintf('Location:%s', $this->CancelledURL()));
				exit;
			}
			
			// all is well. release the kraken!
			$this->download($wlm_file, $forceDownload);
			exit;
		}

		/**
		 * Download a big file
		 * @param string $file full file path and name
		 */

		function download($file, $forceDownload = FALSE){
			@@ini_set('zlib.output_compression','Off');

			// prevent ../ security exploit allowing access to system files
			if(preg_match('/\.\.\//', $file)) {
				header('HTTP/1.0 403 Forbidden');
				exit;
			}

			global $_GET;

			$len = filesize($file);
			$filename = basename($file);
			$file_extension = strtolower(substr(strrchr($filename, "."), 1));


			// Determine correct MIME type

			$ctype = $this->GetMimeType($filename);

			session_write_close();
			$aHeader = array();
			$aHeader[] = "Cache-Control: no-cache, must-revalidate"; // HTTP/1.1
			$aHeader[] = "Expires: Sat, 26 Jul 1997 05:00:00 GMT"; // Date in the past
			//Use the switch-generated Content-Type
			$aHeader[]= "Content-Type: $ctype";

			// Accounts for IE 11 - User Agent has Changed
			if (strstr($_SERVER['HTTP_USER_AGENT'], "MSIE") || strstr($_SERVER['HTTP_USER_AGENT'], " rv:11.")) {

				# workaround for IE filename bug with multiple periods / multiple dots in filename
				# that adds square brackets to filename - eg. setup.abc.exe becomes setup[1].abc.exe
				$iefilename = preg_replace('/\./', '%2e', $filename, substr_count($filename, '.') - 1);

				if ($forceDownload) {
					$aHeader[] = "Content-Disposition: attachment; filename=\"$iefilename\"";
				} else {
					$aHeader[] = "Content-Disposition:  filename=\"$iefilename\"";
				}
			} else {

				if ($forceDownload) {
					$aHeader[] = "Content-Disposition: attachment; filename=\"$filename\"";
				} else {
					$aHeader[] = "Content-Disposition:   filename=\"$filename\"";
				}
			}

			$aHeader[] = "Accept-Ranges: bytes";

			$size = filesize($file);

			while (ob_get_level()) {
    			ob_end_clean();
			}

			// If it's a negative number, then it can't be handled by this system!
			if ($size < 0) {
				header('HTTP/1.1 416 Requested Range Not Satisfiable');
				header('Content-Range: bytes */' . $size); // Required in 416.
				echo '<pre>File Too Big: Invalid File Length ('. $size .')</pre>';
				exit;
			}

			// multipart-download and download resuming support
			if(isset($_SERVER['HTTP_RANGE']))
			{
				list($a, $range) = explode("=",$_SERVER['HTTP_RANGE'],2);
				list($range) = explode(",",$range,2);
				list($range, $range_end) = explode("-", $range);
				$range=intval($range);
				if(!$range_end)
				{
					$range_end=$size-1;
				}
				else {
					$range_end=intval($range_end);
				}
				$new_length = $range_end-$range+1;
				$aHeader[] = "HTTP/1.1 206 Partial Content";
				$aHeader[] = "Content-Length: $new_length";
				$aHeader[] = "Content-Range: bytes $range-$range_end/$size";
			}
			else {
				$new_length=$size;
				$aHeader[] = "Content-Length: ".$size;
			}

			// Execute Actions related to this event... Allow for Header Changes!
			do_action('wishlistmember_download_folder_action', $file, array('header'=>&$aHeader,'forcedownload'=>$forceDownload,'partial'=>$partial,'this'=>$this,'debug'=>false));

			// echo the header()
			// Publish all the Header details...
			// NOTE: MUST be done before ob_clean & flush!
			foreach ($aHeader as $header) {
				header( $header );
			}


			@ob_clean();
			@flush();


			/* output the file itself */
			$chunksize = $new_length;
			$max_chunk_size = 10 * ( 1024 * 1024 );  // 1 MB chunksize
			if ( $new_length >  $max_chunk_size  ) {
				$chunksize = $max_chunk_size;
			}

			$bytes_send = 0;
			if ($file = fopen($file, 'r'))
			{
				if(isset($_SERVER['HTTP_RANGE']))
					fseek($file, $range);
				while(!feof($file) &&
				(!connection_aborted()) &&
				($bytes_send<$new_length)
					)
				{
					$buffer = fread($file, $chunksize);
					print($buffer); //echo($buffer); // is also possible
					flush();
					$bytes_send += strlen($buffer);
				}
				fclose($file);
			}
			else die('Error - can not open file.');

		}

		/**
		 * Adds/Removes htaccess code to the protected upload folders
		 * @param boolean $remove (optional) Default False.  True removes the code
		 */
		function FolderProtectHtaccess($folderFullPath, $install = true) {
			
			if ($this->GetOption('rootOfFolders')==''){
				return false;				
			}
			$folder = basename($folderFullPath);
			
			if(!$this->GetOption('folder_protection')) {
				$install = false;
			}

 			if (is_dir($folderFullPath)) {

				$htaccess_start = '# BEGIN WishList Member Folder Protection';
				$htaccess_end = '# END WishList Member Folder Protection';
				$htaccess_file = $folderFullPath . '/.htaccess';

				// read .htaccess
				
				$htaccess = file_exists($htaccess_file) ? file_get_contents($htaccess_file) : '';
				
				// remove our .htaccess code
				list($start) = explode($htaccess_start, $htaccess);
				list($x, $end) = explode($htaccess_end, $htaccess);
				$htaccess = trim($start) . "\n" . trim($end);

				if ($install) {
					/*
					 * prepare ignore list
					 */
					$ignorelist = trim($this->GetOption('file_protection_ignore'));
					if (empty($ignorelist)){
						$ignorelist = 'jpg,jpeg,png,gif,bmp';
					}
					$ignorelist = explode(',', $ignorelist);
					foreach ($ignorelist AS $i => $ext) {
						$ext = preg_replace('/[^A-Za-z0-9]/', '', trim($ext));
						$ignorelist[$i] = $ext;
					}
					$this->SaveOption('file_protection_ignore', implode(', ', $ignorelist));
					$ignorelist = implode('|', $ignorelist);

					/*
					 * prepare htaccess code
					 */
					$siteurl = parse_url(get_option('home'));
					$siteurl = $siteurl['path'] . '/index.php';
					$htaccess.="\n{$htaccess_start}";
					$htaccess.="\nOptions FollowSymLinks";
					$htaccess.="\nRewriteEngine on";
					$htaccess.="\n#RewriteCond %{REQUEST_URI}  ^{$folder}/*";
					$htaccess.="\nRewriteRule ^(.*)$ {$siteurl}?wlmfolder={$folder}&restoffolder=$1 [L]";
					$htaccess.="\n{$htaccess_end}";
				}
				
				// write .htaccess
				$f = fopen($htaccess_file, 'w');
				fwrite($f, trim($htaccess));
				fclose($f);
			}
		}

		function stringToSlug($string) {

			//$slug = str_replace('-', ' ', $string);
			$slug = sanitize_title_with_dashes($string);
			return $slug;
		}

		/**
		 * Setup Easy Folder Protection
		 */
		function EasyFolderProtection() {
			global $wpdb;
			
			//reset
			$wpdb->query("DELETE FROM `{$this->Tables->contentlevels}` WHERE `type`='~FOLDER'");

			//some clean up
			$default_parent_folder_name = 'files';

			$rootOfFolders = ABSPATH . '/' . $default_parent_folder_name;
			$this->SaveOption('rootOfFolders', $rootOfFolders);

			if (!is_dir($rootOfFolders)) {
				// if folder does not exist, we create it
				if (!mkdir($rootOfFolders)) {
					$_POST['err']=__('<b>Could not create folder.</b><br>', 'wishlist-member');
					return false;
				}
			}
			
			$this->SaveOption('parentFolder', $default_parent_folder_name);

			$wpm_levels = $this->GetOption('wpm_levels');

			foreach ((array) $wpm_levels AS $level_id => $level) {
				$levelName = $level["name"];
				$subfolder = $rootOfFolders . "/" . $this->stringToSlug($levelName);
				if (!is_dir($subfolder)) {
					mkdir($subfolder);
				}
				$folder_id = $this->FolderID($subfolder);
				$this->FolderProtected($folder_id, true);
				$this->SetContentLevels('folders', $folder_id, $level_id);
			}

			$this->RemoveAllHtaccessFromProtectedFolders();
			$this->AddHtaccessToProtectedFolders();
			$this->SaveOption('folder_protection', 1);

			$_POST['msg'] = sprintf('Folder protection successfully auto-configured at <b>%s</b>', $rootOfFolders);
			return true;
		}

		/**
		 * FolderProtectionParentFolder
		 */
		function FolderProtectionParentFolder() {

			$parentFolder = $_POST['parentFolder'];

			if (
				$parentFolder == '' ||
				$parentFolder == 'wp-content' ||
				$parentFolder == 'wp-includes' ||
				$parentFolder == 'wp-admin' ||
				$parentFolder == 'uploads' ||
				$parentFolder == 'themes' ||
				$parentFolder == 'plugins'
			) {
				$_POST['err'] = __('Parent Folder can not be one of WordPress default folders such as wp-content, wp-includes, wp-admin, uploads, themes or plugins folder.<br /><br />Try to create a folder inside your WordPress instalation path and set it as Parent Folder.', 'wishlist-member');
				return false;
			}

			$rootOfFolders = addslashes(ABSPATH . $parentFolder);
			
			if(!is_dir($rootOfFolders)) {
				$_POST['err'] = __('Folder not found. Please create it first.', 'wishlist-member');
				return false;
			}
			
			$this->RemoveAllHtaccessFromProtectedFolders();
			
			$this->SaveOption('parentFolder', $parentFolder);
			$this->SaveOption('rootOfFolders', $rootOfFolders);
			
			$this->AddHtaccessToProtectedFolders();

			$_POST['msg'] = __('<b>Parent Folder Updated.</b><br>', 'wishlist-member');
			return true;
		}

		function FolderProtectionMigrate() {
			$needMigrate = $this->GetOption($this->PluginOptionName . '_MigrateFolderProtectionData');

			if ($needMigrate != 1) {
				$parentFolder = $this->folder_protection_relative_path($this->GetOption('rootOfFolders'));
				$this->SaveOption('parentFolder', $parentFolder);
				$this->SaveOption($this->PluginOptionName . '_MigrateFolderProtectionData', '1');
			}
		}

		// end folder protection method

		/**
		 * Get user information using WP_User then patching it with WishList Member user info
		 * @global object $wpdb
		 * @param int|string $id User ID or Username
		 * @param string $login Optional. Username
		 * @return WP_User or false on error
		 */
		function Get_UserData($id, $login = '') {
			global $wpdb;
			if (!function_exists('get_userdata')) {
				require_once(ABSPATH . WPINC . '/pluggable.php');
			}

			if (!empty($id) && !is_numeric($id)) {
				$login = $id;
				$id = 0;
			}

			if ($id) {
				$user = get_user_by('id', $id);
			} else {
				$user = get_user_by('login', $login);

				if(!$user)
					$user = get_user_by('email', $login);
			}

			if (empty($user->ID) || !$user || is_wp_error($user))
				return false;

			if (is_object($user)) {

				if (!is_null($GLOBALS['wp_rewrite'])) {
					// This produces a feedurl with the wpmfeedkey already added, commenting this out for now
					//$user->wlm_feed_url = get_bloginfo('rss2_url');
					
					// Manually build the feed url for now.
					$user->wlm_feed_url = get_bloginfo('url').'/feed/';
				}
				if (!strpos($user->wlm_feed_url, 'wpmfeedkey=')) {
					$user->wlm_feed_url = $this->FeedLink($user->wlm_feed_url, $this->FeedKey($user->ID, true));
				}

				$query = $wpdb->prepare("SELECT `option_name`,`option_value` FROM `{$this->Tables->user_options}` WHERE `user_id`=%d", $user->ID);
				$results = $wpdb->get_results($query);
				if ($results) {
					foreach ($results AS $result) {
						$value = maybe_unserialize($result->option_value);
						$key = str_replace('-', '', $result->option_name);
						$user->data->{$key} = $value;
						$user->{$key} = $value;
					}
				}
				return $user;
			}
			return false;
		}

		/**
		 * Retrieves all user saved searches
		 *
		 * @global object $wpdb
		 * @return array
		 */
		function GetAllSavedSearch() {
			global $wpdb;
			$results = $wpdb->get_results("SELECT `option_name`,`option_value` FROM `{$this->Tables->options}` WHERE `option_name` LIKE 'SaveSearch%'");
			$option_values = array();
			if ($results) {
				foreach ($results AS $result) {
					$value['name'] = $result->option_name;
					$value['value'] = maybe_unserialize($result->option_value);
					array_push($option_values, $value);
				}
			}
			return $option_values;
		}

		/**
		 * Retrieve Existing Saved Search
		 *
		 * @global object $wpdb
		 * @param type $name
		 * @return array
		 */
		function GetSavedSearch($name) {
			global $wpdb;
			$option_values = array();
			if ($name) {
				$results = $wpdb->get_results("SELECT `option_name`,`option_value` FROM `{$this->Tables->options}` WHERE `option_name` = '{$name}'");
			}
			if ($results) {
				$value = maybe_unserialize($results[0]->option_value);
				$value['name'] = $results[0]->option_name;
				array_push($option_values, $value);
			}
			return $option_values;
		}

		function GetContinueRegistrationURLFromShort($short, $clean = true) {
			global $wpdb;
			$results = $wpdb->get_results("SELECT `option_name`,`option_value` FROM `{$this->Tables->options}` WHERE `option_value` like '{$short}||%'");
			if (empty($results)) {
				return false;
			} else {
				$value = $results[0]->option_value;
				$longurl = explode("||", $value, 3);
				return $longurl[1];
			}
		}

		/**
		 * Generate and return the Continue Registration URL for incomplete / temp accounts
		 * @param string $email
		 * @return string URL
		 */
		function GetContinueRegistrationURL($email) {
			$longurl = '/continue&e=' . urlencode($email) . '&h=' . urlencode(md5($email . '__' . $this->GetAPIKey()));
			if ($this->GetOption('enable_short_registration_links') != 1) {
				return WLMREGISTERURL . $longurl;
			}

			$shorturl = base_convert(microtime(), 10, 35);
			$key = sprintf("tinylink_%s", sha1($longurl));
			$value = $shorturl . '||' . $longurl . '||' . $email;

			if (!$this->GetContinueRegistrationURLFromShort($short, false)) {
				$this->SaveOption($key, $value);
			}
			return WLMREGISTERURL . '/continue&to=' . $shorturl;
		}

		function GetFallbackRegistrationURL() {
			$time = time();
			return WLMREGISTERURL . '/fallback&h=' . md5($_SERVER['REMOTE_ADDR'] . '__' . $time . '__' . $this->GetAPIKey()) . '/' . $time;
		}

		/**
		 * Deletes an entire directory tree
		 * @param string $dir Folder Name
		 */
		function Recursive_Delete($dir) {
			if (substr($dir, -1) != '/')
				$dir.='/';
			$files = glob($dir . '*', GLOB_MARK);
			foreach ($files AS $file) {
				if (is_dir($file)) {
					$this->Recursive_Delete($file);
					@rmdir($file);
				} else {
					@unlink($file);
				}
			}
			@rmdir($dir);
		}

		function Recursive_Copy($source, $dest) {
			if (substr($source, -1) != '/')
				$source.='/';
			$files = glob($source . '*', GLOB_MARK);
			if (!file_exists($dest) || !is_dir($dest)) {
				mkdir($dest, 0777, true);
			}
			foreach ($files AS $file) {
				if (is_dir($file)) {
					$this->Recursive_Copy($file, $dest . '/' . basename($file));
				} else {
					copy($file, $dest . '/' . basename($file));
				}
			}
		}

		function RequestURL() {
			list($wpm_request_url) = explode('/', strtolower($_SERVER['SERVER_PROTOCOL']), 2);
			$wpm_request_url.='://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
			if ($_SERVER['QUERY_STRING'])
				$wpm_request_url.='?' . $_SERVER['QUERY_STRING'];
			return $wpm_request_url;
		}

		/**
		 *
		 * @param String $template_name the name of the template
		 * @param Array $postarr additional post/page variables
		 * @param String $ext the extension of the template
		 */
		function CreatePostFromTemplate($template_name, $postarr = array(), $ext = 'php') {
			$template_name = sprintf("%s/resources/page_templates/%s.%s", $this->pluginDir, $template_name, $ext);
			include $template_name;
			$post['post_title'] = $title;
			$post['post_content'] = $content;
			$post = array_merge($post, $postarr);
			$id = wp_insert_post($post, true);
			return $id;
		}

		/**
		 * Attempt to fix user address data for each user
		 */
		function FixUserAddress() {
			global $wpdb;
			@ini_set('memory_limit', '256M');

			if (get_transient('wlm_fixing_user_address') == 1 || $this->GetOption('FixedUserAddress') == 1) {
				return;
			}
			set_transient('wlm_fixing_user_address', 1, 86400);
			$query = "SELECT `t1`.`user_id`,`t1`.`option_value` FROM `wp_wlm_user_options` AS `t1` LEFT JOIN `wp_wlm_user_options` AS `t2` ON `t1`.`user_id`=`t2`.`user_id` AND `t2`.`option_name`='wpm_useraddress' WHERE `t1`.`option_name`='wlm_reg_post' AND (`t2`.`option_value` IS NULL OR TRIM(`t2`.`option_value`) IN ('','a:0:{}'))";
			$count = $wpdb->query($query);
			$perpage = 10000;
			$pages = ceil($count / $perpage);
			$limits = array();
			for ($i = 0; $i < $pages; $i++) {
				$limits[] = $i * $perpage . ',' . $perpage;
			}
			foreach ($limits AS $limit) {
				$q = $query . ' LIMIT ' . $limit;
				$results = $wpdb->get_results($q, ARRAY_N);
				while ($result = array_shift($results)) {
					list($uid, $post) = $result;
					$post = $this->WLMDecrypt($post);
					$address = array();
					if (isset($post['status']) && isset($post['sku1'])) {
						// 1shoppingcart
						$address['company'] = $post['shipCompany'];
						$address['address1'] = $post['shipAddress1'];
						$address['address2'] = $post['shipAddress2'];
						$address['city'] = $post['shipCity'];
						$address['state'] = $post['shipState'];
						$address['zip'] = $post['shipZip'];
						$address['country'] = $post['shipCountry'];
					} elseif (isset($post['product_sku']) && isset($post['customer_email'])) {
						// premium web cart
						$address['company'] = $post['shipping_company_name'];
						$address['address1'] = $post['billing_address_line1'];
						$address['address2'] = $post['billing_address_line2'];
						$address['city'] = $post['billing_customer_city'];
						$address['state'] = $post['billing_customer_state'];
						$address['zip'] = $post['billing_customer_zip'];
						$address['country'] = $post['billing_customer_country'];
						$address['phone'] = $post['phone'];
						$address['fax'] = $post['fax'];
					} elseif (isset($post['item_number']) && isset($post['payer_email'])) {
						// paypal
						$address['company'] = $post['payer_business_name'] ? $post['payer_business_name'] : $post['address_name'];
						$address['address1'] = $post['address_street'];
						$address['address2'] = '';
						$address['city'] = $post['address_city'];
						$address['state'] = $post['address_state'];
						$address['zip'] = $post['address_zip'];
						$address['country'] = $post['address_country'];
					} elseif (isset($post['level']) && isset($post['cmd']) && isset($post['hash'])) {
						// generic integration or generic integration plr
						$address['company'] = $post['company'];
						$address['address1'] = $post['address1'];
						$address['address2'] = $post['address2'];
						$address['city'] = $post['city'];
						$address['state'] = $post['state'];
						$address['zip'] = $post['zip'];
						$address['country'] = $post['country'];
						$address['phone'] = $post['phone'];
						$address['fax'] = $post['fax'];
					}
					if (trim(implode('', $address)) != '') {
						$this->Update_UserMeta($uid, 'wpm_useraddress', $address);
					}
				}
			}
			$this->SaveOption('FixedUserAddress', 1);
			delete_transient('wlm_fixing_user_address');
		}

		/**
		 * Sanitizes a string by replacing whitespace with hyphens,
		 * and removing characters that are not from A-Z, a-z, 0-9, _ and -
		 *
		 * Also replaces duplicate hyphens with just a single hyphen
		 *
		 * @param string $string String to sanitize
		 * @param bool $toLowerCase (optional) TRUE to transfrom string to lowercase
		 * @return string
		 */
		function SanitizeString($string, $toLowerCase = true) {
			if (is_null($regex)) {
				$regex = '/[^A-Za-z0-9_-]/';
			}
			$string = preg_replace('/\s/', '-', $string);

			$string = preg_replace($regex, '', $string);
			if ($toLowerCase) {
				$string = strtolower($string);
			}
			$string = preg_replace('/-+/', '-', $string);
			return $string;
		}

		/**
		 * Retrieves all Custom Registration Forms from Database
		 */
		function GetCustomRegForms() {
			global $wpdb;
			$forms = $wpdb->get_results("SELECT * FROM `{$this->Tables->options}` WHERE `option_name` LIKE 'CUSTOMREGFORM-%' ORDER BY `option_name` ASC");
			$form_sort = array();
			foreach ($forms AS $i => $form) {
				$form = maybe_unserialize($form->option_value);
				$forms[$i]->option_value = $form;
				$form_sort[$i] = $form['form_name'];
			}
			$form_sort2 = $form_sort;
			array_multisort($form_sort, SORT_ASC, $form_sort2, SORT_DESC, $forms);
			return $forms;
		}

		/**
		 * Retrieve legacy registration form
		 * @param string $form_id
		 * @param string (optional) $before_submit HTML to insert before submit button
		 * @param boolean $disableprefilled determines whether we disable the firstname, lastname and email text box
		 * @return string Form HTML
		 */
		function get_legacy_registration_form($form_id, $captcha_code = "", $foredit = false, $disableprefilled = false) {
			$form_id = 'CUSTOMREGFORM-' . substr($form_id, 14);
			$form_data = $this->GetOption($form_id);
			$captcha_code = trim($captcha_code);


			$disable_prefilled_js = <<<STRING
			<script type="text/javascript">
				jQuery(document).ready(function() {
				
					if(jQuery('input[name="mergewith"]').val() == null) {
						return;
					} 
					
					if (jQuery('input[name="orig_firstname"]').val() != "") {
					  jQuery('input[name="firstname"]').attr("readonly", "readonly").css("opacity", 0.6).css("background-color", "#F3F3EF");
					}

					if (jQuery('input[name="orig_lastname"]').val() != "") {
					 jQuery('input[name="lastname"]').attr("readonly", "readonly").css("opacity", 0.6).css("background-color", "#F3F3EF");
					}

					if (jQuery('input[name="orig_email"]').val() != "") {
					 jQuery('input[name="email"]').attr("readonly", "readonly").css("opacity", 0.6).css("background-color", "#F3F3EF");
					}
			   });
			</script>
STRING;
			$disable_prefill_info = ($disableprefilled) ? $disable_prefilled_js : '';

			if ($captcha_code) {
				$captcha_code = <<<STRING
					<tr class="li_fld captcha_html">
						<td class="label">&nbsp;</td>
						<td class="fld_div">{$captcha_code}</td>
					</tr>
STRING;
			}

			if (!$form_data['form']) {

				$txt_username = __('Username', 'wishlist-member');
				$txt_firstname = __('First Name', 'wishlist-member');
				$txt_lastname = __('Last Name', 'wishlist-member');
				$txt_email = __('Email', 'wishlist-member');
				$txt_password = __('Password (twice)', 'wishlist-member');
				$txt_password_desc = __('Enter your desired password twice. Must be at least [wlm_min_passlength] characters long.', 'wishlist-member');
				$txt_password_hint_label = __('Password Hint', 'wishlist-member');
				$txt_password_hint_desc = __('Enter a password hint that will remind you of your password in case you forget it.', 'wishlist-member');
				$txt_submit = __('Submit Registration', 'wishlist-member');

				$password_hint = <<<STRING
							<tr class="li_fld systemFld">
								<td class="label">{$txt_password_hint_label}:</td>
								<td class="fld_div">
									<input type="text" class="fld" name="passwordhint" size="12" />
									<div class="desc">
										{$txt_password_hint_desc}
									</div>
								</td>
							</tr>
STRING;

				$password_hint = $this->GetOption('password_hinting') ? $password_hint : '';

				$form = <<<STRING
				$disable_prefill_info
					<table class="wpm_regform_table wpm_registration" cellpadding="0" cellspacing="0">

						<tr class="li_fld systemFld">
							<td class="label">{$txt_username}:</td>
							<td class="fld_div">
								<input type="text" class="fld" name="username" size="25" value="" />
								<div class="desc"></div>
							</td>
						</tr>

						<tr class="li_fld required wp_field">
							<td class="label">{$txt_firstname}:</td>
							<td class="fld_div">
								<input type="text" class="fld" name="firstname" size="25" value="" />
								<div class="desc"></div>
							</td>
						</tr>

						<tr class="li_fld required wp_field">
							<td class="label">{$txt_lastname}:</td>
							<td class="fld_div">
								<input type="text" class="fld" name="lastname" size="25" value="" />
								<div class="desc"></div>
							</td>
						</tr>

						<tr class="li_fld systemFld">
							<td class="label">{$txt_email}:</td>
							<td class="fld_div">
								<input type="email" class="fld" name="email" size="25" value="" />
								<div class="desc"></div>
							</td>
						</tr>

						<tr class="li_fld systemFld">
							<td class="label">{$txt_password}:</td>
							<td class="fld_div">
								<input type="password" class="fld" name="password1" size="25" />
								<br />
								<input type="password" class="fld" name="password2" size="25" />
								<div class="desc">
									{$txt_password_desc}
								</div>
							</td>
						</tr>
						{$password_hint}
						{$captcha_code}
						<tr class="li_submit">
							<td class="label">&nbsp;</td>
							<td class="fld_div form_button">
								<input type="submit" class="fld button" value="{$txt_submit}" />
							</td>
						</tr>

					</table>
STRING;
			} else {

				//extract this so we can get the value of $fields and $required
				extract((array) $this->GetOption($form_id));
				$before_submit = $captcha_code;
				$form = $form_data['form'];
				if (!$foredit) {
					$form .= $disable_prefill_info;
					$form .= '<input type="hidden" name="custom_fields" value="' . $fields . '" />';
					$form .= '<input type="hidden" name="required_fields" value="' . $required . '" />';
				}
			}

			$form = str_replace('<tr class="li_submit">', $before_submit . '<tr class="li_submit">', $form);
			return str_replace(array("\n", "\r", "\t"), '', $form);
		}

		/**
		 * Retrieve improved registration form
		 * @param string $form_id
		 * @param string (optional) $before_submit HTML to insert before submit button
		 * @param boolean $disableprefilled determines whether we disable the firstname, lastname and email text box
		 * @return string Form HTML
		 */
		function get_improved_registration_form($form_id, $captcha_code = "", $foredit = false, $disableprefilled = false) {

			$form_id = 'CUSTOMREGFORM-' . substr($form_id, 14);
			$form_data = $this->GetOption($form_id);
			$captcha_code = trim($captcha_code);

			$disable_prefilled_js = <<<STRING
			<script type="text/javascript">
				jQuery(document).ready(function() {
				
					if(jQuery('input[name="mergewith"]').val() == null) {
						return;
					} 

					if (jQuery('input[name="orig_firstname"]').val() != "") {
					  jQuery('input[name="firstname"]').attr("readonly", "readonly").css("opacity", 0.6).css("background-color", "#F3F3EF");
					}

					if (jQuery('input[name="orig_lastname"]').val() != "") {
					 jQuery('input[name="lastname"]').attr("readonly", "readonly").css("opacity", 0.6).css("background-color", "#F3F3EF");
					}

					if (jQuery('input[name="orig_email"]').val() != "") {
					 jQuery('input[name="email"]').attr("readonly", "readonly").css("opacity", 0.6).css("background-color", "#F3F3EF");
					}
			   });
			</script>
STRING;
			$disable_prefill_info = ($disableprefilled) ? $disable_prefilled_js : '';

			if ($captcha_code) {
				$captcha_code = <<<STRING
					<div class="wlm_form_group captcha_html">
						{$captcha_code}
					</div>
STRING;
			}

			if (!$form_data['form']) {

				$txt_username = __('Username', 'wishlist-member');
				$txt_firstname = __('First Name', 'wishlist-member');
				$txt_lastname = __('Last Name', 'wishlist-member');
				$txt_email = __('Email', 'wishlist-member');
				$txt_password = __('Password (twice)', 'wishlist-member');
				$txt_password_desc = __('Enter your desired password twice. Must be at least [wlm_min_passlength] characters long.', 'wishlist-member');
				$txt_password_hint_label = __('Password Hint', 'wishlist-member');
				$txt_password_hint_desc = __('Enter a password hint that will remind you of your password in case you forget it.', 'wishlist-member');
				$txt_submit = __('Submit Registration', 'wishlist-member');

				$password_hint = <<<STRING
						<div class="wlm_form_group wlm_required_field">
							<label for="wlm_password_field1" class="wlm_form_label" id="wlm_password_label">
								<span class="wlm_label_text" id="wlm_password_text">{$txt_password_hint_label}:</span>
							</label>
							<input type="text" class="fld wlm_input_text" id="wlm_passwordhint" name="passwordhint">
							<p class="wlm_field_description">{$txt_password_hint_desc}</p>
						</div>
STRING;

				$password_hint = $this->GetOption('password_hinting') ? $password_hint : '';

				$form = <<<STRING
					$disable_prefill_info
					<div class="wlm_regform_div wlm_registration wlm_regform_2col">
						<div class="wlm_form_group wlm_required_field">
							<label for="wlm_firstname_field" class="wlm_form_label" id="wlm_firstname_label">
								<span class="wlm_label_text" id="wlm_firstname_text">{$txt_firstname}:</span>
							</label>
							<input class="fld wlm_input_text" id="wlm_firstname_field" name="firstname" type="text">
							<p class="wlm_field_description"></p>
						</div>
						<div class="wlm_form_group wlm_required_field">
							<label for="wlm_lastname_field" class="wlm_form_label" id="wlm_lastname_label">
								<span class="wlm_label_text" id="wlm_lastname_text">{$txt_lastname}:</span>
							</label>
							<input class="fld wlm_input_text" id="wlm_lastname_field" name="lastname" type="text">
							<p class="wlm_field_description"></p>
						</div>
						<div class="wlm_form_group wlm_required_field">
							<label for="wlm_email_field" class="wlm_form_label" id="wlm_email_label">
								<span class="wlm_label_text" id="wlm_email_text">{$txt_email}:</span>
							</label>
							<input class="fld wlm_input_text" id="wlm_email_field" name="email" type="email">
							<p class="wlm_field_description"></p>
						</div>
						<div class="wlm_form_group wlm_required_field">
							<label for="wlm_username_field" class="wlm_form_label" id="wlm_username_label">
								<span class="wlm_label_text" id="wlm_username_text">{$txt_username}:</span>
							</label>
							<input class="fld wlm_input_text" id="wlm_username_field" name="username" type="text">
							<p class="wlm_field_description"></p>
						</div>
						<div class="wlm_form_group wlm_required_field">
							<label for="wlm_password_field1" class="wlm_form_label" id="wlm_password_label">
								<span class="wlm_label_text" id="wlm_password_text">{$txt_password}:</span>
							</label>
							<input class="fld wlm_input_text" id="wlm_password_field1" name="password1" type="password">
							<input class="fld wlm_input_text wlm_password_field2" id="wlm_password_field2" name="password2" type="password">
							<p class="wlm_field_description">{$txt_password_desc}</p>
						</div>
						{$password_hint}
						{$captcha_code}
						<p class="submit">
							<input class="submit" id="wlm_submit_button" type="submit" value="{$txt_submit}" />
						</p>
					</div>
STRING;
			} else {
				$form = $form_data['form'];
				if (!$foredit) {
					if (!$form_data['form_dissected']) {
						$form_data['form_dissected'] = wlm_dissect_custom_registration_form($form_data);
						$this->SaveOption($form_id, $form_data);
					}
					$dissected = $form_data['form_dissected'];

					if( !is_array( $dissected['fields'] ) )
						return $form;

					$hiddens = '';
					$form = '<div class="wlm_regform_div wlm_registration wlm_regform_2col">';
					foreach ($dissected['fields'] AS $entry) {
						$required = $entry['required'] ? ' wlm_required_field' : '';
						$attributes = '';

						if( is_array( $entry['attributes'] ) ) {
							foreach ($entry['attributes'] AS $key => $val) {
								$attributes.= ' ' . $key . '="' . $val . '"';
							}
						}
						
						switch ($entry['type']) {
							case 'input':
								if ($entry['system_field'] == 1 && $entry['attributes']['type'] == 'password') {
									$form .= <<<STRING
						<div class="wlm_form_group wlm_required_field">
							<label for="wlm_password_field1" class="wlm_form_label" id="wlm_password_label">
								<span class="wlm_label_text" id="wlm_password_text">{$entry[label]}</span>
							</label>
							<input class="fld wlm_input_text" id="wlm_password_field1" name="password1" type="password">
							<input class="fld wlm_input_text wlm_password_field2" id="wlm_password_field2" name="password2" type="password">
							<p class="wlm_field_description">{$entry[description]}</p>
						</div>
STRING;
								} else {
									$form .= <<<STRING
						<div class="wlm_form_group {$required}">
							<label for="wlm_{$entry[attributes][name]}_field" class="wlm_form_label" id="wlm_{$entry[attributes][name]}_label">
								<span class="wlm_label_text" id="wlm_{$entry[attributes][name]}_text">{$entry[label]}</span>
							</label>
							<input class="fld wlm_input_text" id="wlm_{$entry[attributes][name]}_field" {$attributes}>
							<p class="wlm_field_description">{$entry[description]}</p>
						</div>
STRING;
								}
								break;
							case 'textarea':
								$value = $entry['attributes']['value'];
								$form .= <<<STRING
						<div class="wlm_form_group {$required}">
							<label for="wlm_{$entry[attributes][name]}_field" class="wlm_form_label" id="wlm_{$entry[attributes][name]}_label">
								<span class="wlm_label_text" id="wlm_{$entry[attributes][name]}_text">{$entry[label]}</span>
							</label>
							<textarea class="fld wlm_input_text" id="wlm_{$entry[attributes][name]}_field" {$attributes}>{$entry[attributes][value]}</textarea>
							<p class="wlm_field_description">{$entry[description]}</p>
						</div>
STRING;
								break;
							case 'paragraph':
								$form .= <<<STRING
						<div class="wlm_form_group wlm_form_paragraph">
							{$entry[text]}
						</div>
STRING;
								break;
							case 'header':
								$form .= <<<STRING
						<div class="wlm_form_group wlm_form_section_header">
							{$entry[text]}
						</div>
STRING;
								break;
							case 'select':
								$options = '';
								foreach ($entry['options'] AS $option) {
									$options .= sprintf('<option value="%s"%s>%s</option>', $option['value'], $option['selected'] ? ' selected="selected"' : '', $option['text']);
								}
								$form .= <<<STRING
						<div class="wlm_form_group {$required}">
							<label for="wlm_{$entry[attributes][name]}_field" class="wlm_form_label" id="wlm_{$entry[attributes][name]}_label">
								<span class="wlm_label_text" id="wlm_{$entry[attributes][name]}_text">{$entry[label]}</span>
							</label>
							<select class="fld" {$attributes}>{$options}</select>
							<p class="wlm_field_description">{$entry[description]}</p>
						</div>
STRING;
								break;
							case 'checkbox':
							case 'radio':
								$options = '';
								foreach ($entry['options'] AS $option) {
									$options .= sprintf('<label><input %s value="%s"%s>%s</label>', $attributes, $option['value'], $option['checked'] ? ' checked="v"' : '', $option['text']);
								}
								$form .= <<<STRING
						<div class="wlm_form_group {$required}">
							<label for="wlm_{$entry[attributes][name]}_field" class="wlm_form_label" id="wlm_{$entry[attributes][name]}_label">
								<span class="wlm_label_text" id="wlm_{$entry[attributes][name]}_text">{$entry[label]}</span>
							</label>
							<div class="wlm_option_group">{$options}</div>
							<p class="wlm_field_description">{$entry[description]}</p>
						</div>
STRING;
								break;
							case 'hidden':
								$hiddens.='<input ' . $attributes . '>';
								break;
							case 'tos':
								$x = print_r($entry, true);
								$lightbox = $entry['lightbox'] ? ' wlm_tos_lightbox' : '';
								$tos = $entry['description'];

								$text = $entry['text'];
								if ($lightbox) {
									$text = '<a href="/#TB_inline?inlineId=tos_data_terms_of_service" class="thickbox">' . $text . '</a>';
								}
								$form .= <<<STRING
						<div class="wlm_form_group {$required} wlm_form_tos">
							<label class="wlm_form_label"></label>
							<div class="wlm_option_group">
								<label><input{$attributes}> {$text}</label>
							</div>
							<div class="wlm_field_tos_content {$lightbox}" id="tos_data_terms_of_service">{$tos}</div>
						</div>
STRING;
								break;
						}
					}
					$form .= <<<STRING
						{$captcha_code}
						<p class="submit">
							<input class="submit" id="wlm_submit_button" type="submit" value="{$dissected[submit]}" />
						</p>
STRING;
					$form .= $hiddens;
					$form .= '<input type="hidden" name="custom_fields" value="' . $form_data['fields'] . '" />';
					$form .= '<input type="hidden" name="required_fields" value="' . $form_data['required'] . '" />';
					$form .= '</div>';
					$form .= $disable_prefill_info;
				}
			}

			return str_replace(array("\n", "\r", "\t"), '', $form);
		}

		/**
		 * Saves Custom Registration Form
		 */
		function SaveCustomRegForm($redirect = true) {
			$fname = $_POST['form_name'];
			$fields = $_POST['form_fields'];
			$required = $_POST['form_required'];
			$fid = substr($_POST['form_id'], 14);

			if (empty($fid)) {
				$fid = $this->SanitizeString(microtime());
			}
			$fdata = stripslashes(wlm_arrval($_POST, 'rfdata'));
			$fid = 'CUSTOMREGFORM-' . $fid;

			$data = array(
				'form_name' => $fname,
				'fields' => $fields,
				'required' => $required,
				'form' => $fdata
			);

			$data['form_dissected'] = wlm_dissect_custom_registration_form($data);

			$this->SaveOption($fid, $data);
			$query_string = $this->QueryString('form_id') . '&form_id=' . $fid . '&msg=' . __('<b>Custom Registration Form Saved.</b>', 'wishlist-member');
			if ($redirect === true) {
				header("Location:?{$query_string}");
				exit;
			}
		}

		/**
		 * Delete Custom Registration Form
		 * @param string $form_id Unique Form ID
		 */
		function DeleteCustomRegForm($form_id) {
			$form_id = 'CUSTOMREGFORM-' . $this->SanitizeString(substr($form_id, 14));
			$this->DeleteOption($form_id);
			$_POST['msg'] = __('Form deleted.', 'wishlist-member');
		}

		/**
		 * Clone an existing Custom Registration Form
		 * @param string $form_id Unique Form ID
		 */
		function CloneCustomRegForm($form_id) {
			$form_id = 'CUSTOMREGFORM-' . $this->SanitizeString(substr($form_id, 14));
			$form = $this->GetOption($form_id);
			if ($form) {
				$form['form_name'] = 'Copy of ' . $form['form_name'];
				$form_id = $this->SanitizeString('CUSTOMREGFORM-' . microtime(), false);
				$this->AddOption($form_id, $form);
				$_POST['msg'] = sprintf(__('Form <b>%1$s</b> cloned to <b>%2$s</b>.', 'wishlist-member'), $form['form_name'], 'Copy of ' . $form['form_name']);
			}
		}

		/**
		 * Get User Custom Fields
		 * @global object $wpdb
		 * @param integer $user_id User ID
		 * @return Associative Array
		 */
		function GetUserCustomFields($user_id) {
			global $wpdb;
			$query = $wpdb->prepare("SELECT * FROM `{$this->Tables->user_options}` WHERE `user_id`=%d AND `option_name` LIKE 'custom\_%%'", $user_id);
			$results = $wpdb->get_results($query);
			$output = array();
			if (!empty($results)) {
				foreach ($results AS $result) {
					$output[substr($result->option_name, 7)] = maybe_unserialize($result->option_value);
				}
			}
			return $output;
		}

		function GetCustomFieldsMergeCodes() {
			global $wpdb;
//			$query = $wpdb->prepare("SELECT DISTINCT CONCAT('[wlm_custom ', SUBSTRING(`option_name`,8),']') FROM `{$this->Tables->user_options}` WHERE `option_name` LIKE 'custom\_%%'", $user_id);
			$query = $query = "SELECT CONCAT('[wlm_custom ', SUBSTRING(`option_name`,8),']') FROM `{$this->Tables->user_options}` WHERE `option_name` LIKE 'custom\_%' GROUP BY `option_name`";
			return $wpdb->get_col($query);
		}

		/**
		 * Returns the number of user posts for the specified $user_id
		 * @param integer $user_id User ID
		 * @return integer
		 */
		function CountUserPosts($user_id) {
			global $wpdb;
			$query = $wpdb->prepare("SELECT COUNT(*) FROM `{$this->Tables->contentlevels}` WHERE `level_id`=%s", 'U-' . $user_id);
			return $wpdb->get_var($query);
		}

		/**
		 * Count how many users have access to a user post
		 * @global object $wpdb
		 * @param integer $post_id
		 * @param string $type post type
		 * @return integer
		 */
		function count_post_users( $post_id, $type = 'post' ) {
			global $wpdb;
			$query = $wpdb->prepare( "SELECT COUNT(*) FROM `{$this->Tables->contentlevels}` WHERE `content_id`=%d AND `type`=%s AND `level_id` LIKE 'U-%%'", $post_id, $type );
			return $wpdb->get_var( $query );
		}

		/**
		 * Add Transaction ID to user post
		 * @global object $wpdb
		 * @param int $userID
		 * @param int $contentID
		 * @param string $txnid
		 * @param bool $update (optiona) True to update if entry meta already exists. Default TRUE.
		 * @return bool
		 */
		function AddUserPostTransactionID($userID, $contentID, $txnid, $update = true) {
			global $wpdb;
			if (empty($txnid)) {
				$txnid = sprintf('WL-%d-C-%d', $userID, $contentID);
			}
			$levelID = 'U-' . $userID;
			if ($this->Add_ContentLevelMeta($levelID, $contentID, 'transaction_id', $txnid)) {
				return true;
			} else {
				if ($update) {
					if ($this->Update_ContentLevelMeta($levelID, $contentID, 'transaction_id', $txnid)) {
						return true;
					}
				}
			}
			return false;
		}

		/**
		 * Add Timestamp to user post
		 * @global object $wpdb
		 * @param int $userID
		 * @param int $contentID
		 * @param int $timestamp
		 * @param bool $update (optiona) True to update if entry meta already exists. Default TRUE.
		 * @return bool
		 */
		function AddUserPostTimestamp($userID, $contentID, $timestamp, $update = true) {
			global $wpdb;
			if (empty($timestamp)) {
				$timestamp = time();
			}
			$levelID = 'U-' . $userID;
			if ($this->Add_ContentLevelMeta($levelID, $contentID, 'registration_date', $timestamp)) {
				return true;
			} else {
				if ($update) {
					if ($this->Update_ContentLevelMeta($levelID, $contentID, 'registration_date', $timestamp)) {
						return true;
					}
				}
			}
			return false;
		}

		/**
		 * Add post to users
		 * @param string $ContentType
		 * @param int $id Content ID
		 * @param array $userLevels Array of user ids
		 */
		function AddPostUsers($ContentType, $id, $userLevels) {
			$userLevels = (array) $userLevels;
			foreach ($userLevels AS $level) {
				// just in case only the user ID was passed
				if (substr($level, 0, 2) != 'U-') {
					$level = 'U-' . $level;
				}

				$data = array(
					'ContentType' => $ContentType,
					'Level' => $level,
					'ID' => array($id => 0),
					'Checked' => array($id => 1)
				);
				$this->SaveMembershipContent($data);

				$this->AddUserPostTransactionID(substr($level, 2), $id, '');
				$this->AddUserPostTimestamp(substr($level, 2), $id, '');

				//run hook for adding content to user
				if($ContentType == "posts" || $ContentType == "pages"){
					do_action('wishlistmember_addpp_' . $ContentType . '_user', $id,$level);
				}
			}
		}

		/**
		 * Remove post from users
		 * @param string $ContentType
		 * @param int $id Content ID
		 * @param array $userLevels Array of user ids
		 */
		function RemovePostUsers($ContentType, $id, $userLevels) {
			$userLevels = (array) $userLevels;
			foreach ($userLevels AS $level) {
				if (substr($level, 0, 2) != 'U-') {
					$level = 'U-' . $level;
				}

				//run hook for removing content from user
				if($ContentType == "posts" || $ContentType == "pages"){
					do_action('wishlistmember_removepp_' . $ContentType . '_user', $id,$level);
				}

				$data = array(
					'ContentType' => $ContentType,
					'Level' => $level,
					'ID' => array($id => 0)
				);
				$this->SaveMembershipContent($data);
				$this->Delete_AllContentLevelMeta($level, $id);
			}
		}

		function GetPostUsers($ContentType, $post_id) {
			$post_users = preg_grep('/^U-\d+$/', $this->GetContentLevels($ContentType, $post_id));
			return $post_users;
		}

		/**
		 * Checks if a id is a valid For Approval Registration
		 * @param string $id Registration ID
		 * @return mixed FALSE on Error, Level ID on success
		 */
		function IsForApprovalRegistration($id) {
			$wpm_levels = $this->GetOption('wpm_levels');
			$for_approval_registration = $this->GetOption('wlm_for_approval_registration');
			if ($for_approval_registration) {
				$for_approval_registration = unserialize($for_approval_registration);
				if (array_key_exists($id, $for_approval_registration) && $wpm_levels[$for_approval_registration[$id]["level"]]) {
					return $for_approval_registration[$id];
				}
			}
			return false;
		}

		/**
		 * Checks if a level is a valid Pay Per Post Level
		 * @staticvar array $levels Results Cache
		 * @param string $level Level ID
		 * @return mixed FALSE on Error, Post Object on Success
		 */
		function IsPPPLevel($level) {
			static $levels;
			if (empty($levels)) {
				$levels = array();
			}
			if (isset($levels[$level])) {
				return $levels[$level];
			}
			$result = false;
			if (preg_match('/^payperpost-(\d+)$/', $level, $match)) {
				if ($this->PayPerPost($match[1])) {
					$post = get_post($match[1]);
					$result = $post;
				}
			}
			$levels[$level] = $result;
			return $result;
		}

		/**
		 * Sets/Gets Post Pay Per Post status
		 * @param int $id Post ID
		 * @param bool $status (optional) Pay Per Post status
		 * @return bool
		 */
		function PayPerPost($id, $status = null) {
			return $this->SpecialContentLevel($id, 'PayPerPost', $status);
		}

		/**
		 * Assigns Post to User
		 * @param int $id User ID
		 * @param array $posts Array of Special Pay Per Post Levels, each level is formatted as payperpost-xx where xx is the Post/Page ID
		 */
		function SetPayPerPost($id, $posts) {
			$posts = (array) $posts;
			foreach ($posts AS $post) {
				$post = $this->IsPPPLevel($post);
				if ($post) {
					$post_type = $post->post_type;
					$this->AddPostUsers($post_type, $post->ID, 'U-' . $id);
				}
			}
		}

		/**
		 * Retrieves all Pay Per Post enabled posts
		 * @global object $wpdb
		 * @param mixed $data (optional) False to return just IDs, True to return all information in wp_posts, Array of columns to return only the specified columns
		 * @return array
		 */
		function GetPayPerPosts($data = false, $group_by_post_type = true, $search = null, $search_limit = null, &$total_rows = null) {
			global $wpdb;
			if (is_null($search)) {
				$search = '%';
			}
			$search = esc_sql($search);

			$search_limit = trim($search_limit);
			if (!empty($search_limit) && (preg_match('/\d+/', trim($search_limit)) OR !preg_match('/\d+\s*,\s*\d+/', $search_limit))) {
				$search_limit = ' LIMIT ' . $search_limit . ' ';
			} else {
				$search_limit = '';
			}

			if ($data) {
				if ($data === true) {
					$data = '*';
				} else {
					$data[] = 'ID';
					$data['post_type'];
					$cols = array_keys($wpdb->get_row("SELECT * FROM `{$wpdb->posts}` LIMIT 1", ARRAY_A));
					$data = array_intersect(array_unique($data), $cols);
					$data = '`' . implode('`,`', $data) . '`';
				}
				$posts = $wpdb->get_results("SELECT SQL_CALC_FOUND_ROWS {$data} FROM `{$wpdb->posts}` WHERE `post_title` LIKE '{$search}' AND `ID` IN (SELECT DISTINCT `content_id` FROM `{$this->Tables->contentlevels}` WHERE `level_id`='PayPerPost') {$search_limit}");
				$total_rows = $wpdb->get_var("SELECT FOUND_ROWS()");

				if ($group_by_post_type) {
					$xposts = array(
						'post' => array(),
						'page' => array()
					);
					foreach ($posts as $post) {
						$xposts[$post->post_type][] = $post;
					}
					return $xposts;
				} else {
					return $posts;
				}
			} else {
				$posts = $wpdb->get_col("SELECT DISTINCT `content_id` FROM `{$this->Tables->contentlevels}` WHERE `level_id`='PayPerPost'");
			}
			return $posts;
		}

		/**
		 * Injects Pay Per Post settings to wpm_levels
		 * @param array $wpm_levels
		 * @param <type> $levelID
		 */
		function InjectPPPSettings(&$wpm_levels, $levelID = 'payperpost') {

			#make sure ppp_settings is an array or else array_merge will fail
			$ppp_settings = (array) $this->GetOption('payperpost');
			#inject instead of overriding, --erwin
			// cast $wpm_levels[$levelID] as array to make sure it's an array
			$wpm_levels[$levelID] = array_merge((array) $wpm_levels[$levelID], $ppp_settings);
		}

		/**
		 * Injects For Approval Registration settings to wpm_levels
		 * @param array $wpm_levels
		 * @param <type> $levelID
		 */
		function InjectForApprovalSettings(&$wpm_levels, $levelID) {

			$for_approval_registration = $this->GetOption('wlm_for_approval_registration');

			if (!$for_approval_registration)
				return false;

			$for_approval_registration = unserialize($for_approval_registration);
			if (!isset($for_approval_registration[$levelID]))
				return false;

			if ($for_approval_registration[$levelID]["name"] == "PinPayments") {
				$spreedlythankyou = $this->GetOption('spreedlythankyou');
				if ($spreedlythankyou) {
					$url = get_bloginfo('url') . '/index.php/register/' . $spreedlythankyou . "?reg_id=" . $levelID;
					$fapproval_settings = array("afterregredirect" => $url, "requireemailconfirmation" => 0);
					$wpm_levels[$levelID] = array_merge((array) $wpm_levels[$for_approval_registration[$levelID]["level"]], $fapproval_settings);
				}
			} else {
				$wpm_levels[$levelID] = (array) $wpm_levels[$for_approval_registration[$levelID]["level"]];
			}
		}

		/**
		 * Retrieve user posts based on transaction ID
		 * @global object $wpdb
		 * @param <type> $txnid
		 * @return <type>
		 */
		function GetUserPostsFromTxnID($txnid) {
			global $wpdb;
			$query = $wpdb->prepare("SELECT contentlevel_id FROM `{$this->Tables->contentlevel_options}` WHERE `option_name`='transaction_id' AND `option_value`=%s", $txnid);
			$contentlevel_id = $wpdb->get_var($query);
			if ($contentlevel_id) {
				$query = $wpdb->prepare("SELECT `content_id`,`level_id`,`type` FROM `{$this->Tables->contentlevels}` WHERE `ID`=%d", $contentlevel_id);
				return $wpdb->get_results($query);
			}
			return false;
		}

		/**
		 * Sets and gets whether a payperpost is allowed for free registration
		 * @param <type> $id
		 * @param <type> $status
		 * @return <type>
		 */
		function Free_PayPerPost($id, $status = null) {
			return $this->SpecialContentLevel($id, 'Free_PayPerPost', $status);
		}

		/**
		 * Checks whether a custom type is configured to be protected by WishList Member.
		 * @param <type> $type
		 * @return <type>
		 */
		function PostTypeEnabled($type) {
			$protected_types = (array) $this->GetOption('protected_custom_post_types');
			$protected_types[] = 'post';
			$protected_types[] = 'page';
			$protected_types[] = 'attachment';
			return in_array($type, $protected_types);
		}

		function GetCustomRegFields($levels = null) {
			$levelsform = (array) $this->GetOption('regpage_form');
			if (is_array($levels) && !empty($levels)) {
				$levels = array_flip($levels);
				$levelsform = array_intersect_key($levelsform, $levels);
			}
			$forms = $this->GetCustomRegForms();
			$fields = array();
			foreach ($forms AS $form) {
				if (!in_array($form->option_name, (array) $levelsform)) {
					continue;
				}
				$form = $form->option_value['form'];
				preg_match_all('#<tr.*?class=".*?li_fld.*?".*?>.*?</tr>#i', $form, $matches);
				$matches = $matches[0];
				foreach ($matches AS $k => $match) {
					$systemFld = preg_match('#<tr.*?class=".*?systemFld.*?".*?>.*?</tr>#i', $match);
					$wp_field = preg_match('#<tr.*?class=".*?wp_field.*?".*?>.*?</tr>#i', $match);
					$tos = preg_match('#<tr.*?class=".*?field_tos.*?".*?>.*?</tr>#i', $match);
					$hidden = preg_match('#<tr.*?class=".*?field_hidden.*?".*?>.*?</tr>#i', $match);
					if (!$systemFld && !$wp_field && !$tos && !$hidden) {
						preg_match('/<(input|select|textarea) .*name="(.*?)".*?>/i', $match, $field_name);
						$field_name = preg_replace('/\[\]$/', '', $field_name[2]);
						$fields[$field_name] = $match;
					}
				}
			}
			return $fields;
		}

		/**
		 * VerifyFeedKey
		 * Verifies if the feed key passed is valid
		 * @param type $feedkey
		 * @return type User ID for feedkey or 0 on failure
		 */
		function VerifyFeedKey($feedkey) {
			list($id) = explode(';', $feedkey);
			if ($this->FeedKey($id) == $feedkey) {
				return $id;
			} else {
				return 0;
			}
		}

		function GetTempDir() {
			if (function_exists('sys_get_temp_dir')) {
				$tmp = sys_get_temp_dir();
			} else {
				$x = tempnam(rand(100000, 999999), $prefix);
				$tmp = dirname($x);
				unlink($x);
			}
			return $tmp;
		}

		/**
		 * Check if the current Registration URL
		 * is a Fallback URL
		 *
		 * A fallback Registration URL allows the user
		 * to enter the email address he used for payment
		 * to proceed with his incomplete registration
		 *
		 * @param string $reg value of $_GET['reg']
		 */
		function IsFallBackURL($reg) {
			$reg = explode('/', $reg, 3);
			$hash = wlm_arrval($reg, 0);
			$time = wlm_arrval($reg, 1);
			$fallback = wlm_arrval($reg, 2);
			if ($fallback == 'fallback') {
				$expire = $time + 3600;
				if ($expire > time()) {
					if ($hash == md5($_SERVER['REMOTE_ADDR'] . '__' . $time . '__' . $this->GetAPIKey())) {
						return true;
					}
				}
			}
			return false;
		}

		function RegFallbackContent() {
			global $wlm_fallback_error;

			$csscode = str_replace(array("\r", "\n"), '', $this->GetOption('reg_form_css'));
			$css = <<<STRING
<style type="text/css">
{$csscode}
</style>
STRING;

			$error = $wlm_fallback_error ? '<p class="wpm_err">' . __('Email not found', 'wishlist-member') . '</p>' : '';

			$instructions = __('Please enter the email address you used for paying to continue.', 'wishlist-member');
			$continue = __('Continue', 'wishlist-member');
			$content = <<<STRING
				{$css}
				<form method="post" class="wlm_fallback">
					{$error}
					<p>{$instructions}</p>
					<input class="wlm_fallback_email" type="email" name="email" value="" size="40" />
					<input class="wlm_fallback_submit" type="submit" value="{$continue}" />
				</form>
STRING;
			return $content;
		}

		/**
		 * Inserts WishList Member Button on tinymce editor
		 */
		function TMCE_InsertButton() {
			//on the post area only
			$pagenow = $GLOBALS['pagenow'];
			//add the button when editing or adding post
			if ($pagenow != "post.php" && $pagenow != "post-new.php")
				return false;
			//for users who can edit only
			if (!current_user_can('edit_posts') && !current_user_can('edit_pages'))
				return false;
			//for rich editing only
			if (get_user_option('rich_editing') == 'true') {
				add_filter('mce_external_plugins', array(&$this, 'TNMCE_RegisterPlugin'));
				add_filter('tiny_mce_before_init', array(&$this, 'TNMCE_RegisterButton'));
			}
		}

		/**
		 * Add the plugin button on tinymce menu
		 *
		 * @param array $in Array of all buttons in tinymce editor
		 */
		function TNMCE_RegisterButton($in) {
			//where would you like to put the new dropdown?
			$advance_button_place = 1; //1,2,3,4
			$key = 'theme_advanced_buttons' . $advance_button_place;
			$holder = explode(",", $in[$key]);
			$holder[] = 'wlm_shortcodes'; //add our plugin on the menu
			$in[$key] = implode(",", $holder);
			return $in;
		}

		/**
		 * Register our Tinymce Plugin
		 *
		 * @param array $plugin_array Array of registered tinymce plugins
		 */
		function TNMCE_RegisterPlugin($plugin_array) {
			$url = admin_url('admin.php') . '?WLMTNMCEPlugin=1';
			$plugin_array['wlm_shortcodes'] = $url;
			return $plugin_array;
		}

		/**
		 * Ganerate JS Code for WishList Member Tinymce Plugin
		 *
		 * @param string $title The title of tinymce plugin
		 * @param string $name The name of tinymce plugin
		 * @param int $max_width The width of tinymce plugin
		 */
		function TNMCE_GeneratePlugin($title, $plugin_name, $max_width) {
			// $variables = $this->Get_Variables(null,false);
			header('Content-type: text/javascript');
			if (!current_user_can('edit_posts') && !current_user_can('edit_pages'))
				exit(0);

			$shortcodes = "\n";
			$icon_path = $this->pluginURL . "/images/WishListIcon.png";
			foreach ($this->WLPShortcodes as $WLPShortcodes) {
				//for the Title
				if ($WLPShortcodes['name']) {
					$shortcodes .= "sub = m.addMenu({title : '{$WLPShortcodes['name']}'})\n";
				}
				//for shortcodes
				if ($WLPShortcodes['shortcode']) {
					$shortcodes .= "sub2 = sub.addMenu({title : 'Shortcodes'})\n";
					foreach ($WLPShortcodes['shortcode'] as $index => $scode) {
						$shortcodes .= "sub2.add({title : '{$index}', onclick : function() {\n";
						$shortcodes .= "  tinyMCE.activeEditor.execCommand('mceInsertContent', false, '{$scode}');\n";
						$shortcodes .= "}});\n";
					}
				}
				//for mergecodes
				if ($WLPShortcodes['mergecode']) {
					;
					$shortcodes .= "sub2 = sub.addMenu({title : 'Mergecodes'})\n";
					foreach ($WLPShortcodes['mergecode'] as $index => $scode) {
						$scode2 = substr_replace($scode, '/', 1, 0); //implode('/',str_split($scode,1));
						$shortcodes .= "sub2.add({title : '{$index}', onclick : function() {\n";
						$shortcodes .= "  var t = tinyMCE.activeEditor.selection.getContent();\n";
						$shortcodes .= "  t = t != '' ? '{$scode }' +t +'{$scode2}' : '';\n";
						$shortcodes .= "  tinyMCE.activeEditor.selection.setContent(t);\n";
						$shortcodes .= "}});\n";
					}
				}
			}
			echo <<<EOT
tinymce.create('tinymce.plugins.{$plugin_name}', {
		createControl: function(n, cm) {
				switch (n) {
						case '{$plugin_name}':
								var c = cm.createMenuButton('{$plugin_name}', {
										title : '{$title}',
										image : '{$icon_path}',
										icons : false
								});

								c.onRenderMenu.add(function(c, m) {
										var sub;
										m.settings['max_width'] = {$max_width};

										//add our shortcodes
										{$shortcodes}
								});

								// Return the new menu button instance
								return c;
				}

				return null;
		}
});
// Register plugin with a short name
tinymce.PluginManager.add('{$plugin_name}', tinymce.plugins.{$plugin_name});
EOT;
		}

		function SendAdminApprovalNotification($user_id) {
			$macros = array(
				'[memberlevel]' => $this->GetMembershipLevels($user_id, true),
				'[password]' => '********'
			);
			$this->send_email_template('registration_approved', $user_id, $macros);
		}

		function IsTempUser($user_id) {
			$user = $this->Get_UserData($user_id);
			if ($user->user_email == $user->user_login && $user->user_login == 'temp_' . md5($user->wlm_origemail)) {
				return true;
			}
			return false;
		}

		function HasAccess($uid, $pid) {
			if (user_can($uid, 'manage_options')) {
				return true;
			}
			$post = get_post($pid);
			if ($this->GetOption('protect_after_more') && strpos($post->post_content, '<!--more-->') !== false) {
				$protectmore = true;
			} else {
				$protectmore = false;
			}

			$protect = $protectmore || $this->Protect($post->ID);
			if (!$protect) {
				return true;
			}

			$is_userpost = in_array($post->ID, $this->GetMembershipContent($post->post_type, 'U-' . $uid));
			if ($is_userpost) {
				return true;
			}

			// page / post is excluded (special page) so give all
			if (in_array($post->ID, $this->ExcludePages(array()))) {
				return true;
			}

			//not a member
			if (empty($uid)) {
				return false;
			}

			$activeLevels = $thelevels = (array) $this->GetMembershipLevels($uid, null, null, null, true);
			$timestamps = $this->UserLevelTimestamps($uid);
			$time = time();

			$expiredLevels = $unconfirmedLevels = $forAprovalLevels = $cancelledLevels = array();

			foreach ((array) $activeLevels AS $key => $thelevelid) {
				if ($this->LevelExpired($thelevelid, $uid)) {
					unset($activeLevels[$key]);
					$expiredLevels[] = $thelevelid;
				}
			}

			if (!count($activeLevels)) {
				//expired
				return false;
			}

			// check if any of the levels are for confirmation
			foreach ((array) $activeLevels AS $key => $thelevelid) {
				if ($this->LevelUnConfirmed($thelevelid, $uid)) {
					unset($activeLevels[$key]);
					$unconfirmedLevels[] = $thelevelid;
				}
			}

			if (!count($activeLevels)) {
				//for confirmation
				return false;
			}

			foreach ((array) $activeLevels AS $key => $thelevelid) {
				if ($this->LevelForApproval($thelevelid, $uid)) {
					unset($activeLevels[$key]);
					$forAprovalLevels[] = $thelevelid;
				}
			}

			if (!count($activeLevels)) {
				//for approval
				return false;
			}

			// check if any of the levels are cancelled
			foreach ((array) $activeLevels AS $key => $thelevelid) {
				if ($this->LevelCancelled($thelevelid, $uid)) {
					unset($activeLevels[$key]);
					$cancelledLevels[] = $thelevelid;
				}
			}
			if (!count($activeLevels)) {
				//cancelled
				return false;
			}

			$canviewpage = $canviewpost = false;
			foreach ((array) $thelevels AS $thelevelid) {
				if (in_array($thelevelid, $activeLevels)) {
					$thelevel = $wpm_levels[$thelevelid];
					$canviewpage = $canviewpage | isset($thelevel['allpages']);
					$canviewpost = $canviewpost | isset($thelevel['allposts']);
				}
			}

			if (!$canviewpage && is_page()) {
				$access = array_intersect((array) $this->GetContentLevels('pages', $post->ID), $activeLevels);
				if (!empty($access)) {
					return true;
				}
			} elseif (!$canviewpost && is_single()) {
				$access = array_intersect((array) $this->GetContentLevels('posts', $post->ID), $activeLevels);
				if (!empty($access)) {
					return true;
				}
			}
			return false;
		}

		/**
		 * Get User's Pay Per Posts
		 * @global object $wpdb
		 * @param string $id UserLevel ID
		 * @param bool $include_by_post_type
		 * @param string $content_type
		 * @param bool $return_ids_only
		 * @return array array of objects by default or one-dimensional array if $return_ids_only is true
		 */
		function GetUser_PayPerPost($id, $include_by_post_type = false, $content_type = null, $return_ids_only = null) {
			global $wpdb;
			$for_approval = $this->GetUser_ForApproval_PayPerPost( $id );

			$id_query = "";
			$approval_filter = "";
			$type_filter = "";
			if(is_array($id)){
				$id = "'" .implode("','", $id) ."'";
				$id_query = "level_id IN ({$id})";
			}else if ( is_numeric( $id ) ) {
				$id = 'U-' . ((int) $id);
				$id_query = "level_id = '{$id}'";
			}else{
				$id_query = "level_id = '{$id}'";
			}

			if(!is_null($content_type) && !empty($content_type)){
				$type_filter = " AND type='{$content_type}' ";
			}


			if(count($for_approval)){
				$for_approval = implode(",", $for_approval);
				$approval_filter = " AND content_id NOT IN ({$for_approval})";
			}

			if (!$include_by_post_type) {
				$query = "SELECT `content_id` FROM `{$this->Tables->contentlevels}` WHERE {$id_query} {$type_filter} {$approval_filter}";
				if($return_ids_only) {
					$res = $wpdb->get_col( $query );
				} else {
					$res = $wpdb->get_results( $query );
				}
			} else {
				$query = "SELECT `content_id`,`type` FROM `{$this->Tables->contentlevels}` WHERE {$id_query} {$type_filter} {$approval_filter}";
				$res = $wpdb->get_results( $query );
			}

			if ( $res === false ) {
				return array();
			}

			return $res;
		}
		/**
		 * Get User's For Approval Pay Per Posts
		 * @global object $wpdb
		 * @param array $id User Id
		 * @return array
		 */
		function GetUser_ForApproval_PayPerPost( $id ) {
			global $wpdb;
			$id_query = "";

			if(is_array($id)){
				$id = "'" .implode("','", $id) ."'";
				$id_query = "cl.level_id IN ({$id})";
			}else if ( is_numeric( $id ) ) {
				$id = 'U-' . ((int) $id);
				$id_query = "cl.level_id = '{$id}'";
			}else{
				$id_query = "cl.level_id = '{$id}'";
			}

			$where = "WHERE clo.option_name='forapproval' AND  {$id_query}";
			$join = " LEFT JOIN `{$this->Tables->contentlevels}` AS `cl` ON cl.ID=clo.contentlevel_id";
			$query = "SELECT DISTINCT cl.content_id FROM `{$this->Tables->contentlevel_options}` AS clo {$join} {$where}";

			$res = $wpdb->get_results( $query );
			if ( $res === false ) {
				return array();
			}
			//convert to array
			$for_approval = array();
			foreach($res as $f){
				$for_approval[] = $f->content_id;
			}
			return $for_approval;
		}

		/***********************************************************************
		 * MarketPlace Methods
		 * ******************************************************************** */

		public function DoMarketPlaceActions() {
			$nonce = get_transient('wl_market_iframe_nonce');

			if (!empty($_POST)) {
				if (!empty($_POST['wl_market_nonce']) && !empty($_POST['wl_market_action'])) {
					if ($_POST['wl_market_nonce'] == $nonce) {
						if ($_POST['wl_market_action'] == 'download_product') {
							$this->MarketProcessProduct($_POST['product_id'], $_POST['product_slug'], $_POST['download_url'], $_POST['plugin_path'], $_POST['plugin_file'], $_POST['plugin_class_name'], $_POST['plugin_db_prefix']);
						}
					}
				}
			}
		}

		public function MarketProcessProduct($product_id, $product_slug, $download_url, $plugin_path, $plugin_file, $plugin_class_name, $plugin_db_prefix) {
			if (empty($plugin_path)) {
				$plugin_path = trailingslashit(WP_PLUGIN_DIR) . $product_slug;
			} else {
				$plugin_path = trailingslashit(WP_PLUGIN_DIR) . $plugin_path;
			}

			if (empty($plugin_file)) {
				$plugin_file = trailingslashit($plugin_path) . $product_slug . '.php';
			} else {
				$plugin_file = trailingslashit($plugin_path) . $plugin_file;
			}

			if (empty($plugin_path)) {
				wp_die("There's something strange in the neighborhood.", "This Error Shouldn't Happen");
			}

			if (file_exists($plugin_path)) {
				$this->MarketActivatePlugin($plugin_file, $plugin_class_name);
			} else {
				$this->MarketInstallPlugin($download_url, $plugin_path, $plugin_file, $plugin_class_name);
			}

		}

		public function MarketActivatePlugin($plugin_file, $plugin_class_name) {
			$activated = activate_plugin($plugin_file);

			//Attempt to edirect user to activated plugin dashboard page
			if (is_null($activated)) {
				if (empty($plugin_class_name)) {
					wp_redirect(admin_url('admin.php') . '?page=WishListMember&wl=marketplace');
				}

				if (class_exists($plugin_class_name)) {
					wp_redirect(admin_url('admin.php') . '?page=' . $plugin_class_name);
				} else {
					wp_redirect(admin_url('admin.php') . '?page=WishListMember&wl=marketplace');
				}
			}
		}

		public function MarketInstallPlugin($download_url, $plugin_path, $plugin_file, $plugin_class_name) {
			require_once(ABSPATH . 'wp-admin/includes/file.php');

			WP_Filesystem();

			global $wp_filesystem;

			$remote_file = download_url($download_url);

			if (!is_wp_error($remote_file)) {
				$downloaded = unzip_file($remote_file, trailingslashit(WP_PLUGIN_DIR));

				if ($downloaded) {
					unlink($remote_file);

					$this->MarketActivatePlugin($plugin_file, $plugin_class_name);
				}
			}

			unlink($remote_file);
		}

		/***********************************************************************
		 * End MarketPlace Methods
		 * ******************************************************************** */

		function CheckPostToDelete($postid) {

			$prevent_deletion = $this->GetOption('prevent_ppp_deletion');

			if ($this->PayPerPost($postid) && $prevent_deletion == 1) {
				$title = get_the_title($postid);
				$settings = admin_url('admin.php') . "?page=WishListMember&wl=settings&mode2=others";
				$settings = "<a href='{$settings}'>change your settings</a>";
				$postlink = admin_url('post.php') . "?post={$postid}&action=edit";
				$postlink = "<a href='{$postlink}'>Update the post</a>";
				$message = "<strong>WishList Member</strong><br />";
				$message .= "<em>Pay Per Posts cannot be deleted or trashed.</em>";
				$message .= "<p>\"<em>$title</em>\" is a Pay Per Post content.</p>";
				$message .= "<p style='text-align:right;'>{$postlink} or {$settings} and try again.</p>";
				wp_die($message);
				exit;
			}

		}

		function GetAfterRegRedirect($level_id) {
			// Get after registration page
			$wpm_levels = $this->GetOption('wpm_levels');

			if ($this->IsPPPLevel($level_id)) {
              	$this->InjectPPPSettings($wpm_levels, $level_id);
			}

			$wpm_level = $wpm_levels[$level_id];

			if ($wpm_level['afterregredirect'] == '---') { // default after registration page
				$afterreg = $this->GetOption('after_registration_internal');
				if ($afterreg) {
					$afterreg = get_permalink($afterreg);
				} else {
					$afterreg = trim($this->GetOption('after_registration'));
				}
			} elseif ($wpm_level['afterregredirect'] == '') { // after registration is homepage
				$afterreg = get_bloginfo('url');
			} elseif ($this->IsPPPLevel($level_id) && $wpm_level['afterregredirect'] == 'backtopost') { // PPP + back to post
				$afterreg = get_permalink(substr($level_id, 11));
			} elseif ($is_forapproval && isset($wpm_level['afterregredirect']) && $wpm_level['afterregredirect'] != '') {
				$afterreg = $wpm_level['afterregredirect'];
			} else { // per level after reg page
				$afterreg = get_permalink($wpm_level['afterregredirect']);
			}

			// Check if level require email confirmation to show email confirm page after registration.
			if ($wpm_level['requireemailconfirmation']) {
				$afterreg = $this->GetOption('membership_forconfirmation_internal');
				if ($afterreg) {
					$afterreg = get_permalink($afterreg);
				} else {
					$afterreg = trim($this->GetOption('membership_forconfirmation'));
				}
			}

			// if no after registration url specified then set it to homepage
			if (!$afterreg) {
				$afterreg = get_bloginfo('url');
			}
			return $afterreg;

		}

		function IntegrationActive($integration_file, $status = null) {
			$integrations = (array) $this->GetOption('ActiveIntegrations');
			if(!is_null($status)) {
				$integrations[$integration_file] = (bool) $status;
				$this->SaveOption('ActiveIntegrations', $integrations);
			}

			if(isset($integrations[$integration_file])) {
				return (bool) $integrations[$integration_file];
			}else{
				return null;
			}
		}

		/**
		 * generate macros for email template
		 * @param  string  $email_template
		 * @param  integer $user_id
		 * @param  array   $more_macros optional associative array of additional macros
		 * @return array   associative array of macros
		 */
		function generate_email_macros($email_template, $user_id, $more_macros = array()) {
 			static $levels = null, $previous_user_id = null, $user_macros = array();

			if(!is_array($more_macros)) {
				$more_macros = array();
			}

			if(is_null($levels)) {
				$levels = $this->GetOption('wpm_levels');
			}

			if($previous_user_id != $user_id) {
				$previous_user_id = $user_id;
				$user = $this->Get_UserData($user_id);
				$current_user = wp_get_current_user();

				if(!$user) {
					return false;
				}

				$user_macros['[firstname]']	= $user->first_name;
				$user_macros['[lastname]']	= $user->last_name;
				$user_macros['[username]']	= $user->user_login;
				if ($email_template == 'incomplete_registration'){
				    $user_macros['[email]']	= $user->wlm_origemail;
				}else {
				    $user_macros['[email]'] = $user->user_email;
				}
				$user_macros['[loginurl]']   = wp_login_url();

				$custom_fields = (array) $this->GetUserCustomFields($user->ID);
				foreach ($custom_fields AS $key => $value) {
				    if (is_array($value)){
					$user_macros["[wlm_custom {$key}]"] = implode("\n", $value);
				    }else{
					$user_macros["[wlm_custom {$key}]"] = $value;
				    }
				}
				$user_macros['[wlm_website]'] = $current_user->user_url;
				//support for user address mergecodes
				$user_macros['[wlm_company]'] 	= wlm_arrval($user->wpm_useraddress, 'company');
                $user_macros['[wlm_address1]'] 	= wlm_arrval($user->wpm_useraddress, 'address1');
                $user_macros['[wlm_address2]'] 	= wlm_arrval($user->wpm_useraddress, 'address2');
                $user_macros['[wlm_city]'] 		= wlm_arrval($user->wpm_useraddress, 'city');
                $user_macros['[wlm_state]'] 	= wlm_arrval($user->wpm_useraddress, 'state');
                $user_macros['[wlm_zip]']		= wlm_arrval($user->wpm_useraddress, 'zip');
                $user_macros['[wlm_country]'] 	= wlm_arrval($user->wpm_useraddress, 'country');
			}

			$macros = $user_macros + $more_macros;

			return $macros;
		}
		/**
		 * Sends email based on email template
		 * - Automatically generates macros for user information
		 * - Additional macros can be passed
		 * - Accepted Email Templates:
				- require_admin_approval
				- email_confirmation
				- registration
				- admin_new_member_notice
				- admin_unsubscribe_notice
				- registration_approved
				- password_hint
				- expiring_level
				- incomplete_registration
				- lost_password
		 *
		 * @param string $email_template
		 * @param int $user_id
		 * @param array $more_macros additional macros to pass
		 * @param string $recipient_email
		 * @param boolean $html
		 * @return boolean
		 */
		function send_email_template($email_template, $user_id, $more_macros = array(), $recipient_email = null, $html = false) {
			static $accepted_templates = array(
				'require_admin_approval'    => array('requireadminapproval_email_subject', 'requireadminapproval_email_message'),
				'email_confirmation'        => array('confirm_email_subject', 'confirm_email_message'),
				'registration'              => array('register_email_subject', 'register_email_body'),
				'admin_new_member_notice'   => array('newmembernotice_email_subject', 'newmembernotice_email_message'),
				'admin_unsubscribe_notice'  => array('unsubscribe_notice_email_subject', 'unsubscribe_notice_email_message'),
				'registration_approved'     => array('registrationadminapproval_email_subject', 'registrationadminapproval_email_message'),
				'password_hint'             => array('password_hint_email_subject', 'password_hint_email_message'),
				'expiring_level'            => array('expiringnotification_email_subject', 'expiringnotification_email_message'),
				'incomplete_registration'   => array('incnotification_email_subject', 'incnotification_email_message'),
				'lost_password'             => array('lostinfo_email_subject', 'lostinfo_email_message'),
			);

			if(!isset($accepted_templates[$email_template])) {
				return false;
			}

			$macros = $this->generate_email_macros($email_template, $user_id, $more_macros);

			$subject = str_replace(array_keys($macros), $macros, $this->GetOption($accepted_templates[$email_template][0]));
			$message = str_replace(array_keys($macros), $macros, $this->GetOption($accepted_templates[$email_template][1]));

			if(empty($recipient_email)) {
				$recipient_email = $macros['[email]'];
			}

			return $this->SendTheMail($recipient_email, $subject, $message, array(), false, (bool) $html);

		}

		/**
		 * This method gets called on the first install of WishList Member
		 * Note: That this is not called on upgrade
		 */
		function first_install() {
			// set form version to improved
			$this->SaveOption('FormVersion', 'improved');
			$this->SaveOption('ActiveShoppingCarts', array());
		}

		/**
		 * Inherit protection of parents (both parent posts and terms/categories)
		 * @global object $wpdb
		 * @param integer $content_id The post ID or term ID of the child content
		 * @param boolean $is_taxonomy True if $content_id is a taxonomy
		 * @param boolean $is_comment True if applying to $content_id's comment
		 * @return null
		 */
		function inherit_protection( $content_id, $is_taxonomy = false, $is_comment = false, &$new_protect = null, &$new_levels = null ) {
			global $wpdb;

			if(!$is_taxonomy) {

				$post_type = get_post_type( $content_id );
				if(empty($post_type)) {
					return;
				}

				if($is_comment) {
					$post_type = '~COMMENT';
				}

				$this->SpecialContentLevel( $content_id, 'Inherit', 'Y', $post_type );

				if($is_comment) {
					$new_protect = $this->Protect($content_id);
					$new_levels = $this->GetContentLevels(get_post_type($content_id));
					$this->do_not_pass_protection = true;
					$this->SpecialContentLevel($content_id, 'Protection', $new_protect, $post_type);
					$this->SetContentLevels($post_type, $content_id, $new_levels, $content_id);
				}else{
					
					$levels = array();

					$taxonomy_names = get_taxonomies( array( '_builtin' => false ), 'names' );
					array_unshift( $taxonomy_names, 'category' );
					$taxonomies     = wp_get_object_terms( $content_id, $taxonomy_names, array( 'fields' => 'ids' ) );

					if(!is_wp_error($taxonomies) AND !empty($taxonomies)) {
						foreach($taxonomies AS $taxonomy) {
							$levels += $this->GetContentLevels('categories', $taxonomy, null, null, $immutable);
						}
					}

					$ancestor = get_post_ancestors( $content_id );
					if(!empty($ancestor)) {
						$levels += $this->GetContentLevels( get_post_type( $ancestor[0] ), $ancestor[0], null, null, $immutable );
					}

					if(empty($levels)) {
						$this->do_not_pass_protection = true;
						$new_protect = $this->Protect( $content_id, 'N' );
						$new_levels = array();
						$this->SetContentLevels( $post_type, $content_id, $new_levels );
						return;
					}

					$protect = in_array('Protection', $levels) ? 'Y' : 'N';
					$this->do_not_pass_protection = true;
					$new_protect = $this->Protect( $content_id, $protect );
					$new_levels = $levels;
					$this->SetContentLevels( $post_type, $content_id, $levels );
				}
				
				return;
			} else {

				// taxonomy
				$levels = array();
				$ancestor = $wpdb->get_var( $wpdb->prepare( "SELECT `parent` FROM `{$wpdb->term_taxonomy}` WHERE `term_id`=%d", $content_id ) );
				if(!empty($ancestor)) {
					$levels = $this->GetContentLevels('categories', $ancestor);
				}

				if(empty($levels)) {
					$this->do_not_pass_protection = true;
					$this->CatProtected( $content_id, 'N' );
					$this->SetContentLevels( 'categories', $content_id, array() );
					return;
				}

				$protect = in_array('Protection', $levels) ? 'Y' : 'N';
				$this->do_not_pass_protection = true;
				$new_protect = $this->CatProtected( $content_id, $protect );
				$new_levels = $levels;
				$this->SetContentLevels( 'categories', $content_id, $levels );
				$this->SpecialContentLevel( $content_id, 'Inherit', 'Y', '~CATEGORY' );
				return;
			}
		}

		/**
		 * Pass protection settings of content to its children marked with "Inherit"
		 * @global object $wpdb
		 * @param integer $content_id The post ID or term ID of the parent content
		 * @param boolean $is_taxonomy True if $content_id is a taxonomy
		 * @return null
		 */
		function pass_protection( $content_id, $is_taxonomy = null ) {
			static $call_record = array();
			global $wpdb;

			if($this->do_not_pass_protection) {
				$this->do_not_pass_protection = false;
				return;
			}
			if(empty($content_id)) return;

			wlm_set_time_limit(60*60*24);


			$args = md5(serialize(func_get_args()));
			if(isset($call_record[$args])) {
				return;
			}
			$call_record[$args] = 1;

			if(!$is_taxonomy) {
				$protect  = $this->Protect( $content_id );
				$children = get_children( $content_id );

				foreach(array_keys($children) AS $child) {
					if($this->SpecialContentLevel($child, 'Inherit')) {
						$this->inherit_protection($child);
					}
				}

				if($this->SpecialContentLevel($content_id, 'Inherit', null, '~COMMENT')) {
					$this->inherit_protection($content_id, false, true);
				}
				unset($call_record[$args]);
				return;
			} else {

				// taxonomy
				$protect = $this->CatProtected( $content_id );

				$taxonomy = $wpdb->get_var( $wpdb->prepare( "SELECT `taxonomy` FROM `{$wpdb->term_taxonomy}` WHERE `term_id`=%d", $content_id ) );

				if(empty($taxonomy)) {
					unset($call_record[$args]);
					return;
				}

				// sub-taxonomies
				$children = get_term_children( $content_id, $taxonomy );
				foreach($children AS $child) {
					if($this->SpecialContentLevel($child, 'Inherit', null, '~CATEGORY')) {
						$this->inherit_protection($child, true);
					}
				}

				// posts under taxonomy
				$children = get_objects_in_term( $content_id, $taxonomy );
				foreach($children AS $child) {
					if ( $this->SpecialContentLevel( $child, 'Inherit' ) ) {
						$this->inherit_protection($child);
					}
				}
				unset($call_record[$args]);
				return;
			}
		}

		/**
		 * migrate file protection settings to wlm_contentlevels
		 */
		function migrate_file_protection() {
			if ( $this->GetOption( 'file_protection_migrated' ) < 2 ) {

				$file_not_inherit = (array) $this->GetOption('FileNotInherit');
				$file_protect     = (array) $this->GetOption('FileProtect');

				wlm_set_time_limit(0);

				$data = array(
					'post_type'   => 'attachment',
					'numberposts' => -1,
					'fields'      => 'id=>parent',
				);
				$file_attachments = get_posts($data);

				$api_queue = new WishlistAPIQueue;

				foreach($file_attachments AS $file_attachment_id => $file_attachment_parent) {
					$levels = array();
					if ( ! in_array( $file_attachment_id, $file_not_inherit ) && $file_attachment_parent) {
						$api_queue->add_queue('file_protect_migrate', serialize(array('inherit', $file_attachment_id)));
						// $this->inherit_protection($file_attachment_id);
					} else {
						$api_queue->add_queue('file_protect_migrate', serialize(array('set', $file_attachment_id)));
						// foreach ( array_keys( $file_protect ) AS $level ) {
						// 	if(in_array($file_attachment_id, (array) $file_protect[$level])){
						// 		if($level == 'Protection') {
						// 			$this->Protect($file_attachment_id, 'Y');
						// 		}else{
						// 			$levels[] = $level;
						// 		}
						// 	}
						// }
						// $this->SetContentLevels( 'attachment', $file_attachment_id, $levels );
					}
				}

				$this->SaveOption('file_protection_migrated', 2);

			}
		}

		/**
		 * migrate folder protection settings to wlm_contentlevels
		 */
		function migrate_folder_protection() {
			if ( $this->GetOption( 'folder_protection_migrated' ) != 1) {
				$folder_levels = (array) $this->GetOption('FolderProtect');

				$x = array();
				foreach($folder_levels AS $level => $folders) {
					if(is_array($folders)) {
						foreach($folders AS $folder) {
							if($level == 'Protection') {
								$this->FolderProtected($this->FolderID($folder), 'Y');
							} else {
								$x[$folder][] = $level;
							}
						}
					}
				}

				foreach($x AS $folder => $levels) {
					$this->SetContentLevels('~FOLDER', $this->FolderID($folder), $levels);
				}

				$force_download = (array) $this->GetOption('FolderForceDownload');
				foreach($force_download AS $level => $folders) {
					if(is_array($folders)) {
						foreach(array_keys($folders) AS $folder) {
							$this->FolderForceDownload($this->FolderID($folder), 'Y');
						}
					}
				}

				$this->SaveOption('folder_protection_migrated', 1);
			}
		}

		function level_ids_to_level_names($levels) {
			static $wpm_levels;
			if(is_null($wpm_levels)) {
				$wpm_levels = $this->GetOption('wpm_levels');
			}
			$names = array();
			foreach($levels AS $level) {
				if(isset($wpm_levels[$level])) {
					$names[$level] = $wpm_levels[$level]['name'];
				}
			}
			return $names;
		}

		function get_wp_tzstring() {
			static $timezone_string;
			if(is_null($timezone_string)) {
				$timezone_string = get_option('timezone_string');
				if(empty($timezone_string)) {
					$timezone_string = 'UTC';
					$gmt_offset = (int) get_option('gmt_offset');
					if($gmt_offset >= 0) {
						$timezone_string .= '+' . $gmt_offset;
					} else {
						$timezone_string .= $gmt_offset;
					}
				}
			}
			return $timezone_string;
		}

		function set_timezone_to_wp() {
			date_default_timezone_set($this->get_wp_tzstring());
		}

		/**
		 * Process _wlping_ data
		 * (Terminates script when done)
		 * @author Mike Lopez <mike@wishlistproducts.com>
		 * 
		 * @param  string $data JSON data
		 * @param  string $hash Comma-separated list of hashes
		 */
		function process_wlping($data, $hash) {
			// clean up
			$data = stripslashes($data);

			// compute hash
			$key = $this->GetOption('LicenseKey');
			$myhash = substr(md5($data.$key), -10);

			// check hash
			if(in_array($myhash, explode(',', $hash))) {
				// decode data
				$data = json_decode($data);

				// HQ: update license expiration
				if(!empty($data->rd)) {
					$this->SaveOption('LicenseExpiration', $data->rd);
				}

				// Support: set flag to update support tickets
				if(!empty($data->update_tickets_list)) {
					$this->SaveOption('do_update_tickets_list', 1);
				}

				// Support: set flag to update single ticket (i.e. for replies made from SD)
				if(!empty($data->update_ticket)) {
					$this->SaveOption('do_update_ticket_' . $data->update_ticket, 1);
				}
			}
			exit;
		}
	}

}
