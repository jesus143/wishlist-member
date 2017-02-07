<?php
/*
 * Infusionsoft Autoresponder API
 * Original Author : Fel Jun Palawan
 * Version: $Id: integration.autoresponder.infusionsoft.php 2935 2015-12-08 08:10:11Z mike $
 */

/*
  GENERAL PROGRAM NOTES: (This script was based on Mike's Autoresponder integrations.)
  Purpose: This is the UI part of the code. This is displayed as the admin area for Infusionsoft Integration in WLM Dashboard.
  Location: admin/
  Calling program : integration.autoresponder.php
  Logic Flow:
  1. integration.autoresponder.php displays this script (integration.autoresponder.infusionsoft.php)
  and displays current or default settings
  2. on user update, this script submits value to integration.autoresponder.php, which in turn save the value
  3. after saving the values, integration.autoresponder.php call this script again with $wpm_levels contains the membership levels and $data contains the Infusionsoft Integration settings for each membership level.
 */

$__index__ = 'infusionsoft';
$__ar_options__[$__index__] = 'Infusionsoft';
//$__ar_videotutorial__[$__index__] = wlm_video_tutorial ( 'integration', 'ar', $__index__ );

require_once($this->pluginDir . '/lib/integration.autoresponder.infusionsoft.php');

if ($data['ARProvider'] == $__index__):

		$debug = isset( $_GET['debug'] ) ? (int) $_GET['debug'] : "";

		if (wlm_arrval($_POST,'update_ifauto')) {

			$tagsSelections = array();
			foreach ((array) $wpm_levels AS $sku => $level){
				$n = 'auto_istag_add_app'.$sku;
				if(isset($_POST[$n])){
					$tagsSelections[$sku] = $_POST[$n];
				}
			}
			$istags = maybe_serialize($tagsSelections);
			$this->SaveOption('auto_istags_add_app',$istags);

			$tagsSelections = array();
			foreach ((array) $wpm_levels AS $sku => $level){
				$n = 'auto_istag_add_rem'.$sku;
				if(isset($_POST[$n])){
					$tagsSelections[$sku] = $_POST[$n];
				}
			}
			$istags = maybe_serialize($tagsSelections);
			$this->SaveOption('auto_istags_add_rem',$istags);

			$tagsSelections = array();
			foreach ((array) $wpm_levels AS $sku => $level){
				$n = 'auto_istag_remove_app'.$sku;
				if(isset($_POST[$n])){
					$tagsSelections[$sku] = $_POST[$n];
				}
			}
			$istags = maybe_serialize($tagsSelections);
			$this->SaveOption('auto_istags_remove_app',$istags);

			$tagsSelections = array();
			foreach ((array) $wpm_levels AS $sku => $level){
				$n = 'auto_istag_remove_rem'.$sku;
				if(isset($_POST[$n])){
					$tagsSelections[$sku] = $_POST[$n];
				}
			}
			$istags = maybe_serialize($tagsSelections);
			$this->SaveOption('auto_istags_remove_rem',$istags);

			$tagsSelections = array();
			foreach ((array) $wpm_levels AS $sku => $level){
				$n = 'auto_istag_cancelled_app'.$sku;
				if(isset($_POST[$n])){
					$tagsSelections[$sku] = $_POST[$n];
				}
			}
			$istags = maybe_serialize($tagsSelections);
			$this->SaveOption('auto_istags_cancelled_app',$istags);

			$tagsSelections = array();
			foreach ((array) $wpm_levels AS $sku => $level){
				$n = 'auto_istag_cancelled_rem'.$sku;
				if(isset($_POST[$n])){
					$tagsSelections[$sku] = $_POST[$n];
				}
			}
			$istags = maybe_serialize($tagsSelections);
			$this->SaveOption('auto_istags_cancelled_rem',$istags);

			$ifauto_current_tab = wlm_arrval($_POST,'ifauto_current_tab');
			$ifauto_current_tab = $ifauto_current_tab != "sequence" ? "tag" : "sequence";
			$this->SaveOption('auto_isauto_current_tab',$ifauto_current_tab);

			if ( $debug === 1 ) {
				$this->SaveOption('auto_isenable_log', true );
			} elseif ( $debug === 0) {
				$this->SaveOption('auto_isenable_log', false );
			}
		}

	$isapikey = $data[$__index__]['iskey'];
	$ismachine = $data[$__index__]['ismname'];
	$ismachine = trim( $ismachine );
	$ismachine = preg_replace('/\.infusionsoft\.com$/', '', $ismachine);
	$ismachine = preg_replace('/^.*\/+/', '', $ismachine);

	$this->SaveOption('auto_isapikey', $isapikey);
	$this->SaveOption('auto_ismachine', $ismachine);

	$isTagsCategory = array();
	$isTags = array();
	$ifsdk  = null;
	if ( class_exists('WLM_Infusionsoft') ) {
		if ( $isapikey && $ismachine ) {
			$ifsdk = new WLM_Infusionsoft( $ismachine, $isapikey );
			if ( $ifsdk->is_api_connected() ) {
				$isTagsCategory = $ifsdk->get_tag_categories();
				$isTags         = $ifsdk->get_tags();
				$isTagsCategory = (array) $isTagsCategory;
				$isTagsCategory[0] = "- No Category -";
				asort($isTagsCategory);
			}
		}
	}

	if(preg_match('/\.infusionsoft\.com$/', $ismachine)) {
		$ismachine = preg_replace('\.infusionsoft\.com$', '', $ismachine);
		$this->SaveOption('auto_ismachine', $ismachine);
	}
	if(preg_match('/^.*\/+/', $ismachine)) {
		$ismachine = preg_replace('/^.*\/+/', '', $ismachine);
		$this->SaveOption('auto_ismachine', $ismachine);
	}

	$tag_placeholder = count($isTags) > 0 ? "Select tags...":"No tags available";

		$auto_istags_add_app = $this->GetOption('auto_istags_add_app');
		if($auto_istags_add_app) $auto_istags_add_app = maybe_unserialize($auto_istags_add_app);
		else $auto_istags_add_app = array();

		$auto_istags_add_rem = $this->GetOption('auto_istags_add_rem');
		if($auto_istags_add_rem) $auto_istags_add_rem = maybe_unserialize($auto_istags_add_rem);
		else $auto_istags_add_rem = array();

		$auto_istags_remove_app = $this->GetOption('auto_istags_remove_app');
		if($auto_istags_remove_app) $auto_istags_remove_app = maybe_unserialize($auto_istags_remove_app);
		else $auto_istags_remove_app = array();

		$auto_istags_remove_rem = $this->GetOption('auto_istags_remove_rem');
		if($auto_istags_remove_rem) $auto_istags_remove_rem = maybe_unserialize($auto_istags_remove_rem);
		else $auto_istags_remove_rem = array();

		$auto_istags_cancelled_app = $this->GetOption('auto_istags_cancelled_app');
		if($auto_istags_cancelled_app) $auto_istags_cancelled_app = maybe_unserialize($auto_istags_cancelled_app);
		else $auto_istags_cancelled_app = array();

		$auto_istags_cancelled_rem = $this->GetOption('auto_istags_cancelled_rem');
		if($auto_istags_cancelled_rem) $auto_istags_cancelled_rem = maybe_unserialize($auto_istags_cancelled_rem);
		else $auto_istags_cancelled_rem = array();

		$ifauto_current_tab = $this->GetOption('auto_isauto_current_tab');
		$ifauto_current_tab = $ifauto_current_tab != "sequence" ? "tag" : "sequence";

		$auto_isenable_log = (bool) $this->GetOption('auto_isenable_log');

	if ($__INTERFACE__):
		?>
		<blockquote>
			<form method="post" style="position:relative">
				<input type="hidden" name="saveAR" value="saveAR" />
				<h2 class="wlm-integration-steps"><?php _e('Step 1. Configure the Infusionsoft App Name and Encrypted Key:', 'wishlist-member'); ?></h2>
				<?php include $this->pluginDir . '/resources/ads/infusionsoft.php'; ?>
				<table class="form-table">
					<tr valign="top">
						<th scope="row"><?php _e('App Name', 'wishlist-member'); ?></th>
						<td>
							<input type="text" name="ar[ismname]" value="<?php echo $ismachine; ?>.infusionsoft.com" size="70" />
							<?php echo $this->Tooltip("integration-autoresponder-infusionsoft-tooltips-machine-name"); ?>
							<br />
							Example: <small><b><span style="background:#ffff00">appname</span></b>.infusionsoft.com</small><br />
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php _e('Encrypted Key: ', 'wishlist-member'); ?></th>
						<td>
							<input type="text" name="ar[iskey]" value="<?php echo $isapikey; ?>" size="70" />
							<?php echo $this->Tooltip("integration-autoresponder-infusionsoft-tooltips-api-key"); ?>
							<p>The Encrypted Key is located in the Infusionsoft account in the following section: <br> Admin &raquo; Settings &raquo; Application</p>
						</td>
					</tr>
				</table>
				<?php
					if ( $auto_isenable_log ) {
						if ( $debug === 0 ) {
							echo "<strong style='color:green;'>Click Save Settings to DISABLE API Logs.</strong>";
						} else {
							echo "<strong style='color:red;'>API Logs Enabled</strong>";
						}
					} else {
						if ( $debug === 1 ) {
							echo "<strong style='color:green;'>Click Save Settings to ENABLE API Logs.</strong>";
						}
					}
				?>
				<h2 class="wlm-integration-steps">Step 2. Assign the Membership Levels to the corresponding Infusionsoft Lists:</h2>
				<?php if ( $ifsdk && ! $ifsdk->is_api_connected() ) : ?>
					<p style="color:red;">
						WishList Member could not establish connection to Infusionsoft using the <strong>App Name</strong> and <strong>API Key</strong> that you entered.
						Please make sure that the information you entered are correct and Infusionsoft is not blocked in your server.
					</p>
				<?php endif; ?>
				<ul class="wlm-sub-menu">
					<li <?php echo $ifauto_current_tab == 'tag' ? 'class="current"' : '' ?>><a class="ifauto_tag_tab" href="javascript:void(0);"><?php _e('Infusionsoft Tags', 'wishlist-member'); ?></a></li>
					<li <?php echo $ifauto_current_tab == 'sequence' ? 'class="current"' : '' ?>><a class="ifauto_sequence_tab" href="javascript:void(0);"><?php _e('Follow-Up Sequence (Legacy)', 'wishlist-member'); ?></a></li>
				</ul>
				<!-- Tagging -->
				<div class="ifauto_tagging_holder <?php echo $ifauto_current_tab == 'sequence' ? 'hidden' : '' ?>">
					<blockquote>
						<p><?php _e('Infusionsoft Tags allow tags to be applied to members when they are Added To, Cancelled From and Removed From Membership Levels..', 'wishlist-member'); ?></p>
						<table class="widefat">
							<thead>
								<tr>
									<th scope="col" width="33%"><?php _e('Level', 'wishlist-member'); ?></th>
									<th scope="col" width="33%">&nbsp;</th>
									<th scope="col" width="33%">&nbsp;</th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ((array) $wpm_levels AS $levelid => $level): ?>
									<tr class="<?php echo ++$autoresponder_row % 2 ? 'alternate' : ''; ?>">
										<td><?php echo $level['name']; ?></td>
										<td>&nbsp;</td>
										<td><a class="if_edit_tag_level ifshow" href="javascript:void(0);">[+] Edit Level Tag Settings</a></td>		
									</tr>
									<tr class="<?php echo $autoresponder_row % 2 ? 'alternate' : ''; ?> hidden">
										<td style="z-index:0;overflow:visible;">
											<p><b>When Added:</b></p>
											<p>
											Apply Tags:<br />
											 <select name="auto_istag_add_app<?php echo $levelid; ?>[]" data-placeholder='<?php echo $tag_placeholder; ?>' style="width:300px;" class='chzn-select' multiple="multiple" >
											<?php
												foreach($isTagsCategory as $catid=>$name){
													if(isset($isTags[$catid]) && count($isTags[$catid]) > 0){
														asort($isTags[$catid]);
														echo "<optgroup label='{$name}'>";
														foreach($isTags[$catid] as $id=>$d){
															$selected = "";
															if(isset($auto_istags_add_app[$levelid]) && in_array($d['Id'],$auto_istags_add_app[$levelid])){
																$selected = "selected='selected'";
															}
															
															echo "<option value='{$d['Id']}' {$selected}>{$d['Name']}</option>";
														}
													echo "</optgroup>";
													}
												}			
											?>
											</select>
											</p>
											<p>
											Remove Tags:<br />
											<select name="auto_istag_add_rem<?php echo $levelid; ?>[]" data-placeholder='<?php echo $tag_placeholder; ?>' style="width:300px;" class='chzn-select' multiple="multiple" >
											<?php
												foreach($isTagsCategory as $catid=>$name){
													if(isset($isTags[$catid]) && count($isTags[$catid]) > 0){
														asort($isTags[$catid]);
														echo "<optgroup label='{$name}'>";
														foreach($isTags[$catid] as $id=>$d){
															$selected = "";
															if(isset($auto_istags_add_rem[$levelid]) && in_array($d['Id'],$auto_istags_add_rem[$levelid])){
																$selected = "selected='selected'";
															}
															
															echo "<option value='{$d['Id']}' {$selected}>{$d['Name']}</option>";
														}
													echo "</optgroup>";
													}
												}			
											?>
											</select>
											</p>								
										</td>
										<td style="z-index:0;overflow:visible;">
											<p><b>When Removed:</b></p>
											<p>
											Apply Tags:<br />
											 <select name="auto_istag_remove_app<?php echo $levelid; ?>[]" data-placeholder='<?php echo $tag_placeholder; ?>' style="width:300px;" class='chzn-select' multiple="multiple" >
											<?php
												foreach($isTagsCategory as $catid=>$name){
													if(isset($isTags[$catid]) && count($isTags[$catid]) > 0){
														asort($isTags[$catid]);
														echo "<optgroup label='{$name}'>";
														foreach($isTags[$catid] as $id=>$d){
															$selected = "";
															if(isset($auto_istags_remove_app[$levelid]) && in_array($d['Id'],$auto_istags_remove_app[$levelid])){
																$selected = "selected='selected'";
															}
															
															echo "<option value='{$d['Id']}' {$selected}>{$d['Name']}</option>";
														}
													echo "</optgroup>";
													}
												}			
											?>
											</select>
											</p>
											<p>
											Remove Tags:<br />
											<select name="auto_istag_remove_rem<?php echo $levelid; ?>[]" data-placeholder='<?php echo $tag_placeholder; ?>' style="width:300px;" class='chzn-select' multiple="multiple" >
											<?php
												foreach($isTagsCategory as $catid=>$name){
													if(isset($isTags[$catid]) && count($isTags[$catid]) > 0){
														asort($isTags[$catid]);
														echo "<optgroup label='{$name}'>";
														foreach($isTags[$catid] as $id=>$d){
															$selected = "";
															if(isset($auto_istags_remove_rem[$levelid]) && in_array($d['Id'],$auto_istags_remove_rem[$levelid])){
																$selected = "selected='selected'";
															}
															
															echo "<option value='{$d['Id']}' {$selected}>{$d['Name']}</option>";
														}
													echo "</optgroup>";
													}
												}			
											?>
											</select>
											</p>			
										</td>
										<td style="z-index:0;overflow:visible;">
											<p><b>When Cancelled:</b></p>
											<p>
											Apply Tags:<br />
											 <select name="auto_istag_cancelled_app<?php echo $levelid; ?>[]" data-placeholder='<?php echo $tag_placeholder; ?>' style="width:300px;" class='chzn-select' multiple="multiple" >
											<?php
												foreach($isTagsCategory as $catid=>$name){
													if(isset($isTags[$catid]) && count($isTags[$catid]) > 0){
														asort($isTags[$catid]);
														echo "<optgroup label='{$name}'>";
														foreach($isTags[$catid] as $id=>$d){
															$selected = "";
															if(isset($auto_istags_cancelled_app[$levelid]) && in_array($d['Id'],$auto_istags_cancelled_app[$levelid])){
																$selected = "selected='selected'";
															}
															
															echo "<option value='{$d['Id']}' {$selected}>{$d['Name']}</option>";
														}
													echo "</optgroup>";
													}
												}			
											?>
											</select>
											</p>
											<p>
											Remove Tags:<br />
											<select name="auto_istag_cancelled_rem<?php echo $levelid; ?>[]" data-placeholder='<?php echo $tag_placeholder; ?>' style="width:300px;" class='chzn-select' multiple="multiple" >
											<?php
												foreach($isTagsCategory as $catid=>$name){
													if(isset($isTags[$catid]) && count($isTags[$catid]) > 0){
														asort($isTags[$catid]);
														echo "<optgroup label='{$name}'>";
														foreach($isTags[$catid] as $id=>$d){
															$selected = "";
															if(isset($auto_istags_cancelled_rem[$levelid]) && in_array($d['Id'],$auto_istags_cancelled_rem[$levelid])){
																$selected = "selected='selected'";
															}
															
															echo "<option value='{$d['Id']}' {$selected}>{$d['Name']}</option>";
														}
													echo "</optgroup>";
													}
												}			
											?>
											</select>
											</p>			
										</td>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</blockquote>
				</div>
				<!-- Sequence -->
				<div class="ifauto_sequence_holder <?php echo $ifauto_current_tab == 'tag' ? 'hidden' : '' ?>">
					<blockquote>
						<p class="wlm-color-error"><?php _e('The Follow-Up Sequence is a Legacy Feature for Infusionsoft that is not recommended.', 'wishlist-member'); ?></p>
						<p><?php _e('Enter the Follow-Up Sequence ID a user will be added to when added to a Membership Level.', 'wishlist-member'); ?></p>
						<p><?php _e('Note: The Follow-Up Sequences are located in the Infusionsoft account in the following section:', 'wishlist-member'); ?><br />
						<em>Marketing &raquo; Legacy &raquo; View Follow-Up Sequences (The Follow-Up Sequence ID can be found in the ID column)</em></p>
						<table class="widefat">
							<thead>
								<tr>
									<th scope="col" width="35%"><?php _e('Level', 'wishlist-member'); ?></th>
									<th scope="col" width="30%"><?php _e('Sequence ID', 'wishlist-member'); ?>
										<?php echo $this->Tooltip("integration-autoresponder-infusionsoft-tooltips-sequence-id"); ?>
									</th>
									<th class="num" width="40%"><?php _e('Unsubscribe if Removed from Level', 'wishlist-member'); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ((array) $wpm_levels AS $levelid => $level): ?>
									<tr class="<?php echo ++$autoresponder_row % 2 ? 'alternate' : ''; ?>">
										<td><?php echo $level['name']; ?></td>
										<td><input type="text" name="ar[isCID][<?php echo $levelid; ?>]" value="<?php echo $data[$__index__]['isCID'][$levelid]; ?>" size="20" /></td>
										<?php $isUnsub = ($data[$__index__]['isUnsub'][$levelid] == 1 ? true : false); ?>
										<td class="num"><input type="checkbox" name="ar[isUnsub][<?php echo $levelid; ?>]" value="1" <?php echo $isUnsub ? "checked='checked'" : ""; ?> /></td>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</blockquote>
				</div>
				<p class="submit">
					<input name="ifauto_current_tab" id="ifauto_current_tab" type="hidden" value="<?php echo $ifauto_current_tab; ?>" />
					<input name="update_ifauto" class="button-primary" type="submit" value="<?php _e('Save Settings', 'wishlist-member'); ?>" />
				</p>
			</form>
		</blockquote>
		<?php
		include_once($this->pluginDir . '/admin/tooltips/integration.autoresponder.infusionsoft.tooltips.php');
	endif;
endif;
