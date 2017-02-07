<!-- start added by Andy & Mike -->
<!-- start Backup Settings -->
<h2><?php _e('Settings &raquo; Backup', 'wishlist-member'); ?></h2>
<!--<h2><?php _e('Backup/Restore/Reset WishList Member Settings', 'wishlist-member'); ?></h2>-->
<br>
<h3><?php _e('Backup WishList Member', 'wishlist-member'); ?></h3>
<blockquote>
	<p><?php _e('Include the following within a Backup of the current WishList Member settings:', 'wishlist-member'); ?></p>
	<form method="post" class="backup_setting">
		<blockquote>
			<div><label><input type="checkbox" name="backup_include_users" value="1" <?php $this->Checked($this->GetOption('backup_include_users'), 1); ?> /> <?php _e('Users', 'wishlist-member'); ?></label></div>
			<div><label><input type="checkbox" name="backup_include_posts" value="1" <?php $this->Checked($this->GetOption('backup_include_posts'), 1); ?> /> <?php _e('Content', 'wishlist-member'); ?></label></div>
		</blockquote>
		<input type="hidden" name="WishListMemberAction" value="BackupSettings" />
		<input class="button-secondary" type="submit" value="<?php _e('Create Backup', 'wishlist-member'); ?>" />
	</form>
</blockquote>
<hr />

<?php $listOfBackups = $this->Backup_ListAll(); ?>
<?php if (count($listOfBackups)): ?>
	<h3><?php _e('Restore Backup', 'wishlist-member'); ?></h3>
	<blockquote>
		<p><?php _e('Restore WishList Member settings from a Backup file.', 'wishlist-member'); ?></p>
		<table class="widefat">
			<thead>
				<tr>
					<th scope="col"><?php _e('Date', 'wishlist-member'); ?></th>
					<th scope="col"><?php _e('Contains', 'wishlist-member'); ?></th>
					<th scope="col"><?php _e('WishList Member Version', 'wishlist-member'); ?></th>
					<th scope="col"><?php _e('Restore', 'wishlist-member'); ?></th>
					<th scope="col"><?php _e('Download', 'wishlist-member'); ?></th>
					<th scope="col"><?php _e('Delete', 'wishlist-member'); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($listOfBackups AS $backup) { ?>
					<tr valign="top">
						<td style="vertical-align:middle; font-size:1em;">
							<?php echo $this->FormatDate($backup['date']); ?>
						</td>
						<td style="vertical-align:middle; font-size:1em;">
							<?php
							$contains = array(__('WishList Member Settings'));
							if ($backup['users'])
								$contains[] = __('Users');
							if ($backup['posts'])
								$contains[] = __('Content');

							echo implode(', ', $contains);
							?>
						</td>
						<td style="vertical-align:middle; font-size:1em;">
							<?php echo $backup['ver']; ?>
						</td>
						<td>
							<form method="post" action="">
								<input type="hidden" name="SettingsName" value="<?php echo $backup['full']; ?>" />
								<input type="hidden" name="WishListMemberAction" value="RestoreSettings" />
								<input class="restoreSettingSubmit button" type="submit" value="<?php _e('Restore', 'wishlist-member'); ?>" />
							</form>
						</td>
						<td>
							<form method="post" action="">
								<input type="hidden" name="SettingsName" value="<?php echo $backup['full']; ?>" />
								<input type="hidden" name="WishListMemberAction" value="ExportSettings" />
								<input class="button-secondary" type="submit" value="<?php _e('Download', 'wishlist-member'); ?> " />
							</form>
						</td>
						<td>
							<form method="post" action="">
								<input type="hidden" name="SettingsName" value="<?php echo $backup['full']; ?>" />
								<input type="hidden" name="WishListMemberAction" value="DeleteSettings" />
								<input class="deleteSettingSubmit button" type="submit" value="<?php _e('Delete', 'wishlist-member'); ?> " />
							</form>
						</td>
					</tr>
				<?php } ?>
			</tbody>
		</table>
	</blockquote>
	<hr />
<?php endif; ?>
<?php
	$maxfilesize = get_file_upload_max_size();
?>
<h3><?php _e('Restore Backup from File', 'wishlist-member'); ?></h3>
<blockquote>
	<p><?php _e('Restore your WishList Member settings from a backup file', 'wishlist-member'); ?></p>
	<form method="post" enctype="multipart/form-data">
		<input type="hidden" name="SettingsName" value="<?php echo $backup; ?>" />
		<input type="hidden" name="WishListMemberAction" value="ImportSettings" />
		<?php if ( $maxfilesize > 1 ) : ?>
			<input type="hidden" name="MAX_FILE_SIZE" value="<?php echo $maxfilesize; ?>">
		<?php endif; ?>
		<input type="file" name="ImportSettingsfile"   /><br />
		<blockquote>
			<label><input type="checkbox" name="backup_first" value="1" />
				<?php _e('Run Backup before Import', 'wishlist-member'); ?></label>
		</blockquote>
		<?php if ( $maxfilesize > 1 ) : ?>
			<p>Maximum file size allowed is <strong><?php echo number_format($maxfilesize); ?> KB</strong> only.</p>
		<?php endif; ?>
		<input class="button-secondary" type="submit" value="<?php _e('Upload and Restore WishList Member settings', 'wishlist-member'); ?> " />
	</form>
