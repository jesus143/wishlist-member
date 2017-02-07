<?php

/**
 * Level Class for WishList Member
 * @author Mike Lopez <mjglopez@gmail.com>
 * @package wishlistmember
 *
 * @version $Rev: 3101 $
 * $LastChangedBy: mike $
 * $LastChangedDate: 2016-09-13 11:39:16 -0400 (Tue, 13 Sep 2016) $
 */
if (!defined('ABSPATH'))
	die();
if (!class_exists('WishListMember_Level')) {

	/**
	 * WishList Member Level Class
	 * @package wishlistmember
	 * @subpackage classes
	 */
	class WishListMember_Level {

		function __construct($levelID) {
			global $WishListMemberInstance;

			if (get_class($WishListMemberInstance) != 'WishListMember')
				return;

			$wpm_levels = $WishListMemberInstance->GetOption('wpm_levels');
			if (isset($wpm_levels[$levelID])) {
				$level = $wpm_levels[$levelID];
				$level['ID'] = $levelID;
				foreach ($level AS $key => $value) {
					$this->$key = $value;
				}
			}
		}

		function CountMembers($activeOnly = false) {
			global $wpdb, $WishListMemberInstance;
			$table = $WishListMemberInstance->TablePrefix . 'userlevels';
			$table_options = $WishListMemberInstance->TablePrefix . 'userlevel_options';

			$member_count = wlm_cache_get('wishlist_member_all_levels_members_count', 'wishlist-member');
			if ($member_count === false) {
				$results = $wpdb->get_results("SELECT `level_id`,COUNT(*) AS `cnt` FROM `{$table}` GROUP BY `level_id`");
				foreach ($results AS $result) {
					$member_count[$result->level_id] = $result->cnt;
				}
				wlm_cache_set('wishlist_member_all_levels_members_count', $member_count, 'wishlist-member');
			}

			if ($activeOnly) {
				$date = $this->noexpire == 1 ? '1000-00-00 00:00:00' : date('Y-m-d H:i:s', strtotime("-{$this->expire} {$this->calendar}"));
				$query = sprintf("SELECT COUNT(DISTINCT `%1\$s`.`user_id`) FROM `%1\$s` LEFT JOIN `%2\$s`
					ON `%1\$s`.`ID`=`%2\$s`.`userlevel_id`
					AND (`%2\$s`.`option_name` IN ('cancelled','forapproval','unconfirmed','registration_date')
					AND `%2\$s`.`option_value`<>''
					AND `%2\$s`.`option_value`<>0
					and `%2\$s`.`option_value`<='%3\$s')
						WHERE `%1\$s`.`level_id`=%4\$d
						AND `%2\$s`.`userlevel_id` IS NULL", $table, $table_options, $date, $this->ID);
				return $wpdb->get_var($query);
			} else {

				return (isset($member_count[$this->ID]) ? $member_count[$this->ID] : '');
			}
		}

		/**
		 * Get All Membership Levels
		 * @global object $WishListMemberInstance
		 * @param boolean $fullData TRUE to return complete level information or FALSE to return just the IDs
		 * @return array
		 */
		static function GetAllLevels($fullData = false) {
			global $WishListMemberInstance;

			$levelIDs = array_keys($WishListMemberInstance->GetOption('wpm_levels'));
			if ($fullData) {
				$levels = array();
				foreach ($levelIDs AS $levelID) {
					$level = new WishListMember_Level($levelID);
					if ($level->ID == $levelID) {
						$levels[] = $level;
					}
				}
				return $levels;
			} else {
				return $levelIDs;
			}
		}

		static function UpdateLevelsCount() {
			global $WishListMemberInstance;
			$levels = WishListMember_Level::GetAllLevels(true);
			$wpm_levels = $WishListMemberInstance->GetOption('wpm_levels');
			foreach ($levels AS $level) {
				$wpm_levels[$level->ID]['count'] = $level->CountMembers();
			}
			$WishListMemberInstance->SaveOption('wpm_levels', $wpm_levels);
		}

	}

}
?>
