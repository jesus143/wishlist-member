<?php
$registerurl = WLMREGISTERURL;
$manage_content_url = $this->wlm->GetMenu('managecontent');
$manage_content_url = $manage_content_url->URL;
?>
<tr class="wlmEditRow" id="wlmEditRow-<?php echo $id?>" style="display:none">
<td colspan="5">
	<form method="post" id="form-<?php echo $id?>">
	<table width="100%" class="widefat wpm_nowrap wlmEditForm" id="wpm_membership_levels">
		<thead>
			<tr>
				<th scope="col"  style="line-height:20px;"><?php _e('Membership Level', 'wishlist-member'); ?></th>
				<th scope="col"  style="line-height:20px;"><?php _e('Registration URL', 'wishlist-member'); ?> <?php echo $this->wlm->Tooltip("membershiplevels-default-tooltips-Registration-URL"); ?></th>
				<th scope="col"  style="line-height:20px;"><?php _e('Redirects', 'wishlist-member'); ?></th>
				<th scope="col"  style="line-height:20px;"><?php _e('Access to', 'wishlist-member'); ?> <?php echo $this->wlm->Tooltip("membershiplevels-default-tooltips-Access-to"); ?> </th>
				<th scope="col" class="num"  nowrap style="line-height:18px;"><?php _e('Length of Subscription', 'wishlist-member'); ?> <?php echo $this->wlm->Tooltip("membershiplevels-default-tooltips-Length-of-Subscription"); ?></th>
			</tr>
		</thead>
		<tbody>
			<tr class="alt wpm_level_row_editor" id="wpm_level_row_<?php echo $id ?>">
				<td<?php if ($errlevel) echo ' style="background:#ff8888"'; ?>>
					<input type="text" name="wpm_levels[<?php echo $id ?>][name]" value="<?php echo esc_attr($level['name']) ?>" id="wpm_inputID" size="20" />
					<br />
					<br />
					<br />
				</td>
				<td<?php if ($errurl) echo ' style="background:#ff8888"'; ?>>
					<a href="<?php echo $registerurl ?>/<?php echo $level['url'] ?>" target="_blank" style="color:#000000"><?php echo $registerurl ?>/<?php echo $level['url'] ?></a>
					<div style="margin-top:5px">
						<a class="wlmClipButton" data-clipboard-text="<?php echo $registerurl ?>/<?php echo $level['url'] ?>" href="javascript:;"><?php _e('Copy URL', 'wishlist-member'); ?></a> | <a href="javascript:;" wlm-target='wpmregurl_<?php echo $id; ?>' onclick='wpm_show_advanced(this); return false'><?php _e('Edit URL', 'wishlist-member'); ?></a>
						<div id="wpmregurl_<?php echo $id; ?>" style="display:none">
							<?php echo $registerurl ?>/<input type="text" name="wpm_levels[<?php echo $id ?>][url]" value="<?php echo esc_attr($level['url']) ?>" size="6" />
						</div>
					</div>
				</td>
				<td>
					<?php _e('After Login', 'wishlist-member'); ?><?php echo $this->wlm->Tooltip("membershiplevels-default-tooltips-After-Login"); ?><br />
					<select name="wpm_levels[<?php echo $id ?>][loginredirect]" style="width:120px">
						<option value='---'>--- <?php _e('Default', 'wishlist-member'); ?> ---</option>
						<option value=''<?php $this->wlm->Selected('', $level['loginredirect'], true); ?>><?php _e('Home Page', 'wishlist-member'); ?></option>
						<?php
						$selected_page = $level['loginredirect'];
						echo str_replace('<option value="' . $selected_page . '">', '<option value="' . $selected_page . '" selected="selected">', $pages_options);
						?>
					</select><br />
					<?php _e('After Registration', 'wishlist-member'); ?><?php echo $this->wlm->Tooltip("membershiplevels-default-tooltips-After-Registration"); ?><br />
					<select name="wpm_levels[<?php echo $id ?>][afterregredirect]" style="width:120px">
						<option value='---'>--- <?php _e('Default', 'wishlist-member'); ?> ---</option>
						<option value=''<?php $this->wlm->Selected('', $level['afterregredirect'], true); ?>><?php _e('Home Page', 'wishlist-member'); ?></option>
						<?php
						$selected_page = $level['afterregredirect'];
						echo str_replace('<option value="' . $selected_page . '">', '<option value="' . $selected_page . '" selected="selected">', $pages_options);
						?>
					</select>
				</td>
				<td>
					<label><input type="checkbox" name="wpm_levels[<?php echo $id ?>][allpages]" <?php echo ($level['allpages']) ? ' checked="true"' : ''; ?> />
						<?php _e('All Pages', 'wishlist-member'); ?></label><br />
					<label><input type="checkbox" name="wpm_levels[<?php echo $id ?>][allcategories]" <?php echo ($level['allcategories']) ? ' checked="true"' : ''; ?> />
						<?php _e('All Categories', 'wishlist-member'); ?></label><br />
					<label><input type="checkbox" name="wpm_levels[<?php echo $id ?>][allposts]" <?php echo ($level['allposts']) ? ' checked="true"' : ''; ?> />
						<?php _e('All Posts', 'wishlist-member'); ?></label><br />
					<label><input type="checkbox" name="wpm_levels[<?php echo $id ?>][allcomments]" <?php echo ($level['allcomments']) ? ' checked="true"' : ''; ?> />
						<?php _e('All Comments', 'wishlist-member'); ?></label><br />
					&nbsp;&nbsp;&nbsp;&nbsp;<a href="<?php echo $manage_content_url; ?>"><?php _e('Detailed Access','wishlist-member'); ?> </a> <?php echo $this->wlm->Tooltip("membershiplevels-default-tooltips-detailedaccess"); ?><br />
				</td>
				<td class="num"<?php if (!$level['noexpire'] && $level['expire'] < 1) echo ' style="background:#ff8888"'; ?>><input type="text" name="wpm_levels[<?php echo $id ?>][expire]" value="<?php echo esc_attr($level['expire']) ?>" size="3"<?php echo ($level['noexpire']) ? ' disabled="true"' : ''; ?> /><select name="wpm_levels[<?php echo $id ?>][calendar]"<?php echo ($level['noexpire']) ? ' disabled="true"' : ''; ?>>
						<option value="Days"<?php echo ($level['calendar'] == 'Days') ? ' selected="true"' : ''; ?>><?php _e('Days', 'wishlist-member'); ?></option>
						<option value="Weeks"<?php echo ($level['calendar'] == 'Weeks') ? ' selected="true"' : ''; ?>><?php _e('Weeks', 'wishlist-member'); ?></option>
						<option value="Months"<?php echo ($level['calendar'] == 'Months') ? ' selected="true"' : ''; ?>><?php _e('Months', 'wishlist-member'); ?></option>
						<option value="Years"<?php echo ($level['calendar'] == 'Years') ? ' selected="true"' : ''; ?>><?php _e('Years', 'wishlist-member'); ?></option>
					</select><br />
					<br />
					&nbsp;<label><input type="checkbox" name="wpm_levels[<?php echo $id ?>][noexpire]" value="1"<?php echo ($level['noexpire']) ? ' checked="true"' : ''; ?> onclick="this.parentNode.parentNode.childNodes[0].disabled = this.parentNode.parentNode.childNodes[1].disabled = this.checked" />
						<?php _e('No Expiration Date', 'wishlist-member'); ?></label>
					<input type="hidden" name="wpm_levels[<?php echo $id ?>][upgradeMethod]" value="<?php echo $level['upgradeMethod'] ?>" />
					<input type="hidden" name="wpm_levels[<?php echo $id ?>][upgradeTo]" value="<?php echo $level['upgradeTo'] ?>" />
					<input type="hidden" name="wpm_levels[<?php echo $id ?>][upgradeSchedule]" value="<?php echo $level['upgradeSchedule'] ?>" />
					<input type="hidden" name="wpm_levels[<?php echo $id ?>][upgradeAfter]" value="<?php echo $level['upgradeAfter'] ?>" />
					<input type="hidden" name="wpm_levels[<?php echo $id ?>][upgradeAfterPeriod]" value="<?php echo $level['upgradeAfterPeriod'] ?>" />
					<input type="hidden" name="wpm_levels[<?php echo $id ?>][upgradeOnDate]" value="<?php echo $level['upgradeOnDate'] ?>" />
					<input type="hidden" name="wpm_levels[<?php echo $id ?>][count]" value="<?php echo $level['count'] ?>" />
				</td>
			</tr>
			<?php /* advanced settings - START */ ?>
			<tr class="alternate wpm_level_row_editor_advanced" id="wpm_level_row_advanced_<?php echo $id ?>">
				<td colspan="5">
					<table width="100%" class="MembershipLevelsAdvanced">
						<tr>
							<td style="width:310px;" class="first">
								<b>Registration Requirements</b> <?php echo $this->wlm->Tooltip("membershiplevels-default-tooltips-regrequirements"); ?><br />
								<label><input type="checkbox" name="wpm_levels[<?php echo $id ?>][requirecaptcha]" value="1" <?php $this->wlm->Checked(1, $level['requirecaptcha']); ?> /> <?php _e('Require Captcha Image on Registration Page', 'wishlist-member'); ?></label>
								<?php echo $this->wlm->Tooltip("membershiplevels-default-tooltips-recaptchasettings"); ?>
								<br />
								<label><input type="checkbox" name="wpm_levels[<?php echo $id ?>][requireemailconfirmation]" value="1" <?php $this->wlm->Checked(1, $level['requireemailconfirmation']); ?> /> <?php _e('Require Email Confirmation After Registration', 'wishlist-member'); ?></label>
								<?php echo $this->wlm->Tooltip("membershiplevels-default-tooltips-requireemailconfirmation"); ?>
								<br />
								<br />
                                <?php _e('Require Admin Approval After Registration:', 'wishlist-member'); ?>
                                <br />
								<label><input type="checkbox" name="wpm_levels[<?php echo $id ?>][requireadminapproval]" value="1" <?php $this->wlm->Checked(1, $level['requireadminapproval']); ?> /> <?php _e('For Free Registrations', 'wishlist-member'); ?></label>
								<?php echo $this->wlm->Tooltip("membershiplevels-default-tooltips-requireadminapproval"); ?>
								<br />
								<label><input type="checkbox" name="wpm_levels[<?php echo $id ?>][requireadminapproval_integrations]" value="1" <?php $this->wlm->Checked(1, $level['requireadminapproval_integrations']); ?> <?php echo $this->wlm->GetOption( 'admin_approval_shoppingcart_reg' ) ? '':'disabled="disabled"'; ?> /> <?php _e('For Shopping Cart Integrations', 'wishlist-member'); ?></label>
								<?php if ( $this->wlm->GetOption( 'admin_approval_shoppingcart_reg' )  ): ?>
									<?php echo $this->wlm->Tooltip( "membershiplevels-default-tooltips-requireadminapproval-integrations" ); ?>
								<?php else: ?>
									<?php echo $this->wlm->Tooltip( "membershiplevels-default-tooltips-requireadminapproval-integrations-disabled" ); ?>
								<?php endif; ?>
							</td>
							<td style="width:215px;">
								<br />
								<label><input type="checkbox" name="wpm_levels[<?php echo $id ?>][isfree]" value="1" <?php $this->wlm->Checked(1, $level['isfree']); ?> /> <?php _e('Grant Continued Access', 'wishlist-member'); ?></label>
								<?php echo $this->wlm->Tooltip("membershiplevels-default-tooltips-Grant-Continued-Access"); ?>
								<br  />
								<label><input type="checkbox" name="wpm_levels[<?php echo $id ?>][disableexistinglink]" value="1" <?php $this->wlm->Checked(1, $level['disableexistinglink']); ?> /> <?php _e('Disable Existing Users Link', 'wishlist-member'); ?></label>
								<?php echo $this->wlm->Tooltip("membershiplevels-default-tooltips-disableexistinglink"); ?>
								<br  />
								<label><input type="checkbox" name="wpm_levels[<?php echo $id ?>][registrationdatereset]" value="1" <?php $this->wlm->Checked(1, $level['registrationdatereset']); ?> /> <?php _e('Registration Date Reset', 'wishlist-member'); ?></label>
								<?php echo $this->wlm->Tooltip("membershiplevels-default-tooltips-registrationdatereset"); ?>
								<br  />
								<label><input type="checkbox" name="wpm_levels[<?php echo $id ?>][uncancelonregistration]" value="1" <?php $this->wlm->Checked(1, $level['uncancelonregistration']); ?> /> <?php _e('Un-cancel on Re-registration', 'wishlist-member'); ?></label>
								<?php echo $this->wlm->Tooltip("membershiplevels-default-tooltips-uncancelonregistration"); ?>
								<br  />
								<label><input type="checkbox" name="wpm_levels[<?php echo $id ?>][disableprefilledinfo]" value="1" <?php $this->wlm->Checked(1, $level['disableprefilledinfo']); ?> /> <?php _e('Make pre-filled User info not editable', 'wishlist-member'); ?></label>
								<?php echo $this->wlm->Tooltip("membershiplevels-default-tooltips-disableprefilledinfo"); ?>
							</td>
							<td style="width:220px;">
								<br />
								<table cellspacing="0" cellpadding="0" border="0">
									<tr>
										<td style="border:none" valign="middle"><?php _e('Role', 'wishlist-member'); ?></td>
										<td style="border:none" valign="middle">
											<select name="wpm_levels[<?php echo $id ?>][role]" style="width:100px">
												<?php foreach ((array) $roles AS $rolekey => $rolename): ?>
													<option value="<?php echo $rolekey; ?>"<?php $this->wlm->Selected($rolekey, $level['role']); ?>><?php echo $rolename; ?></option>
												<?php endforeach; ?>
											</select><?php echo $this->wlm->Tooltip("membershiplevels-default-tooltips-Role"); ?>
										</td>
									</tr>
									<tr>
										<td style="border:none" valign="middle"><?php _e('Level Order', 'wishlist-member'); ?></td>
										<td style="border:none" valign="middle"><!--1--><input type="text" name="wpm_levels[<?php echo $id ?>][levelOrder]" value="<?php echo esc_attr($level['levelOrder']) ?>"  size="4" /><?php echo $this->wlm->Tooltip("membershiplevels-default-tooltips-levelorder"); ?></td>
									</tr>
								</table>
							</td>
							<td>
								<b><?php _e('Remove From','wishlist-member'); ?></b> <?php echo $this->wlm->Tooltip( "membershiplevels-default-tooltips-removefrom" ); ?><br />
								<select style="width:100%" name="wpm_levels[<?php echo $id; ?>][removeFromLevel][]" class="select2" multiple="multiple" placeholder="Select Level(s)...">
									<?php 
										foreach($wpm_levels AS $rlevel_id => $rlevel) {
											if($id != $rlevel_id) {
												$selected = isset($level['removeFromLevel'][$rlevel_id]) ? 'selected="selected"' : '';
												printf('<option value="%s" %s>%s</option>', $rlevel_id, $selected, $rlevel['name']);
											}
										}
									?>
								</select>
								<hr>
								<b><?php _e('Add to','wishlist-member'); ?></b> <?php echo $this->wlm->Tooltip( "membershiplevels-default-tooltips-addto" ); ?><br />
								<select style="width:100%" name="wpm_levels[<?php echo $id; ?>][addToLevel][]" class="select2" multiple="multiple" placeholder="Select Level(s)...">
									<?php 
										foreach($wpm_levels AS $rlevel_id => $rlevel) {
											if($id != $rlevel_id) {
												$selected = isset($level['addToLevel'][$rlevel_id]) ? 'selected="selected"' : '';
												printf('<option value="%s" %s>%s</option>', $rlevel_id, $selected, $rlevel['name']);
											}
										}
									?>
								</select>
								<br />
								<label><input type="checkbox" name="wpm_levels[<?php echo $id ?>][inheritparent]" value="1" <?php $this->wlm->Checked(1, $level['inheritparent']); ?> /> <?php _e( 'Inherit Parent Level status', 'wishlist-member' ); ?></label>
								<?php echo $this->wlm->Tooltip( "membershiplevels-default-tooltips-inheritparent" ); ?>
							</td>
						</tr>
						<tr>
							<td colspan="4" class="first">
								<div>
									<b><?php _e('Sales Page URL:', 'wishlist-member'); ?></b> <i>(optional)</i>
									<input type="text" name="wpm_levels[<?php echo $id ?>][salespage]" value="<?php echo esc_attr($level['salespage']) ?>" size="80" />
									<?php echo $this->wlm->Tooltip("membershiplevels-default-tooltips-levelsalespage"); ?>
								</div>
							</td>
						</tr>
					</table>
					<p>

						<!-- wpm_levels -->
						<input type="hidden" name="WLOptions" value="wpm_levels">
						<input type="hidden" name="WLRequiredOptions" value="">
						<input type="hidden" name="WLSaveMessage" value="Membership Levels Updated">
						<input type="hidden" name="WishListMemberAction" value="SaveMembershipLevels">
						<a href="#" data-id="<?php echo $id?>" class="wpm_row_editor_close button-secondary"><?php _e('Cancel', 'wishlist-member') ?></a>
						<span class="spinner" style="float: left; position: absolute; margin-left: 58px;"></span><a href="#" data-id="<?php echo $id?>" class="wpm_row_editor_submit button-primary"><?php _e("Save", "wishlist-member")?></a>
					</p>
				</td>
			</tr>
			<?php /* advanced settings - END */ ?>
		</tbody>
	</table>
</form>
</td>
</tr>