<?php

/**
 * WishListMemberUserSearch for WordPress 2.9
 */

class WishListMemberUserSearch extends WP_User_Search {

	function WishListMemberUserSearch($search_term = '', $page = '', $role = '', $ids = '', $sortby = '', $sortorder = '', $howmany = 15, $more_filters = array()) {
		if (is_array($ids)) {
			$this->IDs = (array) $ids;
			$this->IDs[] = 0;
			$this->IDs = array_unique($this->IDs);
		} else {
			$this->IDs = '';
		}
		$this->SortBy = $sortby;
		$this->SortOrder = $sortorder ? $sortorder : 'ASC';

		$this->users_per_page = $howmany;

		$this->WP_User_Search($search_term, $page, $role);
	}

	function prepare_query() {
		global $wpdb;
		global $WishListMemberInstance;

		$wpm_levels = $WishListMemberInstance->GetOption('wpm_levels');

		$this->first_user  = ($this->page - 1) * $this->users_per_page;
		$this->query_limit = $wpdb->prepare(" LIMIT %d, %d", $this->first_user, $this->users_per_page);


		$search_sql = null;
		if ($this->search_term) {
			$searches = array();
			$term_search = '(';
			foreach (array('user_login', 'user_nicename', 'user_email', 'user_url', 'display_name') as $col)
				$searches[] = $col . " LIKE '%$this->search_term%'";
			$term_search .= implode(' OR ', $searches);
			$term_search .= ')';

			$search_sql[] = $term_search;
		}


		/**
		 * Filters By Membership Level
		 * incomplete/nonmembers has special handling
		 **/
		$level = $this->additional_filters['level'];
		if(!empty($level)) {
			if($level == 'incomplete') {
				$search_sql[] = "$wpdb->users.user_login LIKE 'temp_%'";
			} else if ($level == 'nonmembers') {
				$search_sql[] = "(ul.level_id  IS NULL)";
			} else {
				$search_sql[] = $wpdb->prepare("ul.level_id=%d", $this->additional_filters['level']);
			}
		}

		/** Filters By Sequential Status **/
		$sequential_filter = isset($this->additional_filters['sequential']) ? $this->additional_filters['sequential'] : false;
		if ($sequential_filter) {
			$filter = $sequential_filter == 'on' ? 1 : 0;
			$search_sql[] = $wpdb->prepare("( uo.option_name='sequential' AND uo.option_value=%d ) ", $filter);
		}

		/**
		 * Filters By Status
		 * Note that expired members are handled differently
		 */
		$status = isset($this->additional_filters['status']) ? $this->additional_filters['status'] : false;
		if($status) {

			$expired_sql  = array();
			$inactive_sql = array();
			$active_sql   = array();

			//expired members are specially handled
			$ids            = array();
			$expiredmembers = $WishListMemberInstance->ExpiredMembersID();
			//flatten the result
			$ids            = call_user_func_array('array_merge', $expiredmembers);
			if(empty($ids)) {
				$ids = array(-1);
			}
			$expired_sql[] = "$wpdb->users.ID IN (".implode(',', $ids).")";


			$inactives = array('cancelled', 'unconfirmed', 'forapproval');
			foreach($inactives as $i) {
				$inactive_sql[] = $wpdb->prepare("( ulo.option_name=%s AND ulo.option_value=%d )", $i, 1);
			}

			switch ($status) {
				case 'expired':
					$search_sql = array_merge($search_sql, $expired_sql);
					break;
				case 'inactive':
					$or_sql = array_merge($expired_sql, $inactive_sql);
					$search_sql[] = "(" . implode(' OR ',  $or_sql) . ")";
					break;
				case 'cancelled':
				case 'uncomfirmed':
				case 'forapproval':
					$search_sql = $wpdb->prepare("( ulo.option_name=%s AND ulo.option_value=%d )", $status, 1);
				default:
					break;
			}
		}

		/**
		 * Filter by Date Ranges
		 * Again, due to expired being computed on the fly
		 * it has to be handled in a specific way
		 */
		$date_meta = !empty($this->additional_filters['date_type'])? $this->additional_filters['date_type'] : false;
		if ($date_meta) {
			//no real option rather than initiate a sub-query since dates are stored as strings
			if($date_meta == 'expiration_date') {
				$ids             = array();
				$expired_ts_from = strtotime($this->additional_filters['from_date']);
				$expired_ts_to   = strtotime($this->additional_filters['to_date']);
				$expiredmembers  = $WishListMemberInstance->ExpiredMembersID();
				foreach($expiredmembers as $level_id => $expired_per_level) {
					foreach($expired_per_level as $user_id) {
						$expired_ts = $WishListMemberInstance->LevelExpireDate($level_id, $user_id);
						if(($expired_ts >= $expired_ts_from) && ($expired_ts <= $expired_ts_to)) {
							$ids[] = $user_id;
						}
					}
				}
			} else {
				$ids = $WishListMemberInstance->GetMembersIDByDateRange($date_meta, $this->additional_filters['from_date'], $this->additional_filters['to_date']);
			}
			//nothing found? force to return nothing
			if(empty($ids)) {
				$ids = array(-1);
			}
			$search_sql[] = "$wpdb->users.ID IN (".implode(',', $ids).")";

		}

		if(!empty($search_sql)) {
			$search_sql = ' WHERE ' . implode(' AND ' , $search_sql);
		}

		$this->query_from = " FROM $wpdb->users"
			." LEFT JOIN {$WishListMemberInstance->Tables->userlevels} ul on ($wpdb->users.ID=ul.user_id)"
			." LEFT JOIN {$WishListMemberInstance->Tables->userlevel_options} ulo on (ulo.userlevel_id=ul.ID)"
			." LEFT JOIN {$WishListMemberInstance->Tables->user_options} uo on ($wpdb->users.ID=uo.user_id)";
		$this->query_where = "$search_sql";


		if ($this->SortBy) {
			$this->query_orderby = " ORDER BY {$this->SortBy} {$this->SortOrder}";
		}

		$this->group_by = " GROUP BY wp_users.ID";

		if (!$this->users_per_page)
			$this->query_limit = '';
	}

}

