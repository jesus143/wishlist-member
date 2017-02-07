<?php
/*
 * SEnd Broadcast message to members
 */
?>
<h2>
	<?php _e('Members &raquo; Email Broadcast &raquo; Create', 'wishlist-member'); ?>
	<a class="add-new-h2" href="?<?php echo $this->QueryString('usersearch', 'mode', 'level') ?>&mode=broadcast"><?php _e('View Broadcast Mails', 'wishlist_member') ?></a>
</h2>
<br>
<?php
$current_user = wp_get_current_user();//we will be using
$sender = $this->GetOption('email_sender_name');
$senderemail = $this->GetOption('email_sender_address');
$total_recipients = 0;
if ($sender && $senderemail):
	global $wpdb;
	if (wlm_arrval($_GET,'id') != "" && (wlm_arrval($_POST,'subject') == "" && wlm_arrval($_POST,'message') == "" && wlm_arrval($_GET,'msg') == "")) {
		$email = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "wlm_emailbroadcast WHERE id=" . $_GET['id']);
	}
	$form_action = $this->QueryString('msg');
	$back_form_action = $this->QueryString('msg');
	if ( wlm_arrval( $_POST, 'preview' ) ) {
		$form_action = $this->QueryString( 'msg', 'mode', 'id' ) ."&mode=broadcast";
	}
