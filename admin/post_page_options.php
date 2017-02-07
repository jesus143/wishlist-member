<?php
$ptype = $post->post_type;
if ( ! $ptype ) {
	$ptype = 'post';
}
if ( $this->PostTypeEnabled( $ptype ) ) :
	?>
	<script type="text/javascript">
		jQuery(function($) {
			$('.wpm_toggleAllLevels').on('change', function(ev) {
				$('.allLevels').not('.allindex').prop('checked', $(this).prop('checked'));
			});

			var wlm_radioshack = '';

			function wlm_save_radios() {
				wlm_radioshack = $('input[type=radio][name=wpm_protect]:checked').val();
			}
			function wlm_restore_radios() {
				$('input[type=radio][name=wpm_protect][value=' + wlm_radioshack + ']').attr('checked', 'checked');
			}

			$('.meta-box-sortables').sortable({beforeStop: wlm_save_radios, stop: wlm_restore_radios});

			$('.wlm_inherit_protection').on('change', function(ev) {
				var prop = $(this).prop('checked');
				$('.wlm_inherit_toggle input[type=radio],.wlm_inherit_toggle input[type=checkbox]').not('.allindex').prop('disabled', prop);
			});

			$('.wlm_inherit_protection').trigger('change');
		});
	</script>

	<style type="text/css">
		.wlm_levels{
			float:left;
		}
		.wlm_levels li{
			margin: 0;
			padding: 0;
		}
		.wlm_levels li.first{
			padding: 0 0 2px 0;
			margin: 0 0 2px 0;
			border-bottom:1px solid #ddd;
		}
		#wpm_options_div h2{
			margin:0;
		}
		#wpm_options_div blockquote{
			margin-top:0;
		}

		.user_post_exists td{
			text-decoration:line-through;
		}
		.wishlist_member_system_page_input,
		.wishlist_member_system_page_select,
		.wishlist_member_system_page_select option{
			 
			width:300px;
			overflow:hidden;
 
			
		}
	</style>
	<div class="inside">
		<!-- Content Protection -->
		<h2><?php _e( 'Content Protection', 'wishlist-member' ); ?></h2>
		<blockquote>
			<?php if ( ! is_post_type_hierarchical( $ptype ) OR $post->post_parent OR $wlm_inherit_protection ): ?>
				<p>
					<label><input type="checkbox" class="wlm_inherit_protection" <?php if ( $wlm_inherit_protection ) echo 'checked="checked"' ?> name="wlm_inherit_protection" value="Y" /> <?php _e( 'Inherit protection settings of parent', 'wishlist-member' ); ?></label>
				</p>
			<?php endif; ?>
			<div>
				<?php _e( 'Do you want to protect this content?', 'wishlist-member' ); ?>
			</div>
			<div style="margin-left:1.5em">
				<ul class="wlm_levels wlm_inherit_toggle">
					<li><label><input type="radio" name="wpm_protect" value="N"<?php echo ! $wpm_protect ? ' checked="checked"' : ''; ?> /> <?php _e( 'No, do not protect this content (non-members can access it)', 'wishlist-member' ); ?></label></li>
					<li><label><input type="radio" name="wpm_protect" value="Y"<?php echo $wpm_protect ? ' checked="checked"' : ''; ?> /> <?php _e( 'Yes, protect this content (members only)', 'wishlist-member' ); ?></label></li>
				</ul>
			</div>
			<br clear="all" />
		</blockquote>

		<!-- Membership Levels -->
		<?php if ( count( $wpm_levels ) ): ?>
			<h2><?php _e( 'Membership Levels', 'wishlist-member' ); ?></h2>
			<blockquote>
				<div><?php _e( 'Select the membership level that can access this content:', 'wishlist-member' ); ?></div>
				<div style="margin-left:1.5em">
					<ul class="wlm_levels wlm_inherit_toggle">
						<li class="first"><label><input type="checkbox" class="wpm_toggleAllLevels" /> <?php _e( 'Select/Unselect All Levels', 'wishlist-member' ); ?></label></li>
						<?php foreach ( ( array ) $wpm_levels AS $id => $level ): ?>
							<li>
								<label>
									<input 
										class="allLevels <?php if ( $level[$allindex] ) echo 'allindex' ?>" 
										type="checkbox" 
										name="wpm_access[<?php echo $id ?>]"
										<?php
										echo (isset( $wpm_access[$id] ) || ! empty( $level[$allindex] )) ? ' checked="true"' : '';
										echo ( ! empty( $level[$allindex] )) ? ' disabled="true"' : '';
										?>
										value="<?php echo $post->ID ?>" 
										/>
										<?php echo $level['name'] ?>
								</label>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
			</blockquote>
			<br clear="all" />
		<?php endif; ?>

		<!-- Pay Per Post Access -->
		<?php if ( $post->post_type != 'attachment' ) : ?>
			<h2><?php _e( 'Per User Access', 'wishlist-member' ); ?></h2>
			<blockquote>
				<div><?php _e( 'Do you want to enable Pay Per Post for this content?', 'wishlist-member' ); ?></div>
				<div style="margin-left:1.5em">
					<ul class="wlm_levels">
						<li><label><input onclick="if (this.checked)
											jQuery('#wlm_payperpost_enable, #wlm_payperpost_freereg, #wlm_user_search_window, #wlm_individual_user_post_access_div').css('display', 'none');" type="radio" name="wlm_payperpost" value="N"<?php echo ! $wlm_payperpost ? ' checked="checked"' : ''; ?> /> <?php _e( 'No, do not enable Pay Per Post for this content', 'wishlist-member' ); ?></label></li>
						<li><label><input onclick="if (this.checked)
											jQuery('#wlm_payperpost_enable, #wlm_payperpost_freereg, #wlm_user_search_window, #wlm_individual_user_post_access_div').css('display', 'block');" type="radio" name="wlm_payperpost" value="Y"<?php echo $wlm_payperpost ? ' checked="checked"' : ''; ?> /> <?php _e( 'Yes, enable Pay Per Post for this content', 'wishlist-member' ); ?></label></li>
					</ul>
				</div>
				<div id="wlm_payperpost_enable" style="display:<?php echo $wlm_payperpost ? 'block' : 'none'; ?>">
					<div style="margin-left:1.5em;clear:both">
						<p><strong><?php _e( 'Shopping Cart Integration', 'wishlist-member' ); ?></strong>
							<br>
						<p><?php _e( 'SKU:', 'wishlist-member' ); ?>
							<?php $integration_menu = $this->GetMenu( 'integration' ); ?>
							<strong>payperpost-<?php echo $post->ID; ?></strong></p>
						<p><a href="admin.php<?php echo $integration_menu->URL; ?>" target="_blank"><?php _e( 'Click here for integration instructions', 'wishlist-member' ); ?></a></p>
					</div>
					<br>
				</div>
				<div id="wlm_payperpost_freereg" style="display:<?php echo $wlm_payperpost ? 'block' : 'none'; ?>">
					<div><?php _e( 'Do you want to allow Free Registration for this content?', 'wishlist-member' ); ?></div>
					<div style="margin-left:1.5em">
						<ul class="wlm_levels">
							<li><label><input onclick="if (this.checked)
												jQuery('#wlm_payperpost_free_url').css('display', 'none');" type="radio" name="wlm_payperpost_free" value="N"<?php echo ! $wlm_payperpost_free ? ' checked="checked"' : ''; ?> /> <?php _e( 'No, do not allow free registration for this content', 'wishlist-member' ); ?></label></li>
							<li><label><input onclick="if (this.checked)
												jQuery('#wlm_payperpost_free_url').css('display', 'block');" type="radio" name="wlm_payperpost_free" value="Y"<?php echo $wlm_payperpost_free ? ' checked="checked"' : ''; ?> /> <?php _e( 'Yes, enable free registration for this content', 'wishlist-member' ); ?></label></li>
						</ul>
						<div id="wlm_payperpost_free_url" style="clear:both; margin-left:1.5em; display:<?php echo $wlm_payperpost_free ? 'block' : 'none'; ?>">
							<div><strong><?php _e( 'Free Registration URL:', 'wishlist-membner' ); ?></strong>
								<br>
								<?php
								echo WLMREGISTERURL;
								echo '/payperpost/' . $post->ID;
								?>
							</div>
						</div>
					</div>
				</div>
				<br clear="all" />
				<?php
				$user_access      = array_keys( $wpm_access );
				foreach ( $user_access AS $key => $user ) {
					if ( substr( $user, 0, 2 ) != 'U-' ) {
						unset( $user_access[$key] );
					} else {
						$user = get_userdata( substr( $user, 2 ) );
						if ( $user ) {
							$name = trim( $user->user_firstname . ' ' . $user->user_lastname );
							if ( ! $name ) {
								$name = $user->user_login;
							}
							$user_access[$key] = array(
								$user->ID,
								$name,
								$user->user_login,
								$user->user_email
							);
						} else {
							unset( $user_access[$key] );
						}
					}
				}
				$tbl_display = (count( $user_access ) && $wlm_payperpost) ? '' : 'display:none';
				?>
				<div id="wlm_individual_user_post_access_div" style="<?php echo $tbl_display; ?>">
					<p><strong><?php _e( 'The following users have specific access to this post', 'wishlist-member' ); ?></strong></p>
					<table class="widefat" id="wlm_individual_user_post_access">
						<thead>
							<tr>
								<th class="num"><?php _e( 'ID', 'wishlist-member' ); ?></th>
								<th><?php _e( 'Name', 'wishlist-member' ); ?></th>
								<th><?php _e( 'Username', 'wishlist-member' ); ?></th>
								<th><?php _e( 'Email', 'wishlist-member' ); ?></th>
								<th class="num"><?php _e( 'Remove', 'wishlist-member' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php
							$alternate   = '.';
							foreach ( $user_access AS $user ):
								$alternate = $alternate ? '' : ' alternate';
								echo vsprintf( '<tr class="user_%1$d' . $alternate . '"><td class="num">%1$d</td><td>%2$s</td><td>%3$s</td><td>%4$s</td><td class="num"><input type="checkbox" name="remove_user_post_access[]" value="U-%1$d" /><input type="hidden" name="user_post_access[]" value="U-%1$d" /></td></tr>', $user );
							endforeach;
							?>
						</tbody>
					</table>
				</div>
				<div id="wlm_user_search_window" style="display:<?php echo $wlm_payperpost ? 'block' : 'none'; ?>">
					<div>
						<p><?php _e( 'Add users to this post using the search form below', 'wishlist-member' ); ?></p>
						<form onsubmit="return false;" method="GET">
							<select name="search_by" id="wlm_user_search_by" onchange="jQuery('.wlm_search_types_field').hide();
											jQuery('#wlm_search_' + jQuery(this).val()).show()">
								<option value="by_user"><?php _e( 'Search by User', 'wishlist-member' ); ?></option>
								<option value="by_level"><?php _e( 'Search by Level', 'wishlist-member' ); ?></option>
							</select>
							<span id="wlm_search_by_user" class="wlm_search_types_field">
								<input type="search" id="user_search_input" placeholder="Name, Username, Email" style="width:25em;height:2.5em" value="" />
							</span>
							<span id="wlm_search_by_level" style="display:none" class="wlm_search_types_field">
								<select id="wlm_level_search_input" name="search_level" multiple="multiple" style="width:25em">
									<?php foreach ( $wpm_levels AS $id => $level ) : ?>
										<option value="<?php echo $id; ?>"><?php echo $level['name']; ?></option>
									<?php endforeach; ?>
								</select>
							</span>
							<input onclick="wlm_user_search('#', '<?php _e( 'Click a user to grant access to this post', 'wishlist-member' ); ?>', update_user_links)" type="button" class="button-secondary" value="<?php _e( 'Search', 'wishlist-member' ); ?>" />
						</form>
						<div id="wlm_user_search_ajax_output"></div>
					</div>
				</div>
			</blockquote>
			<script type="text/javascript">
				function update_user_links() {
					var links = jQuery('#wlm_user_search_ajax_output a');
					var tbl = jQuery('#wlm_individual_user_post_access');
					jQuery.each(links, function(index, link) {
						link = jQuery(link);
						var id = link.attr('href').split('#');
						id = id[1];
						if (id && tbl.find('tr.user_' + id).length) {
							link.parents('tr').addClass('user_post_exists');
							link.parents('td').html('');
						}
					});
					links.click(function() {
						var url = jQuery(this).attr('href').split('#');
						var id = url[1];
						if (id) {
							var tbl = jQuery('#wlm_individual_user_post_access');
							var parent = jQuery(this).parents('tr');
							tbl.append(parent);
							var links = parent.find('td');
							jQuery.each(links, function(index, td) {
								td.innerHTML = td.innerText;
							});
							if (parent.prev().hasClass('alternate')) {
								parent.removeClass('alternate');
							} else {
								parent.addClass('alternate');
							}
							parent.find('td.select_link').remove();
							parent.append('<td class="num"><input type="checkbox" name="remove_user_post_access[]" value="U-' + id + '" /><input type="hidden" name="user_post_access[]" value="U-' + id + '" /></td>');
							jQuery('#wlm_individual_user_post_access_div').css('display', '');
						}
						return false;
					});
				}
				;
				jQuery('#user_search_input').keypress(function(event) {
					if (event.which == 13) {
						event.preventDefault();
					}
				});
				jQuery('#wlm_level_search_input').select2();
			</script>
		<?php endif; ?>
		<?php if ( $post->post_type != 'attachment' ) : ?>
			<!-- System Pages -->
			<hr>
			<h2>Specific System Pages</h2>
			<?php
			$option_names = array(
				"non_members_error_page_internal"     => "non_members_error_page_internal_" . $post->ID,
				"non_members_error_page"              => "non_members_error_page_" . $post->ID,
				"wrong_level_error_page_internal"     => "wrong_level_error_page_internal_" . $post->ID,
				"wrong_level_error_page"              => "wrong_level_error_page_" . $post->ID,
				"membership_cancelled_internal"       => "membership_cancelled_internal_" . $post->ID,
				"membership_cancelled"                => "membership_cancelled_" . $post->ID,
				"membership_expired_internal"         => "membership_expired_internal_" . $post->ID,
				"membership_expired"                  => "membership_expired_" . $post->ID,
				"membership_forapproval_internal"     => "membership_forapproval_internal_" . $post->ID,
				"membership_forapproval"              => "membership_forapproval_" . $post->ID,
				"membership_forconfirmation_internal" => "membership_forconfirmation_internal_" . $post->ID,
				"membership_forconfirmation"          => "membership_forconfirmation_" . $post->ID,
			);

			$non_members_error_page_internal     = $this->GetOption( $option_names['non_members_error_page_internal'] );
			$non_members_error_page              = $this->GetOption( $option_names['non_members_error_page'] );
			$wrong_level_error_page_internal     = $this->GetOption( $option_names['wrong_level_error_page_internal'] );
			$wrong_level_error_page              = $this->GetOption( $option_names['wrong_level_error_page'] );
			$membership_cancelled_internal       = $this->GetOption( $option_names['membership_cancelled_internal'] );
			$membership_cancelled                = $this->GetOption( $option_names['membership_cancelled'] );
			$membership_expired_internal         = $this->GetOption( $option_names['membership_expired_internal'] );
			$membership_expired                  = $this->GetOption( $option_names['membership_expired'] );
			$membership_forapproval_internal     = $this->GetOption( $option_names['membership_forapproval_internal'] );
			$membership_forapproval              = $this->GetOption( $option_names['membership_forapproval'] );
			$membership_forconfirmation_internal = $this->GetOption( $option_names['membership_forconfirmation_internal'] );
			$membership_forconfirmation          = $this->GetOption( $option_names['membership_forconfirmation'] );
			?>
			<p><?php _e( 'Please specify the error pages that people will see when they try to access this post:', 'wishlist-member' ); ?></p>
			<?php $pages                               = get_pages( 'exclude=' . implode( ',', $this->ExcludePages( array(), true ) ) ); ?>
			<table class="form-table">
				<tr valign="top">
					<th scope="row" style="border:none"><?php _e( 'Non-Members:', 'wishlist-member' ); ?></th>
					<td style="border:none">
				<select class="wishlist_member_system_page_select"   name="<?php echo $option_names['non_members_error_page_internal']; ?>" onchange="document.getElementsByName('<?php echo $option_names['non_members_error_page']; ?>')[0].disabled = this.selectedIndex > 0">
							<option value="0"><?php _e( 'Enter an external URL below', 'wishlist-member' ); ?></option>
							<?php foreach ( $pages AS $page ): ?>
								<option value="<?php echo $page->ID ?>" <?php echo $non_members_error_page_internal == $page->ID ? 'selected="selected"' : ""; ?>><?php echo $page->post_title ?></option>
							<?php endforeach; ?>
						</select>
						<br />
				<input<?php if ($non_members_error_page_internal) echo ' disabled="true"'; ?> type="text" name="<?php echo $option_names['non_members_error_page']; ?>" value="<?php echo $non_members_error_page; ?>" class="wishlist_member_system_page_input" />
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><?php _e( 'Wrong Membership Level:', 'wishlist-member' ); ?></th>
					<td>
				<select class="wishlist_member_system_page_select" name="<?php echo $option_names['wrong_level_error_page_internal']; ?>" onchange="document.getElementsByName('<?php echo $option_names['wrong_level_error_page']; ?>')[0].disabled = this.selectedIndex > 0">
							<option value="0"><?php _e( 'Enter an external URL below', 'wishlist-member' ); ?></option>
							<?php foreach ( $pages AS $page ): ?>
								<option value="<?php echo $page->ID ?>" <?php echo $wrong_level_error_page_internal == $page->ID ? 'selected="selected"' : ""; ?>><?php echo $page->post_title ?></option>
							<?php endforeach; ?>
						</select>
						<br />
				<input<?php if ($wrong_level_error_page_internal) echo ' disabled="true"'; ?> type="text" name="<?php echo $option_names['wrong_level_error_page']; ?>" value="<?php echo $wrong_level_error_page; ?>" class="wishlist_member_system_page_input" />
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><?php _e( 'Membership Cancelled:', 'wishlist-member' ); ?></th>
					<td>
				<select class="wishlist_member_system_page_select" name="<?php echo $option_names['membership_cancelled_internal']; ?>" onchange="document.getElementsByName('<?php echo $option_names['membership_cancelled']; ?>')[0].disabled = this.selectedIndex > 0">
							<option value="0"><?php _e( 'Enter an external URL below', 'wishlist-member' ); ?></option>
							<?php foreach ( $pages AS $page ): ?>
								<option value="<?php echo $page->ID ?>" <?php echo $membership_cancelled_internal == $page->ID ? 'selected="selected"' : ""; ?>><?php echo $page->post_title ?></option>
							<?php endforeach; ?>
						</select>
						<br />
				<input<?php if (membership_cancelled_internal) echo ' disabled="true"'; ?> type="text" name="<?php echo $option_names['membership_cancelled']; ?>" value="<?php echo $membership_cancelled; ?>" class="wishlist_member_system_page_input" />
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><?php _e( 'Membership Expired:', 'wishlist-member' ); ?></th>
					<td>
				<select class="wishlist_member_system_page_select" name="<?php echo $option_names['membership_expired_internal']; ?>" onchange="document.getElementsByName('<?php echo $option_names['membership_expired']; ?>')[0].disabled = this.selectedIndex > 0">
							<option value="0"><?php _e( 'Enter an external URL below', 'wishlist-member' ); ?></option>
							<?php foreach ( $pages AS $page ): ?>
								<option value="<?php echo $page->ID ?>" <?php echo $membership_expired_internal == $page->ID ? 'selected="selected"' : ""; ?>><?php echo $page->post_title ?></option>
							<?php endforeach; ?>
						</select>
						<br />
				<input<?php if ($membership_expired_internal) echo ' disabled="true"'; ?> type="text" name="<?php echo $option_names['membership_expired']; ?>" value="<?php echo $membership_expired; ?>" class="wishlist_member_system_page_input" />
					</td>
				</tr>

				<tr valign="top">
					<th scope="row"><?php _e( 'Membership For Approval:', 'wishlist-member' ); ?></th>
					<td>
				<select class="wishlist_member_system_page_select" name="<?php echo $option_names['membership_forapproval_internal']; ?>" onchange="document.getElementsByName('<?php echo $option_names['membership_forapproval']; ?>')[0].disabled = this.selectedIndex > 0">
							<option value="0"><?php _e( 'Enter an external URL below', 'wishlist-member' ); ?></option>
							<?php foreach ( $pages AS $page ): ?>
								<option value="<?php echo $page->ID ?>" <?php echo $membership_forapproval_internal == $page->ID ? 'selected="selected"' : ""; ?>><?php echo $page->post_title ?></option>
							<?php endforeach; ?>
						</select>
						<br />
				<input<?php if ($membership_forapproval_internal) echo ' disabled="true"'; ?> type="text" name="<?php echo $option_names['membership_forapproval']; ?>" value="<?php echo $membership_forapproval; ?>" class="wishlist_member_system_page_input" />
					</td>
				</tr>

				<tr valign="top">
					<th scope="row"><?php _e( 'Membership For Confirmation:', 'wishlist-member' ); ?></th>
					<td>
				<select class="wishlist_member_system_page_select" name="<?php echo $option_names['membership_forconfirmation_internal']; ?>" onchange="document.getElementsByName('<?php echo $option_names['membership_forconfirmation']; ?>')[0].disabled = this.selectedIndex > 0">
							<option value="0"><?php _e( 'Enter an external URL below', 'wishlist-member' ); ?></option>
							<?php foreach ( $pages AS $page ): ?>
						<option   value="<?php echo $page->ID ?>" <?php echo $membership_forconfirmation_internal == $page->ID ? 'selected="selected"' : ""; ?>><?php echo $page->post_title ?></option>
							<?php endforeach; ?>
						</select>
						<br />
				<input<?php if ($membership_forconfirmation_internal) echo ' disabled="true"'; ?> type="text" name="<?php echo $option_names['membership_forconfirmation']; ?>" value="<?php echo $membership_forconfirmation; ?>" class="wishlist_member_system_page_input" />
					</td>
				</tr>
			</table>
		<?php endif; ?>
		<br clear="all">
	</div>
<?php else: ?>
	Content Protection is disabled for this Post Type. <a target="_blank" href="<?php echo admin_url("admin.php"); ?>?page=WishListMember&wl=settings&mode2=customposttypes">Click here to enable it.</a>
<?php endif; ?>
