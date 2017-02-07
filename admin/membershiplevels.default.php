<?php
/*
 * Membership Levels
 */

// retrieve pages for login redirection
$pages         = get_pages( 'exclude=' . implode( ',', $this->ExcludePages( array(), true ) ) );
$pages_options = '';
foreach ( ( array ) $pages AS $page ) {
	$pages_options.='<option value="' . $page->ID . '">' . $page->post_title . '</option>';
}

// sorting part 1
list($_GET['s'], $sortorder) = explode( ';', $_GET['s'] );
if ( $sortorder != 'd' ) {
	$sortorder = 'a';
}
$sortorderflip = ($sortorder == 'd') ? 'a' : 'd';
$roles         = $GLOBALS['wp_roles']->roles;
$caps          = array();
foreach ( ( array ) $roles AS $key => $role ) {
	if ( $role['capabilities']['level_10'] || $role['capabilities']['level_9'] || $role['capabilities']['level_8'] ) {
		unset( $roles[$key] );
	} else {
		list($roles[$key]) = explode( '|', $role['name'] );
		$caps[$key] = count( $role['capabilities'] );
	}
}
array_multisort( $caps, SORT_ASC, $roles );

$manage_content_url = $this->GetMenu( 'managecontent' );
$manage_content_url = $manage_content_url->URL;
?>
<p style="display: none"></p>
<table class="widefat wpm_nowrap" id="wpm_membership_levels">
	<thead>
		<tr>
			<th scope="col"  style="line-height:20px;width:280px"><a class="wpm_header_link<?php echo wlm_arrval( $_GET, 's' ) == 'n' ? ' wpm_header_sort' . $sortorder : ''; ?>" href="?<?php echo $this->QueryString( 's' ) ?>&s=n<?php echo wlm_arrval( $_GET, 's' ) == 'n' ? ';' . $sortorderflip : ''; ?>"><?php _e( 'Membership Level', 'wishlist-member' ); ?></a></th>
			<th scope="col"  style="line-height:20px;" colspan="2"><?php _e( 'Registration URL', 'wishlist-member' ); ?></th>
			<th scope="col"  style="line-height:20px;width:110px;"><a class="wpm_header_link<?php echo wlm_arrval( $_GET, 's' ) == 'c' ? ' wpm_header_sort' . $sortorder : ''; ?>" href="?<?php echo $this->QueryString( 's' ) ?>&s=c<?php echo wlm_arrval( $_GET, 's' ) == 'c' ? ';' . $sortorderflip : ''; ?>"><?php _e( 'Creation Date', 'wishlist-member' ); ?></a></th>
			<th scope="col"  style="line-height:20px;width:110px;">Actions</th>
		</tr>
	</thead>
	<tbody>
		<?php
		$prevlevels         = $prevurls           = array();
		ksort( $wpm_levels );
		$sortfield          = 'id';
		if ( wlm_arrval( $_GET, 's' ) == 'n' ) {
			$sortfield = 'name';
		} else if ( wlm_arrval( $_GET, 's' ) == 'c' ) {
			$sortfield = 'id';
		} else {
			$sortfield = 'levelOrder';
		}
		$this->SortLevels( $wpm_levels, $sortorder, $sortfield );
		foreach ( ( array ) $wpm_levels AS $id => $level ):
			if ( ! is_numeric( $id ) ) {
				continue;
			}
			$errlevel     = in_array( strtolower( $level['name'] ), $prevlevels );
			$errurl       = in_array( $level['url'], $prevurls );
			$prevlevels[] = strtolower( $level['name'] );
			$prevurls[]   = $level['url'];
			if ( $level[wpm_newid] == $id && ( ! trim( $level['name'] ) || ! trim( $level['url'] )) ) {
				unset( $wpm_levels[$id] );
				continue;
			}
			if ( $level['noexpire'] ) {
				unset( $level['expire'] );
				unset( $level['calendar'] );
			}
			?>
			<tr class="wpm_level_row" id="wpm_level_row-<?php echo $id ?>">
				<td class="wpm_row">
					<a class="row-title wpm_row_edit" href="javascript:void(0);" rel="<?php echo $id ?>">+ <?php echo esc_attr( $level['name'] ) ?></a>
				</td>
				<td width="1">
					<a class="wpm_regurl" href="<?php echo $registerurl ?>/<?php echo $level['url'] ?>" target="_blank" style="color:#000000"><?php echo $registerurl ?>/<?php echo $level['url'] ?></a>
				</td>
				<td>
					<a class="wlmClipButton" id="wlmClipButton-<?php echo $id ?>" data-clipboard-text="<?php echo $registerurl ?>/<?php echo $level['url'] ?>" href="javascript:;"><?php _e( 'Copy URL', 'wishlist-member' ); ?></a></div>
				</td>
				<td><?php echo date_i18n( 'm/d/Y', $id + $this->GMT ) ?></td>
				<td>
					<div class="actions">
						<a href="javascript:void(0);" rel="<?php echo $id ?>" class="row-edit wpm_row_edit">Edit</a> |
						<?php if ( empty( $level['count'] ) ): ?>
							<a href="#" rel="<?php
							if ( empty( $level['count'] ) )
								echo $id;
							else
								echo -1;
							?>" class="wpm_row_delete">Delete</a>
						   <?php else: ?>
							<span class="link-disabled">Delete</span>
							<?php echo $this->Tooltip( "membershiplevels-default-tooltips-cannot-delete" ); ?>
						<?php endif; ?>
					</div>
				</td>
			</tr>
			<?php
		endforeach;
		$this->SaveOption( 'wpm_levels', $wpm_levels );
		?>
	</tbody>
