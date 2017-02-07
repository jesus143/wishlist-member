<div class="media-modal wp-core-ui media-modal-large" style="display:none;" id="wlm-pppusers-box">
    <a class="media-modal-close" href="javascript:void(0)" title="Close"><span class="media-modal-icon"></span></a>
    <div class="media-modal-content">
        <div class="media-frame hide-menu hide-router wp-core-ui">
            <div class="media-frame-title"><h3><?php _e('Manage Pay Per Post Users for ','wishlist-member'); ?>"<em></em>"</h3></div>
            <div class="media-frame-router">
                <div class="media-router">
                    <a href="#ppp-post-title" id="ppp-post-title" class="media-menu-item active"></a>
                </div>
            </div>
            <div class="media-frame-content">
                <div style="padding:10px 16px">
                	<table cellpadding="0" cellspacing="10" width="100%" class="wlm-ppp-users-container-table">
                	<tr>
                		<td valign="middle" width="50%">
		                    <form id="wlm-ppp-user-search">
		                        <strong><?php _e('User Search', 'wishlist-member'); ?>:</strong> &nbsp;
		                        <select name="search_by" style="width:150px">
		                            <option value="by_name"><?php _e('Search by User','wishlist-member'); ?></option>
		                            <option value="by_level"><?php _e('Search by Level(s)','wishlist-member'); ?></option>
		                        </select>
		                        <span id="wlm_search_by_name" class="wlm_search_by_field">
		                            <input type="text" name="search_by_name" placeholder="<?php _e('Name, Username, Email', 'wishlist-member'); ?>" style="width:250px;height:30px;margin:0;vertical-align:middle;">
		                        </span>
		                        <span id="wlm_search_by_level" class="wlm_search_by_field" style="display:none">
		                            <select name="search_by_level" multiple="multiple" style="width:250px">
		                                <?php
		                                    foreach($wpm_levels AS $k => $v) {
		                                        printf('<option value="%s">%s</option>', $k, $v['name']);
		                                    }
		                                ?>
		                            </select>
		                        </span>
		                        <input type="submit" class="button" value="Search">
		                        <span class="spinner"></span>
		                    </form>
                		</td>
                		<td valign="middle">
                			<h3>Pay Per Post Users</h3>
                		</td>
                	</tr>
                		<tr>
                			<td valign="top">
			                    <table class="wp-list-table widefat wlm-ppp-users-table">
			                        <thead>
			                            <tr>
			                                <th><?php _e('Name', 'wishlist-member'); ?></th>
			                                <th><?php _e('Email / Username', 'wishlist-member'); ?></th>
			                                <th>&nbsp;</th>
			                            </tr>
			                        </thead>
			                        <tbody class="search_results">
			                        </tbody>
			                    </table>
                			</td>
                			<td valign="top">
			                    <table class="wp-list-table widefat wlm-ppp-users-table wlm-ppp-queue">
			                        <thead>
			                            <tr>
			                                <th><?php _e('Name', 'wishlist-member'); ?></th>
			                                <th><?php _e('Email / Username', 'wishlist-member'); ?></th>
			                                <th><?php _e('Access', 'wishlist-member'); ?></th>
			                            </tr>
			                        </thead>
			                        <tbody class="queue">
			                        </tbody>
                   				 </table>
                			</td>
                		</tr>
                	</table>
                </div>
            </div>
            <div class="media-frame-toolbar">
                <div class="media-toolbar">
                    <div class="media-toolbar-primary" style="width:100%">
                        <button onclick="jQuery('a.media-modal-close').click();" class="button media-button button-large" style="float:left;margin-left:0"><?php _e('Cancel', 'wishlist-member')?></button>
                        <input type="submit" id="wlm-ppp-queue-update-button" onclick="wlm_prepare_ppp_queue()" class="button media-button button-primary button-large" style="float:right" value="<?php _e('Continue...', 'wishlist-member')?>">
		                <span class="wlm-ppp-queue-update-button spinner"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div id="wlm-ppp-modal-backdrop" class="media-modal-backdrop" style="display:none;">
    <div>
        <p><?php _e('Loading Users...', 'wishlist-member'); ?></p>
        <img src="<?php echo includes_url('js/thickbox/loadingAnimation.gif'); ?>">
    </div>
</div>
