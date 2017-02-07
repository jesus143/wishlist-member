<?php
    if (class_exists('WLM_AUTORESPONDER_MAILCHIMP_INIT')) {
        $api_key = $data[$__index__]['mcapi'];
        if ( $api_key != "" ) {
            $WLM_AUTORESPONDER_MAILCHIMP_INIT = new WLM_AUTORESPONDER_MAILCHIMP_INIT;
            $lists = $WLM_AUTORESPONDER_MAILCHIMP_INIT->mcCallServer("lists", array("limit"=>100), $api_key);
            $start = floor ( $lists["total"] / 100); //100 is the maximum number of lists to return with each call
            $offset = 1;
            while ($offset <= $start){
                $lists2 = $WLM_AUTORESPONDER_MAILCHIMP_INIT->mcCallServer("lists", array("start"=>$offset, "limit"=>100), $api_key);
                $lists = array_merge_recursive($lists, $lists2);
                $offset += 1;
            }
            if (!isset($lists['error']) && $lists['total'] > 0) {
                $lists = $lists['data'];
            } else {
                $lists = array();
            }
        }
    }
?>
<script type="text/javascript">
    jQuery(document).ready(function(){
        jQuery('.wlmmcAction').change(function(){
            var selected = jQuery(this).val();
            if(selected == "unsub" || selected == ""){
                jQuery(this).parent().find("input").val("");
                jQuery(this).parent().find("input").prop("disabled",true);
                jQuery(this).parent().find("input").addClass("disabled");
            }else{
                jQuery(this).parent().find("input").removeClass("disabled");
                jQuery(this).parent().find("input").prop("disabled",false);
            }
        });
    });
</script>
<div style="clear: both; overflow: hidden;">
    <form method="post">
        <input type="hidden" name="saveAR" value="saveAR" />
        <input type="hidden" name="ar[api_v3]" value="1" />
        <input type="hidden" name="ar[mcapi]" value="<?php echo $data[$__index__]['mcapi']; ?>" />
        <input type="hidden" name="ar[optin]" value="<?php echo $data[$__index__]['optin']; ?>" />
        <?php $list_groups_holder = array(); ?>
        <?php foreach ((array) $wpm_levels AS $levelid => $level): ?>
            <?php if ( $data[$__index__]['mcID'][$levelid] != "" ): ?>
                <input type="hidden" name="ar[mcID][<?php echo $levelid; ?>]" value="<?php echo $data[$__index__]['mcID'][$levelid]; ?>" />
                <input type="hidden" name="ar[mcOnRemCan][<?php echo $levelid; ?>]" value="<?php echo $data[$__index__]['mcOnRemCan'][$levelid]; ?>" />
                <?php
                    $list_id = $data[$__index__]['mcID'][$levelid];
                    $list_groups = array();
                    if ( class_exists('WLM_AUTORESPONDER_MAILCHIMP_INIT') ) {
                        $api_key = $data[$__index__]['mcapi'];
                        $WLM_AUTORESPONDER_MAILCHIMP_INIT = new WLM_AUTORESPONDER_MAILCHIMP_INIT;
                        if ( $api_key != "" ) {
                            if ( isset( $list_groups_holder[$list_id] ) ) {
                                $list_groups = $list_groups_holder[$list_id];
                            } else {
                                $list_groups = $WLM_AUTORESPONDER_MAILCHIMP_INIT->mc_get_lists_groups( $api_key, $list_id );
                                $list_groups_holder[ $list_id ] = $list_groups;
                            }
                        }
                    }
                    $c_groups = isset( $data[$__index__]['mcGping'][ $levelid ] ) ? explode( ",", $data[$__index__]['mcGping'][ $levelid ] ) : array();
                    $rc_groups = isset( $data[$__index__]['mcRCGping'][ $levelid ] ) ? explode( ",", $data[$__index__]['mcRCGping'][ $levelid ] ) : array();
                    if ( count( $c_groups ) > 0 ) {
                        $grp_cnt = 0;
                        foreach ( $list_groups as $groupid=>$lg ) {
                            if ( isset( $lg['interests'] ) && count( $lg['interests'] ) > 0 ) {
                                foreach ( $lg['interests'] as $interest_id=>$interest ){
                                    if ( in_array( $interest, $c_groups ) ) {
                                        echo "<input type='hidden' name='ar[mcGping][{$levelid}][{$grp_cnt}]' value='{$interest_id}' />";
                                        $grp_cnt++;
                                    }
                                }
                            }
                        }
                    }

                    if ( count( $rc_groups ) > 0 ) {
                        $grp_cnt = 0;
                        foreach ( $list_groups as $groupid=>$lg ) {
                            if ( isset( $lg['interests'] ) && count( $lg['interests'] ) > 0 ) {
                                foreach ( $lg['interests'] as $interest_id=>$interest ){
                                    if ( in_array( $interest, $rc_groups ) ) {
                                        echo "<input type='hidden' name='ar[mcRCGping][{$levelid}][{$grp_cnt}]' value='{$interest_id}' />";
                                        $grp_cnt++;
                                    }
                                }
                            }
                        }
                    }
                ?>
            <?php endif; ?>
        <?php endforeach; ?>
        <div style="padding: 20px;">
            <p style="color: red;">
                 MailChimp has stated they will stop supporting the older versions of their API by December 31, 2016.  As of that date, all previous versions of the MailChimp API will no longer be supported or updated, but will continue to be operational at that time.
            </p>
            <p style="color: red;">
                But MailChimp will no longer guarantee the continued functionality using the older APIs at that time or moving forward and a migration to the API v3.0 Integration is recommended to maintain an integration with no interruptions in functionality. The blue button to the right can be used to Migrate to the MailChimp API v3.0 Integration. All existing settings will be retained during the migration.
            </p>
            <p style="color: red;">
                Additional information from MailChimp is <a href="http://devs.mailchimp.com/blog/api-v3-0-updates/" target="_blank">available here.</a>
            </p>
            <p style="float: right; clear: both;">
                <input onclick=" return confirm('Please confirm the migration of your settings to the MailChimp API3 Integration.  All your existing settings will be retained.')" type="submit" name="migrate_mcar" class="button-primary" value="<?php _e('Migrate to Mailchimp API v3.0 Integration', 'wishlist-member'); ?>" />
            </p>
        </div>
    </form>