</table>

<form id="membership-levels-frm" method="post">
	<h2><?php _e( 'Add a New Membership Level', 'wishlist-member' ); ?></h2>
	<br />
	<!-- start the new membership -->
	<table class="widefat wpm_nowrap">
		<tr class="wlmEditRow">
			<td>
				<table class="widefat">
					<thead>
						<tr>
							<th scope="col"  style="line-height:20px;"><?php _e( 'Membership Level', 'wishlist-member' ); ?></th>
							<th scope="col"  style="line-height:20px;"><?php _e( 'Registration URL', 'wishlist-member' ); ?> <?php echo $this->Tooltip( "membershiplevels-default-tooltips-Registration-URL" ); ?></th>
							<th scope="col"  style="line-height:20px;"><?php _e( 'Redirects', 'wishlist-member' ); ?></th>
							<th scope="col"  style="line-height:20px;"><?php _e( 'Access to', 'wishlist-member' ); ?> <?php echo $this->Tooltip( "membershiplevels-default-tooltips-Access-to" ); ?> </th>
							<th scope="col" class="num"  nowrap style="line-height:18px;"><?php _e( 'Length of Subscription', 'wishlist-member' ); ?> <?php echo $this->Tooltip( "membershiplevels-default-tooltips-Length-of-Subscription" ); ?></th>
						</tr>
					</thead>
					<tbody>
						<tr class="<?php echo $alt ++ % 2 ? '' : 'alternate'; ?> wpm_level_row_editor" id="wpm_new_row">
							<td>
								<input type="hidden" name="wpm_levels[<?php echo $wpm_newid ?>][wpm_newid]" value="<?php echo $wpm_newid ?>" /><input type="text" name="wpm_levels[<?php echo $wpm_newid ?>][name]" size="20" placeholder="<?php _e( 'Level Name', 'wishlist-member' ); ?>" />
								<br />
								<br />
								   <a href="javascript:;" wlm-target="wpm_level_row_advanced_<?php echo $wpm_newid ?>" onclick="wpm_show_advanced(this);
			return false">[<span>+</span>] <?php _e( 'Advanced Settings', 'wishlist-member' ); ?></a>
							</td>
							<td>
								<div>
									<?php echo $registerurl ?>/<input type="text" name="wpm_levels[<?php echo $wpm_newid ?>][url]" value="<?php echo $newurl ?>" size="6" />
								</div>
								<label for="doclone" style="display:block;margin:5px 0 0 1px;"><input style="float:left;margin:1px 5px 0 0" type="checkbox" name="doclone" id="doclone" value="<?php echo $wpm_newid ?>" onclick="wpm_clone_level(this.form)" /> <a><?php _e( 'Copy an Existing Membership Level', 'wishlist-member' ); ?></a></label>
								<div style="margin:0 0 0 18px">
									<select name="clonefrom" style="width:200px" id="clonefrom" onchange="wpm_clone_level(this.form)">
										<option value="0">--- Select a Level ---</option>
										<?php foreach ( ( array ) $wpm_levels AS $key => $level ): ?>
											<option value="<?php echo $key ?>"><?php echo $level['name'] ?></option>
										<?php endforeach; ?>
									</select>
								</div>
							</td>
							<td>
								<?php _e( 'After Login', 'wishlist-member' ); ?><?php echo $this->Tooltip( "membershiplevels-default-tooltips-After-Login" ); ?><br />
								<select name="wpm_levels[<?php echo $wpm_newid ?>][loginredirect]" style="width:120px">
									<option value='---'>--- <?php _e( 'Default', 'wishlist-member' ); ?> ---</option>
									<option value=''><?php _e( 'Home Page', 'wishlist-member' ); ?></option>
									<?php echo $pages_options; ?>
								</select><br />
								<?php _e( 'After Registration', 'wishlist-member' ); ?><?php echo $this->Tooltip( "membershiplevels-default-tooltips-After-Registration" ); ?><br />
								<select name="wpm_levels[<?php echo $wpm_newid ?>][afterregredirect]" style="width:120px">
									<option value='---'>--- <?php _e( 'Default', 'wishlist-member' ); ?> ---</option>
									<option value=''><?php _e( 'Home Page', 'wishlist-member' ); ?></option>
									<?php echo $pages_options; ?>
								</select>
							</td>
							<td>
								<label><input type="checkbox" name="wpm_levels[<?php echo $wpm_newid ?>][allpages]" />
									<?php _e( 'All Pages', 'wishlist-member' ); ?></label><br />
								<label><input type="checkbox" name="wpm_levels[<?php echo $wpm_newid ?>][allcategories]" />
									<?php _e( 'All Categories', 'wishlist-member' ); ?></label><br />
								<label><input type="checkbox" name="wpm_levels[<?php echo $wpm_newid ?>][allposts]" />
									<?php _e( 'All Posts', 'wishlist-member' ); ?></label><br />
								<label><input type="checkbox" name="wpm_levels[<?php echo $wpm_newid ?>][allcomments]" />
									<?php _e( 'All Comments', 'wishlist-member' ); ?></label><br />
							</td>
							<td class="num"><input type="text" name="wpm_levels[<?php echo $wpm_newid ?>][expire]" size="3" /><select name="wpm_levels[<?php echo $wpm_newid ?>][calendar]">
									<option value="Days"><?php _e( 'Days', 'wishlist-member' ); ?></option>
									<option value="Weeks"><?php _e( 'Weeks', 'wishlist-member' ); ?></option>
									<option value="Months"><?php _e( 'Months', 'wishlist-member' ); ?></option>
									<option value="Years"><?php _e( 'Years', 'wishlist-member' ); ?></option>
								</select><br />
								<br />
								<label><input type="checkbox" name="wpm_levels[<?php echo $wpm_newid ?>][noexpire]" value="1" onclick="this.parentNode.parentNode.childNodes[0].disabled = this.parentNode.parentNode.childNodes[1].disabled = this.checked" />
									<?php _e( 'No Expiration Date', 'wishlist-member' ); ?></label>
							</td>
						</tr>
						<?php /* advanced settings for new levels - START */ ?>
						<tr class="wpm_level_row_editor_advanced" id="wpm_level_row_advanced_<?php echo $wpm_newid ?>" style="display:none">
							<td colspan="5">
								<table width="100%" class="MembershipLevelsAdvanced">
									<tr>
										<td style="width:310px;" class="first">
											<b>Registration Requirements</b> <?php echo $this->Tooltip( "membershiplevels-default-tooltips-regrequirements" ); ?><br />
											<label><input type="checkbox" name="wpm_levels[<?php echo $wpm_newid ?>][requirecaptcha]" value="1" /> <?php _e( 'Require Captcha Image on Registration Page', 'wishlist-member' ); ?></label>
											<?php echo $this->Tooltip( "membershiplevels-default-tooltips-recaptchasettings" ); ?>
											<br />
											<label><input type="checkbox" name="wpm_levels[<?php echo $wpm_newid ?>][requireemailconfirmation]" value="1" /> <?php _e( 'Require Email Confirmation After Registration', 'wishlist-member' ); ?></label>
											<?php echo $this->Tooltip( "membershiplevels-default-tooltips-requireemailconfirmation" ); ?>
											<br />
											<br />
											<?php _e( 'Require Admin Approval After Registration:', 'wishlist-member' ); ?>
											<br />
											<label><input type="checkbox" name="wpm_levels[<?php echo $wpm_newid ?>][requireadminapproval]" value="1" /> <?php _e( 'For Free Registrations', 'wishlist-member' ); ?></label>
											<?php echo $this->Tooltip( "membershiplevels-default-tooltips-requireadminapproval" ); ?>
											<br />
											<label><input type="checkbox" name="wpm_levels[<?php echo $wpm_newid ?>][requireadminapproval_integrations]" value="1" <?php echo $this->GetOption( 'admin_approval_shoppingcart_reg' ) ? '':'disabled="disabled"'; ?> /> <?php _e( 'For Shopping Cart Integrations', 'wishlist-member' ); ?></label>
											<?php if ( $this->GetOption( 'admin_approval_shoppingcart_reg' )  ): ?>
												<?php echo $this->Tooltip( "membershiplevels-default-tooltips-requireadminapproval-integrations" ); ?>
											<?php else: ?>
												<?php echo $this->Tooltip( "membershiplevels-default-tooltips-requireadminapproval-integrations-disabled" ); ?>
											<?php endif; ?>
										</td>
										<td style="width:215px;">
											<br />
											<label><input type="checkbox" name="wpm_levels[<?php echo $wpm_newid ?>][isfree]" value="1" /> <?php _e( 'Grant Continued Access', 'wishlist-member' ); ?></label>
											<?php echo $this->Tooltip( "membershiplevels-default-tooltips-Grant-Continued-Access" ); ?>
											<br />
											<label><input type="checkbox" name="wpm_levels[<?php echo $wpm_newid ?>][disableexistinglink]" value="1" /> <?php _e( 'Disable Existing Users Link', 'wishlist-member' ); ?></label>
											<?php echo $this->Tooltip( "membershiplevels-default-tooltips-disableexistinglink" ); ?>
											<br  />
											<label><input type="checkbox" name="wpm_levels[<?php echo $wpm_newid ?>][registrationdatereset]" value="1" /> <?php _e( 'Registration Date Reset', 'wishlist-member' ); ?></label>
											<?php echo $this->Tooltip( "membershiplevels-default-tooltips-registrationdatereset" ); ?>
											<br  />
											<label><input type="checkbox" name="wpm_levels[<?php echo $wpm_newid ?>][uncancelonregistration]" value="1" /> <?php _e( 'Un-cancel on Re-registration', 'wishlist-member' ); ?></label>
											<?php echo $this->Tooltip( "membershiplevels-default-tooltips-uncancelonregistration" ); ?>
											<br  />
											<label><input type="checkbox" name="wpm_levels[<?php echo $wpm_newid ?>][disableprefilledinfo]" value="1" /> <?php _e( 'Make pre-filled User info not editable', 'wishlist-member' ); ?></label>
											<?php echo $this->Tooltip( "membershiplevels-default-tooltips-disableprefilledinfo" ); ?>
										</td>
										<td style="width:220px;">
											<table>
												<tr>
													<td style="border:none" valign="middle"><?php _e( 'Role', 'wishlist-member' ); ?></td>
													<td style="border:none" valign="middle">
														<select name="wpm_levels[<?php echo $wpm_newid ?>][role]">
															<?php foreach ( ( array ) $roles AS $rolekey => $rolename ): ?>
																<option value="<?php echo $rolekey; ?>"><?php echo $rolename; ?></option>
															<?php endforeach; ?>
														</select><?php echo $this->Tooltip( "membershiplevels-default-tooltips-Role" ); ?>
													</td>
												</tr>
												<tr>
													<td style="border:none" valign="middle"><?php _e( 'Level Order', 'wishlist-member' ); ?></td>
													<td style="border:none" valign="middle"><!--2--><input type="text" name="wpm_levels[<?php echo $wpm_newid ?>][levelOrder]" size="4" value="<?php echo count( $wpm_levels ) ?>" /><?php echo $this->Tooltip( "membershiplevels-default-tooltips-levelorder" ); ?></td>
												</tr>
											</table>
										</td>
										<td>
											<b>Remove From</b> <?php echo $this->Tooltip( "membershiplevels-default-tooltips-removefrom" ); ?><br />
											<select style="width:100%" name="wpm_levels[<?php echo $wpm_newid; ?>][removeFromLevel][]" class="select2" multiple="multiple" placeholder="Select Level(s)...">
												<?php 
													foreach($wpm_levels AS $level_id => $level) {
														printf('<option value="%s">%s</option>', $level_id, $level['name']);
													}
												?>
											</select>
											<hr>
											<b>Add to</b> <?php echo $this->Tooltip( "membershiplevels-default-tooltips-addto" ); ?><br />
											<select style="width:100%" name="wpm_levels[<?php echo $wpm_newid; ?>][addToLevel][]" class="select2" multiple="multiple" placeholder="Select Level(s)...">
												<?php 
													foreach($wpm_levels AS $level_id => $level) {
														printf('<option value="%s">%s</option>', $level_id, $level['name']);
													}
												?>
											</select>
											<br  />
											<label><input type="checkbox" name="wpm_levels[<?php echo $wpm_newid ?>][inheritparent]" value="1" /> <?php _e( 'Inherit Parent Level status', 'wishlist-member' ); ?></label>
											<?php echo $this->Tooltip( "membershiplevels-default-tooltips-inheritparent" ); ?>
										</td>
									</tr>
									<tr>
										<td colspan="4" class="first">
											<div>
												<b><?php _e( 'Sales Page URL:', 'wishlist-member' ); ?></b> <i>(optional)</i>
												<input type="text" name="wpm_levels[<?php echo $wpm_newid ?>][salespage]" size="80" />
												<?php echo $this->Tooltip( "membershiplevels-default-tooltips-levelsalespage" ); ?>
											</div>
										</td>
									</tr>
								</table>
							</td>
						</tr>
					</tbody>
				</table>
			</td>
		</tr>
	</table>
	<p class="submit">
		<?php
		echo '<!-- ';
		$this->Option( 'wpm_levels' );
		echo ' -->';
		$this->Options();
		$this->RequiredOptions();
		?>
		<input type="hidden" name="WLSaveMessage" value="Membership Levels Updated" />
		<input type="hidden" name="WishListMemberAction" value="SaveMembershipLevels" />
		<input type="submit" class="button button-primary" value="<?php _e( 'Add New Level', 'wishlist-member' ); ?>" />
	</p>
</form>
<script> 
	jQuery("document").ready(function () {
		
		if (!jQuery('#doclone').is(':checked')){			
			jQuery("#clonefrom").attr('disabled', true);
		}
		
		jQuery('#doclone').click(function() {
        if(jQuery(this).is(':checked'))  {
            jQuery("#clonefrom").attr('disabled', false);
        } else{
        	jQuery('#clonefrom').attr('disabled', true);    
        }   	
    	});
	});
</script>
