<?php
$webinars = $this->WebinarIntegrations;
$webinar_provider = $this->GetOption('WebinarProvider');
$webinar_settings = $this->GetOption('webinar');
if(isset($_POST['WebinarProvider'])) {
	$webinar_provider = $_POST['WebinarProvider'];
	$this->SaveOption('WebinarProvider', $webinar_provider);
}


if (!empty($_POST['webinar'][$webinar_provider])) {
	$webinar_settings[$webinar_provider] = $_POST['webinar'][$webinar_provider];
	$this->SaveOption('webinar', $webinar_settings);
}

if($webinar_provider == 'gotomeeting') {
	echo '<div class="error">';
	echo '<form method="post">';
	echo '<p style="color:red;font-weight:bold">Important: The regular GoToWebinar <small><sup>&reg;</sup></small>  integration is now deprecated. It is strongly recommended that the GoToWebinar API Integration be used instead.</p>';
	echo '</form>';
	echo '</div>';
}
?>


<form method="post">
	<table class="form-table">
		<tr valign="top">
			<td colspan="3" style="padding-left:0">
				<p><?php _e('Automatically add newly registered members to scheduled webinars.', 'wishlist-member'); ?></p>
			</td>
			<td>
				<?php if (!empty($webinar_provider)): ?>
					<p class="alignright" style="margin-top:0"><a id="wlm-integration-video-tutorial" style="display:none" href="" target="_blank"><?php _e('Watch Integration Video Tutorial', 'wishlist-member'); ?></a></p>
				<?php endif; ?>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php _e('Webinar Provider', 'wishlist-member'); ?></th>
			<td style="white-space:nowrap" width="1">
				<select name="WebinarProvider">
					<?php $provider_name = ''; ?>
					<option value=""><?php _e('None', 'wishlist-member'); ?></option>
					<?php foreach($GLOBALS['wishlist_member_webinars'] as $w): ?>
					<?php $selected = $w['optionname'] == $webinar_provider ? 'selected="selected"' : false; ?>
					<option <?php echo $selected?> value="<?php echo $w['optionname']?>"><?php echo $w['name']?></option>
					<?php if($selected) $provider_name = $w['name']; ?>
					<?php endforeach; ?>
				</select> <?php echo $this->Tooltip("integration-webinar-tooltips-webinar-Provider"); ?>
			</td>
			<td>
				<p class="submit" style="margin:0;padding:0"><input type="submit" class="button-secondary" value="<?php _e('Set Webinar Provider', 'wishlist-member'); ?>" /></p>
			</td>
			<td style="text-align:right">
				<?php if (!empty($webinar_provider)): ?>
					<a href="" style="display:none" target="_blank" id="wlm-integration-affiliate"><?php printf(__('Learn more about %1$s', 'wishlist-member'), $provider_name); ?></a>
				<?php endif; ?>
			</td>
		</tr>
	</table>
	<hr />
	<br />
</form>
<?php
$__integrations__ = glob($this->pluginDir . '/admin/integration.webinar.*.php');
foreach ((array) $__integrations__ AS $__integration__) {
	if(stripos($__integration__, 'webinar.'.$webinar_provider.'.php') > 0) {
		include $__integration__;
	}
}
include_once($this->pluginDir . '/admin/tooltips/integration.webinar.tooltips.php');
?>
<script>
	jQuery(function($) {
		if($('.integration-links').attr('data-video') != "") {
			$('#wlm-integration-video-tutorial').attr('href', $('.integration-links').attr('data-video')).show();
		} else {
			$('#wlm-integration-video-tutorial').hide();
		}
		if($('.integration-links').attr('data-affiliate') != "") {
			$('#wlm-integration-affiliate').attr('href', $('.integration-links').attr('data-affiliate')).show();
		} else {
			$('#wlm-integration-affiliate').hide();
		}
	});
</script>