</div>
<form method="post">
    <input type="hidden" name="saveAR" value="saveAR" />
    <h2 class="wlm-integration-steps">Step 1. Configure the MailChimp API Settings:</h2>
    <p><?php _e('API Credentials can be found within the MailChimp account by using the following link:', 'wishlist-member'); ?><br><a href="http://admin.mailchimp.com/account/api/" target="_blank">http://admin.mailchimp.com/account/api/</a></p>
    <table class="form-table">
        <tr valign="top">
            <th scope="row"><?php _e('MailChimp API Key', 'wishlist-member'); ?></th>
            <td>
                <input type="text" name="ar[mcapi]" value="<?php echo $data[$__index__]['mcapi']; ?>" size="60" />
                <?php echo $this->Tooltip("integration-autoresponder-mailchimp-tooltips-API-Key"); ?>
                <br />
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><?php _e('Double Opt-in:', 'wishlist-member'); ?></th>
            <td colspan="2">
                <p>
                    <?php $optin = ($data[$__index__]['optin'] == 1 ? true : false); ?>
                    <input type="checkbox" name="ar[optin]" value="1" <?php echo $optin ? "checked='checked'" : ""; ?> /> Disable Double Opt-in <?php echo $this->Tooltip("integration-autoresponder-mailchimp-tooltips-optin"); ?>
                </p>
            </td>
        </tr>
    </table>
    <h2 class="wlm-integration-steps">Step 2. Assign the Membership Levels to the corresponding MailChimp Lists:</h2>
    <p>Membership Levels can be assigned to Email Lists by selecting a List ID from the corresponding column below.</p>
    <table class="widefat">
        <thead>
            <tr>
                <th scope="col" style="width:38%;"><?php _e('Membership Level', 'wishlist-member'); ?></th>
                <th scope="col" style="width:30%;"><?php _e('List Id', 'wishlist-member'); ?>
                    <?php echo $this->Tooltip("integration-autoresponder-mailchimp-tooltips-Lists-Unique-Id"); ?>
                </th>
                <th class="col" style="width:2%;">&nbsp;</th>
                <th class="col" style="width:30%;"><?php _e('If Removed from Level', 'wishlist-member'); ?></th>
            </tr>
        </thead>
        <tbody valign="top">
            <?php foreach ((array) $wpm_levels AS $levelid => $level): ?>
                <tr class="<?php echo ++$autoresponder_row % 2 ? 'alternate' : ''; ?>">
                    <th scope="row"><?php echo $level['name']; ?></th>
                    <td>
                        <select class='wlmmcAction' name="ar[mcID][<?php echo $levelid; ?>]" style="width:100%;" onchange="jQuery(this).next('div.group-info').css('display',this.selectedIndex > 0 ? 'block' : 'none')" >
                            <option value='' >- Select a List -</option>
                            <?php
                            foreach ((array)$lists as $list) {
                                $selected = $data[$__index__]['mcID'][$levelid] == $list['id'] ? "selected='selected'" : "";
                                echo "<option value='{$list['id']}' {$selected}>{$list['name']}</option>";
                            }
                            ?>
                        </select>
                        <?php $isDisabled = ( $data[$__index__]['mcID'][$levelid] == "" ) ? true : false; ?>
                        <div class="group-info" <?php echo $isDisabled ? 'style="display:none"' : ''; ?> >
                            <blockquote>
                                <div>
                                    Group Title <em>(optional)</em>: <?php echo $this->Tooltip("integration-autoresponder-mailchimp-tooltips-groupings-title"); ?><br>
                                    <input type="text" name="ar[mcGp][<?php echo $levelid; ?>]" value="<?php echo $data[$__index__]['mcGp'][$levelid]; ?>" style="width:100%" />
                                </div>
                                <div>
                                    Group Names <em>(optional)</em>:    <?php echo $this->Tooltip("integration-autoresponder-mailchimp-tooltips-groupings-group"); ?><br>
                                    <input type="text" name="ar[mcGping][<?php echo $levelid; ?>]" value="<?php echo $data[$__index__]['mcGping'][$levelid]; ?>" style="width:100%" />
                                </div>
                            </blockquote>
                        </div>
                    </td>
                    <td>&nbsp;</td>
                    <?php $mcOnRemCan = isset($data[$__index__]['mcOnRemCan'][$levelid]) ? $data[$__index__]['mcOnRemCan'][$levelid] : ""; ?>
                    <td >
                        <select class='wlmmcAction' name="ar[mcOnRemCan][<?php echo $levelid; ?>]" style="width:100%;" onchange="jQuery(this).next('div.group-info').css('display',this.selectedIndex > 1 ? 'block' : 'none')">
                            <option value='' <?php echo $mcOnRemCan == "" ? "selected='selected'" : ""; ?> >- Select a Action -</option>
                            <option value='unsub' <?php echo $mcOnRemCan == "unsub" ? "selected='selected'" : ""; ?> >Unsubscribe from List</option>
                            <option value='move' <?php echo $mcOnRemCan == "move" ? "selected='selected'" : ""; ?> >Move to Group</option>
                            <option value='add' <?php echo $mcOnRemCan == "add" ? "selected='selected'" : ""; ?> >Add to Group</option>
                        </select>
                        <?php $isDisabled = ($mcOnRemCan == "" || $mcOnRemCan == "unsub") ? true : false; ?>
                        <div class="group-info" <?php echo $isDisabled ? 'style="display:none"' : ''; ?> >
                            <blockquote>
                                <div>
                                    Group Title: <?php echo $this->Tooltip("integration-autoresponder-mailchimp-tooltips-groupings-title"); ?><br>
                                    <input type="text" name="ar[mcRCGp][<?php echo $levelid; ?>]" value="<?php echo $data[$__index__]['mcRCGp'][$levelid]; ?>" style="width:100%" <?php echo $isDisabled ? "disabled='disabled' class='disabled'" : ""; ?> />
                                </div>
                                <div>
                                    Group Names: <?php echo $this->Tooltip("integration-autoresponder-mailchimp-tooltips-groupings-group"); ?><br>
                                    <input type="text" name="ar[mcRCGping][<?php echo $levelid; ?>]" value="<?php echo $data[$__index__]['mcRCGping'][$levelid]; ?>" style="width:100%" <?php echo $isDisabled ? "disabled='disabled' class='disabled'" : ""; ?> />
                                </div>
                            </blockquote>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <p class="submit">
        <input type="submit" class="button-primary" value="<?php _e('Save Settings', 'wishlist-member'); ?>" />
    </p>
</form>