?>
	<form method="post" action="?<?php echo $form_action; ?>">
		
		<?php
		if (!$_POST) {
			$_POST = $this->GetOption('broadcast');
		}

		if (wlm_arrval($_POST,'preview')) {
			echo '<h3>' . __('Preview Message', 'wishlist-member') . '</h3>';
			$previewclass = 'style="display:none"';

			if (isset($_POST['send_to_admin']) && wlm_arrval($_POST,'send_to_admin') == 1) {
				$current_user = wp_get_current_user();//we will be using
				// get can spam requirements
				$canspamaddress = '';
				$canspamaddress = trim(wlm_arrval($_POST,'canspamaddr1')) . "\n";
				if (trim(wlm_arrval($_POST,'canspamaddr2')))
					$canspamaddress.=trim(wlm_arrval($_POST,'canspamaddr2')) . "\n";
				$canspamaddress.=trim(wlm_arrval($_POST,'canspamcity')) . ", ";
				$canspamaddress.=trim(wlm_arrval($_POST,'canspamstate')) . "\n";
				$canspamaddress.=trim(wlm_arrval($_POST,'canspamzip')) . "\n";
				$canspamaddress.=trim(wlm_arrval($_POST,'canspamcountry'));
				//prepare the message
				$msg = trim(wlm_arrval($_POST,'message'));
				$footer = "\n\n" . trim(wlm_arrval($_POST,'signature')) . "\n\n" . $canspamaddress;
				$header_type = (wlm_arrval($_POST,'sent_as') != "html") ? 'plain' : 'html';

				//add unsubcribe and user details link
				$footer .= "\n\n" . sprintf(WLMCANSPAM, $current_user->ID . '/' . substr(md5($current_user->ID . WLMUNSUBKEY), 0, 10));
				//process shortcodes
				$shortcode_data = $this->wlmshortcode->manual_process($current_user->ID, $msg, true);
				//lets make sure that it is an array
				if (!is_array($shortcode_data)) {
					$shortcode_data = array();
				}
				/* strip tags for membership levels */
				if ($shortcode_data['wlm_memberlevel']) {
					$shortcode_data['wlm_memberlevel'] = strip_tags($shortcode_data['wlm_memberlevel']);
				}
				if ($shortcode_data['wlmmemberlevel']) {
					$shortcode_data['wlmmemberlevel'] = strip_tags($shortcode_data['wlmmemberlevel']);
				}
				if ($shortcode_data['memberlevel']) {
					$shortcode_data['memberlevel'] = strip_tags($shortcode_data['memberlevel']);
				}

				if (wlm_arrval($_POST,'sent_as') == "html") {
					$fullmsg = $msg . nl2br(wordwrap($footer));
				} else {
					$fullmsg = $msg . $footer;
					$fullmsg = wordwrap($fullmsg);
				}

				$x = array(get_bloginfo('admin_email'),stripslashes(wlm_arrval($_POST,'subject')),stripslashes($fullmsg), $header_type);
				$name = 'wlmember_preview_mail' . '_' . md5(serialize($x));
				$mailed = add_option($name, $x, '', 'no');

				$mails = $wpdb->get_results("SELECT `option_name`,`option_value` FROM {$wpdb->options} WHERE `option_name` LIKE 'wlmember\_preview\_mail\_%'");

				if ($mails) {
					// go through and send the emails
					foreach ((array) $mails AS $mail) {
						$name = $mail->option_name;
						$mail = maybe_unserialize($mail->option_value);
						if (strpos($mail[3], 'html') !== false) {
							$result = $this->SendHTMLMail($mail[0], $mail[1], $mail[2], $shortcode_data,false, null, 'UTF-8');
						} else {
							$result = $this->SendMail($mail[0], $mail[1], $mail[2], $shortcode_data,false, null, 'UTF-8');
						}
						delete_option($name);
					}
				}
			}
		} else {
			if ($email->subject != "" || $email->text_body != "") {
				if(strpos($email->mlevel,"SaveSearch") !== false){
					$_POST['save_searches'] = $email->mlevel;
					$_POST['send_to'] = "send_search";
				}else{
					$_POST['send_mlevels'] = explode("#", $email->mlevel);
					$_POST['send_to'] = "send_mlevels";
				}
				$_POST['subject'] = trim($email->subject);
				$_POST['message'] = trim($email->text_body);
				$_POST['sent_as'] = trim($email->sent_as);
			}
			$previewclass = '';
		}
		?>
		<script type='text/javascript' src='<?php echo $this->pluginURL ?>/js/nicEdit.js'></script>
		<script type="text/javascript">
			//<![CDATA[
			var area1;
			function toggleHTML(html) {
				if(html) {
					area1 = new nicEditor({iconsPath : '<?php echo $this->pluginURL ?>/images/nicEditorIcons.gif',fullPanel : true}).panelInstance('broadcastmessage',{hasPanel : true});
				} else {
					if(area1){
						if( nicEditors.findEditor('broadcastmessage').getContent("") == "<br>" ) {
							nicEditors.findEditor('broadcastmessage').setContent("");
						}
						area1.removeInstance('broadcastmessage');
						area1 = null;
					}
				}
			}
			function addContent(content){
				var editor = nicEditors.findEditor('broadcastmessage');
				var editingArea = editor.getElm();
				var userSelection = editor.getSel();
				editingArea.focus();
				// IE.
				if (document.selection) {
					editingArea.focus();
					userSelection.createRange().text = content;
				}
				else {
					// Convert selection to range.
					var range;
					// W3C compatible.
					if (userSelection.getRangeAt) {
						range = userSelection.getRangeAt(0);
					}
					// Safari.
					else {
						range = editingArea.ownerDocument.createRange();
						range.setStart(userSelection.anchorNode, userSelection.anchorOffset);
						range.setEnd(userSelection.focusNode, userSeletion.focusOffset);
					}
					// The code below doesn't work in IE, but it never gets here.
					var fragment = editingArea.ownerDocument.createDocumentFragment();
					// Fragments don't support innerHTML.
					var wrapper = editingArea.ownerDocument.createElement('div');
					wrapper.innerHTML = content;
					while (wrapper.firstChild) {
						fragment.appendChild(wrapper.firstChild);
					}
					range.deleteContents();
					// Only fragment children are inserted.
					range.insertNode(fragment);
				}
			}
			bkLib.onDomLoaded(function() { toggleHTML(<?php echo (wlm_arrval($_POST,'sent_as') == "html" ? "true" : "false"); ?>); });
			//]]>
		</script>

		<?php if ($previewclass == ""): /* Include only when not in preview */ ?>
		<script type='text/javascript'>
			jQuery(document).ready(function($) {
				<?php
				if (wlm_arrval($_POST,'sent_as') == "html") {
					echo "$('div#tinyMCE_insertHTML').css('display','inline');";
					echo "$('div#insertHTML').css('display','none');";
					echo "$('div#tinyMCE_insertHTML').css('float','left');";
				} else if (wlm_arrval($_POST,'sent_as') == "text") {
					echo "$('div#tinyMCE_insertHTML').css('display','none');";
					echo "$('div#insertHTML').css('display','inline');";
					echo "$('div#insertHTML').css('float','left');";
				} else {
					echo "$('div#tinyMCE_insertHTML').css('display','none');";
					echo "$('div#insertHTML').css('display','inline');";
					echo "$('div#insertHTML').css('float','left');";
				}
				?>
				$('input.toggleHTML').click(
				function() {
					toggleHTML(true);
					$('div#tinyMCE_insertHTML').css("display","inline");
					$('div#insertHTML').css("display","none");
					$('div#tinyMCE_insertHTML').css("float","left");
				});
				$('input.toggleTEXT').click(
				function() {
					toggleHTML(false);
					$('div#insertHTML').css("display","inline");
					$('div#tinyMCE_insertHTML').css("display","none");
					$('div#insertHTML').css("float","left");
				});
				$( "#send_to" ).change(function() {
				 	if(this.value == "send_mlevels"){
				 		$("tr#send_mlevels").css( "display", "table-row" );
				 		$("tr#include_levels").css( "display", "table-row" );
				 		$("tr#send_search").css( "display", "none" );
				 	}else if(this.value == "send_search"){
				 		$("tr#send_search").css( "display", "table-row" );
				 		$("tr#send_mlevels").css( "display", "none" );
				 		$("tr#include_levels").css( "display", "none" );
				 		
				 	}
				});				
			});					
		</script>
		<?php endif; ?>

		<table class="form-table">
			<tr valign="top">
				<th scope="row" style="border-bottom:none"><?php _e('Subject', 'wishlist-member'); ?></th>
				<td style="border-bottom:none">
					<input <?php echo $previewclass; ?> type="text" name="subject" value="<?php _e(stripslashes(wlm_arrval($_POST,'subject'))); ?>" size="60" />
					<?php
					if (wlm_arrval($_POST,'preview'))
						_e(stripslashes($_POST['subject'] ? $_POST['subject'] : '<font color="red">(no subject)</font>'));
					?>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php _e('Send As', 'wishlist-member'); ?></th>
				<td>
					<p align="left">
						<span <?php echo $previewclass; ?> ><input class="toggleHTML" type="radio" name="sent_as" value="html" <?php echo (wlm_arrval($_POST,'sent_as') == "html" ? 'checked="checked"' : '') ?> /> HTML</span>
						<span <?php echo $previewclass; ?> ><input <?php echo $previewclass; ?> class="toggleTEXT" type="radio" name="sent_as" value="text" <?php echo (wlm_arrval($_POST,'sent_as') != "html" ? 'checked="checked"' : '') ?> />Text</span>
						<?php
						if (wlm_arrval($_POST,'preview'))
							_e(strtoupper(wlm_arrval($_POST,'sent_as')));
						?>
					</p>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e('Message', 'wishlist-member'); ?></th>
				<td>
					<div <?php echo $previewclass; ?> style="float:left; margin-right:10px;">
						<textarea cols="67" style="height:300px;" id="broadcastmessage" name="message" ><?php _e(stripslashes(wlm_arrval($_POST,'message'))); ?></textarea>
					</div>
					<?php
					/* Include only when not in preview */
					if ($previewclass == ""):
						?>
						<div <?php echo $previewclass; ?> id="insertHTML" >
								<?php _e('WishList Member Merge Codes', 'wishlist-member'); ?>
								<?php echo $this->Tooltip("members-broadcast-tooltips-message-insert-info"); ?>
								<br />
							<select onchange="if(this.value!=''){wpm_insertHTML(this.value,'broadcastmessage');}this.selectedIndex=0;">
								<option value="">---</option>
								<?php for ($i = 0; $i < count($this->wlmshortcode->shortcodes); $i = $i + 3): ?>
									<?php if ($this->wlmshortcode->shortcodes[$i + 1] != "Password"): ?>
										<option value="[<?php echo $this->wlmshortcode->shortcodes[$i][0]; ?>]"><?php echo $this->wlmshortcode->shortcodes[$i + 1]; ?></option>
									<?php endif; ?>
								<?php endfor; ?>
							</select>
							<br /><br />
							<?php if ( count($this->wlmshortcode->custom_user_data) > 0 ): ?>
								<?php _e('Custom Registration Fields', 'wishlist-member'); ?>
								<?php echo $this->Tooltip("members-broadcast-tooltips-message-insert-custom"); ?>
								<br />
								<select onchange="if(this.selectedIndex!=''){wpm_insertHTML(this.value,'broadcastmessage');}this.selectedIndex=0;">
									<option value="">---</option>
									<?php for ($i = 0; $i < count($this->wlmshortcode->custom_user_data); $i++): ?>
										<?php if ( trim( $this->wlmshortcode->custom_user_data[$i] ) != "" && strlen( trim( $this->wlmshortcode->custom_user_data[$i] ) ) > 1 ): ?>
											<option value="[wlm_custom <?php echo $this->wlmshortcode->custom_user_data[$i]; ?>]"><?php echo $this->wlmshortcode->custom_user_data[$i]; ?></option>
										<?php endif; ?>
									<?php endfor; ?>
								</select>
							<?php endif; ?>
						</div>
						<div <?php echo $previewclass; ?> id="tinyMCE_insertHTML" >
								<?php _e('WishList Member Merge Codes', 'wishlist-member'); ?>
								<?php echo $this->Tooltip("members-broadcast-tooltips-message-insert-info"); ?>
								<br />
							<select onchange="if(this.selectedIndex>0){addContent(this.value);this.selectedIndex=0}">
								<option value="">---</option>
								<?php for ($i = 0; $i < count($this->wlmshortcode->shortcodes); $i = $i + 3): ?>
									<option value="[<?php echo $this->wlmshortcode->shortcodes[$i][0]; ?>]"><?php echo $this->wlmshortcode->shortcodes[$i + 1]; ?></option>
							<?php endfor; ?>
							</select>
							<br /><br />
							<?php if ( count($this->wlmshortcode->custom_user_data) > 0 ): ?>
								<?php _e('Custom Registration Fields', 'wishlist-member'); ?>
								<?php echo $this->Tooltip("members-broadcast-tooltips-message-insert-custom"); ?>
								<br />
								<select onchange="if(this.selectedIndex!=''){addContent(this.value);}this.selectedIndex=0;">
									<option value="">---</option>
									<?php for ($i = 0; $i < count($this->wlmshortcode->custom_user_data); $i++): ?>
										<?php if ( trim( $this->wlmshortcode->custom_user_data[$i] ) != "" && strlen( trim( $this->wlmshortcode->custom_user_data[$i] ) ) > 1 ): ?>
											<option value="[wlm_custom <?php echo $this->wlmshortcode->custom_user_data[$i]; ?>]"><?php echo $this->wlmshortcode->custom_user_data[$i]; ?></option>
										<?php endif; ?>
									<?php endfor; ?>
								</select>
							<?php endif; ?>
						</div>
					<?php endif; ?>
					<?php
					if (wlm_arrval($_POST,'preview')) {
						$str_msg = wlm_arrval($_POST,'message');
						$str_msg = $str_msg ? $str_msg : '<font color="red">' . __('(no message)', 'wishlist-member') . '</font>';
						$str_msg = stripslashes($str_msg);
						if (wlm_arrval($_POST,'sent_as') != "html") {
							$str_msg = wordwrap($str_msg, 70, "\r\n");
							$str_msg = nl2br($str_msg);
						}
						_e($str_msg);
					}
					?>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e('Signature', 'wishlist-member'); ?> <?php echo $this->Tooltip("members-broadcast-tooltips-Signature"); ?></th>
				<td>
					<textarea <?php echo $previewclass; ?> name="signature" id="broadcastsignature" cols="40" rows="10" style="float:left;margin-right:10px"><?php _e(stripslashes(wlm_arrval($_POST,'signature'))); ?></textarea>
					<?php
					if (wlm_arrval($_POST,'preview'))
						_e(nl2br(wordwrap(stripslashes($_POST['signature'] ? $_POST['signature'] : ''))));
					?>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">CAN SPAM <?php echo $this->Tooltip("members-broadcast-tooltips-CAN-SPAM"); ?><br />
					<p>
						<small><?php _e('Note that adding a physical address to the email is optional but is also highly recommended in order to help prevent it from being viewed and marked as SPAM.', 'wishlist-member'); ?></small>
					</p>
				</th>
				<td>
					<div <?php echo $previewclass; ?>>
						<?php _e('Address 1', 'wishlist-member'); ?>:<br />
						<input size="40" type="text" name="canspamaddr1" value="<?php _e(stripslashes(wlm_arrval($_POST,'canspamaddr1'))); ?>" /><br />
						<?php _e('Address 2', 'wishlist-member'); ?>:<br />
						<input size="40" type="text" name="canspamaddr2" value="<?php _e(stripslashes(wlm_arrval($_POST,'canspamaddr2'))); ?>" /><br />
						<?php _e('City', 'wishlist-member'); ?>:<br />
						<input size="30" type="text" name="canspamcity" value="<?php _e(stripslashes(wlm_arrval($_POST,'canspamcity'))); ?>" /><br />
						<?php _e('State', 'wishlist-member'); ?>:<br />
						<input size="30" type="text" name="canspamstate" value="<?php _e(stripslashes(wlm_arrval($_POST,'canspamstate'))); ?>" /><br />
						<?php _e('ZIP', 'wishlist-member'); ?>:<br />
						<input size="10" type="text" name="canspamzip" value="<?php _e(stripslashes(wlm_arrval($_POST,'canspamzip'))); ?>" /><br />
						<?php _e('Country', 'wishlist-member'); ?>:<br />
						<input size="30" type="text" name="canspamcountry" value="<?php _e(stripslashes(wlm_arrval($_POST,'canspamcountry'))); ?>" /><br />
					</div>
					<?php
					if (wlm_arrval($_POST,'preview')) {
						$address = '';
						$address = $_POST['canspamaddr1'] . "\n";
						if (wlm_arrval($_POST,'canspamaddr2'))
							$address.=$_POST['canspamaddr2'] . "\n";
						$address.=$_POST['canspamcity'] . ", ";
						$address.=$_POST['canspamstate'] . "\n";
						$address.=$_POST['canspamzip'] . "\n";
						$address.=$_POST['canspamcountry'];
						$addressok = true;
						if (empty($_POST['canspamaddr1']) || empty($_POST['canspamcity']) || empty($_POST['canspamstate']) || empty($_POST['canspamzip']) || empty($_POST['canspamcountry'])) {
							$address = '<font color="blue">(Incomplete Address: This email is most likely to be considered as SPAM.)</font><br />' . $address;
						}
						_e(nl2br(stripslashes($address)));
						echo "<br /><br />";
						_e(sprintf(nl2br(WLMCANSPAM), 'xxxxxxxx'));
					}
					?>
				</td>
			</tr>
			<?php
			$requeue = false;
			if ((wlm_arrval($_GET,'action') == 'requeue' && is_numeric(wlm_arrval($_GET,'id')))) {
				$broadcast = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "wlm_emailbroadcast WHERE id =" . $_GET['id']);
				if (!is_null($broadcast)) {
					if ($broadcast->failed_address != '') {
						$requeue = true;
						?>
						<tr valign="top">
							<th scope="row"><?php _e('Send To', 'wishlist-member'); ?></th>
							<td style="color:red;">
								<strong><?php echo $broadcast->failed_address; ?></strong>
								<input type="hidden" name="failed_recipients" value="<?php echo $broadcast->failed_address; ?>" />
								<input type="hidden" name="failed_action" value="requeue" />
								<input type="hidden" name="record_id" value="<?php echo $_GET['id']; ?>" />
							</td>
						</tr>
						<?php
					} else {
						echo '<strong style="color:red;">No recipients</strong>';
					}
				}
			} else {
				?>
				<tr valign="top">
					<th scope="row"><?php _e('Send To:', 'wishlist-member'); ?></th>
					<td>
						<div <?php echo $previewclass; ?>>
							<select name="send_to" id="send_to">
								<option value="send_mlevels" <?php echo wlm_arrval($_POST,'send_to') == "send_mlevels" ? "selected='selected'":""; ?>>Membership Levels</option>
								<option value="send_search" <?php echo wlm_arrval($_POST,'send_to') == "send_search" ? "selected='selected'":""; ?>>Save Searches</option>
							</select>
						</div>
						<?php
						if (wlm_arrval($_POST,'preview')) {
							if (wlm_arrval($_POST,'send_to') == "send_mlevels"){
								_e("Membership Level", 'wishlist-member');
							} else {
								_e("Save Searches", 'wishlist-member');
							}
						}
						?>
					</td>
				</tr>
				<tr id="send_search" valign="top" <?php echo wlm_arrval($_POST,'send_to') == "send_search" ? "":"style='display:none;'"; ?>>
					<th scope="row"><?php _e('Save Searches:', 'wishlist-member'); ?></th>
					<td>
						<div <?php echo $previewclass; ?>>
							<select id="save_searches" name="save_searches">
								<option value="">- Saved Searches -</option>
								<?php foreach ($this->GetAllSavedSearch() as $value): ?>
									<option value="<?php echo $value['name']; ?>" <?php echo wlm_arrval($_POST,'save_searches') == $value['name'] ? "selected='selected'":""; ?>><?php echo $value['name'] ?></option>
								<?php endforeach; ?>
							</select>
						</div>
							<?php
							if (wlm_arrval($_POST,'preview')) {
								if (wlm_arrval($_POST,'save_searches') != ""){
									require_once($this->pluginDir . '/core/UserSearch.php');
									$ssearch = wlm_arrval($_POST,'save_searches');
									$save_searches = $this->GetSavedSearch( $ssearch );
									if ( $save_searches ) {
										$save_searches = $save_searches[0];
										$usersearch = isset( $save_searches["search_term"] ) ? $save_searches["search_term"] : '';
										$usersearch = isset( $save_searches["usersearch"] ) ? $save_searches["usersearch"] : $usersearch;
										$wp_user_search = new WishListMemberUserSearch( $usersearch, '', '', '', '', '', 99999999, $save_searches );
										$total_recipients = $wp_user_search->total_users;
									}
									if( $total_recipients ) {
										echo $ssearch ." <em>( <strong>{$total_recipients}</strong> recipient/s )</em>";
									} else {
										echo $ssearch ." <em style='color:red'>( 0 recipient )</em>";
									}
								} else {
									_e("<span style='color:red';>(no saved search selected)</span>", 'wishlist-member');
								}
							}
							?>
					</td>
				</tr>
				<tr id="send_mlevels" valign="top" <?php echo wlm_arrval($_POST,'send_to') != "send_search" ? "":"style='display:none;'"; ?>>
					<th scope="row"><?php _e('Membership Level', 'wishlist-member'); ?> <?php echo $this->Tooltip("members-broadcast-tooltips-Membership-Level"); ?></th>
					<td>
						<div <?php echo $previewclass; ?>>
							<label><input type="checkbox" onclick="var c=document.getElementById('allwpmlevels').getElementsByTagName('input');for(var i=0;i < c.length;i++)c[i].checked=this.checked;" /> <?php _e('All Levels', 'wishlist-member'); ?></label>
							<hr />
							<div id="allwpmlevels">
								<?php
								foreach ((array) $wpm_levels AS $id => $level) {
									$checked = in_array($id, (array) $_POST['send_mlevels']) ? ' checked="true"' : '';
									echo "<label><input type='checkbox' name='send_mlevels[]' value='{$id}'{$checked} /> {$level[name]}</label><br />";
								}
								?>
							</div>
						</div>
						<?php
						if (wlm_arrval($_POST,'preview')) {
							if (count(wlm_arrval($_POST,'send_mlevels'))) {
								foreach ((array) $_POST['send_mlevels'] AS $id) {
									echo $wpm_levels[$id]['name'] . '<br />';
								}
							} else {
								_e("<span style='color:red';>(no level selected)</span>", 'wishlist-member');
							}
						}
						?>
					</td>
				</tr>
				<tr id="include_levels" <?php echo wlm_arrval($_POST,'send_to') != "send_search" ? "":"style='display:none;'"; ?>>
					<th scope="row"><?php _e('Include', 'wishlist-member'); ?> <?php echo $this->Tooltip("members-broadcast-tooltips-Include"); ?></th>
					<td>
						<div <?php echo $previewclass; ?>>
							<label><input type="checkbox" name="otheroptions[]" value="p" <?php
								  if (in_array('p', (array) $_POST['otheroptions']))
									  echo ' checked="true" ';
								  ?> /> <?php _e('Pending Members', 'wishlist-member'); ?></label><br />
							<label><input type="checkbox" name="otheroptions[]" value="c" <?php
								  if (in_array('c', (array) $_POST['otheroptions']))
									  echo ' checked="true" ';
						?> /> <?php _e('Cancelled Levels', 'wishlist-member'); ?></label><br />
						</div>
						<?php
						if (wlm_arrval($_POST,'preview')) {
							if (in_array('p', (array) $_POST['otheroptions'])) {
								echo __('Pending Members', 'wishlist-member') . '<br />';
							}
							if (in_array('c', (array) $_POST['otheroptions'])) {
								echo __('Cancelled Levels', 'wishlist-member') . '<br />';
							}
							if (!isset($_POST['otheroptions'])) {
								_e('Send only to Active members', 'wishlist-member');
							}
						}
						?>
					</td>
				</tr>
			<?php } ?>
		</table>
		<?php
			$disabled = false;
			$disabled = (!wlm_arrval($_POST,'subject') || !wlm_arrval($_POST,'message') || !$addressok) ? true : false;			
			if(!$disabled){
				if(wlm_arrval($_POST,'send_to') == "send_mlevels" && !count(wlm_arrval($_POST,'send_mlevels'))){
					$disabled = true;
				}else if(wlm_arrval($_POST,'send_to') == "send_search" ){
					if ( !wlm_arrval($_POST,'save_searches') || ! $total_recipients ) {
						$disabled = true;
					}
				}
			}
		?>
		<p class="submit">
			<?php if (wlm_arrval($_POST,'preview')): ?>
				<input type="hidden" name="WishListMemberAction" value="EmailBroadcast" />
				<input type="hidden" name="EmailBroadcastAction" value="Save" />
				<?php if (!$disabled || $requeue): ?>
					<small><?php _e('<b>Note:</b> This may take several minutes to complete if you have many members.', 'wishlist-member'); ?><br /></small>
					<input class="button button-primary" type="submit" value="<?php _e('Send Message to Members', 'wishlist-member'); ?>" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<?php endif; ?>
				<input type="button" class="button button-secondary" value="<?php _e('Go Back and Edit', 'wishlist-member'); ?>" onclick="this.form.action='?<?php echo $back_form_action; ?>';this.form.WishListMemberAction.value='';this.form.submit()" />
			<?php else : ?>
				<input class="button button-primary" type="submit" name="preview" value="<?php _e('Preview Message', 'wishlist-member'); ?>" />&nbsp;&nbsp;&nbsp;
				<?php if (!$requeue): ?><label><input type='checkbox' name='send_to_admin' value='1' /> Check this box to also send a test email to the site admin email address <i>(<?php echo get_bloginfo('admin_email'); ?>)</i>.</label><?php endif; ?>
			<?php endif; ?>
		</p>
	</form>
<?php else: ?>
	<p><?php _e('You need to specify a sender\'s name and sender\'s email address before you can use this feature.', 'wishlist-member'); ?></p>
	<?php $x = $this->GetMenu('settings'); ?>
	<p><?php printf(__('You can do this by going to the <a href="%1$s&mode=email">WishList Member Settings Page</a>.', 'wishlist-member'), $x->URL); ?></p>
<?php endif; ?>


