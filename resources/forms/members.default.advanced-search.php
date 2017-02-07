<div class="media-modal wp-core-ui media-modal-small" style="display:none;" id="advanced-search-box">
	<a class="media-modal-close" href="javascript:void(0)" title="Close"><span class="media-modal-icon"></span></a>
	<div class="media-modal-content">
		<div class="media-frame hide-menu wp-core-ui">
			<form method="GET" action="">
			<div class="media-frame-title"><h1>Advanced Search</h1></div>
			<div class="media-frame-router">
				<div class="media-router">
					<a href="#search" rel="search" class="media-menu-item active">Advanced Search</a>
					<a href="#saves" rel="saves" class="media-menu-item">Saved Searches</a>
				</div>
			</div>
			<div class="media-frame-content">
				<div id="search" class="panel">
					<input type="hidden" name="page" value="WishListMember"/>
					<input type="hidden" name="wl" value="members"/>
					<table id="advanced-search" class="form-table" width="100%">
						<tr valign="top">
							<td style="width:130px">Search</td>
							<td><input type="text" value="<?php echo esc_attr(stripslashes(wlm_arrval($_GET, 'usersearch'))) ?>" name="usersearch" id="advanced_search_field" style="width:98%" /></td>
						</tr>
						<tr valign="top">
							<td style="width:130px">Transaction ID</td>
							<td><input type="text" value="<?php echo esc_attr(stripslashes(wlm_arrval($_GET, 'transactionid'))) ?>" name="transactionid" id="transaction-id" style="width:98%" /></td>
						</tr>
						<tr valign="top">
							<td>Level</td>
							<td>
								<select style="width: 300px; vertical-align: top;"  id="search-levels" name="level">
								<option value="">All Users</option>
								<option <?php if (wlm_arrval($_GET, 'level') == 'nonmembers') echo " selected='true'"; ?> value="nonmembers"><?php _e('Non-Members', 'wishlist-member'); ?> (<?php echo $this->NonMemberCount(); ?>)</option>
								<option <?php if (wlm_arrval($_GET, 'level') == 'incomplete') echo " selected='true'"; ?> value="incomplete"><?php _e('Incomplete Registrations', 'wishlist-member'); ?> (<?php echo $incomplete_count; ?>)</option>
								<?php foreach ((array) $wpm_levels AS $id => $level): ?>
									<option value="<?php echo $id; ?>" <?php if (wlm_arrval($_GET, 'level') == $id) echo " selected='true'"; ?>><?php echo $level['name'] ?> (<?php echo (int) $level['count'] ?>)</option>
								<?php endforeach; ?>
								</select>
							</td>
						</tr>

						<tr valign="top">
							<td>Status</td>
							<td>
								<select name="status" id="filter_status" style="width: 200px;">
									<option value="">---</option>
									<option <?php if($default_filters['status'] == 'active') echo 'selected="selected"'?> value="active">Active</option>
									<option <?php if($default_filters['status'] == 'inactive') echo 'selected="selected"'?> value="inactive">Inactive</option>
									<option <?php if($default_filters['status'] == 'cancelled') echo 'selected="selected"'?> value="cancelled">Canceled</option>
									<option <?php if($default_filters['status'] == 'expired') echo 'selected="selected"'?> value="expired">Expired</option>
									<option <?php if($default_filters['status'] == 'unconfirmed') echo 'selected="selected"'?> value="unconfirmed">Unconfirmed</option>
									<option <?php if($default_filters['status'] == 'forapproval') echo 'selected="selected"'?> value="forapproval">Needs Approval</option>
									<option <?php if($default_filters['status'] == 'sequential_cancelled') echo 'selected="selected"'?> value="sequential_cancelled">Sequential Upgrade Stopped</option>
								</select>
							</td>
						</tr>
						<tr valign="top">
							<td>Sequential</td>
							<td>
								<select name="sequential" id="filter_sequential" style="width: 80px;">
									<option value="">---</option>
									<option <?php if($default_filters['sequential'] == 'on') echo 'selected="selected"'?> value="on">On</option>
									<option <?php if($default_filters['sequential'] == 'off') echo 'selected="selected"'?> value="off">Off</option>
								</select>
							</td>
						</tr>
						<tr valign="top">
							<td>Date Range</td>
							<td>
								<select name="date_type" id="filter_dates" style="width: 200px;">
									<option value="">---</option>
									<option <?php if($default_filters['date_type'] == 'registration_date') echo 'selected="selected"'?> value="registration_date">Registration Date</option>
									<option <?php if($default_filters['date_type'] == 'cancelled_date') echo 'selected="selected"'?>value="cancelled_date">Cancelation Date</option>
									<option <?php if($default_filters['date_type'] == 'expiration_date') echo 'selected="selected"'?>value="expiration_date">Expiry Date</option>
								</select>
								&nbsp;
								<span id="date_ranges" style="display:none">
									From:
									<input size="10" type="text" id="from_date" name="from_date" value="<?php echo $default_filters['from_date']?>">
									To:
									<input size="10" type="text" id="to_date" name="to_date" value="<?php echo $default_filters['to_date']?>"></input>
								</span>
							</td>
						</tr>
						<tr valign="top">
							<td>
								<label for="save_search">
									<?php _e('Save Search', 'wishlist-member'); ?>
								</label>
							</td>
							<td>
								<input type="checkbox" name="save_search" id="save_search">
								<input type="text" name="save_searchname" id="save_searchname" style="width:50%; display:none" placeholder="Name of Saved Search" />
							</td>
						</tr>
					</table>
				</div>
				<div id="saves" class="panel">
					<table class="form-table">
				 		<?php foreach($this->GetAllSavedSearch() as $s): ?>
				 		<tr>
				 			<td><?php echo str_replace('SaveSearch - ', '', $s['name']); ?></td>
				 			<td>
				 				<a href="?page=<?php echo $this->MenuID?>&wl=members&<?php echo http_build_query($s['value'])?>" class="button button-primary run-search">Run Saved Search</a>
				 				<a href="" rel="<?php echo $s['name']?>" class="button button-secondary remove-saved-search">Delete Save Search</a>
				 			</td>
				 		</tr>
				 		<?php endforeach; ?>
				 	</table>
				</div>

			</div>
			<div class="media-frame-toolbar">
				<div class="media-toolbar">
					<div class="media-toolbar-primary">
						<input type="submit" class="button media-button button-primary button-large" value="<?php _e("Advanced Search", "wishlist-member")?>" />
					</div>
				</div>
			</div>
			</form>
		</div>
	</div>
</div>
<div class="media-modal-backdrop" style="display:none;"></div>

<script type="text/javascript">
jQuery(function($) {
	$('#advanced-search-box').WishListLightBox({trigger: $('.advanced-search')});
});
</script>
<style>
	input[type=text] {
		height:2.2em;
	}
</style>