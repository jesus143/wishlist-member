<?php
    $lists = array();
    if (class_exists('WLM_AUTORESPONDER_MAILCHIMP_INIT')) {
        $api_key = $data[$__index__]['mcapi'];
        if ( $api_key != "" ) {
            $WLM_AUTORESPONDER_MAILCHIMP_INIT = new WLM_AUTORESPONDER_MAILCHIMP_INIT;
            $lists = $WLM_AUTORESPONDER_MAILCHIMP_INIT->mc_get_lists( $api_key );
        }
    }
?>
<?php $list_groups_holder = array(); ?>
<form method="post">
    <input type="hidden" name="saveAR" value="saveAR" />
    <input type="hidden" name="ar[api_v3]" value="1" />
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
    <p>Membership Levels can be assigned to Email Lists by selecting a List Name in the corresponding area below.</p>
    <table class="widefat">
        <thead>
            <tr>
                <th scope="col" style="width:38%;"><?php _e('Membership Level', 'wishlist-member'); ?></th>
                <th scope="col" style="width:30%;"><?php _e('List Options', 'wishlist-member'); ?>
                    <?php echo $this->Tooltip("integration-autoresponder-mailchimp-tooltips-Lists-Unique-Id"); ?>
                </th>
                <th class="col" style="width:2%;">&nbsp;</th>
                <th class="col" style="width:30%;"><?php _e('If Removed from Level', 'wishlist-member'); ?>
                    <?php echo $this->Tooltip("integration-autoresponder-mailchimp-tooltips-remove-action"); ?>
                </th>
            </tr>
        </thead>
        <tbody valign="top">
            <?php foreach ((array) $wpm_levels AS $levelid => $level): ?>
                <?php
                    $list_id = isset( $data[$__index__]['mcID'][$levelid] ) ? trim( $data[$__index__]['mcID'][$levelid] ) : false;
                    $list_id = $list_id ? $list_id : false; //make sure its not empty
                    $list_groups = array();
                    if ( class_exists('WLM_AUTORESPONDER_MAILCHIMP_INIT') &&  $list_id ) {
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
                ?>
                <tr class="<?php echo ++$autoresponder_row % 2 ? 'alternate' : ''; ?>">
                    <th scope="row"><?php echo $level['name']; ?></th>
                    <td>
                        List Name
                        <select class='wlmmcAction ar_mc_lists' name="ar[mcID][<?php echo $levelid; ?>]" style="width:100%;" >
                            <option value='' >- Select a List -</option>
                            <?php
                            foreach ( (array) $lists as $list ) {
                                $selected = $list_id == $list['id'] ? "selected='selected'" : "";
                                echo "<option value='{$list['id']}' {$selected}>{$list['name']}</option>";
                            }
                            ?>
                        </select>
                        <?php $isDisabled = ( $data[$__index__]['mcID'][$levelid] == "" || ! $lists ) ? true : false; ?>
                        <div class="group-info" <?php echo $isDisabled ? 'style="visibility:hidden"' : 'style="visibility:visible"'; ?> >
                            <blockquote>
                                <?php
                                    $c_group = isset( $data[$__index__]['mcGping'][$levelid] ) ? $data[$__index__]['mcGping'][$levelid] : array();
                                    $c_group = is_array( $c_group ) ? $c_group : array();
                                    $placeholder = count( $c_group ) ? "Select a group" : "No group available";
                                ?>
                                Interest Groups<br>
                                <select name="ar[mcGping][<?php echo $levelid; ?>][]" data-placeholder='<?php echo $placeholder; ?>' style="width:100%" class='chzn-select' multiple="multiple" >
                                <?php
                                    foreach ( $list_groups as $groupid=>$lg ) {
                                        if ( isset( $lg['interests'] ) && count( $lg['interests'] ) > 0 ) {
                                            echo "<optgroup label=\"{$lg['title']}\">";
                                            foreach ( $lg['interests'] as $interest_id=>$interest ){
                                                $selected = "";
                                                if ( in_array( $interest_id, $c_group ) ) {
                                                    $selected = "selected='selected'";
                                                }
                                                echo "<option value='{$interest_id}' {$selected}>{$interest}</option>";
                                            }
                                            echo "</optgroup>";
                                        }
                                    }
                                ?>
                                </select>
                            </blockquote>
                        </div>
                    </td>
                    <td>&nbsp;</td>
                    <?php $mcOnRemCan = isset($data[$__index__]['mcOnRemCan'][$levelid]) ? $data[$__index__]['mcOnRemCan'][$levelid] : ""; ?>
                    <td >
                        Actions
                        <select class='wlmmcAction ar_mc_remove' name="ar[mcOnRemCan][<?php echo $levelid; ?>]" style="width:100%;" onchange="jQuery(this).next('div.group-info').css('visibility',this.selectedIndex > 1 ? 'visible' : 'hidden')">
                            <option value='' <?php echo $mcOnRemCan == "" ? "selected='selected'" : ""; ?> >- Select an Action -</option>
                            <option value='unsub' <?php echo $mcOnRemCan == "unsub" ? "selected='selected'" : ""; ?> >Unsubscribe from List</option>
                            <option value='move' <?php echo $mcOnRemCan == "move" ? "selected='selected'" : ""; ?> >Move to Group</option>
                            <option value='add' <?php echo $mcOnRemCan == "add" ? "selected='selected'" : ""; ?> >Add to Group</option>
                        </select>
                        <?php $isDisabled = ($mcOnRemCan == "" || $mcOnRemCan == "unsub" || ! $lists ) ? true : false; ?>
                        <div class="group-info" <?php echo $isDisabled ? 'style="visibility:hidden"' : 'style="visibility:visible"'; ?> >
                            <blockquote>
                                <?php
                                    $c_group = isset( $data[$__index__]['mcRCGping'][$levelid] ) ? $data[$__index__]['mcRCGping'][$levelid] : array();
                                    $c_group = is_array( $c_group ) ? $c_group : array();
                                    $placeholder = count( $c_group ) ? "Select a group" : "No groups available";
                                ?>
                                Interest Groups<br>
                                <select name="ar[mcRCGping][<?php echo $levelid; ?>][]" data-placeholder='<?php echo $placeholder; ?>' style="width:100%" class='chzn-select' multiple="multiple" >
                                <?php
                                    foreach ( $list_groups as $groupid=>$lg ) {
                                        if ( isset( $lg['interests'] ) && count( $lg['interests'] ) > 0 ) {
                                            echo "<optgroup label=\"{$lg['title']}\">";
                                            foreach ( $lg['interests'] as $interest_id=>$interest ){
                                                $selected = "";
                                                if ( in_array( $interest_id, $c_group ) ) {
                                                    $selected = "selected='selected'";
                                                }
                                                echo "<option value='{$interest_id}' {$selected}>{$interest}</option>";
                                            }
                                            echo "</optgroup>";
                                        }
                                    }
                                ?>
                                </select>
                            </blockquote>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <p class="submit">
        <input type="submit" class="button-primary" value="<?php _e('Save Settings', 'wishlist-member'); ?>" />
        <small style="float: right">This integration is using MailChimp API v3.0</small>
    </p>
</form>
<script type="text/javascript">
    jQuery(document).ready(function(){
        jQuery('.wlmmcAction').change(function(){
            var selected = jQuery(this).val();
            if ( selected == "unsub" || selected == "" ){
                jQuery(this).parent().find("input").val("");
                jQuery(this).parent().find("input").prop("disabled",true);
                jQuery(this).parent().find("input").addClass("disabled");
            } else {
                jQuery(this).parent().find("input").removeClass("disabled");
                jQuery(this).parent().find("input").prop("disabled",false);
            }
        });

        jQuery('.ar_mc_lists').change( function() {
            var list_select = jQuery(this);
            var remove_select = list_select.parent().next().next().find(".ar_mc_remove");
            var list_id = list_select.val();
            var remove_action = remove_select.val();
            list_select.next('div.group-info').css('visibility','hidden');
            if ( remove_action == "move" || remove_action == "add" ) {
                remove_select.next('div.group-info').css('visibility','hidden');
            }
            if ( ! list_id ) return;

            list_select.after("<span>Retrieving interest groups...<span>");
            var data = {
                ar_action: 'get_list_interest_groups',
                list_id: list_id,
                api_key: '<?php echo $data[$__index__]['mcapi']; ?>'
            }

            jQuery.ajax({
                type: 'POST',
                // url: admin_main_js.wlm_broacast_url,
                data: data,
                success: function( response ) {
                    list_groups = jQuery.parseJSON(response);
                    if ( list_groups && Object.keys(list_groups).length > 0 ) {
                        var new_options = "";
                        jQuery.each( list_groups, function( key, value ) {
                            if ( value.interests && Object.keys(value.interests).length ) {
                                new_options = new_options + '<optgroup label="' +value.title +'">';
                                jQuery.each( value.interests, function( key2, value2 ) {
                                    new_options = new_options + '<option value="' +key2 +'">' +value2 +'</option>';
                                });
                                new_options = new_options + '</optgroup>';
                            }
                        });

                        if ( new_options != "" ) {
                            list_select.parent().find(".group-info").find(".chzn-select").attr("data-placeholder", "Select a group");
                            remove_select.parent().find(".group-info").find(".chzn-select").attr("data-placeholder", "Select a group");
                        } else {
                            list_select.parent().find(".group-info").find(".chzn-select").attr("data-placeholder", "No group available");
                            remove_select.parent().find(".group-info").find(".chzn-select").attr("data-placeholder", "No group available");
                        }

                        list_select.parent().find(".group-info").find(".chzn-select").html(new_options);
                        list_select.parent().find(".group-info").find(".chzn-select").trigger('chosen:updated');

                        remove_select.parent().find(".group-info").find(".chzn-select").html( new_options );
                        remove_select.parent().find(".group-info").find(".chzn-select").trigger('chosen:updated');
                        list_select.next().remove();
                        list_select.next('div.group-info').css('visibility','visible');
                        if ( remove_action == "move" || remove_action == "add" ) {
                            remove_select.next('div.group-info').css('visibility','visible');
                        }
                    } else {
                        list_select.parent().find(".group-info").find(".chzn-select").attr("data-placeholder", "No group available");
                        remove_select.parent().find(".group-info").find(".chzn-select").attr("data-placeholder", "No group available");

                        list_select.parent().find(".group-info").find(".chzn-select").html("");
                        list_select.parent().find(".group-info").find(".chzn-select").trigger('chosen:updated');

                        remove_select.parent().find(".group-info").find(".chzn-select").html("");
                        remove_select.parent().find(".group-info").find(".chzn-select").trigger('chosen:updated');
                        list_select.next().remove();
                        list_select.next('div.group-info').css('visibility','visible');
                        if ( remove_action == "move" || remove_action == "add" ) {
                            remove_select.next('div.group-info').css('visibility','visible');
                        }

                        console.log("List has no interest groups");
                    }
                },
                error: function() {
                    list_select.parent().find(".group-info").find(".chzn-select").attr("data-placeholder", "Cannot retrieve groups");
                    remove_select.parent().find(".group-info").find(".chzn-select").attr("data-placeholder", "Cannot retrieve groups");

                    list_select.parent().find(".group-info").find(".chzn-select").html("");
                    list_select.parent().find(".group-info").find(".chzn-select").trigger('chosen:updated');

                    remove_select.parent().find(".group-info").find(".chzn-select").html("");
                    remove_select.parent().find(".group-info").find(".chzn-select").trigger('chosen:updated');
                    list_select.next().remove();
                    list_select.next('div.group-info').css('visibility','visible');
                    if ( remove_action == "move" || remove_action == "add" ) {
                        remove_select.next('div.group-info').css('visibility','visible');
                    }

                    console.log("An error occured while retrieving list interest groups");
                },
            });
        });
    });
</script>
<script type="text/javascript">
    jQuery(document).ready(function() {
        jQuery(".chzn-select").chosen({width:'100%'});
    });
</script>