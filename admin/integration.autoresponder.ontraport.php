<?php
add_thickbox(); 
$__index__ = 'ontraport';
$__ar_options__[$__index__] = 'Ontraport';
$__ar_affiliates__[$__index__] = 'http://ontraport.com/';
// $__ar_videotutorial__[$__index__] = wlm_video_tutorial ( 'integration', 'ar', $__index__ );

if ($data['ARProvider'] == $__index__):
	if ($__INTERFACE__):

		$appid = $data['ontraport']['app_id'];
		$key = $data['ontraport']['api_key'];

		$class_file = $this->pluginDir . '/lib/integration.autoresponder.ontraport.php';
		include $class_file;
		$integration = new WLM_AUTORESPONDER_ONTRAPORT;
		$integration->set_wlm($this);
		$integration->set_appid($appid);
		$integration->set_key($key);

// Don't do API Calls if either App Id or Key is empty
if(!empty($appid) && !empty($key)) {
	$sequences = (array) $integration->ontraport_fetch_sequences();
	$tags = (array) $integration->ontraport_fetch_tags();
}

		?>
		
		<form method="post">
			<input type="hidden" name="saveAR" value="saveAR" />

			<h2 class="wlm-integration-steps">Step 1. Configure the Ontraport API Settings:</h2>
			<p><a class="thickbox" href="#TB_inline?width=350&height=550&inlineId=divinstructions">Click Here to view instructions to locate API ID and API Key</a></p>
			<table class="form-table">
				<tr>
					<th>API ID</th>
					<td><input size="50" type="text" name="ar[app_id]" value="<?php echo $data['ontraport']['app_id']?>"/></td>
				</tr>
				<tr>
					<th>API Key</th>
					<td><input size="50" type="text" name="ar[api_key]" value="<?php echo $data['ontraport']['api_key']?>"/></td>
				</tr>
			</table>
			
			<h2 class="wlm-integration-steps">Step 2. Select the Membership Levels to Enable:</h2>
			<p>A contact will be added to the Ontraport contacts once a member is added to an enabled Membership Level.</p>
			
			<table class="widefat">
				<thead>
					<tr>
						<th scope="col"><?php _e('Membership Level', 'wishlist-member'); ?> </th>
						<th class="num" scope="col"><?php _e("Enable", "wishlist-member"); ?></th>
						<th class="num" scope="col"><?php _e("Add Tags", "wishlist-member"); ?></th>
						<th class="num" scope="col"><?php _e("Add Sequences", "wishlist-member"); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ((array) $wpm_levels AS $levelid => $level): ?>

						<tr class="<?php echo ++$autoresponder_row % 2 ? 'alternate' : ''; ?>">
							<th scope="row"><?php echo $level['name']; ?></th>
							<td class="num">
								<?php $checked = $data['ontraport']['addenabled'][$levelid] == 'yes' ? 'checked="checked"' : null ?>
									<input <?php echo $checked ?> type="checkbox" name="ar[addenabled][<?php echo $levelid ?>]" value="yes">
							</td>
							<td class="num">
								<select data-placeholder="Select Tags..." multiple="multiple" style="width:200px;padding:0px !important;" class="ontraport-select2" name="ar[tags][<?php echo $levelid; ?>][]" >
									<?php foreach($tags as $tags_id => $tags_name): ?>
										<option value="<?php echo $tags_id; ?>" <?php echo in_array($tags_id, (array)$data['ontraport']['tags'][$levelid]) ? 'selected="selected"' : '' ?> ><?php echo $tags_name; ?></option>
									<?php endforeach; ?>

								</select>
							</td>
							<td class="num">
								<select data-placeholder="Select Sequences..." multiple="multiple" style="width:200px;padding:0px !important;" class="ontraport-select2" name="ar[sequences][<?php echo $levelid; ?>][]" >
									<?php foreach($sequences as $sequence_id => $sequence_name): ?>
									<option value="<?php echo $sequence_id; ?>" <?php echo in_array($sequence_id, (array)$data['ontraport']['sequences'][$levelid]) ? 'selected="selected"' : '' ?> ><?php echo $sequence_name; ?></option>
									<?php endforeach; ?>
								</select>
							</td>
						<?php endforeach; ?>
				</tbody>
			</table>
			<p class="submit">
				<input type="submit" class="button-primary" value="<?php _e('Save Settings', 'wishlist-member'); ?>" />
			</p>
		</form>

<!-- ---------------------------------------------------------->
<!-- INSTRUCTIONS MODAL DIV -->
<!-- ---------------------------------------------------------->
<div id="divinstructions" style="display:none;">
			<p>Use of the Contacts API requires an API App ID and API Key. <br> These can be generated within the Ontraport account in the Admin > API Settings and Key Manager section.</b> </p> 
			<a href="https://support.ontraport.com/attachments/token/ndjwc5dpgrc79ri/?name=2014-01-22_1020.png" target="_blank" >
				<img src="https://support.ontraport.com/attachments/token/ndjwc5dpgrc79ri/?name=2014-01-22_1020.png" width="600px"> 
			</a><br><br>
			<p> Be sure to select the appropriate permissions for the Key that is to be generated. <br><i>Note: If a Key does not display “Add" permissions as selected, it cannot be used to add type requests.</i></p>
			<a href="https://support.ontraport.com/attachments/token/5nmvq7hinungzta/?name=2014-01-22_1021.png" target="_blank" >
				<img src="https://support.ontraport.com/attachments/token/5nmvq7hinungzta/?name=2014-01-22_1021.png" width="600px"> 
			</a><br><br>
</div>

		<?php
	endif;
endif;
?>

<script>

jQuery(document).ready(function($) {
     $('select.ontraport-select2').select2({
		allowClear: true
		
	});
	
    $("select.ontraport-select2").select().change(function(){ 
	
	$str_selected = jQuery(this).select2().val();
	
		if($str_selected != null){
			$pos = $str_selected.lastIndexOf("select_all");
			if($pos >= 0){
				jQuery(this).find('option').each(function() {
					if(jQuery(this).val() == "select_all"){
						jQuery(this).attr("selected",false);
						
					}else{
						jQuery(this).attr("selected","selected");
					}
					jQuery(this).trigger("change");
				});

			}
		}
		
	});
});
</script>