</blockquote>
<hr />

<h3><?php _e('Reset WishList Member Settings', 'wishlist-member'); ?></h3>
<blockquote>
	<p><?php _e('Warning: This will remove all current WishList Member settings and will restore all the default settings.  <b>All current WishList Member settings will be lost!</b>', 'wishlist-member'); ?></p>
	<form method="post" id="formResetSettingSubmit">
		<blockquote>
			<label for="resetSettingConfirm"><input type="checkbox" name="resetSettingConfirm" id="resetSettingConfirm" />
				<?php _e('I want to reset all current WishList Member settings.', 'wishlist-member'); ?></label>
		</blockquote>
		<input type="hidden" name="WishListMemberAction" value="ResetSettings" />
		<input class="button-secondary" type="submit" id="resetSettingSubmit" value="<?php _e('Reset Settings', 'wishlist-member'); ?>" />
	</form>
</blockquote>
<div id="dialogResetConfirm1" title="<?php _e('Reset your WishList Member Settings?', 'wishlist-member'); ?>">
	<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>
		<?php _e('Please confirm to remove all your current WishList Member settings.', 'wishlist-member'); ?></p>
</div>

<div id="dialogResetConfirm2" title="<?php _e('Reset your WishList Member Settings?', 'wishlist-member'); ?>">
	<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>
		<?php _e('All levels and WishList Member settings will be permanently deleted and cannot be recovered. Are you sure?', 'wishlist-member'); ?></p>
</div>

<div id="dialogRestoreConfirm" title="<?php _e('Restore WishList Member Settings?', 'wishlist-member'); ?>">
	<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 100px 0;"></span>
		<?php _e('Restoring to a previous backup will replace your current configuration.  This will also include users, pages, posts, and comments if they are included in the backup.', 'wishlist-member'); ?><br />
		<br /><?php _e('Are you sure you want to continue?', 'wishlist-member'); ?></p>
</div>

<div id="dialogDeleteConfirm" title="<?php _e('Restore WishList Member Settings?', 'wishlist-member'); ?>">
	<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>
		<?php _e('Are you sure you want to delete this backup?', 'wishlist-member'); ?></p>
</div>


<script type="text/javascript">
	jQuery(document).ready(function($) {

		$("#dialogResetConfirm1").dialog({
			autoOpen: false,
			bgiframe: true,
			resizable: false,
			width: 400,
			modal: true,
			buttons: {
				'<?php _e('Ok', 'wishlist-member'); ?>': function() {
					$(this).dialog('close');
				}
			}
		});

		$("#dialogResetConfirm2").dialog({
			autoOpen: false,
			bgiframe: true,
			resizable: false,
			width: 400,
			modal: true,
			overlay: {
				backgroundColor: '#000',
				opacity: 0.5
			},
			buttons: {
				'<?php _e('Reset WishList Member Settings to default', 'wishlist-member'); ?>': function() {
					$('#formResetSettingSubmit').submit();
				},
				Cancel: function() {
					$(this).dialog('close');
				}
			}
		});


		$('#resetSettingSubmit').bind('click', function() {
			if ($('#resetSettingConfirm').attr("checked") == false) {
				$("#dialogResetConfirm1").dialog("open");
				return false;
			} else {
				$("#dialogResetConfirm2").dialog("open");
			}
			return false;
		});

		$("#dialogRestoreConfirm").dialog({
			autoOpen: false,
			bgiframe: true,
			resizable: false,
			width: 400,
			modal: true,
			buttons: {
				'<?php _e('Restore Settings', 'wishlist-member'); ?>': function() {
					$('#dialogRestoreConfirm').data('form').submit();
				},
				Cancel: function() {
					$(this).dialog('close');
				}
			}
		});

		$('.restoreSettingSubmit').bind('click', function() {
			$('#dialogRestoreConfirm').data('form', this.form);
			$('#dialogRestoreConfirm').dialog('open');
			return false;
		});

		$("#dialogDeleteConfirm").dialog({
			autoOpen: false,
			bgiframe: true,
			resizable: false,
			width: 400,
			modal: false,
			buttons: {
				'<?php _e('Delete Backup', 'wishlist-member'); ?>': function() {
					$('#dialogDeleteConfirm').data('form').submit();
				},
				Cancel: function() {
					$(this).dialog('close');
				}
			}
		});

		$('.deleteSettingSubmit').bind('click', function() {
			$('#dialogDeleteConfirm').data('form', this.form);
			$('#dialogDeleteConfirm').dialog('open');
			return false;
		});

	});
</script>

<?php
function get_file_upload_max_size() {
    $max_size   = parse_size(ini_get('post_max_size'));
    $upload_max = parse_size(ini_get('upload_max_filesize'));
    if ( $upload_max > 0 && $upload_max < $max_size ) {
    	$max_size = $upload_max;
    }
    return $max_size;
}
function parse_size( $size ) {
  $unit = preg_replace('/[^bkmgtpezy]/i', '', $size); // Remove the non-unit characters from the size.
  $size = preg_replace('/[^0-9\.]/', '', $size); // Remove the non-numeric characters from the size.
  if ( $unit ) {
    return round( $size * pow(1024, stripos('bkmgtpezy', $unit[0] ) ) );
  } else {
    return round( $size );
  }
}
?>

<!-- end Backup Settings -->
<!-- end added by Andy -->