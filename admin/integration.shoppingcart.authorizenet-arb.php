<?php
/*
 * Authorize.Net AIM and ARB Payment Integration
 * Original Author : Peter Indiola
 * Version: $Id: integration.shoppingcart.authorize-arb.php 2797 2015-07-22 18:02:57Z feljun $
 */

$__index__ = 'authorizenet_arb';
$__sc_options__[$__index__] = 'Authorize.Net - Automatic Recurring Billing';
//$__sc_affiliates__[$__index__] = '#';
//$__sc_videotutorial__[$__index__] = wlm_video_tutorial ( 'integration', 'sc', $__index__ );

if (wlm_arrval($_GET,'cart') == $__index__) {
	if (!$__INTERFACE__) {
		// BEGIN Initialization

		$anetarbthankyou = $this->GetOption('anetarbthankyou');
		if (!$anetarbthankyou) {
			$this->SaveOption('anetarbthankyou', $anetarbthankyou = $this->MakeRegURL());
		}

		// save POST URL
		if (wlm_arrval($_POST, 'anetarbthankyou')) {
			$_POST['anetarbthankyou'] = trim(wlm_arrval($_POST, 'anetarbthankyou'));
			$wpmx = trim(preg_replace('/[^A-Za-z0-9]/', '', $_POST['anetarbthankyou']));
			if ($wpmx == $_POST['anetarbthankyou']) {
				if ($this->RegURLExists($wpmx, null, 'anetarbthankyou')) {
					echo "<div class='error fade'>" . __('<p><b>Error:</b> authorize.net arb Thank You URL (' . $wpmx . ') is already in use by a Membership Level or another Shopping Cart.  Please try a different one.</p>', 'wishlist-member') . "</div>";
				} else {
					$this->SaveOption('anetarbthankyou', $anetarbthankyou = $wpmx);
					echo "<div class='updated fade'>" . __('<p>Thank You URL Changed.&nbsp; Make sure to update authorize.net arb with the same Thank You URL to make it work.</p>', 'wishlist-member') . "</div>";
				}
			} else {
				echo "<div class='error fade'>" . __('<p><b>Error:</b> Thank You URL may only contain letters and numbers.</p>', 'wishlist-member') . "</div>";
			}
		}

		if (isset($_POST['anetarbsettings'])) {
			$anetarbsettings = $_POST['anetarbsettings'];
			$this->SaveOption('anetarbsettings', $anetarbsettings);
			echo "<div class='updated fade'>" . __('Your Authorize.net API was successfully changed.', 'wishlist-member') . "</div>";
		}

		if (isset($_POST['authnet_arb_formsettings'])) {
			$authnet_arb_formsettings = $_POST['authnet_arb_formsettings'];
			$this->SaveOption('authnet_arb_formsettings', $authnet_arb_formsettings);
			echo "<div class='updated fade'>" . __('Your Form Settings was successfully changed.', 'wishlist-member') . "</div>";
		}

		$anetarbthankyou_url = $wpm_scregister . $anetarbthankyou;
		$anetarbsettings = $this->GetOption('anetarbsettings');
		$formsettings = $this->GetOption('authnet_arb_formsettings');
		$formsettings = is_array( $formsettings ) ? $formsettings : array();
		// END Initialization

	} else {
		// START Interface
		$xposts = $this->GetPayPerPosts(array('post_title', 'post_type'));
		$post_types = get_post_types('', 'objects');

		$level_names = array();
		foreach($wpm_levels as $sku => $level) {
			$level_names[$sku] = $level['name'];
		}

		foreach ($xposts AS $post_type => $posts) {
			foreach ((array) $posts AS $post) {
				$level_names['payperpost-' . $post->ID] = $post->post_title;
			}
		}

		$currencies = array('USD', 'AUD','BRL','CAD','CZK','DKK','EUR','HKD','HUF','ILS','JPY','MYR','MXN','NOK','NZD','PHP','PLN','GBP','RUB','SGD','SEK','CHF','TWD','THB','TRY');
	    $card_types = array(
	      'Visa'       => 'Visa',
	      'MasterCard' => 'MasterCard',
	      'Discover'   => 'Discover',
	      'Amex'       => 'American Express',
	      'Diners Club'=> 'Diners Club',
	      'JCB'		   => 'JCB',
	    );

		?>
		<style type="text/css">
			.col-edit { display: none;}
			#logo-preview{ width: 90px; height: 40px; border: 1px dashed #cccccc;}
			#logo-preview img { width: 90px; height: 40px; margin: 0px; padding: 0px;}
			#logo-preview p {margin-top: 12px; color: #bbbbbb; font-size: 12px; text-align: center;}
			.shortcodes span {background-color: #cccccc; color: #000000; padding: 2px 4px 2px 4px; margin: 6px 6px 6px 0px; display: inline-block;}
			.wideinput { width: 95%;}
			.mediuminput { width: 50%;}
		</style>
		<form method="post">
			<h2 class="wlm-integration-steps"><?php _e('Step 1. Set Up Authorize.net API Credentials:','wishlist-member'); ?></h2>
			<p><?php _e('API Credentials are in the Authorize.net Merchant Interface in the following section: <br> Account > Settings > API Login ID and Transaction Key','wishlist-member'); ?></strong></p>
			<table class="form-table">
				<tr>
					<th><?php _e('API Login ID','wishlist-member'); ?></th>
					<td><input type="text" style="width: 300px" name="anetarbsettings[api_login_id]" value="<?php echo $anetarbsettings['api_login_id'] ?>"><br/></td>
				</tr>
				<tr>
					<th><?php _e('Transaction Key','wishlist-member'); ?></th>
					<td><input type="text" style="width: 300px" name="anetarbsettings[api_transaction_key]" value="<?php echo $anetarbsettings['api_transaction_key']  ?>"><br/></td>
				</tr>
				<tr>
					<th><?php _e('Sandbox Mode','wishlist-member'); ?></th>
					<td>
						<label><input type="checkbox" class="sandbox_mode" name="anetarbsettings[sandbox_mode]" value="1" <?php if($anetarbsettings['sandbox_mode'] == 1) echo "checked='checked'"?>><?php _e('Enable','wishlist-member'); ?></label>
					</td>
				</tr>
			</table>
			<input type="submit" name="submit" value="Update API Credentials" class="button-primary"/>
		</form>

		<h2 class="wlm-integration-steps"><?php _e('Step 2. Configure Authorize.net Silent Post URL:','wishlist-member'); ?></h2>
		<p><?php _e('Copy and paste the URL below into the Authorize.net Merchant Interface in the following section: <br> Account > Settings > Silent Post URL','wishlist-member'); ?></p>
		<p>&nbsp;<a href="<?php echo $anetarbthankyou_url ?>?action=silent-post" onclick="return false"><?php echo $anetarbthankyou_url ?>?action=silent-post</a></p>

		<h2 class="wlm-integration-steps"><?php _e('Step 3. Manage Subscriptions:','wishlist-member'); ?></h2>
		<p>
			<?php _e('If the Billing Period is "Month","Frequency" can be any number from 1 to 12.','wishlist-member'); ?><br/ >
			<?php _e('If the Billing Period is "Day","Frequency" can be any number from  7 to 365.','wishlist-member'); ?>
		</p>

		<!-- Add Subscriptions -->
		<div class="add-subscription" style="display:none">
			<p><em><?php _e('Select a Membership Level or Pay Per Post then click "New Subscription" to create a new subscription','wishlist-member'); ?></em><br>
				<select name="sku" class="new-product-level">
					<optgroup label="Membership Levels">
						<?php foreach($wpm_levels as $sku => $l): ?>
						<option value="<?php echo $sku?>"><?php echo $l['name']?></option>
						<?php endforeach; ?>
					</optgroup>

					<?php foreach ($xposts AS $post_type => $posts) : ?>
					<optgroup label="<?php echo $post_types[$post_type]->labels->name; ?>">
						<?php foreach ((array) $posts AS $post): ?>
						<option value="payperpost-<?php echo $post->ID?>"><?php echo $post->post_title?></option>
						<?php endforeach; ?>
					</optgroup>
					<?php endforeach; ?>
				</select>
				<a href="<?php echo $anetarbthankyou_url?>?action=new-subscription" class="button-primary new-subscription">New Subscription</a>
				<span class="new-subscription-spinner spinner"></span>
			</p>
		</div>
		<!-- Add Subscriptions Ends -->

		<!-- Subscriptions List -->
		<p class="product-list-loading"><em>Loading subscriptions. Please wait...</em></p>
		<p class="product-list-nothing" style="display:none"><em><?php _e('There are currently no Subscriptions. Create Subscriptions using the options below.','wishlist-member'); ?></em></p>
		<table class="widefat product-list" style="display:none">
			<thead>
				<tr>
					<th scope="col" width="30%"><?php _e('Name', 'wishlist-member'); ?></th>
					<th scope="col" width="10%"><?php _e('Recurring', 'wishlist-member'); ?></th>
					<th scope="col" width="10%"><?php _e('Currency', 'wishlist-member'); ?></th>
					<th scope="col" width="20%"><?php _e('Amount', 'wishlist-member'); ?></th>
					<th scope="col" width="30%"><?php _e('Membership Level', 'wishlist-member'); ?></th>
				</tr>
			</thead>
			<tbody>
			</tbody>
		</table>
		<script type="text/template" id='product-row'>
			<tr id="product-<%=obj.id%>" class="product-row">
				<td class="column-title col-info col-name">
					<strong><a class="row-title"><%= obj.name %></a></strong>
					<div class="row-actions">
						<span class="edit"><a href="#" rel="<%=obj.id%>" class="edit-product">Edit</a> | </span>
						<span class="delete"><a href="#" rel="<%=obj.id%>" class="delete-product">Delete</a></span>
					</div>
				</td>
				<td class="col-info col-recurring"><% if(obj.recurring == 1) print("YES"); else print ("NO"); %></td>
				<td class="col-info col-currency"><%=obj.currency%></td>
				<td class="col-info col-amount">
					<%
						if (obj.recurring == 1) {
							print("<div style='text-align:left;'>");
							if ( obj.trial_billing_cycle > 1 ) {
								print( obj.currency +"<strong>" +obj.trial_amount +"</strong>" );
								print( " every <strong>" +obj.recur_billing_frequency +"</strong> " +obj.recur_billing_period +"/s" );
								print( " for <strong>" +obj.trial_billing_cycle +"</strong> cycle/s <strong>THEN</strong><br />" );
							}
							print( obj.currency +"<strong>" +obj.recur_amount +"</strong>" );
							print( " every <strong>" +obj.recur_billing_frequency +"</strong> " +obj.recur_billing_period +"/s" );
							if ( obj.recur_billing_cycle != "" || obj.recur_billing_cycle > 0 ) {
								print( " for <strong>" +obj.recur_billing_cycle +"</strong> cycle/s" );
							} else {
								print( " for <strong>Unlimited</strong> cycle/s" );
							}
							print("</div>");
						} else {
							print("<strong>" +obj.amount +"</strong>");
						}
					%>
				</td>
				<td class="col-info col-sku">
					<%= obj.name %>
				</td>

				<td class="col-edit col-name">
					<input class="form-val" style="width:98%;" size="30" type="text" name="name" value="<%= obj.name %>"/>
				</td>
				<td class="col-edit col-recurring" style="text-align:center;">
					<input type="checkbox" class="form-val"  name="recurring" value="1" <% if(obj.recurring == 1) print('checked=checked') %>/>
				</td>
				<td class="col-edit col-currency">
					<select class="form-val" name="currency">
						<?php foreach($currencies as $c): ?>
							<option <% if(obj.currency == '<?php echo $c?>') print ('selected="selected"') %> name="<?php echo $c?>"><?php echo $c?></option>
						<?php endforeach; ?>
					</select>
				</td>
				<td class="col-edit col-amount">
					<div class="recurring">
						<table>
							<tr>
								<td>Amount</td>
								<td><input class="form-val" size="15" maxlength="15" type="text" name="recur_amount" value="<%=obj.recur_amount%>"/> <br/></td>
							</tr>
							<tr>
								<td>Period</td>
								<td>
									<select class="form-val dropwide" name="recur_billing_period" >
										<option <% if(obj.recur_billing_period == 'Day') print ('selected="selected"') %> value="Day">Day</option>
										<option <% if(obj.recur_billing_period == 'Month') print ('selected="selected"') %> value="Month">Month</option>
									</select>
								</td>
							</tr>
							<tr>
								<td>Frequency</td>
								<td>
									<input class="form-val" size="3" maxlength="3" type="text" name="recur_billing_frequency" value="<%=obj.recur_billing_frequency%>"/>
								</td>
							</tr>
							<tr>
								<td>Cycle</td>
								<td>
									<input class="form-val" size="4" maxlength="4" type="text" name="recur_billing_cycle" value="<%=obj.recur_billing_cycle%>"/>
								</td>
							</tr>
							<tr><td><em><strong>Trial Period</strong></em></td></tr>
							<tr>
								<td>&nbsp;&nbsp;Cycle</td>
								<td>
									<input class="form-val" size="4" maxlength="4" type="text" name="trial_billing_cycle" value="<%=obj.trial_billing_cycle%>"/>
								</td>
							</tr>
							<tr>
								<td>&nbsp;&nbsp;Amount:</td>
								<td><input class="form-val" size="15" maxlength="15" type="text" name="trial_amount" value="<%=obj.trial_amount%>"/> <br/></td>
							</tr>
						</table>
					</div>
					<div class="onetime">
						<input class="form-val" type="text" size="15" maxlength="15" name="amount" value="<%=obj.amount%>"/>
					</div>

				</td>
				<td class="col-edit col-sku">
					<select name="sku" class="form-val dropwide">
						<optgroup label="Membership Levels">
							<?php foreach($wpm_levels as $sku => $l): ?>
							<option <% if(obj.sku == '<?php echo $sku?>') print('selected="selected"')%> value="<?php echo $sku?>"><?php echo $l['name']?></option>
							<?php endforeach; ?>
						</optgroup>

						<?php foreach ($xposts AS $post_type => $posts) : ?>
						<optgroup label="<?php echo $post_types[$post_type]->labels->name; ?>">
							<?php foreach ((array) $posts AS $post): ?>
							<option <% if(obj.sku == 'payperpost-<?php echo $post->ID?>') print('selected="selected"')%> value="payperpost-<?php echo $post->ID?>"><?php echo $post->post_title?></option>
							<?php endforeach; ?>
						</optgroup>
						<?php endforeach; ?>
					</select>

					<hr/>
					<p>
						<input class="form-val" type="hidden" name="id" value="<%=obj.id%>"/>
						<button class="button-primary save-product">Save Product</button>
						<button class="button-secondary cancel-edit">Cancel</button>
						<span class="spinner"></span>
					</p>
				</td>

			</tr>
		</script>
		<!-- Subscriptions List Ends -->

		<!-- CRON Settings -->
		<h2 class="wlm-integration-steps"><?php _e('Step 4. Set Up Cron Job (optional):', 'wishlist-member'); ?></h2>
		<p>
			<?php _e('WishList Member uses built-in <a href="https://codex.wordpress.org/Function_Reference/wp_schedule_event" target="_blank">WordPress Cron</a> to sync user\'s membership level status with its corresponding Authorize.net ARB transactions <strong>twice a day</strong>.', 'wishlist-member'); ?><br />
			<?php _e('In case your site is having issues with WordPress Cron or you want to sync in different and regular interval, you can setup your <strong>server cron job</strong> using details below.', 'wishlist-member'); ?>
		</p>
		<p class="shortcodes">
			<?php _e('Settings:', 'wishlist-member'); ?><br />
			&nbsp;&nbsp;&nbsp;&nbsp;<span>0 0,12 * * *</span>
		</p>
		<p class="shortcodes">
			<?php _e('Command:', 'wishlist-member'); ?><br />
			&nbsp;&nbsp;&nbsp;&nbsp;<span>/usr/bin/wget -O - -q -t 1 <?php echo $anetarbthankyou_url ?>?action=sync-arb</span>
		</p>
		<p class="shortcodes">
			<em>
				<?php _e('Copy the line above and paste it into the command line of your Cron job.', 'wishlist-member'); ?><br />
				<?php _e('Note: If the above command doesn\'t work, please try the following instead:', 'wishlist-member'); ?>
			</em><br />
			&nbsp;&nbsp;&nbsp;&nbsp;<span>/usr/bin/GET -d <?php echo $anetarbthankyou_url ?>?action=sync-arb</span>
		</p>
		<?php $logs = $this->GetOption('auhtnetarb_sync_log'); ?>
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

		<!-- Settings -->
		<form method="post">
			<h2 class="wlm-integration-steps"><?php _e("Step 5. Customize Payment Form:", "wishlist-member") ?></h2>
			<table class="widefat">
				<tr>
					<th width="30%">Address Fields</th>
					<td width="35%">
						<?php $checked = ! isset( $formsettings['display_address'] ) || $formsettings['display_address'] != 1 ?  "": "checked='checked'"; ?>
						<input type="checkbox" name="authnet_arb_formsettings[display_address]" value="1" <?php echo $checked ?> /> Show address fields
					</td>
					<td width="35%">&nbsp;</td>
				</tr>
				<tr>
					<th width="30%"><?php _e("Support Email", "wishlist-member") ?></th>
					<td width="35%">
						<?php $supportemail = !isset($formsettings['supportemail']) ||  empty($formsettings['supportemail']) ? "" : $formsettings['supportemail']; ?>
						<input type="text" name="authnet_arb_formsettings[supportemail]" value="<?php echo $supportemail ?>" class="mediuminput" />
					</td>
					<td width="35%">&nbsp;</td>
				</tr>
				<tr>
					<th><?php _e("Heading Logo", "wishlist-member") ?></th>
					<td colspan="2">
						<?php $logo = !isset($formsettings['logo']) ||  empty($formsettings['logo']) ? "" : $formsettings['logo']; ?>
						<div id="logo-preview">
							<?php if (!empty($logo)): ?>
								<img src="<?php echo $logo ?>" alt="Form Logo" />
							<?php else: ?>
								<p>No Logo</p>
							<?php endif; ?>
						</div>
						<input id="form-logo" type="text" name="authnet_arb_formsettings[logo]" value="<?php echo $logo ?>" class="mediuminput" />
						<a href="media-upload.php?type=image&amp;TB_iframe=true" class="thickbox logo-upload button-secondary">Change</a>
					</td>
				</tr>
				<tr>
					<th colspan="3">&nbsp;</th>
				</tr>
				<tr>
					<th width="30%">&nbsp;</th>
					<th width="35%"><?php _e("Non-Recurring", "wishlist-member") ?></th>
					<th width="35%"><?php _e("Recurring", "wishlist-member") ?></th>
				</tr>
				<tr>
					<th><?php _e("Form Heading", "wishlist-member") ?></th>
					<td>
						<?php $formheading = !isset($formsettings['formheading']) ||  empty($formsettings['formheading']) ? "Register to %level" : $formsettings['formheading']; ?>
						<input type="text" name="authnet_arb_formsettings[formheading]" value="<?php echo $formheading ?>" class="wideinput" />
					</td>
					<td>
						<?php $formheadingrecur = !isset($formsettings['formheadingrecur']) ||  empty($formsettings['formheadingrecur']) ? "Subscribe to %level" : $formsettings['formheadingrecur']; ?>
						<input type="text" name="authnet_arb_formsettings[formheadingrecur]" value="<?php echo $formheadingrecur ?>" class="wideinput" />
					</td>
				</tr>
				<tr>
					<th><?php _e("Form Button Label", "wishlist-member") ?></th>
					<td>
						<?php $formbuttonlabel = !isset($formsettings['formbuttonlabel']) ||  empty($formsettings['formbuttonlabel']) ? "Pay %amount" : $formsettings['formbuttonlabel']; ?>
						<input type="text" name="authnet_arb_formsettings[formbuttonlabel]" value="<?php echo $formbuttonlabel ?>" class="wideinput"/>
					</td>
					<td>
						<?php $formbuttonlabel_recur = !isset($formsettings['formbuttonlabelrecur']) ||  empty($formsettings['formbuttonlabelrecur']) ? "Pay %amount" : $formsettings['formbuttonlabelrecur']; ?>
						<input type="text" name="authnet_arb_formsettings[formbuttonlabelrecur]" value="<?php echo $formbuttonlabel_recur ?>" class="wideinput" />
					</td>
				</tr>
				<tr>
					<th><?php _e("Text BEFORE Payment Form", "wishlist-member") ?></th>
					<td>
						<?php $beforetext = !isset($formsettings['beforetext']) ||  empty($formsettings['beforetext']) ? "" : $formsettings['beforetext']; ?>
						<textarea cols="60" rows="4" name="authnet_arb_formsettings[beforetext]" class="wideinput" ><?php echo $beforetext ?></textarea>
					</td>
					<td>
						<?php $beforetextrecur = !isset($formsettings['beforetextrecur']) ||  empty($formsettings['beforetextrecur']) ? "" : $formsettings['beforetextrecur']; ?>
						<textarea cols="60" rows="4" name="authnet_arb_formsettings[beforetextrecur]" class="wideinput" ><?php echo $beforetextrecur ?></textarea>
					</td>
				</tr>
				<tr>
					<th><?php _e("Text AFTER Payment Form", "wishlist-member") ?></th>
					<td>
						<?php $aftertext = !isset($formsettings['aftertext']) ||  empty($formsettings['aftertext']) ? "" : $formsettings['aftertext']; ?>
						<textarea cols="60" rows="4" name="authnet_arb_formsettings[aftertext]" class="wideinput" ><?php echo $aftertext ?></textarea>
					</td>
					<td>
						<?php $aftertextrecur = !isset($formsettings['aftertextrecur']) ||  empty($formsettings['aftertextrecur']) ? "" : $formsettings['aftertextrecur']; ?>
						<textarea cols="60" rows="4" name="authnet_arb_formsettings[aftertextrecur]" class="wideinput" ><?php echo $aftertextrecur ?></textarea>
					</td>
				</tr>
				<tr>
					<th>&nbsp;</th>
					<td class="shortcodes" colspan="3">
						<p><?php _e("These are the shortcodes that you can use in the settings above.", "wishlist-member") ?></p>
						<span title="Level Name">%level</span>
						<span title="Amount">%amount</span>
						<span title="Billing Frequency for Recurring Payments only.">%frequency</span>
						<span title="Billing Period for Recurring Payments only.">%period</span>
						<span title="Billing Cycle for Recurring Payments only.">%cycle</span>
						<span title="Trial Cycles for Recurring Payments only.">%trial_cycle</span>
						<span title="Trial Amount for Recurring Payments only.">%trial_amount</span>
						<span title="Billing Cycle + Trial Cycle for Recurring Payments only.">%total_cycle</span>
						<span title="Currency">%currency</span>
						<span title="Support Email if set.">%supportemail</span>
						<p><small><em>* <?php _e("Hover your mouse on the shortcodes for description.", "wishlist-member") ?></em></small></p>
					</td>
				</tr>
				<tr>
					<th>Supported Credit Cards</th>
					<td colspan="3">
						<p>
							<?php
								$selected_card_types = isset( $formsettings['credit_cards'] ) ? $formsettings['credit_cards'] : array("Visa","MasterCard","Discover","Amex");
								$selected_card_types = count( $selected_card_types ) > 0 ? $selected_card_types : array("Visa","MasterCard","Discover","Amex");
							?>
							<?php foreach( $card_types AS $key=>$name ) :?>
								<?php $check = in_array( $key, $selected_card_types ) ? "checked='checked'" : ""; ?>
								<input type="checkbox" id="cc_<?php echo $key; ?>" name="authnet_arb_formsettings[credit_cards][]" value="<?php echo $key; ?>" <?php echo $check; ?> />
								<label for="cc_<?php echo $key; ?>"><?php echo $name; ?></label>&nbsp;&nbsp;&nbsp;&nbsp;
							<?php endforeach; ?>
						</p>
					</td>
				</tr>
			</table>
			<p><input type="submit" name="submit" value="Save Form Settings" class="button-primary"/></p>
		</form>
		<!-- Settings Ends -->

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

				var send_to_editor = function(html) {
					imgurl = jQuery('img', html).attr('src');
					var el = jQuery('#form-logo');
					el.val(imgurl);
					tb_remove();
					//also update the img preview
					jQuery('#logo-preview').html('<img src="' + imgurl + '">');
				}

				var level_names = JSON.parse('<?php echo json_encode($level_names)?>');

				jQuery(function($) {

					jQuery('.shortcodes span').click(selectable);

					$('.dropmenu').on('click', function(ev) {
						ev.preventDefault();
						$('li.dropme ul').not( $(this).parent()).hide();
						console.log($(this).parent().find('ul'));
					});

					function update_fields(el, tr) {
						if (el.val() == 1) {
							tr.find('.amount').find('input').attr('disabled', true).val('');
							tr.find('.plans').find('select').removeAttr('disabled');
						} else {
							tr.find('.plans').find('select').attr('disabled', true).val('');
							tr.find('.amount').find('input').removeAttr('disabled');
						}
					}
					/** table handler **/

					var table_handler = {};

					table_handler.toggle_recurring = function(id) {
						var row = $('#product-' + id);
						var el = row.find('input[name=recurring]');
						if(el.prop('checked')) {
							row.find('.recurring').show();
							row.find('.onetime').hide();
						} else {
							row.find('.recurring').hide();
							row.find('.onetime').show();
						}
					}
					table_handler.remove_row = function(id) {
						$('#product-' + id).remove();
						self.table.find('tr').each(function(i, e) {
							$(e).removeClass('alternate');
							if(i % 2 == 0) {
								$(e).addClass('alternate');
							}
						});
						table_handler.hide_show();
					}

					table_handler.render_row = function(obj) {
						var cnt      = self.table.find('tr').length;
						var template = $("#product-row").html();
						var str      = _.template(template, {'obj': obj} );
						var el       = $('#product-' + obj.id);


						if(el.length > 0) {
							el.replaceWith(str);
						} else {
							self.table.find('tbody').eq(0).append(str);
						}

						table_handler.toggle_recurring(obj.id);


						self.table.find('tr.product-row').removeClass('alternate');
						self.table.find('tr.product-row:even').addClass('alternate');

						table_handler.hide_show();

					}

					table_handler.hide_show = function() {
						$('.product-list-loading').hide();
						$('.add-subscription').show();
						if(self.table.find('tbody tr').length) {
							self.table.show();
							$('.product-list-nothing').hide();
						}else{
							self.table.hide();
							$('.product-list-nothing').show();
						} 
					}
					table_handler.end_edit = function(id) {
						$('#product-' + id).find('td.col-info').show();
						$('#product-' + id).find('td.col-edit').hide();
					}
					table_handler.edit_row = function(id) {
						$('#product-' + id).find('td.col-info').hide();
						$('#product-' + id).find('td.col-edit').show();
					}
					table_handler.fetch = function() {
						$.post(ajaxurl + '?action=wlm_anetarb_all-subscriptions', {}, function(res) {
							var obj = JSON.parse(res);
							if(obj === false || obj.length <= 0) {
									table_handler.hide_show();
							} else {
								for(i in obj) {
									table_handler.render_row(obj[i]);
								}
							}
						});
					}
					table_handler.edit_product = function(id) {
						table_handler.edit_row(id);
					}
					table_handler.delete_subscription = function(id) {
						if(confirm('Are you sure you want to delete this subscription?')) {
							$.post(ajaxurl + '?action=wlm_anetarb_delete-subscription', {id: id}, function(res) {
								table_handler.remove_row(id);
							});
						}
					}
					table_handler.save_subscription = function(id) {
						var row = $('#product-' + id);
						row.find('.spinner').show();
						row.find('.spinner').addClass("is-active");

						var data = {};
						row.find('.form-val').each(function(i, e) {
							var el = $(e);
							if ( $(el).is(':checkbox') ) {
								data[el.prop('name')] = $(el).is(':checked') ? 1 : 0;
							} else {
								data[el.prop('name')] =  el.val();
							}
						});


						$.post(ajaxurl + '?action=wlm_anetarb_save-subscription', data, function(res) {
							row.find('.spinner').hide();
							row.find('.spinner').removeClass("is-active");
							table_handler.render_row(JSON.parse(res));
							table_handler.end_edit(id);
						});


					}
					table_handler.new_subscription = function() {
						$('.new-subscription').attr('disabled','disabled');
						$('.new-subscription-spinner').show();
						$('.new-subscription-spinner').addClass("is-active");
						var data = {
							'name' : $('.new-product-level option:selected').html(),
							'sku'  : $('.new-product-level').val()
						};
						$.post(ajaxurl + '?action=wlm_anetarb_new-subscription', data, function(res) {
							var obj = JSON.parse(res);
							var template = $("#product-row").html();
							table_handler.render_row(obj);
							$('.new-subscription').removeAttr('disabled');
							$('.new-subscription-spinner').hide();
							$('.new-subscription-spinner').removeClass("is-active");
						});
					}
					table_handler.init = function(table) {
						self.table = table;

						$('.new-subscription').on('click', function(ev) {
							ev.preventDefault();
							table_handler.new_subscription();
						});

						$('.product-list').on('click', '.delete-product', function(ev) {
							ev.preventDefault();
							table_handler.delete_subscription( $(this).attr('rel'));
						});

						$('.product-list').on('click', '.edit-product', function(ev) {
							ev.preventDefault();
							table_handler.edit_product( $(this).attr('rel'));
						});

						$('.product-list').on('click', '.save-product', function(ev) {
							ev.preventDefault();
							var id = $(this).parent().find('input[name=id]').val();
							table_handler.save_subscription(id);
						});

						$('.product-list').on('click', '.cancel-edit', function(ev) {
							ev.preventDefault();
							var id = $(this).parent().find('input[name=id]').val();
							table_handler.end_edit(id);
						});

						$('.product-list').on('change', '.col-recurring input', function(ev) {
							ev.preventDefault();
							var id = $(this).parent().parent().find('input[name=id]').val();
							table_handler.toggle_recurring(id);
						});

						table_handler.fetch();
					}
					table_handler.init($('.product-list'));
					/* end table handler **/
				});
		</script>
		<?php
		include_once($this->pluginDir . '/admin/tooltips/integration.shoppingcart.authorize-arb.tooltips.php');
		// END Interface
	}
}
