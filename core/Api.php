<?php

/**
 * Application Programming Interface Class for WishList Member
 * WishList Member Addon that allows external applications to integrate with WishList Member
 * @author Mike Lopez <mjglopez@gmail.com> Glen Barnhardt glen@wishlistproducts.com
 * @package wishlistmember
 *
 * @version 0.1
 * @revision $Rev: 2882 $
 * $LastChangedBy: mike $
 * $LastChangedDate: 2015-09-15 12:14:36 -0400 (Tue, 15 Sep 2015) $
 */
if (!class_exists('WLMAPI')) {
	define('WLMAPI_VERSION', '0.1.' . preg_replace('/[^0-9]/i', '', '$Rev: 2882 $'));

	/**
	 * WishList Member API Class
	 * @package wishlistmember
	 * @subpackage classes	
	 */
	class WLMAPI {

		/**
		 * Get various WishList Member Option Settings.
		 * 
		 * Use this to get:
		 *      register_email_body, register_email_subject, email_sender_name, email_sender_address, 
		 *      CurrentVersion
		 * 
		 * @param string $option Option to retrieve.
		 * @return var Current setting.
		 */
		function GetOption($option, $dec = null) {
			global $WishListMemberInstance;
			$setting = $WishListMemberInstance->GetOption($option, $dec);
			return $setting;
		}

		/**
		 * Check to see if the license is active.
		 * 
		 * Use this to get:
		 * true if license is active false if it is not.
		 * 
		 * @return var Current status.
		 */
		function CheckLicense() {
			global $WishListMemberInstance;
			if ($WishListMemberInstance->GetOption('LicenseStatus') != '1') {
				return false;
			} else {
				return true;
			}
		}

		/**
		 * Get an array with a string of members
		 *
		 * No parameters
		 * @return array Key: Level SKU plus pending and nonsequential, Value: comma delimited string of member ids
		 * Example return:
		 *   Array
		 *       (
		 *           [1253923651] => 381,390,426
		 *           [1255382921] => 
		 *           [pending] => 
		 *           [nonsequential] => 
		 *       )
		 */
		function GetMembers() { // Get array of levels&members: key=level value=list of member ids
			global $WishListMemberInstance;
			$members = $WishListMemberInstance->MemberIDs(null, true);
			$cancelled = $WishListMemberInstance->CancelledMemberIDs(null, true);

			foreach (array_keys($members) AS $sku) {
				$members[$sku] = implode(',', array_diff($members[$sku], (array) $cancelled[$sku]));
			}
			return $members;
		}

		/**
		 * Retrieves all Membership Levels
		 * @return array Membership Levels
		 */
		function GetLevels() {
			global $WishListMemberInstance;
			$levels = $WishListMemberInstance->GetOption('wpm_levels');
			foreach ((array) $levels AS $id => $level) {
				$level['ID'] = $id;
				$levels[$id] = $level;
			}
			return $levels;
		}

		/**
		 * Pass list of possibly mixed skus or names, Get an array as 'sku=>name' or 'sku=>sku'.
		 * 
		 * @param string $level Either 'all' or a comma delimited string of level names or skus to return.
		 * @param string $return Either 'names' or 'skus'.
		 * @return array of levels arranged as 'sku=>name' or 'sku=>sku'.
		 */
		function GetLevelArray($levels = 'all', $return = 'skus') {
			$all_levels = WLMAPI::GetLevels();
			$ret = array();
			if (is_string($levels) && $levels == 'all') {
				foreach ($all_levels as $key => $onelevel) {
					$ret[$key] = ($return == 'skus') ? $key : $onelevel['name'];
				}
				return $ret;
			}

			if (is_string($levels) && strpos($levels, ',') !== FALSE)
				$levels = explode(',', $levels);
			else
				$levels = (array) $levels;

			foreach ($levels as $level) {
				foreach ($all_levels as $key => $onelevel) {
					if ($onelevel['name'] == trim($level) || $key == trim($level)) {
						$ret[$key] = ($return == 'skus') ? $key : $onelevel['name'];
						break;
					}
				}
			}
			return $ret;
		}

		/**
		 * Get a list of posts and pages for a specific level
		 * 
		 * @param string $ContentType can be categories, pages, posts, comments
		 * @param string $Level must be a single level to capture posts and pages.       
		 */
		function GetContentByLevel($ContentType, $Level) {
			global $WishListMemberInstance;
			$content = $WishListMemberInstance->GetMembershipContent($ContentType, $Level);
			return $content;
		}

		/**
		 * Get a list of members in one or more levels.
		 * 
		 * @param string $levels Either 'all' or a comma delimited string of level names or skus to return.
		 * @param bool $strippending Optional. Default is false. True strips pending from return.
		 * @return string Comma delimited string of member ids for all members in matching levels.
		 */
		function MergedMembers($levels = 'all', $strippending = 0) {
			$members = WLMAPI::GetMembers();
			return $members;
			exit;

			if ($levels == 'pending')
				return $members['pending'];

			$levels = $this->GetLevelArray($levels);
			$ret = array();

			foreach ($levels as $k => $level) {
				if (isset($members[$level]) && $members[$level] && $k != 'pending') {
					$ra = explode(',', $members[$level]);
					//$ra = preg_split('/,/i', $members[$level]);
					$ret = array_merge($ret, $ra);
				}
			}
			$ret = array_unique($ret);

			if ($strippending && $members['pending']) {
				$ret = array_diff($ret, explode(',', $members['pending']));
			}
			return implode(',', $ret);
		}

		/**
		 * Get a count of members in a level or levels.
		 * 
		 * @param string $level Either 'all' or a comma delimited string of level names or skus to return.
		 *              For nonmembers pass 'nonmembers' For pending pass 'pending'
		 * @return int Number of members in target level(s).
		 */
		function GetMemberCount($level) {
			global $WishListMemberInstance;

			if ($level == 'nonmembers')
				return $WishListMemberInstance->NonMemberCount();
			/*
			  elseif ($level == 'pending')
			  return $WishListMemberInstance->PendingCount();
			 */

			$m = $WishListMemberInstance->MemberIDs($level, null, true);
			return $m;
			/*
			  if ($m)
			  return count(explode(',', $m));
			  return 0;
			 */
		}

		/**
		 * Make Members pending.
		 * 
		 * @param int or array $ids ID or array of IDs.
		 * @return int Count of IDs.
		 */
		function MakePending($ids) {
			global $WishListMemberInstance;
			$ids = (array) $ids;
			foreach ($ids AS $id) {
				$levels = $WishListMemberInstance->GetMembershipLevels($id);
				foreach ($levels AS $level) {
					$WishListMemberInstance->LevelForApproval($level, $id, true);
				}
			}
			return true;
			//return WLMAPI::_makeit($ids, 'pending');
		}

		/**
		 * Make Members Active.
		 * 
		 * @param int or array $ids ID or array of IDs.
		 * @return int Count of IDs.
		 */
		function MakeActive($ids) {
			global $WishListMemberInstance;
			$ids = (array) $ids;
			foreach ($ids AS $id) {
				$levels = $WishListMemberInstance->GetMembershipLevels($id);
				foreach ($levels AS $level) {
					$WishListMemberInstance->LevelForApproval($level, $id, false);
				}
			}
			return true;
			// return WLMAPI::_makeitnot($ids, 'pending');
		}

		/**
		 * Make Members Sequential.
		 * 
		 * @param int or array $ids ID or array of IDs.
		 * @return int Count of IDs.
		 */
		function MakeSequential($ids) {
			global $WishListMemberInstance;
			$WishListMemberInstance->IsSequential($ids, true);
			return true;
			//return WLMAPI::_makeitnot($ids, 'nonsequential');
		}

		/**
		 * Make Members Nonsequential.
		 * 
		 * @param int or array $ids ID or array of IDs.
		 * @return int Count of IDs.
		 */
		function MakeNonSequential($ids) {
			global $WishListMemberInstance;
			$WishListMemberInstance->IsSequential($ids, false);
			return true;
			//return WLMAPI::_makeit($ids, 'nonsequential');
		}

		/**
		 * Adds a WP User
		 * @param string $username
		 * @param string $email
		 * @param string $password
		 * @param string $firstname (optional)
		 * @param string $lastname (optional)
		 * @return integer User ID on success or False on failure
		 */
		function AddUser($username, $email, $password, $firstname = '', $lastname = '') {
			global $WishListMemberInstance;
			require_once(ABSPATH . WPINC . '/pluggable.php');
			require_once(ABSPATH . WPINC . '/registration.php');
			$username = trim($username);
			$password = trim($password);
			$email = trim($email);
			$firstname = trim($firstname);
			$lastname = trim($lastname);

			$passmin = $WishListMemberInstance->GetOption('min_passlength');
			if (!$passmin)
				$passmin = 8;

			if (!$username)
				return WLMAPI::__setError('Username required');
			if (username_exists($username))
				return WLMAPI::__setError('Username already in use');
			if (!wlm_is_email($email))
				return WLMAPI::__setError('Invalid email address');
			if (email_exists($email))
				return WLMAPI::__setError('Email address already in use');
			if (!$password)
				return WLMAPI::__setError('Password required');
			if (strlen($password) < $passmin)
				return WLMAPI::__setError('Password has to be at least ' . $passmin . ' characters long');

			$userdata = array(
				'user_pass' => $password,
				'user_login' => $username,
				'user_email' => $email
			);

			if ($firstname) {
				$userdata['nickname'] = $userdata['first_name'] = $userdata['display_name'] = $firstname;
			}
			if ($lastname) {
				$userdata['last_name'] = $lastname;
				$userdata['display_name'].=' ' . $lastname;
			}

			$id = wp_create_user($username, $password, $email);

			add_filter( 'send_password_change_email', create_function('', 'return false;') ); // added to prevent WP from sending the password change email (since WP 4.3)
			if ($id) {
				$userdata['ID'] = $id;
				wp_update_user($userdata);
				return $id;
			} else {
				return WLMAPI::__setError('Unknown error');
			}
		}

		/**
		 * Edits a WP User
		 * @param integer $id User ID
		 * @param string $email (optional)
		 * @param string $password (optional)
		 * @param string $firstname (optional)
		 * @param string $lastname (optional)
		 * @param string $displayname (optional)
		 * @param string $nickname (optional)
		 * @return integer User ID on success or False on failure
		 */
		function EditUser($id, $email = '', $password = '', $firstname = '', $lastname = '', $displayname = '', $nickname = '') {
			global $WishListMemberInstance;
			require_once(ABSPATH . WPINC . '/pluggable.php');
			require_once(ABSPATH . WPINC . '/registration.php');
			$id+=0;
			$password = trim($password);
			$email = trim($email);
			$firstname = trim($firstname);
			$lastname = trim($lastname);
			$displayname = trim($displayname);
			$nickname = trim($nickname);

			$passmin = $WishListMemberInstance->GetOption('min_passlength');
			if (!$passmin)
				$passmin = 8;

			$user = $WishListMemberInstance->Get_UserData($id);
			;
			if (!$user)
				return WLMAPI::__setError('Invalid user ID');
			if ($email != '') {
				if (!wlm_is_email($email))
					return WLMAPI::__setError('Invalid email address');
				if ($email != $user->user_email && email_exists($email))
					return WLMAPI::__setError('Email address already in use');
			}
			if ($password != '' && strlen($password) < $passmin)
				return WLMAPI::__setError('Password has to be at least ' . $passmin . ' characters long');

			if ($email != '')
				$user->user_email = $email;
			if ($password != '')
				$user->user_pass = $password;
			if ($firstname != '')
				$user->first_name = $firstname;
			if ($lastname != '')
				$user->last_name = $lastname;
			if ($displayname != '')
				$user->display_name = $displayname;
			if ($nickname != '')
				$user->nickname = $nickname;

			$data = (array) $user;
			$id = wp_update_user($data);
			if ($id) {
				return $id;
			} else {
				return WLMAPI::__setError('Unknown error');
			}
		}

		/**
		 * Delete a WP User
		 * @param integer $id User ID
		 * @param integer $reassign (optional) Reassign posts and links to new User ID
		 * @return boolean
		 */
		function DeleteUser($id, $reassign = null) {
			require_once(ABSPATH . '/wp-admin/includes/user.php');
			$id+=0;
			if (!is_null($reassign))
				$reassign+=0;
			if ($id) {
				if (!$reassign) {
					$ret = wp_delete_user($id);
				} else {
					$ret = wp_delete_user($id, $reassign);
				}
			}
			if ($ret) {
				return true;
			} else {
				return WLMAPI::__setError('Unknown error');
			}
		}

		/**
		 * Get an array of Levels for a user.
		 * 
		 * This enhanced version of GetUserLevels() has several advantages:
		 * 
		 *   1. The existing WishList Member API version trigger a database read for every member checked. A list
		 *      of 500 members adds 500 reads to the page.
		 *   2. The existing WishList Member API version, like many of the functions, uses syntax that works
		 *      in php 5 but not in php4.
		 *   3. The $levels parameter allows you to restrict the return information to levels in a
		 *      list. Get a list of key levels but omit special purchases.
		 *   4. It will return a list of Level names OR SKUs.
		 *   5. Optionally, Add Pending or Sequential status.
		 *   6. Optionally, Get cancelled levels. And optionally with lineout tags.
		 * 
		 * @param int $memid Member ID
		 * @param string $levels Either 'all' or a comma delimited string of level names or skus to return.
		 * @param string $return Either 'names' or 'skus'.
		 * @param bool $addpending Optional. Default is false. True adds Pending status to array, if pending.
		 * @param bool $addsequential Optional. Default is false. True adds Sequential status to array, if sequential.
		 * @param int $cancelled Optional. Default is no cancelled levels returned.
		 *          1=Names returned with lineout. 2=Names returned.
		 * @return array Levels. Key: Level SKU. Value: Level SKU or Name.
		 * 
		 * Overide of the memid so that when empty it looks for the current user. This only works with extentions 
		 * and not with remote calls.
		 */
		function GetUserLevels($memid = '', $levels = 'all', $return = 'names', $addpending = 0, $addsequential = 0, $cancelled = 0) {
			global $WishListMemberInstance;

			if ($memid == '') {
				$memid = wp_get_current_user();
				$memid = $memid->ID;
			}

			if ($memid == '') {
				$ret = "Member ID was not supplied or found";
				return $ret;
			}

			$all_levels = WLMAPI::GetLevelArray($levels, 'names');
			$his_levels = $WishListMemberInstance->GetMembershipLevels($memid); // array of skus
			$ret = array();

			if ($addpending && $WishListMemberInstance->IsPending($memid))
				$ret[] = 'Pending';

			if ($addsequential && $WishListMemberInstance->IsSequential($memid))
				$ret[] = 'Sequential';

			foreach ($all_levels as $key => $name) {
				if (in_array($key, $his_levels)) {
					if ($cancelled) {
						if ($cancelled == 1 && $WishListMemberInstance->LevelCancelled($key, $memid))
							$ret[$key] = ($return == 'names') ? "<strike>$name</strike>" : $key;
						else
							$ret[$key] = ($return == 'names') ? $name : $key;
					} elseif (!$WishListMemberInstance->LevelCancelled($key, $memid)) {
						$ret[$key] = ($return == 'names') ? $name : $key;
					}
				}
			}
			return $ret;
		}

		/**
		 * Adds the user to the specified levels
		 * @param int $user User ID
		 * @param array $levels Membership Level IDs
		 * @param array $txid Transaction ID for integration with shopping carts
		 * @param bool $autoresponder Default FALSE. Set to TRUE if user is to be subscribed to autoresponder for the specified levels
		 * @return bool FALSE if the user ID is invalid or TRUE otherwise
		 */
		function AddUserLevels($user, $levels, $txid = '', $autoresponder = false) {
			global $WishListMemberInstance, $log;
			// check to see if the levels are passed as an array.
			if (!is_array($levels)) {
				$levels = explode(',', $levels);
			}

			// retrieve levels for user
			$ulevels = WLMAPI::GetUserLevels($user, 'all', 'skus');
			if ($ulevels === false)
				return WLMAPI::__setError('Invalid User ID');
			$alllevels = array_unique(array_merge($ulevels, $levels));
			$WishListMemberInstance->SetMembershipLevels($user, $alllevels, !$autoresponder);

			// save transaction ids
			foreach ($levels as $level) {
				$WishListMemberInstance->SetMembershipLevelTxnID($user, $level, $txid);
			}
			return true;
		}

		/**
		 * Removes the user from the specified levels
		 * @param int $user User ID
		 * @param array $levels Membership Level IDs
		 * @param bool $autoresponder Default TRUE. Set to FALSE to keep the user subscribed to the level's autoresponder
		 * @return bool FALSE if the user ID is invalid or TRUE otherwise
		 */
		function DeleteUserLevels($user, $levels, $autoresponder = true) {
			global $WishListMemberInstance;
			// retrieve levels for user
			$ulevels = WLMAPI::GetUserLevels($user, 'all', 'skus');
			if ($ulevels === false)
				return WLMAPI::__setError('Invalid User ID');
			$levels = array_diff($ulevels, $levels);
			$WishListMemberInstance->SetMembershipLevels($user, $levels, !$autoresponder);
			return true;
		}

		/**
		 * Move Members To New Level.
		 * 
		 * Can only "move" a member if they have only one level assigned,
		 * because we otherwise don't know which to remove.
		 * 
		 * @param int or array $ids ID or array of IDs.
		 * @param string $lev SKU or Name of Level to change Member to.
		 * @return int Count of IDs successfully changed.
		 */
		function MoveLevel($ids, $lev = '') {
			global $WishListMemberInstance;
			$ids = (array) $ids;
			//$lev = (array)$lev;
			$lev = WLMAPI::GetLevelArray($lev, 'skus');
			$count = 0;
			foreach ($ids as $id) {
				$currlevels = WLMAPI::GetUserLevels($id, 'all', 'skus');
				$newlevels = array_unique(array_merge($currlevels, $lev));
				if (count($currlevels) == 1 && count($newlevels) == 2) {
					$WishListMemberInstance->SetMembershipLevels($id, $lev, FALSE);
					$count++;
				}
			}
			return $count;
		}

		/**
		 * Cancel Members From a Level.
		 * 
		 * @param int or array $ids ID or array of IDs.
		 * @param string $lev SKU or Name of Level to Cancel Member from.
		 * @return int Count of IDs successfully changed.
		 */
		function CancelLevel($ids, $lev = '') {
			$ids = (array) $ids;
			//$lev = (array)$lev;
			$lev = WLMAPI::GetLevelArray($lev, 'skus');
			$count = 0;
			foreach ($lev as $one) {
				$count += WLMAPI::_CancelLevel($one, $ids, TRUE);
			}
			return $count;
		}

		/**
		 * UnCancel Members From a Level.
		 * 
		 * @param int or array $ids ID or array of IDs.
		 * @param string $lev SKU or Name of Level to UnCancel Member for.
		 * @return int Count of IDs successfully changed.
		 */
		function UnCancelLevel($ids, $lev = '') {
			$ids = (array) $ids;
			//$lev = (array)$lev;
			$lev = WLMAPI::GetLevelArray($lev, 'skus');
			$count = 0;
			foreach ($lev as $one) {
				$count += WLMAPI::_CancelLevel($one, $ids, FALSE);
			}
			return $count;
		}

		/**
		 * Used Internally.
		 * Cancel/Uncancel Members From a Level.
		 * 
		 * @access private
		 * 
		 * @param string $lev SKU of Level to Cancel/UnCancel Member for.
		 * @param array $uid array of IDs.
		 * @param bool $status True to Cancel, False to UnCancel
		 * @return int Count of IDs successfully changed.
		 */
		function _CancelLevel($level, $uid, $status) {
			global $WishListMemberInstance;
			$count1 = (int) $WishListMemberInstance->CancelledMemberIDs($level, null, true);
			$WishListMemberInstance->LevelCancelled($level, $uid, $status);
			$count2 = (int) $WishListMemberInstance->CancelledMemberIDs($level, null, true);
			return abs($count1 - $count2);
		}

		/**
		 * Retrieves the membership levels that have access to a page
		 * @param integer $id Page ID
		 * @return array
		 */
		function GetPageLevels($id) {
			return WLMAPI::__getContentLevels('pages', $id);
		}

		/**
		 * Adds the page to the specified levels
		 * @param int $id Page ID
		 * @param array $levels Membership Level IDs
		 */
		function AddPageLevels($id, $levels) {
			return WLMAPI::__addContentLevels('pages', $id, (array) $levels);
		}

		/**
		 * Removes the page from the specified levels
		 * @param int $id Page ID
		 * @param array $levels Membership Level IDs
		 */
		function DeletePageLevels($id, $levels) {
			return WLMAPI::__deleteContentLevels('pages', $id, (array) $levels);
		}

		/**
		 * Retrieves the membership levels that have access to a post
		 * @param integer $id Post ID
		 * @return array
		 */
		function GetPostLevels($id) {
			return WLMAPI::__getContentLevels('posts', $id);
		}

		/**
		 * Adds the post to the specified levels
		 * @param int $id Post ID
		 * @param array $levels Membership Level IDs
		 */
		function AddPostLevels($id, $levels) {
			return WLMAPI::__addContentLevels('posts', $id, (array) $levels);
		}

		/**
		 * Removes the post from the specified levels
		 * @param int $id Post ID
		 * @param array $levels Membership Level IDs
		 */
		function DeletePostLevels($id, $levels) {
			return WLMAPI::__deleteContentLevels('posts', $id, (array) $levels);
		}

		/**
		 * Retrieves the memebership levels that have access to a category
		 * @param integer $id Category ID
		 * @return array
		 */
		function GetCategoryLevels($id) {
			return WLMAPI::__getContentLevels('categories', $id);
		}

		/**
		 * Adds the category to the specified levels
		 * @param int $id Category ID
		 * @param array $levels Membership Level IDs
		 */
		function AddCategoryLevels($id, $levels) {
			return WLMAPI::__addContentLevels('categories', $id, (array) $levels);
		}

		/**
		 * Removes the category from the specified levels
		 * @param int $id Category ID
		 * @param array $levels Membership Level IDs
		 */
		function DeleteCategoryLevels($id, $levels) {
			return WLMAPI::__deleteContentLevels('categories', $id, (array) $levels);
		}

		/**
		 * Retrieves the membership levels that have access to post/page comments
		 * @param integer $id Post/Page ID
		 * @return array
		 */
		function GetCommentLevels($id) {
			return WLMAPI::__getContentLevels('comments', $id);
		}

		/**
		 * Adds the post/page comment to the specified levels
		 * @param int $id Post/Page ID
		 * @param array $levels Membership Level IDs
		 */
		function AddCommentLevels($id, $levels) {
			return WLMAPI::__addContentLevels('comments', $id, (array) $levels);
		}

		/**
		 * Removes the post/page comment from the specified levels
		 * @param int $id Post/Page ID
		 * @param array $levels Membership Level IDs
		 */
		function DeleteCommentLevels($id, $levels) {
			return WLMAPI::__deleteContentLevels('comments', $id, (array) $levels);
		}

		/*		 * * OTHER FUNCTIONS GO HERE ** */

		/**
		 * ShowWLMWidget
		 * Displays the WishList Member sidebar widget anywhere you want
		 * @param array $widgetargs
		 * @return none
		 */
		function ShowWLMWidget($widgetargs) {
			global $WishListMemberInstance;
			$WishListMemberInstance->Widget($args);
		}

		/**
		 * Passes a string through the WishList Member Private Tags processor
		 * @param string $content
		 * @return string
		 */
		function PrivateTags($content) {
			global $WishListMemberInstance;
			return $WishListMemberInstance->PrivateTags($content);
		}

		/**
		 * Checks if the current page is a WishList Member Magic Page
		 * @return boolean
		 */
		function isMagicPage() {
			global $WishListMemberInstance;
			global $post;
			if ($post->ID == $WishListMemberInstance->MagicPage(false)) {
				return true;
			} else {
				return false;
			}
		}

		/**
		 * Set a post/page Protection to yes or no
		 * @param int post/page id
		 * @param string "Y","N"
		 * @return bool false / true(meaning protected)
		 */
		function SetProtect($id, $value) {
			global $WishListMemberInstance;
			return $WishListMemberInstance->Protect($id, $value);
		}

		/**
		 * Get a post/page Protection to yes or no
		 * @param int post/page id
		 * @return bool false / true(meaning protected)
		 */
		function IsProtected($id) {
			global $WishListMemberInstance;
			return $WishListMemberInstance->Protect($id);
		}

		/*		 * * INTERNAL FUNCTIONS GO HERE ** */

		/**
		 * Internal Function - retrieves all leves
		 * @param string $type Content Type - categories | pages | posts | comments
		 * @param integer $id Page/Post/Category ID
		 * @return array Membership Levels
		 */
		function __getContentLevels($type, $id) {
			global $WishListMemberInstance;
			$levels = WLMAPI::GetLevels();
			$ls = $WishListMemberInstance->GetContentLevels($type, $id);
			foreach ((array) $levels AS $k => $level) {
				if ($level['all' . $type])
					$ls[] = $k;
			}
			$ls = array_unique($ls);
			$ret = array();
			foreach ((array) $ls AS $l) {
				$ret[$l] = $levels[$l]['name'];
			}
			return $ret;
		}

		/**
		 * Adds content to the speicified membership levels
		 * @param string $type Content Type - categories | pages | posts | comments
		 * @param int $id Content ID
		 * @param array $levels Array of Membership Levels to add the content to
		 * @return bool Always TRUE
		 */
		function __addContentLevels($type, $id, $levels) {
			global $WishListMemberInstance;
			$oldlevels = $WishListMemberInstance->GetContentLevels($type, $id);
			$levels = array_unique(array_merge($oldlevels, $levels));
			$WishListMemberInstance->SetContentLevels($type, $id, $levels);
			return true;
		}

		/**
		 * Removes content from the speicified membership levels
		 * @param string $type Content Type - categories | pages | posts | comments
		 * @param int $id Content ID
		 * @param array $levels Array of Membership Levels to remove the content from
		 * @return bool Always TRUE
		 */
		function __deleteContentLevels($type, $id, $levels) {
			global $WishListMemberInstance;
			$oldlevels = $WishListMemberInstance->GetContentLevels($type, $id);
			$levels = array_diff($oldlevels, $levels);
			$WishListMemberInstance->SetContentLevels($type, $id, $levels);
			return true;
		}

		/**
		 * Sets the error message.  This message is used by the __remoteProcess method
		 * @param string $err Error Message
		 * @returns bool Always FALSE
		 */
		function __setError($err) {
			global $__WLM_APIError;
			$__WLM_APIError = $err;
			return false;
		}

		/**
		 * Calls an API function and returns the results as serialized data
		 * @param string $func Function name to call
		 * @param string $key API Key
		 * @param array $params Parameter
		 * @return string Serialized data
		 */
		function __remoteProcess($func, $key, $params) {
			error_reporting(0);
			global $__WLM_APIError, $WishListMemberInstance;

			// validate the key
			$secret = $WishListMemberInstance->GetAPIKey();
			$hashParams = array();
			foreach ($params AS $value) {
				if (is_array($value))
					$value = implode(',', $value);
				$hashParams[] = $value;
			}
			$myhash = md5($x = $func . '__' . $secret . '__' . implode('|', $hashParams));
			if ($myhash != $key) {
				return serialize(array(false, 'AUTHORIZATION FAILED'));
			}

			// check for valid function name. We don't allow functions starting with _ too
			if (substr($func, 0, 1) == '_' OR !method_exists('WLMAPI', $func)) {
				return serialize(array(false, 'INVALID FUNCTION NAME'));
			}

			// Reset the Error Message
			$__WLM_APIError = '';
			// Call the function
			$result = call_user_func_array(array('WLMAPI', $func), (array) $params);

			if ($result === false) { // is $result == false?  If so return the error message too.
				return serialize(array(false, $__WLM_APIError));
			} else { // all is well, return the result
				return serialize(array(true, $result));
			}
		}

		/**
		 * Used Internally.
		 * Make members pending or nonsequential
		 * 
		 * @access private
		 * 
		 * @param array $ids array of IDs.
		 * @param string $type Which operation to perform: pending or nonsequential
		 * @return int Count of IDs.
		 */
		function _makeit($ids, $type = 'pending') { // pending or nonsequential
			global $WishListMemberInstance;
			$ids = (array) $ids;
			foreach ($ids as $id) {
				$get_levels = $WishListMemberInstance->GetMembershipLevels($id, false, true);

				foreach ($get_levels as $level) {
					$value = $WishListMemberInstance->LevelForApproval($level, $ids, true);
				}
			}

			$members = (array) $WishListMemberInstance->GetOption('Members');

			if ($members[$type])
				$members[$type] = implode(',', array_unique(array_merge(explode(',', $members[$type]), $ids)));
			else
				$members[$type] = implode(',', array_unique($ids));

			$WishListMemberInstance->SaveOption('Members', $members);
			return $value;
		}

		/**
		 * Used Internally.
		 * Remove pending or nonsequential designation from Mebers
		 * 
		 * @access private
		 * 
		 * @param array $ids array of IDs.
		 * @param string $type Which operation to perform: pending or nonsequential
		 * @return int Count of IDs.
		 */
		function _makeitnot($ids, $type = 'pending') {
			global $WishListMemberInstance;
			$ids = (array) $ids;
			foreach ($ids as $id) {
				$get_levels = $WishListMemberInstance->GetMembershipLevels($id, false, false);

				foreach ($get_levels as $level) {
					$value = $WishListMemberInstance->LevelForApproval($level, $ids, false);
				}
			}

			$members = (array) $WishListMemberInstance->GetOption('Members');

			$m = ",{$members[$type]},";

			foreach ($ids AS $key => $id)
				$ids[$key] = ",{$id},";
			$m = str_replace($ids, ',', $m);
			$members[$type] = substr($m, 1, -1);
			$WishListMemberInstance->SaveOption('Members', $members);
			return $value;
		}

	}

}
?>
