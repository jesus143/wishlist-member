<h2><?php _e('Settings &raquo; Import/Export', 'wishlist-member'); ?></h2>
<p><?php _e('Import/Export WishList Member Settings from one site to another.', 'wishlist-member'); ?><?php echo $this->Tooltip("settings-import-export-desc"); ?>
</p>
<blockquote>
	<form method="post" enctype="multipart/form-data">
		<h2 style="font-size:18px;width:100%;border:none;"><?php _e('Select the WishList Member Settings to Import:', 'wishlist-member'); ?><?php echo $this->Tooltip("settings-import-desc"); ?></h2><br />
		<?php
		if (isset($_POST['ImportSettings'])) {
			echo "<blockquote>";
			$this->RestoreSettingsFromFile();
			echo "</blockquote><br />";
		}
		?>
		<input type="hidden" name="MAX_FILE_SIZE" value="2000000">
		<input type="file" name="Settingsfile"   />
		<input type="hidden" name="WishListMemberAction" id="WishListMemberAction" value="RestoreSettingsFromFile" /><?php echo $this->Tooltip("settings-import-file"); ?><br /><br />
		<input class="button-secondary" type="submit" id="ImportSettings" name="ImportSettings" value="<?php _e('Import Settings', 'wishlist-member'); ?>" />
	</form>
</blockquote>
<br /><hr />
<blockquote>
	<form method="post">
		<h2 style="font-size:18px;width:100%;border:none;"><?php _e('Select the WishList Member Settings to Export:', 'wishlist-member'); ?><?php echo $this->Tooltip("settings-export-desc"); ?></h2>
		<blockquote>
			<div><label><input type="checkbox" name="export_configurations" value="1" /> <?php _e('Configurations', 'wishlist-member'); ?></label><?php echo $this->Tooltip("settings-export-config"); ?></div>
			<div><label><input type="checkbox" name="export_emailsettings" value="1" /> <?php _e('Email Settings', 'wishlist-member'); ?></label><?php echo $this->Tooltip("settings-export-email"); ?></div>
			<div><label><input type="checkbox" name="export_advancesettings" value="1" /> <?php _e('Advanced Settings', 'wishlist-member'); ?></label><?php echo $this->Tooltip("settings-export-advance"); ?></div>
			<div><label><input type="checkbox" name="export_membershiplevels" value="1" onclick="jQuery('#export_registration_page').css('display',this.checked ? 'block' : 'none')" /> <?php _e('Membership Levels', 'wishlist-member'); ?></label><?php echo $this->Tooltip("settings-export-membership"); ?></div>
			<div id="export_registration_page" style="display:none;margin-left:1.2em"><label><input type="checkbox" name="export_registrationpage" value="1" /> <?php _e('Include Per Level After Registration &amp; After Login Pages', 'wishlist-member'); ?></label></div>
		</blockquote>
		<input type="hidden" name="WishListMemberAction" id="WishListMemberAction" value="ExportSettingsToFile" />
		<input class="button-secondary" type="submit" id="ExportSettings" name="ExportSettings"  value="<?php _e('Export Settings', 'wishlist-member'); ?>" />
		<?php _e('<i>(A file containing the selected settings will be downloaded to your computer.)</i>', 'wishlist-member'); ?>
	</form>
</blockquote>
<br />