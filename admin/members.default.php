<?php
	$wpdb = &$GLOBALS['wpdb'];
	require_once($this->pluginDir . '/core/UserSearch.php');
	$usersearch = stripslashes(wlm_arrval($_GET, 'usersearch'));


	$default_filters = array(
		'usersearch' => stripslashes(wlm_arrval($_GET, 'usersearch')),
		'transactionid' => trim(stripslashes(wlm_arrval($_GET, 'transactionid'))),
		'status'     => stripslashes(wlm_arrval($_GET, 'status')),
		'sequential' => stripslashes(wlm_arrval($_GET, 'sequential')),
		'date_type'  => stripslashes(wlm_arrval($_GET, 'date_type')),
		'from_date'  => stripslashes(wlm_arrval($_GET, 'from_date')),
		'to_date'    => stripslashes(wlm_arrval($_GET, 'to_date')),
		'level'      => stripslashes(wlm_arrval($_GET, 'level'))
	);

	// I DONT KNOW WHY THIS CODE IS HERE, it overwrite the previous filters
	// $default_filters = array(
	// 	'level'      => stripslashes(wlm_arrval($_GET, 'level'))
	// );

	$sort_request = wlm_arrval($_GET, 's');

	if(empty($sort_request)) {
		$sort_request = 'r;d';
	}

	list($sort_request, $sortorder) = explode(';', $sort_request);

	switch ($sort_request) {
		case 'n':
			$sortby = 'display_name';
			break;
		case 'u':
			$sortby = 'user_login';
			break;
		case 'e':
			$sortby = 'user_email';
			break;
		case 'r':
			$sortby = 'user_registered';
			break;
		case 's':
			$sortby = '';
			break;
		case 'p':
			$sortby = '';
			break;
		default:
			$sortby = '';
	}

	if ($sortorder != 'd'){
		$sortorder = 'a';
	}

	$sortorderflip = ($sortorder == 'd') ? 'a' : 'd';

	$sortord = $sortorder == 'd' ? 'DESC' : 'ASC';

	// grouping
	$lvl = (isset($_GET['level']) ? $_GET['level'] : '');
	if (!$lvl)
		$lvl = '%';
	switch ($lvl) {
		case 'nonmembers':
			$ids = array('-');
			$ids = array_merge($ids, $this->MemberIDs());
			break;

		case 'incomplete':
			$ids = $wpdb->get_col("SELECT `ID` FROM `{$wpdb->users}` WHERE `user_login` REGEXP 'temp_[a-f0-9]{32}' AND `user_login`=`user_email`");
			break;

		default:
			if ($lvl != '%') {
				$ids = $this->MemberIDs($lvl);
			} else {
				$ids = '';
			}
	}


	$incomplete_count = $wpdb->get_var("SELECT COUNT(*) FROM `{$wpdb->users}` WHERE `user_login` REGEXP 'temp_[a-f0-9]{32}' AND `user_login`=`user_email`");

	$howmany = $this->GetOption('member_page_pagination');
	$show_latest_reg = $this->GetOption('show_latest_reg');
	if (is_numeric(wlm_arrval($_GET, 'howmany')) || !$howmany) {
		if (wlm_arrval($_GET, 'howmany')) {
			$howmany = (int) $_GET['howmany'];
		}
		if (!$howmany)
			$howmany = 15;
		$this->SaveOption('member_page_pagination', $howmany);
	}
	if (isset($_GET['show_latest_reg'])) {
		$show_latest_reg = $_GET['show_latest_reg'];
		$show_latest_reg = (strtolower($show_latest_reg) == 'checked') ?
				1 : 0;
		$this->SaveOption('show_latest_reg', $show_latest_reg);
	}
	$show_latest_reg_class = ($show_latest_reg) ? "wlm_hide_previous_levels" : "";

	//Check if user wants to save the search
	$save_search = isset($_GET['save_search']) ? $_GET['save_search'] : false;
	if ($save_search) {
		$data = array(
			'option_name' => 'SaveSearch - ' . $_GET['save_searchname'],
			'option_value' => maybe_serialize($default_filters)
		);
		$wpdb->insert($this->Tables->options, $data);
	}

	$wp_user_search = new WishListMemberUserSearch($usersearch, $_GET['offset'], '', $ids, $sortby, $sortord, $howmany, $default_filters);
	// pagination
	$offset = $_GET['offset'] - 1;
	if ($offset < 0)
		$offset = 0;
	$perpage = $wp_user_search->users_per_page;  // posts per page
        if ($perpage == 0)
		$perpage = $howmany;
	$offset = $offset * $perpage;
	$total_users_cnt = $wp_user_search->total_users_for_query == NULL ? $wp_user_search->total_users : $wp_user_search->total_users_for_query;
	$page_links = paginate_links(array(
		'base' => add_query_arg('offset', '%#%'),
		'format' => '',
		'total' => ceil( $total_users_cnt / $perpage),
		'current' => $offset / $perpage + 1,
		'add_args' =>false
	));
	$page_links = $page_links ? "<div class='tablenav'><div class='tablenav-pages'>{$page_links}</div></div>" : "";

	$this->Preload_UserLevelsMeta($wp_user_search->results);

	$manage_content_url = $this->GetMenu('managecontent')->URL;

	$wp_is_43_or_higher = version_compare($GLOBALS['wp_version'], '4.4', '>=');
	$h1 = $wp_is_43_or_higher ? 'h1' : 'h2';
	$h2 = $wp_is_43_or_higher ? 'h2' : 'h3';
	?>
	<!-- ensure minimal js -->
	<script type="text/javascript">
		var redir_url = "?<?php echo $this->QueryString('show_latest_reg', 'howmany'); ?>";
	</script>

	<form action="?<?php echo $this->QueryString('usersearch', 'offset'); ?>" method="get">
		<input type="hidden" name="page" value="WishListMember"/>
		<input type="hidden" name="wl" value="members"/>
		<p class="search-box" style="margin-top:1em">
			<label for="post-search-input" class="hidden"><?php _e('Search Users:', 'wishlist-member'); ?></label>
			<input style="width: 250px;margin-top:0;height:29px" type="text" value="<?php echo esc_attr(stripslashes(wlm_arrval($_GET, 'usersearch'))) ?>" name="usersearch" id="post-search-input" placeholder="<?php _e('Search Members', 'wishlist-member'); ?>" onchange="jQuery('#advanced_search_field').val(this.value)" />
			<select style="width: 200px; vertical-align: top;"  id="search-levels" name="level">
				<option value="">All Users</option>
				<option <?php if (wlm_arrval($_GET, 'level') == 'nonmembers') echo " selected='true'"; ?> value="nonmembers"><?php _e('Non-Members', 'wishlist-member'); ?> (<?php echo $this->NonMemberCount(); ?>)</option>
				<option <?php if (wlm_arrval($_GET, 'level') == 'incomplete') echo " selected='true'"; ?> value="incomplete"><?php _e('Incomplete Registrations', 'wishlist-member'); ?> (<?php echo $incomplete_count; ?>)</option>
				<?php foreach ((array) $wpm_levels AS $id => $level): ?>
					<option value="<?php echo $id; ?>" <?php if (wlm_arrval($_GET, 'level') == $id) echo " selected='true'"; ?>><?php echo $level['name'] ?> (<?php echo (int) $level['count'] ?>)</option>
				<?php endforeach; ?>
			</select>
			<button type="submit" class="button-secondary" xvalue="<?php _e('Search', 'wishlist-member'); ?>"><i class="icon icon-search"></i></button>
			<a style="vertical-align: top" class="button advanced-search" title="<?php _e('Advanced', 'wishlist-member'); ?>" href="javascript:void(0)"><?php _e('Advanced', 'wishlist-member'); ?></a>
			<?php if ($this->GetAllSavedSearch()): ?>
				<select style="width: 130px; vertical-align: top;" id="save-search" onchange="top.location = this.value">
					<option value="">Saved Searches</option>
					<?php foreach ($this->GetAllSavedSearch() as $value): ?>
						<option value="?page=<?php echo $this->MenuID?>&wl=members&<?php echo http_build_query($value['value'])?>" <?php if (wlm_arrval($_GET, 'saved_search') == $value['name']) echo " selected='true'"; ?>><?php echo str_replace('SaveSearch - ', '', $value['name']); ?></option>
					<?php endforeach; ?>
				</select>
			<?php endif; ?>
		</p>
	</form>
	<<?php echo $h1; ?>><?php _e('Members &raquo; Manage Members', 'wishlist-member'); ?>
		<?php if (count($wpm_levels)) : ?> <a href="javascript:void(0)" class="add-new-h2"><?php _e('Add New', 'wishlist-member'); ?></a><?php endif; ?>
	</<?php echo $h1; ?>>
	<?php echo $page_links; ?>
	<form method="post" action="?<?php echo $this->QueryString('msg'); ?>">
		<div class="tablenav">
			<div style="display:block;float:right;line-height:2em">
				<input type="checkbox" name="show_latest_reg" value="1" <?php if ($show_latest_reg) echo 'checked="checked"' ?>/> &nbsp;Show Only Latest Level&nbsp;
				<?php echo $this->Tooltip("members-default-tooltips-show-only-latest-level"); ?>
				&nbsp;
				<?php _e('Display','wishlist-member'); ?>&nbsp;
				<select name="howmany" style="width:63px">
					<option <?php if ($howmany == 15) echo 'selected="true"'; ?>>15</option>
					<option <?php if ($howmany == 30) echo 'selected="true"'; ?>>30</option>
					<option <?php if ($howmany == 50) echo 'selected="true"'; ?>>50</option>
					<option <?php if ($howmany == 100) echo 'selected="true"'; ?>>100</option>
					<option <?php if ($howmany == 200) echo 'selected="true"'; ?>>200</option>
				</select>
				&nbsp;Rows per Page
				&nbsp;&nbsp;<button href="#" id="update-filters" class="button-secondary" xstyle="display:inline">Update</button>

			</div>

			<select name="wpm_action" onchange="wpm_showHideLevels(this)" style="width:160px">
				<option>-- Select an Action --</option>
				<option value="wpm_change_membership">Move to Level</option>
				<option value="wpm_add_membership">Add to Level</option>
				<option value="wpm_del_membership">Remove from Level</option>
				<option disabled="disabled">------</option>
				<option value="wpm_add_payperposts">Add Pay Per Post</option>
				<option value="wpm_del_payperposts">Remove Pay Per Post</option>
				<option disabled="disabled">------</option>
				<option value="wpm_cancel_membership">Cancel from Level</option>
				<option value="wpm_uncancel_membership">Uncancel from Level</option>
				<option disabled="disabled">------</option>
				<option value="wpm_confirm_membership">Confirm Subscription to Level</option>
				<option value="wpm_unconfirm_membership">Unconfirm Subscription to Level</option>
				<option disabled="disabled">------</option>
				<option value="wpm_approve_membership">Approve Registration to Level</option>
				<option value="wpm_unapprove_membership">Unapprove Registration to Level</option>
				<option disabled="disabled">------</option>
				<option value="wpm_enable_sequential">Turn On Sequential Upgrade</option>
				<option value="wpm_disable_sequential">Turn Off Sequential Upgrade</option>
				<option disabled="disabled">------</option>
				<option value="wpm_clear_scheduled">Clear Scheduled Actions</option>
				<option value="wpm_delete_member">Delete Selected Users</option>
			</select> <?php echo $this->Tooltip("members-default-tooltips-Select-an-Action"); ?>

			<span id="levels" style="display:none" class="wpm_action_options">
				<select class="postform" name="wpm_membership_to" style="width:180px">
					<option value="-"><?php _e('Levels...', 'wishlist-member'); ?></option>
					<?php foreach ((array) $wpm_levels AS $id => $level): ?>
						<option value="<?php echo $id ?>"><?php echo $level['name'] ?></option>
					<?php endforeach; ?>
				</select>
			</span>
			<span id="wpm_payperposts" style="display:none" class="wpm_action_options">
				<input type="hidden" class="postform" name="wpm_payperposts_to" id="wpm_payperposts_to" style="width:200px" data-placeholder="Choose Post or Page">
				&nbsp;
			</span>
			<span id="cancel_date" style="display:none" class="wpm_action_options">
				<input style="font-size:small;" type="text" id="datepicker" name="cancel_date" value="##/##/####" size="10">
				<?php echo $this->Tooltip("members-default-tooltips-cancelation-date"); ?>
			</span>
			<span id="add_to_date" style="display:none" class="wpm_action_options">
				<input style="font-size:small;" type="text" id="dp_add_level" name="dp_add_level" value="##/##/####" size="10">
				<?php echo $this->Tooltip("members-default-tooltips-add-level-date"); ?>
			</span>
			<span id="remove_to_date" style="display:none" class="wpm_action_options">
				<input style="font-size:small;" type="text" id="dp_move_level" name="dp_remove_level" value="##/##/####" size="10">
				<?php echo $this->Tooltip("members-default-tooltips-move-level-date"); ?>
			</span>
			<span id="move_to_date" style="display:none" class="wpm_action_options">
				<input style="font-size:small;" type="text" id="dp_remove_level" name="dp_move_level" value="##/##/####" size="10">
				<?php echo $this->Tooltip("members-default-tooltips-remove-level-date"); ?>
			</span>
			<input class="button-secondary" type="button" value="Go" onclick="wpm_doConfirm(this.form)" /> <?php echo $this->Tooltip("members-default-tooltips-go"); ?>
		</div>
		<table class="widefat" id='wpm_members'>
			<thead>
				<tr>
					<th  nowrap scope="col" class="check-column" style="white-space:nowrap">
						<input type="checkbox" onclick="wpm_selectAll(this, 'wpm_members')" />
						<?php echo $this->Tooltip("members-default-tooltips-select-user-checkbox"); ?>
					</th>
					<th scope="col"><a class="wpm_header_link<?php echo $sort_request == 'u' ? ' wpm_header_sort' . $sortorder : ''; ?>" href="?<?php echo $this->QueryString('s') ?>&s=u<?php echo $sort_request == 'u' ? ';' . $sortorderflip : ''; ?>"><?php _e('Username', 'wishlist-member'); ?></a></th>
					<th scope="col"><a class="wpm_header_link<?php echo $sort_request == 'n' ? ' wpm_header_sort' . $sortorder : ''; ?>" href="?<?php echo $this->QueryString('s') ?>&s=n<?php echo $sort_request == 'n' ? ';' . $sortorderflip : ''; ?>"><?php _e('Name', 'wishlist-member'); ?></a></th>
					<th scope="col"><a class="wpm_header_link<?php echo $sort_request == 'e' ? ' wpm_header_sort' . $sortorder : ''; ?>" href="?<?php echo $this->QueryString('s') ?>&s=e<?php echo $sort_request == 'e' ? ';' . $sortorderflip : ''; ?>"><?php _e('Email', 'wishlist-member'); ?></a></th>
					<th scope="col" class="fixed_widths"><?php _e('Subscribed', 'wishlist-member'); ?></th>
					<th scope="col" class="fixed_widths"><?php _e('Posts', 'wishlist-member'); ?></th>
          			<th scope="col" class="fixed_widths"><?php _e('Sequential', 'wishlist-member'); ?></th>
					<th scope="col" class="fixed_widths"><a class="wpm_header_link<?php echo $sort_request == 'r' ? ' wpm_header_sort' . $sortorder : ''; ?>" href="?<?php echo $this->QueryString('s') ?>&s=r<?php echo $sort_request == 'r' ? ';' . $sortorderflip : ''; ?>"><?php _e('Registered', 'wishlist-member'); ?></a></th>
				</tr>
			</thead>
				<?php foreach ((array) $wp_user_search->results AS $uid): $user = $this->Get_UserData($uid); ?>
					<?php
					/*
					 * WP 2.8 Change
					 * We no longer check for user_email in WordPress 2.8 because WP 2.8's sanitize_email won't save our temp email format
					 * frickin WP 2.8!!!  Oh well, it's for the better anyway...
					 * But since we use the same string for emails and usernames when creating temporary accounts,
					 * we'll just use user_login instead
					 */
					$tempuser = substr($user->user_login, 0, 5) == 'temp_' && $user->user_login == 'temp_' . md5($user->wlm_origemail);
					$xemail = $tempuser ? $user->wlm_origemail : $user->user_email;
					$wlUser = new WishListMemberUser($user->ID);
					$levels_count = count($wlUser->Levels);
					wlm_add_metadata($wlUser->Levels);
					?>
					<tbody>
					<tr class="<?php echo $alt++ % 2 ? '' : 'alternate'; ?> member-row">
						<th scope="row" class="check-column"><input type="checkbox" name="wpm_member_id[]" value="<?php echo $user->ID ?>" /></th>
						<td>
							<?php if ($tempuser): ?>
								<?php _e('Incomplete Registration', 'wishlist-member'); ?><br /><a href="<?php echo $this->GetContinueRegistrationURL($xemail); ?>"><?php _e('Click here to complete.', 'wishlist-member'); ?></a>
							<?php else: ?>
								<strong><a href="<?php echo get_bloginfo('wpurl') ?>/wp-admin/user-edit.php?user_id=<?php echo $user->ID ?>&wp_http_referer=wlm"><?php echo $user->user_login ?></a></strong>
							<?php endif; ?>
						</td>
			            <td>
			               <?php echo $user->display_name ?>
			            </td>
			            <td>
			              <a href="mailto:<?php echo $xemail ?>"><?php echo $xemail ?></a>
			            </td>
			            <td style="text-align: center;">
			            	<?php echo $user->wlm_unsubscribe ? __('No', 'wishlist-member') : __('Yes','wishlist-member'); ?>
			            </td>
			            <td style="text-align: center;">

			                <?php if($wlUser->PayPerPosts['_all_']): ?>
			                	<?php
			                		$link = $this->GetMenu('managecontent');
			                		$link = $link->URL . '&s_level=U-'.$wlUser->ID;
			                		


			                	?>
			                  <a href="<?php echo $link; ?>"><?php echo count($wlUser->PayPerPosts['_all_']); ?></a>
			                <?php else : ?>
			                  <?php echo '-'; ?>
			                <?php endif; ?>
			            </td>
			            <td style="text-align: center;">
			              <?php echo $wlUser->Sequential ? __('On','wishlist-member') : __('Off','wishlist-member'); ?>
			            </td>
						<td class="num <?php echo $show_latest_reg_class ?>" style="text-align:center">
							<ul style="margin:0">
								<?php echo date_i18n('m/d/y', strtotime($user->user_registered) + $this->GMT); ?>
							</ul>
						</td>
					</tr>
                    <?php
                        if($levels_count > 0):
                        if($show_latest_reg) {
                          $levels = array_filter(
                              $wlUser->Levels,
                              create_function('$e', 'return $e->Scheduled === false && $e->is_latest_registration === 1;')
                          );
                        } else {
                          $levels = $wlUser->Levels;
                        }

                    $xlevels = array();
                    foreach(array("Active", "Pending", "UnConfirmed", "Scheduled", "Cancelled", "Expired") AS $status) :
                    	if(!$levels && !$xlevels) break;
                    	if($xlevels) {
                    		$levels = $xlevels;
                    		$xlevels = array();
                    	}
                    while ($level = array_shift($levels)):
                    	if(!$level->Name) {
                    		continue;
                    	}
                		if(!$level->$status) {
                			$xlevels[] = $level;
                			continue;
                		}
                    ?>
	            	<tr class="<?php echo $alt % 2 ? 'alternate' : ''; ?> member-row level-details">
	            		<th></th>
                        <td colspan="6" style="text-indent:1em">
                        	<span class="<?php if ($level->is_latest_registration) echo "first_level"; ?>">
                        	<strong><?php
                        		if(in_array($status, array('Active', 'Scheduled'))) {
                        			echo $status;
                        		} else {
                        			$level_pending = array();
                        			$pending_statuses =  $level->Status;
                        			foreach($pending_statuses as $pending_status) {
                        				if($pending_status == 'For Approval') {
                        					// Check if the reason for "For Approval" is due to a pending payment in Shopping Cart, 
                        					// so far only Pin Payments and Paypal are the SC's that sends pending notifications.
                        					if(in_array($level->Pending, array('Paypal Pending', 'Pin Payments Confirmation')))
                        						$level_pending[] = $pending_status . ' <i>('. $level->Pending . ')</i>';
                        					else 
                        						$level_pending[] = $pending_status;
                        				} else {
                        					$level_pending[] = $pending_status;
                        				}
                        			}

                        			echo implode(', ', $level_pending);
                        		}
                        	?></strong> - 
                        	<?php
                        	if($status == 'Scheduled') {
                        		$link = sprintf('<small><a href="#" data-schedule-type="%s" data-level-id="%s" data-user-id="%d" class="unschedule">[unschedule]</a></small>', $level->ScheduleInfo['type'], $level->Level_ID, $wlUser->ID);
                        		printf('%s to %s on %s &nbsp; %s', ucwords($level->ScheduleInfo['type']), $level->Name, date_i18n('m/d/y', strtotime($level->ScheduleInfo['date']) + $this->GMT), $link);	
                        	} else {
                        		echo $level->Name;

                        		if($level->SequentialCancelled && $level->Active) {
                        			echo '<blockquote><em>Sequential Upgrade stopped</em></blockquote>';
                        		}

                        		$more_schedules = array();

                        		$remove = $this->Get_UserLevelMeta($wlUser->ID, $level->Level_ID, 'scheduled_remove');
                        		if($remove) {
                        			$more_schedules['remove'] = strtotime($remove['date']) + $this->GMT;
                        		}
                        		if($level->CancelDate && !$level->Cancelled) {
                        			$more_schedules['cancel'] = $level->CancelDate;
                        		}
                        		if($level->ExpiryDate && !$level->Expired) {
                        			$more_schedules['expire'] = $level->ExpiryDate;
                        		}
                        		if($more_schedules) {
                        			asort($more_schedules);
                        		}
                        		foreach($more_schedules AS $key => $date) {
                        			$event = '';
                        			$link = '';
                        			switch($key) {
                        				case 'remove':
                        					$event = 'be removed';
                        					$link = sprintf('&nbsp; <small><a href="#" data-schedule-type="remove" data-level-id="%s" data-user-id="%d" class="unschedule">[unschedule]</a></small>', $level->Level_ID, $wlUser->ID);
                        					break;
                        				case 'cancel':
                        					$event = 'be cancelled';
                        					$link = sprintf('&nbsp; <small><a href="#" data-schedule-type="cancel" data-level-id="%s" data-user-id="%d" class="unschedule">[unschedule]</a></small>', $level->Level_ID, $wlUser->ID);
                        					break;
                        				case 'expire':
                        					$event = 'expire';
                        					break;
                        			}
                        			if($event) {
	                        			printf('<blockquote><em>Will %s on %s</em>%s</blockquote>', $event, date_i18n( 'm/d/y', $date + $this->GMT ), $link);
                        			}
                        		}

                        	}
                        	?>
                            </span>
                        </td>

                        <td class="fixed_widths">
                            <?php if($level->Timestamp !== false && !$level->Scheduled): ?>
                              <span class='wlm-level-date'><?php echo date_i18n('m/d/y', $level->Timestamp + $this->GMT); ?></span>
                            <?php else: ?>
                              <span class='wlm-level-date-default'>-</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php 
                    	endwhile;
                    	endforeach;
                	?>
            <?php endif; ?>
                      </tbody>
				<?php endforeach; ?>
		</table>
		<input type="hidden" name="WishListMemberAction" value="SaveMembersData" />
	</form>
	<?php echo $page_links; ?>
<?php include $this->pluginDir . '/resources/forms/members.default.advanced-search.php' ?>
<?php include $this->pluginDir . '/resources/forms/members.default.new-member.php' ?>