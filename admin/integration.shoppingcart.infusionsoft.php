<?php
/*
 * InfusionSoft Shopping Cart Integration
 * Original Author : Mike Lopez
 * Version: $Id: integration.shoppingcart.infusionsoft.php 2935 2015-12-08 08:10:11Z mike $
 */

$__index__ = 'is';
$__sc_options__[$__index__] = 'Infusionsoft';
$__sc_affiliates__[$__index__] = 'http://wlplink.com/go/infusionsoft';
$__sc_videotutorial__[$__index__] = wlm_video_tutorial ( 'integration', 'sc', $__index__ );

if (wlm_arrval($_GET, 'cart') == $__index__) {
	if (!$__INTERFACE__) {
		// BEGIN Initialization
		$isthankyou = $this->GetOption('isthankyou');
		if (!$isthankyou) {
			$this->SaveOption('isthankyou', $isthankyou = $this->MakeRegURL());
		}

		// save POST URL
		if (wlm_arrval($_POST, 'isthankyou')) {
			$_POST['isthankyou'] = trim(wlm_arrval($_POST, 'isthankyou'));
			$wpmx = trim(preg_replace('/[^A-Za-z0-9]/', '', $_POST['isthankyou']));
			if ($wpmx == $_POST['isthankyou']) {
				if ($this->RegURLExists($wpmx, null, 'isthankyou')) {
					echo "<div class='error fade'>" . __('<p><b>Error:</b> Infusionsoft Thank You URL (' . $wpmx . ') is already in use by a Membership Level or another Shopping Cart.  Please try a different one.</p>', 'wishlist-member') . "</div>";
				} else {
					$this->SaveOption('isthankyou', $isthankyou = $wpmx);
					echo "<div class='updated fade'>" . __('<p>Thank You URL Changed.&nbsp; Make sure to update Infusionsoft with the same Thank You URL to make it work.</p>', 'wishlist-member') . "</div>";
				}
			} else {
				echo "<div class='error fade'>" . __('<p><b>Error:</b> Thank You URL may only contain letters and numbers.</p>', 'wishlist-member') . "</div>";
			}
		}

		$debug = isset( $_GET['debug'] ) ? (int) $_GET['debug'] : "";

		if( isset( $_POST['save_api_connection']) ) {
			// save Machine Name
			$_POST['ismachine'] = trim(wlm_arrval($_POST, 'ismachine'));
			$ismachine = $this->GetOption('ismachine');
			$ismachine = $ismachine ? $ismachine : "";
			if($ismachine != $_POST['ismachine']){
				$ismachine = trim( $_POST['ismachine'] );
				$ismachine = preg_replace('/\.infusionsoft\.com$/', '', $ismachine);
				$ismachine = preg_replace('/^.*\/+/', '', $ismachine);
				$this->SaveOption('ismachine', $ismachine);
				echo "<div class='updated fade'>" . __('<p>App Name Changed.</p>', 'wishlist-member') . "</div>";
			}
			// save API Key
			$_POST['isapikey'] = trim(wlm_arrval($_POST, 'isapikey'));
			$isapikey = $this->GetOption('isapikey');
			$isapikey = $isapikey ? $isapikey: "";
			if($isapikey != $_POST['isapikey']){
				$this->SaveOption('isapikey',$_POST['isapikey']);
				echo "<div class='updated fade'>" . __('<p>API Key Changed.&nbsp; Make sure that your API Key matches the one specified in your Infusionsoft account to make it work.</p>', 'wishlist-member') . "</div>";
			}

			if ( $debug === 1 ) {
				$this->SaveOption('isenable_log', true );
			} elseif ( $debug === 0) {
				$this->SaveOption('isenable_log', false );
			}
		}


		if (wlm_arrval($_POST, 'update_tags')) {
			$tagsSelections = array();
			foreach ((array) $wpm_levels AS $sku => $level) {
				$n = 'istag_add_app' . $sku;
				if (isset($_POST[$n])) {
					$tagsSelections[$sku] = $_POST[$n];
				}
			}
			$istags = maybe_serialize($tagsSelections);
			$this->SaveOption('istags_add_app', $istags);

			$tagsSelections = array();
			foreach ((array) $wpm_levels AS $sku => $level) {
				$n = 'istag_add_rem' . $sku;
				if (isset($_POST[$n])) {
					$tagsSelections[$sku] = $_POST[$n];
				}
			}
			$istags = maybe_serialize($tagsSelections);
			$this->SaveOption('istags_add_rem', $istags);

			$tagsSelections = array();
			foreach ((array) $wpm_levels AS $sku => $level) {
				$n = 'istag_remove_app' . $sku;
				if (isset($_POST[$n])) {
					$tagsSelections[$sku] = $_POST[$n];
				}
			}
			$istags = maybe_serialize($tagsSelections);
			$this->SaveOption('istags_remove_app', $istags);

			$tagsSelections = array();
			foreach ((array) $wpm_levels AS $sku => $level) {
				$n = 'istag_remove_rem' . $sku;
				if (isset($_POST[$n])) {
					$tagsSelections[$sku] = $_POST[$n];
				}
			}
			$istags = maybe_serialize($tagsSelections);
			$this->SaveOption('istags_remove_rem', $istags);

			$tagsSelections = array();
			foreach ((array) $wpm_levels AS $sku => $level) {
				$n = 'istag_cancelled_app' . $sku;
				if (isset($_POST[$n])) {
					$tagsSelections[$sku] = $_POST[$n];
				}
			}
			$istags = maybe_serialize($tagsSelections);
			$this->SaveOption('istags_cancelled_app', $istags);

			$tagsSelections = array();
			foreach ((array) $wpm_levels AS $sku => $level) {
				$n = 'istag_cancelled_rem' . $sku;
				if (isset($_POST[$n])) {
					$tagsSelections[$sku] = $_POST[$n];
				}
			}
			$istags = maybe_serialize($tagsSelections);
			$this->SaveOption('istags_cancelled_rem', $istags);

			echo "<div class='updated fade'>" . __('<p>Membership Level tag settings updated.</p>', 'wishlist-member') . "</div>";
		}

		//pay per post tag settings
		if (wlm_arrval($_POST, 'update_tags_pp')) {
			$posts = $this->GetPayPerPosts(array('post_title', 'post_type'),false);

			$istagspp_add_app = array();
			$istagspp_add_rem = array();
			$istagspp_remove_app = array();
			$istagspp_remove_rem = array();
			foreach($posts  as $post){
				$sku = 'payperpost-' . $post->ID;
				
				$n = 'istagpp_add_app' . $sku;
				if (isset($_POST[$n])) {
					$istagspp_add_app[$sku] = $_POST[$n];
				}

				$n = 'istagpp_add_rem' . $sku;
				if (isset($_POST[$n])) {
					$istagspp_add_rem[$sku] = $_POST[$n];
				}

				$n = 'istagpp_remove_app' . $sku;
				if (isset($_POST[$n])) {
					$istagspp_remove_app[$sku] = $_POST[$n];
				}	

				$n = 'istagpp_remove_rem' . $sku;
				if (isset($_POST[$n])) {
					$istagspp_remove_rem[$sku] = $_POST[$n];
				}			
			}
			$istags = maybe_serialize($istagspp_add_app);
			$this->SaveOption('istagspp_add_app', $istags);

			$istags = maybe_serialize($istagspp_add_rem);
			$this->SaveOption('istagspp_add_rem', $istags);

			$istags = maybe_serialize($istagspp_remove_app);
			$this->SaveOption('istagspp_remove_app', $istags);

			$istags = maybe_serialize($istagspp_remove_rem);
			$this->SaveOption('istagspp_remove_rem', $istags);

			echo "<div class='updated fade'>" . __('<p>Pay Per Post tag settings updated.</p>', 'wishlist-member') . "</div>";
		}

		$isthankyou_url = $wpm_scregister . $isthankyou;
		$isapikey = $this->GetOption('isapikey');
		$ismachine = $this->GetOption('ismachine');

		if(preg_match('/\.infusionsoft\.com$/', $ismachine)) {
			$ismachine = preg_replace('\.infusionsoft\.com$', '', $ismachine);
			$this->SaveOption('ismachine', $ismachine);
		}
		if(preg_match('/^.*\/+/', $ismachine)) {
			$ismachine = preg_replace('/^.*\/+/', '', $ismachine);
			$this->SaveOption('ismachine', $ismachine);
		}

		$isTagsCategory = array();
		$isTags = array();
		if ( class_exists('WLM_Infusionsoft') ) {
			if ( $isapikey && $ismachine) {
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
		$tag_placeholder = count($isTags) > 0 ? "Select tags..." : "No tags available";

		$istags_add_app = $this->GetOption('istags_add_app');
		if ($istags_add_app)
			$istags_add_app = maybe_unserialize($istags_add_app);
		else
			$istags_add_app = array();

		$istags_add_rem = $this->GetOption('istags_add_rem');
		if ($istags_add_rem)
			$istags_add_rem = maybe_unserialize($istags_add_rem);
		else
			$istags_add_rem = array();

		$istags_remove_app = $this->GetOption('istags_remove_app');
		if ($istags_remove_app)
			$istags_remove_app = maybe_unserialize($istags_remove_app);
		else
			$istags_remove_app = array();

		$istags_remove_rem = $this->GetOption('istags_remove_rem');
		if ($istags_remove_rem)
			$istags_remove_rem = maybe_unserialize($istags_remove_rem);
		else
			$istags_remove_rem = array();

		$istags_cancelled_app = $this->GetOption('istags_cancelled_app');
		if ($istags_cancelled_app)
			$istags_cancelled_app = maybe_unserialize($istags_cancelled_app);
		else
			$istags_cancelled_app = array();

		$istags_cancelled_rem = $this->GetOption('istags_cancelled_rem');
		if ($istags_cancelled_rem)
			$istags_cancelled_rem = maybe_unserialize($istags_cancelled_rem);
		else
			$istags_cancelled_rem = array();

		//pay per post tag settings
		$istagspp_add_app = $this->GetOption('istagspp_add_app');
		if ($istagspp_add_app)
			$istagspp_add_app = maybe_unserialize($istagspp_add_app);
		else
			$istagspp_add_app = array();

		$istagspp_add_rem = $this->GetOption('istagspp_add_rem');
		if ($istagspp_add_rem)
			$istagspp_add_rem = maybe_unserialize($istagspp_add_rem);
		else
			$istagspp_add_rem = array();

		$istagspp_remove_app = $this->GetOption('istagspp_remove_app');
		if ($istagspp_remove_app)
			$istagspp_remove_app = maybe_unserialize($istagspp_remove_app);
		else
			$istagspp_remove_app = array();

		$istagspp_remove_rem = $this->GetOption('istagspp_remove_rem');
		if ($istagspp_remove_rem)
			$istagspp_remove_rem = maybe_unserialize($istagspp_remove_rem);
		else
			$istagspp_remove_rem = array();		

		$isenable_log = (bool) $this->GetOption('isenable_log');

		// END Initialization
	} else {
		// START Interface
		?>
		<!-- Infusionsoft -->
		<style type="text/css">
			.shortcodes span {background-color: #cccccc; color: #000000; padding: 2px 4px 2px 4px; margin: 6px 6px 6px 0px; display: inline-block;}
		</style>
		<blockquote>
			<form method="post" style="position:relative">
				<h2 class="wlm-integration-steps"><?php _e('Step 1. Configure Infusionsoft API Connection:', 'wishlist-member'); ?></h2>
				<?php include $this->pluginDir . '/resources/ads/infusionsoft.php'; ?>
				<table class="form-table">
					<tr>
						<th scope="row"><?php _e('App Name', 'wishlist-member'); ?></th>
						<td>
							<input type="text" name="ismachine" value="<?php echo $ismachine ?>.infusionsoft.com" size="70" />
							<?php echo $this->Tooltip("integration-shoppingcart-infusionsoft-tooltips-Machine-Name"); ?><br />
							Example: <small><b><span style="background:#ffff00">appname</span></b>.infusionsoft.com</small><br />
						</td>
					</tr>
					<tr>
						<th scope="row"><?php _e('Encrypted Key', 'wishlist-member'); ?></th>
						<td>
							<input type="text" name="isapikey" value="<?php echo $isapikey ?>" size="70" />
							<?php echo $this->Tooltip("integration-shoppingcart-infusionsoft-tooltips-API-Key"); ?><br />
							<p>The Encrypted Key is located in the Infusionsoft account in the following section: <br> Admin &raquo; Settings &raquo; Application</p>
						</td>
					</tr>
				</table>
				<?php if ( $ifsdk && ! $ifsdk->is_api_connected() ) : ?>
					<p style="color:red;">
						WishList Member could not establish connection to Infusionsoft using the <strong>App Name</strong> and <strong>API Key</strong> that you entered.
						Please make sure that the information you entered are correct and Infusionsoft is not blocked in your server.
					</p>
				<?php endif; ?>
				<?php
					if ( $isenable_log ) {
						if ( $debug === 0 ) {
							echo "<strong style='color:green;'>Click 'Save API Connection' button to DISABLE API Logs.</strong>";
						} else {
							echo "<strong style='color:red;'>API Logs Enabled</strong>";
						}
					} else {
						if ( $debug === 1 ) {
							echo "<strong style='color:green;'>Click 'Save API Connection' button to ENABLE API Logs.</strong>";
						}
					}
				?>
				<p class="submit">
					&nbsp;&nbsp;<input name="save_api_connection" type="submit" class="button-secondary" value="<?php _e('Save API Connection','wishlist-member'); ?>" />
				</p>
			</form>
			<h2 class="wlm-integration-steps"><?php _e('Step 2. Create a product for each Membership Level using the SKUs provided below:', 'wishlist-member'); ?></h2>
			<form method="post">
				<table class="widefat" style="z-index:0;">
					<thead>
						<tr>
							<th scope="col"><?php _e('Membership Level', 'wishlist-member'); ?></th>
							<th scope="col" ><?php _e('SKU', 'wishlist-member'); ?><?php echo $this->Tooltip("integration-shoppingcart-infusionsoft-tooltips-sku"); ?></th>
							<th scope="col" >&nbsp;</th>
						</tr>
					</thead>
					<tbody>
		<?php
		$alt = 0;
		foreach ((array) $wpm_levels AS $sku => $level):
			?>
						<tr class="<?php echo $alt++ % 2 ? '' : 'alternate'; ?>" id="wpm_level_row_<?php echo $sku ?>">
							<td width="35%"><b><?php echo $level['name'] ?></b></td>
							<td width="35%"><u style="font-size:1.2em"><?php echo $sku ?></u></td>
							<td><a class="if_edit_tag_level ifshow" href="javascript:void(0);">[+] Edit Level Tag Settings</a></td>		
						</tr>
						<tr class="<?php echo $alt++ % 2 ? '' : 'alternate'; ?> hidden" id="wpm_level_row_<?php echo $sku ?>">

							<td style="z-index:0;overflow:visible;">
								<p><b>When Added:</b></p>
								<p>
									Apply Tags:<br />
									<select name="istag_add_app<?php echo $sku; ?>[]" data-placeholder='<?php echo $tag_placeholder; ?>' style="width:300px;" class='chzn-select' multiple="multiple" >
										<?php
										foreach ($isTagsCategory as $catid => $name) {
											if (isset($isTags[$catid]) && count($isTags[$catid]) > 0) {
												asort($isTags[$catid]);
												echo "<optgroup label='{$name}'>";
												foreach ($isTags[$catid] as $id => $data) {
													$selected = "";
													if (isset($istags_add_app[$sku]) && in_array($data['Id'], $istags_add_app[$sku])) {
														$selected = "selected='selected'";
													}

													echo "<option value='{$data['Id']}' {$selected}>{$data['Name']}</option>";
												}
												echo "</optgroup>";
											}
										}
										?>
									</select>
								</p>
								<p>
									Remove Tags:<br />
									<select name="istag_add_rem<?php echo $sku; ?>[]" data-placeholder='<?php echo $tag_placeholder; ?>' style="width:300px;" class='chzn-select' multiple="multiple" >
										<?php
										foreach ($isTagsCategory as $catid => $name) {
											if (isset($isTags[$catid]) && count($isTags[$catid]) > 0) {
												asort($isTags[$catid]);
												echo "<optgroup label='{$name}'>";
												foreach ($isTags[$catid] as $id => $data) {
													$selected = "";
													if (isset($istags_add_rem[$sku]) && in_array($data['Id'], $istags_add_rem[$sku])) {
														$selected = "selected='selected'";
													}

													echo "<option value='{$data['Id']}' {$selected}>{$data['Name']}</option>";
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
									<select name="istag_remove_app<?php echo $sku; ?>[]" data-placeholder='<?php echo $tag_placeholder; ?>' style="width:300px;" class='chzn-select' multiple="multiple" >
										<?php
										foreach ($isTagsCategory as $catid => $name) {
											if (isset($isTags[$catid]) && count($isTags[$catid]) > 0) {
												asort($isTags[$catid]);
												echo "<optgroup label='{$name}'>";
												foreach ($isTags[$catid] as $id => $data) {
													$selected = "";
													if (isset($istags_remove_app[$sku]) && in_array($data['Id'], $istags_remove_app[$sku])) {
														$selected = "selected='selected'";
													}

													echo "<option value='{$data['Id']}' {$selected}>{$data['Name']}</option>";
												}
												echo "</optgroup>";
											}
										}
										?>
									</select>
								</p>
								<p>
									Remove Tags:<br />
									<select name="istag_remove_rem<?php echo $sku; ?>[]" data-placeholder='<?php echo $tag_placeholder; ?>' style="width:300px;" class='chzn-select' multiple="multiple" >
										<?php
										foreach ($isTagsCategory as $catid => $name) {
											if (isset($isTags[$catid]) && count($isTags[$catid]) > 0) {
												asort($isTags[$catid]);
												echo "<optgroup label='{$name}'>";
												foreach ($isTags[$catid] as $id => $data) {
													$selected = "";
													if (isset($istags_remove_rem[$sku]) && in_array($data['Id'], $istags_remove_rem[$sku])) {
														$selected = "selected='selected'";
													}

													echo "<option value='{$data['Id']}' {$selected}>{$data['Name']}</option>";
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
									<select name="istag_cancelled_app<?php echo $sku; ?>[]" data-placeholder='<?php echo $tag_placeholder; ?>' style="width:300px;" class='chzn-select' multiple="multiple" >
										<?php
										foreach ($isTagsCategory as $catid => $name) {
											if (isset($isTags[$catid]) && count($isTags[$catid]) > 0) {
												asort($isTags[$catid]);
												echo "<optgroup label='{$name}'>";
												foreach ($isTags[$catid] as $id => $data) {
													$selected = "";
													if (isset($istags_cancelled_app[$sku]) && in_array($data['Id'], $istags_cancelled_app[$sku])) {
														$selected = "selected='selected'";
													}

													echo "<option value='{$data['Id']}' {$selected}>{$data['Name']}</option>";
												}
												echo "</optgroup>";
											}
										}
										?>
									</select>
								</p>
								<p>
									Remove Tags:<br />
									<select name="istag_cancelled_rem<?php echo $sku; ?>[]" data-placeholder='<?php echo $tag_placeholder; ?>' style="width:300px;" class='chzn-select' multiple="multiple" >
										<?php
										foreach ($isTagsCategory as $catid => $name) {
											if (isset($isTags[$catid]) && count($isTags[$catid]) > 0) {
												asort($isTags[$catid]);
												echo "<optgroup label='{$name}'>";
												foreach ($isTags[$catid] as $id => $data) {
													$selected = "";
													if (isset($istags_cancelled_rem[$sku]) && in_array($data['Id'], $istags_cancelled_rem[$sku])) {
														$selected = "selected='selected'";
													}

													echo "<option value='{$data['Id']}' {$selected}>{$data['Name']}</option>";
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
				<p style="text-align:right;">
					<input type="submit" class="button-secondary" name="update_tags" value="<?php _e('Update Tags Settings', 'wishlist-member'); ?>" />
				</p>
			</form>
			<?php include_once($this->pluginDir . '/admin/integration.shoppingcart-payperpost-skus-if.php'); ?>
			<form method="post">
				<h2 class="wlm-integration-steps"><?php _e('Step 3. Create an Order Form for each product and set the Web Page URL in Infusionsoft to the following:', 'wishlist-member'); ?></h2>
				<p>&nbsp;&nbsp;<a href="<?php echo $isthankyou_url ?>" onclick="return false"><?php echo $isthankyou_url ?></a> &nbsp; (<a href="javascript:;" onclick="document.getElementById('isthankyou').style.display = 'block';"><?php _e('change', 'wishlist-member'); ?></a>)
				<?php echo $this->Tooltip("integration-shoppingcart-infusionsoft-tooltips-thankyouurlsku"); ?>
				</p>
				<div id="isthankyou" style="display:none">
					<p>&nbsp;&nbsp;<?php echo $wpm_scregister ?><input type="text" name="isthankyou" value="<?php echo $isthankyou ?>" size="8" /> <input type="submit" class="button-secondary" value="<?php _e('Change', 'wishlist-member'); ?>" /></p>
				</div>
				<p><?php _e('The Web Page URL field can be found by selecting Web Address in the Other Options > Thank You Page Settings section of Infusionsoft.'); ?></p>
				<p><?php _e('Note: The "Pass Person\'s Info to Thank You Page URL (This is for Techies)" option in Infusionsoft must be selected to ensure the integration works properly.'); ?></p>
			</form>
			<!-- CRON Settings -->
			<h2 class="wlm-integration-steps"><?php _e('Step 4. Set Up Cron Job:', 'wishlist-member'); ?></h2>
			<p>
				<?php _e('WishList Member uses built-in <a href="https://codex.wordpress.org/Function_Reference/wp_schedule_event" target="_blank">WordPress Cron</a> to sync user\'s membership level status with its corresponding Infusionsoft transaction <strong>twice a day</strong>.', 'wishlist-member'); ?><br />
				<?php _e('In case your site is having issues with WordPress Cron or you want to sync in different and regular interval, you can setup your <strong>server cron job</strong> using details below.', 'wishlist-member'); ?>
			</p>
			<p class="shortcodes">
				<?php _e('Settings:', 'wishlist-member'); ?><br />
				&nbsp;&nbsp;&nbsp;&nbsp;<span>0 0,12 * * *</span>
			</p>
			<p class="shortcodes">
				<?php _e('Command:', 'wishlist-member'); ?><br />
				&nbsp;&nbsp;&nbsp;&nbsp;<span>/usr/bin/wget -O - -q -t 1 <?php echo $isthankyou_url ?>?iscron=1</span>
			</p>
			<p class="shortcodes">
				<em>
					<?php _e('Copy the line above and paste it into the command line of your Cron job.', 'wishlist-member'); ?><br />
					<?php _e('Note: If the above command doesn\'t work, please try the following instead:', 'wishlist-member'); ?>
				</em><br />
				&nbsp;&nbsp;&nbsp;&nbsp;<span>/usr/bin/GET -d <?php echo $isthankyou_url ?>?iscron=1</span>
			</p>
			<?php $logs = $this->GetOption('ifs_sync_log'); ?>
			<?php if ( $logs && is_array( $logs ) ) : ?>
				<p class="shortcodes">
					<small>
						Last Sync >&nbsp;&nbsp;
						Start: <?php echo isset( $logs['start'] ) && $logs['start'] != "" ? $logs['start'] : "--" ; ?>&nbsp;&nbsp;
						End: <?php echo isset( $logs['end'] ) && $logs['end'] != "" ? $logs['end'] : "--" ; ?>&nbsp;&nbsp;&nbsp;&nbsp;
						<?php echo isset( $logs['message'] ) ? "{$logs['message']} ({$logs['count']} records)" : "--" ; ?>
					</small>
				</p>
			<?php endif; ?>
			<!-- CRON Settings END -->
		</blockquote>
		<script type="text/javascript">
		    function selectable() {
		        if (document.selection) {
		            var range = document.body.createTextRange();
		            range.moveToElementText(this);
		            range.select();
		        } else if (window.getSelection) {
		            var range = document.createRange();
		            range.selectNode(this);
		            window.getSelection().addRange(range);
		        }
		    }
		    jQuery(function($) {
		    	jQuery('.shortcodes span').click(selectable);
			});
		</script>
		<?php
		include_once($this->pluginDir . '/admin/tooltips/integration.shoppingcart.infusionsoft.tooltips.php');
		// END Interface
	}
}
