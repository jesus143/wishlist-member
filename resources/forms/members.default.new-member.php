<?php if ($_POST) extract($_POST); ?>
<div class="media-modal wp-core-ui media-modal-small" style="display:none;" id="new-member-box">
	<a class="media-modal-close" href="javascript:void(0)" title="Close"><span class="media-modal-icon"></span></a>
	<div class="media-modal-content">
		<div class="media-frame wp-core-ui">
			<form method="post" action="?<?php echo $this->QueryString(); ?>">
			<div class="media-frame-title"><h1>New Member</h1></div>
			<div class="media-frame-router">
				<div class="media-router">
					<a href="#new-member" class="media-menu-item active">New Member</a>
				</div>
			</div>
			<div class="media-frame-content">
				<div id="new-member" class="panel">
					<?php if(wlm_arrval($_POST, 'err')): ?>
						<div class="media-frame-error"> <p><?php echo wlm_arrval($_POST, 'err')?></p></div>
					<?php endif; ?>
					<input type="hidden" name="WishListMemberAction" value="WPMRegister" />
					<table class="form-table">
						<tr valign="top">
							<th scope="col" class="label">
								<span class="alignleft"><label><?php _e('Username', 'wishlist-member'); ?></label></span>
								<span class="alignright"><abbr title="required" class="required">*</abbr></span>
							</th>
							<td class="field"><input type="text" name="username" value="<?php echo $username ?>" <?php echo 'autocomplete="off"'; ?> /></td>
						</tr>
						<tr valign="top">
							<th scope="col" class="label">
								<span class="alignleft"><label><?php _e('First Name', 'wishlist-member'); ?></label></span>
								<span class="alignright"><abbr title="required" class="required">*</abbr></span>
							</th>
							<td class="field"><input type="text" name="firstname" value="<?php echo $firstname ?>"  <?php echo 'autocomplete="off"'; ?> /></td>
						</tr>
						<tr valign="top">
							<th scope="col" class="label">
								<span class="alignleft"><label><?php _e('Last Name', 'wishlist-member'); ?></label></span>
								<span class="alignright"><abbr title="required" class="required">*</abbr></span>
							</th>
							<td class="field"><input type="text" name="lastname" value="<?php echo $lastname ?>" <?php echo 'autocomplete="off"'; ?> /></td>
						</tr>
						<tr valign="top">
							<th scope="col" class="label">
								<span class="alignleft"><label><?php _e('Email', 'wishlist-member'); ?></label></span>
								<span class="alignright"><abbr title="required" class="required">*</abbr></span>
							</th>
							<td class="field"><input type="text" name="email" value="<?php echo $email ?>" <?php echo 'autocomplete="off"'; ?> /></td>
						</tr>
						<tr valign="top">
							<th scope="col" class="label">
								<span class="alignleft"><label><?php _e('Password (twice)', 'wishlist-member'); ?></label></span>
								<span class="alignright"><abbr title="required" class="required">*</abbr></span>
							</th>
							<td class="field"><input type="password" name="password1"  <?php echo 'autocomplete="off"'; ?> /><br /><input type="password" name="password2"  <?php echo 'autocomplete="off"'; ?> /></td>
						</tr>
						<tr valign="top">
							<th scope="col" class="label">
								<span class="alignleft"><label><?php _e('Membership Level', 'wishlist-member'); ?></label></span>
								<span class="alignright"><abbr title="required" class="required">*</abbr></span>
							</th>
							<td class="field">
								<select name="wpm_id" id="wpm_id" style="width: 150px;">
									<?php foreach ((array) $wpm_levels AS $id => $level): ?>
										<option value="<?php echo $id ?>"<?php echo ($id == $wpm_id) ? ' selected="true"' : ''; ?>><?php echo $level['name'] ?></option>
									<?php endforeach; ?>
								</select>
							</td>
						</tr>
					</table>
				</div>
			</div>
			<div class="media-frame-toolbar">
				<div class="media-toolbar">
					<div class="media-toolbar-primary">
						<input type="submit" class="button media-button button-primary button-large" value="<?php _e("Add Member", "wishlist-member")?>" />
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
	var autoopen = false;
	<?php if (wlm_arrval($_POST, 'WishListMemberAction') == 'WPMRegister' && wlm_arrval($_POST, 'err')): ?>
	autoopen = true;
	<?php endif; ?>
	$('#new-member-box').WishListLightBox({trigger: $('.add-new-h2'), 'autoopen': autoopen});
});
</script>
