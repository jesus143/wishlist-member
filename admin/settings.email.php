<style type="text/css">

body {
	/*Tahoma, Verdana, Arial, Helvetica;*/
	font-size: 12px;
}

#nav, #nav ul { /* all lists */
	padding: 0;
	margin: 0;
	list-style: none;
	line-height: 1;
	
}

#nav a {
	width: 19em;
	text-decoration:none;
}

.nav-2 {
	width: 12em;
    height: 20em;
    line-height: 2em;
    border: 1px solid #ccc;
    padding: 0;
    margin: 0;
    overflow: scroll;

}

#nav li ul li a {
	text-decoration:none;
	color: #000000;
	padding-left:10px;
	padding-top:10px;

    vertical-align: middle; /* | top | bottom */
}

img
{
    vertical-align: middle; /* | top | bottom */
}


#nav li { /* all list items */
	float: left;
	width: 19em; /* width needed or else Opera goes nuts */
	height:20px;
}

#nav li ul { /* second-level lists */
	position: absolute;
	/*background: #EBEAEB;*/
	padding-left:10px;
	padding-top:10px;
	width: 19em;
	left: -999em; /* using left instead of display to hide menus because display: none isn't read by screen readers */
}

#nav li ul li:hover {
	background: #F5F5F5;
}

#nav li:hover ul, #nav li.sfhover ul { /* lists nested under hovered list items */
	left: auto;
	background: #EBEAEB;
}

#content {
	clear: left;
	color: #ccc;
}
.form-table th{
	 padding-left: 10px;
}
</style>

<script type="text/javascript"><!--//--><![CDATA[//><!--

sfHover = function() {
	var sfEls = document.getElementById("nav").getElementsByTagName("LI");
	for (var i=0; i<sfEls.length; i++) {
		sfEls[i].onmouseover=function() {
			this.className+=" sfhover";
		}
		sfEls[i].onmouseout=function() {
			this.className=this.className.replace(new RegExp(" sfhover\\b"), "");
		}
	}
}
if (window.attachEvent) window.attachEvent("onload", sfHover);

//--><!]]></script>
<?php
//custom fields shortcode
$custom_fields = $this->GetCustomFieldsMergeCodes();

$wlm_shortcodes[] = array('title' => '[wlm_website]', 'value' => '[wlm_website]');
$wlm_shortcodes[] = array('title' => '[wlm_aim]', 'value' => '[wlm_aim]');
$wlm_shortcodes[] = array('title' => '[wlm_yim]', 'value' => '[wlm_yim]');
$wlm_shortcodes[] = array('title' => '[wlm_jabber]', 'value' => '[wlm_jabber]');
$wlm_shortcodes[] = array('title' => '[wlm_biography]', 'value' => '[wlm_biography]');
$wlm_shortcodes[] = array('title' => '[wlm_company]', 'value' => '[wlm_company]');
$wlm_shortcodes[] = array('title' => '[wlm_address]', 'value' => '[wlm_address]');
$wlm_shortcodes[] = array('title' => '[wlm_address1]', 'value' => '[wlm_address1]');
$wlm_shortcodes[] = array('title' => '[wlm_address2]', 'value' => '[wlm_address2]');
$wlm_shortcodes[] = array('title' => '[wlm_state]', 'value' => '[wlm_state]');
$wlm_shortcodes[] = array('title' => '[wlm_zip]', 'value' => '[wlm_zip]');
$wlm_shortcodes[] = array('title' => '[wlm_country]', 'value' => '[wlm_country]');

//custom fields shortcode
$custom_fields = $this->GetCustomFieldsMergeCodes();
if (count($custom_fields)) {
	foreach ($custom_fields AS $custom_field) {
		$wlm_shortcodes[] = array('title' => $custom_field, 'value' => $custom_field);
	}
}
?>

<h2><?php _e('Settings &raquo; Email Settings', 'wishlist-member'); ?></h2>
<div style="float: right" class="WLRequiredLegend"><?php _e('Indicates Required Field', 'wishlist-member'); ?></div>

<!-- Email Settings -->
<form method="post">
	<h3><?php _e('Sender Information', 'wishlist-member'); ?></h3>
	<table class="form-table">
		<tr valign="top">
			<td colspan="2" style="border:none"><?php _e('Set the email Sender information that will be used when WishList Member sends emails:', 'wishlist-member'); ?></td>
		</tr>
		<tr valign="top">
			<th scope="row" style="border:none" class="WLRequired"><?php _e('Sender Name', 'wishlist-member'); ?></th>
			<td style="border:none"><input type="text" name="<?php $this->Option('email_sender_name', true); ?>" value="<?php $this->OptionValue(); ?>" size="40" /></td>
		</tr>
		<tr valign="top">
			<th scope="row" style="border:none" class="WLRequired"><?php _e('Sender Email', 'wishlist-member'); ?></th>
			<td style="border:none"><input type="text" name="<?php $this->Option('email_sender_address', true); ?>" value="<?php $this->OptionValue(); ?>" size="40" /></td>
		</tr>
	</table>
	<br />
	<h3><?php _e('Email Throttling', 'wishlist-member'); ?></h3>
	<table class="form-table">
		<tr valign="top">
			<th scope="row" style="border:none" class="WLRequired"><?php _e('Emails per Hour', 'wishlist-member'); ?></th>
			<td style="border:none">
				<input type="text" name="<?php $this->Option('email_per_hour', true); ?>" value="<?php $this->OptionValue(); ?>" size="5" />
				<p><?php _e('The WishList Member Cron Job can be configured for more timely processing of queued emails.', 'wishlist-member'); ?></p>
				<p>
					<?php
					$link = $this->GetMenu('settings');
					printf(__('<a href="%1$s">Click Here</a> to view the WishList Member Cron Job instructions.', 'wishlist-member'), $link->URL . '&mode2=cron');
					?>
				</p>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row" style="border:none" class="WLRequired"><?php _e('Memory Allocation', 'wishlist-member'); ?></th>
			<td style="border:none">
				<select name="<?php $this->Option('email_memory_allocation', true); ?>">
					<?php
					$email_memory_allocation = $this->OptionValue(true);
					$email_memory_allocation = ($email_memory_allocation == "" ? "128M" : $email_memory_allocation);
					?>
					<option value="128M" <?php echo $email_memory_allocation == "128M" ? "selected='selected'" : ""; ?> >128M</option>
					<option value="256M" <?php echo $email_memory_allocation == "256M" ? "selected='selected'" : ""; ?> >256M</option>
					<option value="512M" <?php echo $email_memory_allocation == "512M" ? "selected='selected'" : ""; ?> >512M</option>
				</select>
				<p><?php _e('The Memory Allocation can be increased if email broadcasts are not being sent properly.', 'wishlist-member'); ?></p>
				<p><?php _e('This is especially useful for sites with large numbers of members.', 'wishlist-member'); ?></p>
			</td>
		</tr>
	</table>
	<h3><?php _e('Email Subscription', 'wishlist-member'); ?></h3>
	<table class="form-table">
		<th scope="row" style="border:none"><?php _e('Notify Admin when a Member Unsubscribes:', 'wishlist-member'); ?></th>
		<td style="border:none">
			<label><input type="radio" name="<?php $this->Option('unsub_notification'); ?>" value="1"<?php $this->OptionChecked(1); ?> />
				<?php _e('Yes', 'wishlist-member'); ?></label>
			&nbsp;
			<label><input type="radio" name="<?php $this->Option(); ?>" value="0"<?php $this->OptionChecked(0); ?> />
				<?php _e('No', 'wishlist-member'); ?></label>
		</td>
	</table>
	<br />
	<h2><?php _e('Email Templates', 'wishlist-member'); ?></h2>
	<h3 style="margin-bottom:0"><?php _e('Registration', 'wishlist-member'); ?> <?php echo $this->Tooltip("settings-email-tooltips-Registration"); ?></h3>
	<table class="form-table">
		<tr valign="top">
			<td colspan="2" style="border:none"><?php _e('Enter the subject line and text for the email that will be sent to members once they register:', 'wishlist-member'); ?></td>
		</tr>
		<tr valign="top">
			<th scope="row" style="border:none" class="WLRequired"><?php _e('Subject', 'wishlist-member'); ?></th>
			<td style="border:none"><input type="text" name="<?php $this->Option('register_email_subject', true); ?>" value="<?php $this->OptionValue(); ?>" size="40" /></td>
		</tr>
		<tr valign="top">
			<th scope="row" class="WLRequired"><?php _e('Body', 'wishlist-member'); ?></th>
			<td>
				<textarea name="<?php $this->Option($x = 'register_email_body'); ?>" id="<?php echo $x; ?>" cols="40" rows="10" style="float:left;margin-right:10px"><?php $this->OptionValue(); ?></textarea>
				<ul id="nav">
					<li><a href="javascript:return false;"> <img src="<?php echo $this->pluginURL.'/images/WishList-Icon-Blue-16.png'; ?>"> &nbsp; Merge codes </a> <?php echo $this->Tooltip("settings-email-tooltips-insert-body"); ?>
						<ul class="nav-2">
							<li><a href="javascript:;" onclick="wpm_insertHTML('[firstname]','<?php echo $x; ?>')"><?php _e('Insert First Name', 'wishlist-member'); ?></a> </li>
							<li><a href="javascript:;" onclick="wpm_insertHTML('[lastname]','<?php echo $x; ?>')"><?php _e('Insert Last Name', 'wishlist-member'); ?></a></li>
							<li><a href="javascript:;" onclick="wpm_insertHTML('[email]','<?php echo $x; ?>')"><?php _e('Insert Email', 'wishlist-member'); ?></a></li>
							<li><a href="javascript:;" onclick="wpm_insertHTML('[username]','<?php echo $x; ?>')"><?php _e('Insert Username', 'wishlist-member'); ?></a></li>
							<li><a href="javascript:;" onclick="wpm_insertHTML('[password]','<?php echo $x; ?>')"><?php _e('Insert Password', 'wishlist-member'); ?></a></li>
							<li><a href="javascript:;" onclick="wpm_insertHTML('[loginurl]','<?php echo $x; ?>')"><?php _e('Insert Login URL', 'wishlist-member'); ?></a></li>
							<li><a href="javascript:;" onclick="wpm_insertHTML('[memberlevel]','<?php echo $x; ?>')"><?php _e('Insert Membership Level', 'wishlist-member'); ?></a></li>
							<?php
							if (count($wlm_shortcodes)) {
								echo '<li>--------------------------------------------------</li>';
								foreach ($wlm_shortcodes AS $custom_field) {
								?>
									<li><a href="javascript:;" onclick="wpm_insertHTML('<?php echo $custom_field['title']; ?>','<?php echo $x; ?>')"><?php _e($custom_field['value'], 'wishlist-member'); ?></a></li>
								<?php
								}
							}
							?>
						</ul>
					</li>
				</ul>
				<br clear="all" />
				<?php _e('The available Merge Codes can be used to insert personal information for each member into the email.', 'wishlist-member'); ?>
				<p><?php _e('The list of Merge Codes can be accessed using the blue icon to the right of the Email Body field.', 'wishlist-member'); ?></p>
			</td>
		</tr>
	</table>
	<br />
	<?php 
	// Hide this Lost Info setting if Retrieve Password Overried is disabled in Miscellaneous
	if($this->GetOption('enable_retrieve_password_override')): ?>
	<h3 style="margin-bottom:0"><?php _e('Lost Info', 'wishlist-member'); ?> <?php echo $this->Tooltip("settings-email-tooltips-Body-Lost-Info"); ?></h3>
	<table class="form-table">
		<tr valign="top">
			<td colspan="2" style="border:none"><?php _e('Enter the subject line and text for the email that will be sent to members who request their login info to be reset:', 'wishlist-member'); ?></td>
		</tr>
		<tr valign="top">
			<th scope="row" style="border:none" class="WLRequired"><?php _e('Subject', 'wishlist-member'); ?></th>
			<td style="border:none"><input type="text" name="<?php $this->Option('lostinfo_email_subject', true); ?>" value="<?php $this->OptionValue(); ?>" size="40" /></td>
		</tr>
		<tr valign="top">
			<th scope="row" class="WLRequired"><?php _e('Body', 'wishlist-member'); ?></th>
			<td>
				<textarea name="<?php $this->Option($x = 'lostinfo_email_message', true); ?>" id="<?php echo $x; ?>" cols="40" rows="10" style="float:left;margin-right:10px"><?php $this->OptionValue(); ?></textarea>
				<ul id="nav">
					<li><a href="javascript:return false;"> <img src="<?php echo $this->pluginURL.'/images/WishList-Icon-Blue-16.png'; ?>"> &nbsp; Merge codes </a> <?php echo $this->Tooltip("settings-email-tooltips-insert-body"); ?>
						<ul class="nav-2">
							<li><a href="javascript:;" onclick="wpm_insertHTML('[firstname]','<?php echo $x; ?>')"><?php _e('Insert First Name', 'wishlist-member'); ?></a> </li>
							<li><a href="javascript:;" onclick="wpm_insertHTML('[lastname]','<?php echo $x; ?>')"><?php _e('Insert Last Name', 'wishlist-member'); ?></a></li>
							<li><a href="javascript:;" onclick="wpm_insertHTML('[email]','<?php echo $x; ?>')"><?php _e('Insert Email', 'wishlist-member'); ?></a></li>
							<li><a href="javascript:;" onclick="wpm_insertHTML('[username]','<?php echo $x; ?>')"><?php _e('Insert Username', 'wishlist-member'); ?></a></li>
							<li><a href="javascript:;" onclick="wpm_insertHTML('[reseturl]','<?php echo $x; ?>')"><?php _e('Insert Reset URL', 'wishlist-member'); ?></a></li>
							<li><a href="javascript:;" onclick="wpm_insertHTML('[loginurl]','<?php echo $x; ?>')"><?php _e('Insert Login URL', 'wishlist-member'); ?></a></li>
							<li><a href="javascript:;" onclick="wpm_insertHTML('[memberlevel]','<?php echo $x; ?>')"><?php _e('Insert Membership Level', 'wishlist-member'); ?></a></li>
							<?php
							if (count($wlm_shortcodes)) {
								echo '<li>--------------------------------------------------</li>';
								foreach ($wlm_shortcodes AS $custom_field) {
								?>
									<li><a href="javascript:;" onclick="wpm_insertHTML('<?php echo $custom_field['title']; ?>','<?php echo $x; ?>')"><?php _e($custom_field['value'], 'wishlist-member'); ?></a></li>
								<?php
								}
							}
							?>
						</ul>
					</li>
				</ul>
				<br clear="all" />
				<?php _e('The available Merge Codes can be used to insert personal information for each member into the email.', 'wishlist-member'); ?>
				<p><?php _e('The list of Merge Codes can be accessed using the blue icon to the right of the Email Body field.', 'wishlist-member'); ?></p>
			</td>
		</tr>
	</table>
	<br />
	<?php endif; ?>
	<h3 style="margin-bottom:0"><?php _e('New Member Notification', 'wishlist-member'); ?> <?php echo $this->Tooltip("settings-email-tooltips-New-Member-Notification"); ?></h3>
	<table class="form-table">
		<tr valign="top">
			<td colspan="2" style="border:none"><?php _e('Enter the admin email address, subject line and text for the email that will be sent to the site admin when a new member registers:', 'wishlist-member'); ?></td>
		</tr>
		<tr valign="top">
			<th scope="row" style="border:none" class="WLRequired"><?php _e('Admin Email', 'wishlist-member'); ?></th>
			<td style="border:none"><input type="text" name="<?php $this->Option('newmembernotice_email_recipient', true); ?>" value="<?php $r = trim($this->OptionValue(true)); echo $r == null || empty($r) ? get_bloginfo('admin_email') : $r;?>" size="40" /><?php echo $this->Tooltip("settings-email-tooltips-Admins-Email"); ?></td>
		</tr>
		<tr valign="top">
			<th scope="row" style="border:none" class="WLRequired"><?php _e('Subject', 'wishlist-member'); ?></th>
			<td style="border:none"><input type="text" name="<?php $this->Option('newmembernotice_email_subject', true); ?>" value="<?php $this->OptionValue(); ?>" size="40" /></td>
		</tr>
		<tr valign="top">
			<th scope="row" class="WLRequired"><?php _e('Body', 'wishlist-member'); ?></th>
			<td>
				<textarea name="<?php $this->Option($x = 'newmembernotice_email_message', true); ?>" id="<?php echo $x; ?>" cols="40" rows="10" style="float:left;margin-right:10px"><?php $this->OptionValue(); ?></textarea>
				<ul id="nav">
						<li><a href="javascript:return false;"> <img src="<?php echo $this->pluginURL.'/images/WishList-Icon-Blue-16.png'; ?>"> &nbsp; Merge codes </a> <?php echo $this->Tooltip("settings-email-tooltips-insert-body"); ?>
							<ul class="nav-2">
								<li><a href="javascript:;" onclick="wpm_insertHTML('[firstname]','<?php echo $x; ?>')"><?php _e('Insert First Name', 'wishlist-member'); ?></a> </li>
								<li><a href="javascript:;" onclick="wpm_insertHTML('[lastname]','<?php echo $x; ?>')"><?php _e('Insert Last Name', 'wishlist-member'); ?></a></li>
								<li><a href="javascript:;" onclick="wpm_insertHTML('[email]','<?php echo $x; ?>')"><?php _e('Insert Email', 'wishlist-member'); ?></a></li>
								<li><a href="javascript:;" onclick="wpm_insertHTML('[username]','<?php echo $x; ?>')"><?php _e('Insert Username', 'wishlist-member'); ?></a></li>
								<li><a href="javascript:;" onclick="wpm_insertHTML('[password]','<?php echo $x; ?>')"><?php _e('Insert Password', 'wishlist-member'); ?></a></li>
								<li><a href="javascript:;" onclick="wpm_insertHTML('[loginurl]','<?php echo $x; ?>')"><?php _e('Insert Login URL', 'wishlist-member'); ?></a></li>
								<li><a href="javascript:;" onclick="wpm_insertHTML('[memberlevel]','<?php echo $x; ?>')"><?php _e('Insert Membership Level', 'wishlist-member'); ?></a></li>
								<?php
								if (count($wlm_shortcodes)) {
									echo '<li>--------------------------------------------------</li>';
									foreach ($wlm_shortcodes AS $custom_field) {
									?>
										<li><a href="javascript:;" onclick="wpm_insertHTML('<?php echo $custom_field['title']; ?>','<?php echo $x; ?>')"><?php _e($custom_field['value'], 'wishlist-member'); ?></a></li>
									<?php
									}
								}
								?>
							</ul>
						</li>
					</ul>
				<br clear="all" />
				<?php _e('The available Merge Codes can be used to insert personal information for each member into the email.', 'wishlist-member'); ?>
				<p><?php _e('The list of Merge Codes can be accessed using the blue icon to the right of the Email Body field.', 'wishlist-member'); ?></p>
			</td>
		</tr>
	</table>
	<br />
	<h3 style="margin-bottom:0"><?php _e('Member Unsubscribe', 'wishlist-member'); ?> <?php echo $this->Tooltip("settings-email-tooltips-Member-Unsubscribe"); ?></h3>
	<table class="form-table">
		<tr valign="top">
			<td colspan="2" style="border:none"><?php _e('Enter the admin email address, subject line and text for the email that will be sent to the site admin when a member unsubscribes:', 'wishlist-member'); ?></td>
		</tr>
		<tr valign="top">
			<th scope="row" style="border:none" class="WLRequired"><?php _e('Admin Email', 'wishlist-member'); ?></th>
			<td style="border:none"><input type="text" name="<?php $this->Option('unsubscribe_notice_email_recipient', true); ?>" value="<?php $r = trim($this->OptionValue(true)); echo $r == null || empty($r) ? get_bloginfo('admin_email') : $r;?>" size="40" />
			<?php echo $this->Tooltip("settings-email-tooltips-Admins-Email"); ?></td>
		</tr>
		<tr valign="top">
			<th scope="row" style="border:none" class="WLRequired"><?php _e('Subject', 'wishlist-member'); ?></th>
			<td style="border:none"><input type="text" name="<?php $this->Option('unsubscribe_notice_email_subject', true); ?>" value="<?php $this->OptionValue(); ?>" size="40" /></td>
		</tr>
		<tr valign="top">
			<th scope="row" class="WLRequired"><?php _e('Body', 'wishlist-member'); ?></th>
			<td>
				<textarea name="<?php $this->Option($x = 'unsubscribe_notice_email_message', true); ?>" id="<?php echo $x; ?>" cols="40" rows="10" style="float:left;margin-right:10px"><?php $this->OptionValue(); ?></textarea>
				<ul id="nav">
					<li><a href="javascript:return false;"> <img src="<?php echo $this->pluginURL.'/images/WishList-Icon-Blue-16.png'; ?>"> &nbsp; Merge codes </a> <?php echo $this->Tooltip("settings-email-tooltips-insert-body"); ?>
						<ul  class="nav-2">
							<li><a href="javascript:;" onclick="wpm_insertHTML('[firstname]','<?php echo $x; ?>')"><?php _e('Insert First Name', 'wishlist-member'); ?></a> </li>
							<li><a href="javascript:;" onclick="wpm_insertHTML('[lastname]','<?php echo $x; ?>')"><?php _e('Insert Last Name', 'wishlist-member'); ?></a></li>
							<li><a href="javascript:;" onclick="wpm_insertHTML('[email]','<?php echo $x; ?>')"><?php _e('Insert Email', 'wishlist-member'); ?></a></li>
							<li><a href="javascript:;" onclick="wpm_insertHTML('[username]','<?php echo $x; ?>')"><?php _e('Insert Username', 'wishlist-member'); ?></a></li>
							<?php
							if (count($wlm_shortcodes)) {
								echo '<li>--------------------------------------------------</li>';
								foreach ($wlm_shortcodes AS $custom_field) {
								?>
									<li><a href="javascript:;" onclick="wpm_insertHTML('<?php echo $custom_field['title']; ?>','<?php echo $x; ?>')"><?php _e($custom_field['value'], 'wishlist-member'); ?></a></li>
								<?php
								}
							}
							?>
						</ul>
					</li>
				</ul>
				<br clear="all" />
				<?php _e('The available Merge Codes can be used to insert personal information for each member into the email.', 'wishlist-member'); ?>
				<p><?php _e('The list of Merge Codes can be accessed using the blue icon to the right of the Email Body field.', 'wishlist-member'); ?></p>
			</td>
		</tr>
	</table>
	<br />
	<h3 style="margin-bottom:0"><?php _e('Require Email Confirmation', 'wishlist-member'); ?> <?php echo $this->Tooltip("settings-email-tooltips-Registration-Email-Confirmation"); ?></h3>
	<table class="form-table">
		<tr valign="top">
			<td colspan="2" style="border:none"><?php _e('Enter the subject line and text for the email that will be sent to members in order to confirm their account once they register:', 'wishlist-member'); ?></td>
		</tr>
		<tr valign="top">
			<th scope="row" style="border:none" class="WLRequired"><?php _e('Subject', 'wishlist-member'); ?></th>
			<td style="border:none"><input type="text" name="<?php $this->Option('confirm_email_subject', true); ?>" value="<?php $this->OptionValue(); ?>" size="40" /></td>
		</tr>
		<tr valign="top">
			<th scope="row" class="WLRequired"><?php _e('Body', 'wishlist-member'); ?></th>
			<td>
				<textarea name="<?php $this->Option($x = 'confirm_email_message', true); ?>" id="<?php echo $x; ?>" cols="40" rows="10" style="float:left;margin-right:10px"><?php $this->OptionValue(); ?></textarea>
				<ul id="nav">
					<li><a href="javascript:return false;"> <img src="<?php echo $this->pluginURL.'/images/WishList-Icon-Blue-16.png'; ?>"> &nbsp; Merge codes </a> <?php echo $this->Tooltip("settings-email-tooltips-insert-body"); ?>
						<ul class="nav-2">
							<li><a href="javascript:;" onclick="wpm_insertHTML('[firstname]','<?php echo $x; ?>')"><?php _e('Insert First Name', 'wishlist-member'); ?></a> </li>
							<li><a href="javascript:;" onclick="wpm_insertHTML('[lastname]','<?php echo $x; ?>')"><?php _e('Insert Last Name', 'wishlist-member'); ?></a></li>
							<li><a href="javascript:;" onclick="wpm_insertHTML('[email]','<?php echo $x; ?>')"><?php _e('Insert Email', 'wishlist-member'); ?></a><br />
							<li><a href="javascript:;" onclick="wpm_insertHTML('[username]','<?php echo $x; ?>')"><?php _e('Insert Username', 'wishlist-member'); ?></a></li>
							<li><a href="javascript:;" onclick="wpm_insertHTML('[password]','<?php echo $x; ?>')"><?php _e('Insert Password', 'wishlist-member'); ?></a></li>
							<li><a href="javascript:;" onclick="wpm_insertHTML('[memberlevel]','<?php echo $x; ?>')"><?php _e('Insert Membership Level', 'wishlist-member'); ?></a</li>
							<li><a href="javascript:;" onclick="wpm_insertHTML('[confirmurl]','<?php echo $x; ?>')"><?php _e('Insert Confirmation URL', 'wishlist-member'); ?></a></li>
							<?php
							if (count($wlm_shortcodes)) {
								echo '<li>--------------------------------------------------</li>';
								foreach ($wlm_shortcodes AS $custom_field) {
								?>
									<li><a href="javascript:;" onclick="wpm_insertHTML('<?php echo $custom_field['title']; ?>','<?php echo $x; ?>')"><?php _e($custom_field['value'], 'wishlist-member'); ?></a></li>
								<?php
								}
							}
							?>
						</ul>
					</li>
				</ul>
				<br clear="all" />
				<?php _e('The available Merge Codes can be used to insert personal information for each member into the email.', 'wishlist-member'); ?>
				<p><?php _e('The list of Merge Codes can be accessed using the blue icon to the right of the Email Body field.', 'wishlist-member'); ?></p>
			</td>
		</tr>
	</table>
	<br />
	<h3 style="margin-bottom:0"><?php _e('Require Admin Approval', 'wishlist-member'); ?> <?php echo $this->Tooltip("settings-email-tooltips-Require-Admin-Approval"); ?></h3>
	<table class="form-table">
		<tr valign="top">
			<td colspan="2" style="border:none"><?php _e('Enter the subject line and text for the email that will be sent to members in order to inform them that a site admin needs to confirm their account before they can access protected content:', 'wishlist-member'); ?></td>
		</tr>
		<tr valign="top">
			<th scope="row" style="border:none" class="WLRequired"><?php _e('Subject', 'wishlist-member'); ?></th>
			<td style="border:none"><input type="text" name="<?php $this->Option('requireadminapproval_email_subject', true); ?>" value="<?php $this->OptionValue(); ?>" size="40" /></td>
		</tr>
		<tr valign="top">
			<th scope="row" class="WLRequired"><?php _e('Body', 'wishlist-member'); ?></th>
			<td>
				<textarea name="<?php $this->Option($x = 'requireadminapproval_email_message', true); ?>" id="<?php echo $x; ?>" cols="40" rows="10" style="float:left;margin-right:10px"><?php $this->OptionValue(); ?></textarea>
				<ul id="nav">
					<li><a href="javascript:return false;"> <img src="<?php echo $this->pluginURL.'/images/WishList-Icon-Blue-16.png'; ?>"> &nbsp; Merge codes </a> <?php echo $this->Tooltip("settings-email-tooltips-insert-body"); ?>
						<ul class="nav-2">
							<li><a href="javascript:;" onclick="wpm_insertHTML('[firstname]','<?php echo $x; ?>')"><?php _e('Insert First Name', 'wishlist-member'); ?></a> </li>
							<li><a href="javascript:;" onclick="wpm_insertHTML('[lastname]','<?php echo $x; ?>')"><?php _e('Insert Last Name', 'wishlist-member'); ?></a></li>
							<li><a href="javascript:;" onclick="wpm_insertHTML('[email]','<?php echo $x; ?>')"><?php _e('Insert Email', 'wishlist-member'); ?></a></li>
							<li><a href="javascript:;" onclick="wpm_insertHTML('[username]','<?php echo $x; ?>')"><?php _e('Insert Username', 'wishlist-member'); ?></a></li>
							<li><a href="javascript:;" onclick="wpm_insertHTML('[password]','<?php echo $x; ?>')"><?php _e('Insert Password', 'wishlist-member'); ?></a></li>
							<li><a href="javascript:;" onclick="wpm_insertHTML('[memberlevel]','<?php echo $x; ?>')"><?php _e('Insert Membership Level', 'wishlist-member'); ?></a></li>
							<?php
							if (count($wlm_shortcodes)) {
								echo '<li>--------------------------------------------------</li>';
								foreach ($wlm_shortcodes AS $custom_field) {
								?>
									<li><a href="javascript:;" onclick="wpm_insertHTML('<?php echo $custom_field['title']; ?>','<?php echo $x; ?>')"><?php _e($custom_field['value'], 'wishlist-member'); ?></a></li>
								<?php
								}
							}
							?>
						</ul>
					</li>
				</ul>
				<br clear="all" />
				<?php _e('The available Merge Codes can be used to insert personal information for each member into the email.', 'wishlist-member'); ?>
				<p><?php _e('The list of Merge Codes can be accessed using the blue icon to the right of the Email Body field.', 'wishlist-member'); ?></p>
			</td>
		</tr>
	</table>

	<br />
	<h3 style="margin-bottom:0"><?php _e('Admin Approval Notification', 'wishlist-member'); ?> <?php echo $this->Tooltip("settings-email-tooltips-Registration-Admin-Notification"); ?></h3>
	<table class="form-table">
		<tr valign="top">
			<td colspan="2" style="border:none"><?php _e('Enter the subject line and text for the email that will be sent to members once their account has been approved by the site admin:', 'wishlist-member'); ?></td>
		</tr>
		<tr valign="top">
			<th scope="row" style="border:none" class="WLRequired"><?php _e('Subject', 'wishlist-member'); ?></th>
			<td style="border:none"><input type="text" name="<?php $this->Option('registrationadminapproval_email_subject', true); ?>" value="<?php $this->OptionValue(); ?>" size="40" /></td>
		</tr>
		<tr valign="top">
			<th scope="row" class="WLRequired"><?php _e('Body', 'wishlist-member'); ?></th>
			<td>
				<textarea name="<?php $this->Option($x = 'registrationadminapproval_email_message', true); ?>" id="<?php echo $x; ?>" cols="40" rows="10" style="float:left;margin-right:10px"><?php $this->OptionValue(); ?></textarea>
				<ul id="nav">
					<li><a href="javascript:return false;"> <img src="<?php echo $this->pluginURL.'/images/WishList-Icon-Blue-16.png'; ?>"> &nbsp; Merge codes </a> <?php echo $this->Tooltip("settings-email-tooltips-insert-body"); ?>
						<ul class="nav-2">
							<li><a href="javascript:;" onclick="wpm_insertHTML('[firstname]','<?php echo $x; ?>')"><?php _e('Insert First Name', 'wishlist-member'); ?></a> </li>
							<li><a href="javascript:;" onclick="wpm_insertHTML('[lastname]','<?php echo $x; ?>')"><?php _e('Insert Last Name', 'wishlist-member'); ?></a></li>
							<li><a href="javascript:;" onclick="wpm_insertHTML('[email]','<?php echo $x; ?>')"><?php _e('Insert Email', 'wishlist-member'); ?></a></li>
							<li><a href="javascript:;" onclick="wpm_insertHTML('[username]','<?php echo $x; ?>')"><?php _e('Insert Username', 'wishlist-member'); ?></a></li>
							<li><a href="javascript:;" onclick="wpm_insertHTML('[memberlevel]','<?php echo $x; ?>')"><?php _e('Insert Membership Level', 'wishlist-member'); ?></a></li>
							<?php
							if (count($wlm_shortcodes)) {
								echo '<li>--------------------------------------------------</li>';
								foreach ($wlm_shortcodes AS $custom_field) {
								?>
									<li><a href="javascript:;" onclick="wpm_insertHTML('<?php echo $custom_field['title']; ?>','<?php echo $x; ?>')"><?php _e($custom_field['value'], 'wishlist-member'); ?></a></li>
								<?php
								}
							}
							?>
						</ul>
					</li>
				</ul>
				<br clear="all" />
				<?php _e('The available Merge Codes can be used to insert personal information for each member into the email.', 'wishlist-member'); ?>
				<p><?php _e('The list of Merge Codes can be accessed using the blue icon to the right of the Email Body field.', 'wishlist-member'); ?></p>
			</td>
		</tr>
	</table>

	<br />
	<h3 style="margin-bottom:0"><?php _e('Incomplete Registration Notification', 'wishlist-member'); ?> <?php echo $this->Tooltip("settings-email-tooltips-Incomplete-Registration-Notification"); ?></h3>
	<table class="form-table">
        <tr>
			<th scope="row" style="border:none; width:250px;"><?php _e('Enable: ', 'wishlist-member'); ?></th>
			<td style="border:none">
				<label><input type="radio" name="<?php $this->Option('incomplete_notification'); ?>" value="1"<?php $this->OptionChecked(1); ?> />
<?php _e('Yes', 'wishlist-member'); ?></label>
				&nbsp;
				<label><input type="radio" name="<?php $this->Option(); ?>" value="0"<?php $this->OptionChecked(0); ?> />
<?php _e('No', 'wishlist-member'); ?></label>
			</td>
        </tr>
		<tr valign="top">
			<th scope="row" style="border:none" class="WLRequired"><?php _e('Send First Notification After:', 'wishlist-member'); ?><?php echo $this->Tooltip("settings-email-tooltips-Incomplete-Registration-Notification-First"); ?></th>
			<td style="border:none"><input type="text" name="<?php $this->Option('incomplete_notification_first', true); ?>" value="<?php $this->OptionValue(); ?>" size="3" /> Hour(s)</td>
		</tr>        
		<tr valign="top">
			<th scope="row" style="border:none" class="WLRequired"><?php _e('Additional Number of Notifications:', 'wishlist-member'); ?></th>
			<td style="border:none"><input type="text" name="<?php $this->Option('incomplete_notification_add', true); ?>" value="<?php $this->OptionValue(); ?>" size="3" /></td>
		</tr>
		<tr valign="top">
			<th scope="row" style="border:none" class="WLRequired"><?php _e('Send Additional Notification Every:', 'wishlist-member'); ?></th>
			<td style="border:none"><input type="text" name="<?php $this->Option('incomplete_notification_add_every', true); ?>" value="<?php $this->OptionValue(); ?>" size="3" /> Hour(s)</td>
		</tr>		
		<tr valign="top">
			<td colspan="2" style="border:none"><?php _e('Enter the subject line and text for the email that will be sent to members with an incomplete registration.', 'wishlist-member'); ?></td>
		</tr>
		<tr valign="top">
			<th scope="row" style="border:none" class="WLRequired"><?php _e('Subject', 'wishlist-member'); ?></th>
			<td style="border:none"><input type="text" name="<?php $this->Option('incnotification_email_subject', true); ?>" value="<?php $this->OptionValue(); ?>" size="40" /></td>
		</tr>
		<tr valign="top">
			<th scope="row" class="WLRequired"><?php _e('Body', 'wishlist-member'); ?></th>
			<td>
				<textarea name="<?php $this->Option($x = 'incnotification_email_message', true); ?>" id="<?php echo $x; ?>" cols="40" rows="10" style="float:left;margin-right:10px"><?php $this->OptionValue(); ?></textarea>
				<ul id="nav">
					<li><a href="javascript:return false;"> <img src="<?php echo $this->pluginURL.'/images/WishList-Icon-Blue-16.png'; ?>"> &nbsp; Merge codes </a> <?php echo $this->Tooltip("settings-email-tooltips-insert-body"); ?>
						<ul>
							<li><a href="javascript:;" onclick="wpm_insertHTML('[memberlevel]','<?php echo $x; ?>')"><?php _e('Insert Membership Level', 'wishlist-member'); ?></a></li>
							<li><a href="javascript:;" onclick="wpm_insertHTML('[incregurl]','<?php echo $x; ?>')"><?php _e('Insert Registration URL', 'wishlist-member'); ?></a></li>
						</ul>
					</li>
				</ul>
				<br clear="all" />
				<?php _e('The available Merge Codes can be used to insert personal information for each member into the email.', 'wishlist-member'); ?>
				<p><?php _e('The list of Merge Codes can be accessed using the blue icon to the right of the Email Body field.', 'wishlist-member'); ?></p>
			</td>
		</tr>
	</table>
	<br/>
	<h3 style="margin-bottom:0"><?php _e('Expiring Member Notification', 'wishlist-member'); ?> <?php echo $this->Tooltip("settings-email-tooltips-Expiring-Member-Notification"); ?></h3>
	<table class="form-table">
		<tr>
			<th scope="row" style="border:none"><?php _e('Enable Notification for Expiring Members: ', 'wishlist-member'); ?></th>
			<td style="border:none">
				<label><input type="radio" name="<?php $this->Option('expiring_notification'); ?>" value="1"<?php $this->OptionChecked(1); ?> />
<?php _e('Yes', 'wishlist-member'); ?></label>
				&nbsp;
				<label><input type="radio" name="<?php $this->Option(); ?>" value="0"<?php $this->OptionChecked(0); ?> />
<?php _e('No', 'wishlist-member'); ?></label>
			</td>
		</tr>
		<tr valign="top" style="">
			<th scope="row" style="border:none" class="WLRequired"><?php echo str_replace(' ','&nbsp;',__('Number of Days Before Expiration Date:', 'wishlist-member')); ?>&nbsp;&nbsp;&nbsp;
			<label style="font-weight:normal !important; font-size: 12px;"><?php _e('Note: An Email Notification will be sent once each day based on the number entered into the field below.', 'wishlist-member'); ?> </label></th>
			<td style="border:none"><input type="text" name="<?php $this->Option('expiring_notification_days', true); ?>" value="<?php $this->OptionValue(); ?>" size="5" /></td>
		</tr>
		<tr valign="top">
			<td colspan="2" style="border:none"><?php _e('Enter the subject line and text for the email that will be sent to expiring members.', 'wishlist-member'); ?></td>
		</tr>
		<tr valign="top">
			<th scope="row" style="border:none" class="WLRequired"><?php _e('Subject', 'wishlist-member'); ?></th>
			<td style="border:none"><input type="text" name="<?php $this->Option('expiringnotification_email_subject', true); ?>" value="<?php $this->OptionValue(); ?>" size="40" /></td>
		</tr>
		<tr valign="top">
			<th scope="row" class="WLRequired"><?php _e('Body', 'wishlist-member'); ?></th>
			<td>
				<textarea name="<?php $this->Option($x = 'expiringnotification_email_message', true); ?>" id="<?php echo $x; ?>" cols="40" rows="10" style="float:left;margin-right:10px"><?php $this->OptionValue(); ?></textarea>
				<ul id="nav">
					<li><a href="javascript:return false;"> <img src="<?php echo $this->pluginURL.'/images/WishList-Icon-Blue-16.png'; ?>"> &nbsp; Merge codes </a> <?php echo $this->Tooltip("settings-email-tooltips-insert-body"); ?>
						<ul class="nav-2">
							<li><a href="javascript:;" onclick="wpm_insertHTML('[firstname]','<?php echo $x; ?>')"><?php _e('Insert First Name', 'wishlist-member'); ?></a> </li>
							<li><a href="javascript:;" onclick="wpm_insertHTML('[lastname]','<?php echo $x; ?>')"><?php _e('Insert Last Name', 'wishlist-member'); ?></a></li>
							<li><a href="javascript:;" onclick="wpm_insertHTML('[email]','<?php echo $x; ?>')"><?php _e('Insert Email', 'wishlist-member'); ?></a></li>
							<li><a href="javascript:;" onclick="wpm_insertHTML('[username]','<?php echo $x; ?>')"><?php _e('Insert Username', 'wishlist-member'); ?></a></li>
							<li><a href="javascript:;" onclick="wpm_insertHTML('[memberlevel]','<?php echo $x; ?>')"><?php _e('Insert Membership Level', 'wishlist-member'); ?></a></li>
							<li><a href="javascript:;" onclick="wpm_insertHTML('[expirydate]','<?php echo $x; ?>')"><?php _e('Insert Expiry Date', 'wishlist-member'); ?></a></li>
							<?php
							if (count($wlm_shortcodes)) {
								echo '<li>--------------------------------------------------</li>';
								foreach ($wlm_shortcodes AS $custom_field) {
								?>
									<li><a href="javascript:;" onclick="wpm_insertHTML('<?php echo $custom_field['title']; ?>','<?php echo $x; ?>')"><?php _e($custom_field['value'], 'wishlist-member'); ?></a></li>
								<?php
								}
							}
							?>
						</ul>
					</li>
				</ul>
				<br clear="all" />
				<?php _e('The available Merge Codes can be used to insert personal information for each member into the email.', 'wishlist-member'); ?>
				<p><?php _e('The list of Merge Codes can be accessed using the blue icon to the right of the Email Body field.', 'wishlist-member'); ?></p>
			</td>
		</tr>
	</table>
	
	<br/>
	<h3 style="margin-bottom:0"><?php _e('Password Hint Notification', 'wishlist-member'); ?> <?php echo $this->Tooltip("settings-email-tooltips-Password-Hint-Notification"); ?></h3>
	<table class="form-table">
		<tr valign="top">
			<td colspan="2" style="border:none"><?php _e('Enter the subject line and text for the email that will be sent to members who request the Password Hint.', 'wishlist-member'); ?></td>
		</tr>
		<tr valign="top">
			<th scope="row" style="border:none" class="WLRequired"><?php _e('Subject', 'wishlist-member'); ?></th>
			<td style="border:none"><input type="text" name="<?php $this->Option('password_hint_email_subject', true); ?>" value="<?php $this->OptionValue(); ?>" size="40" /></td>
		</tr>
		<tr valign="top">
			<th scope="row" class="WLRequired"><?php _e('Body', 'wishlist-member'); ?></th>
			<td>
				<textarea name="<?php $this->Option($x = 'password_hint_email_message', true); ?>" id="<?php echo $x; ?>" cols="40" rows="10" style="float:left;margin-right:10px"><?php $this->OptionValue(); ?></textarea>
				<ul id="nav">
					<li><a href="javascript:return false;"> <img src="<?php echo $this->pluginURL.'/images/WishList-Icon-Blue-16.png'; ?>"> &nbsp; Merge codes </a> <?php echo $this->Tooltip("settings-email-tooltips-insert-body"); ?>
						<ul class="nav-2">
							<li><a href="javascript:;" onclick="wpm_insertHTML('[firstname]','<?php echo $x; ?>')"><?php _e('Insert First Name', 'wishlist-member'); ?></a> </li>
							<li><a href="javascript:;" onclick="wpm_insertHTML('[lastname]','<?php echo $x; ?>')"><?php _e('Insert Last Name', 'wishlist-member'); ?></a></li>
							<li><a href="javascript:;" onclick="wpm_insertHTML('[email]','<?php echo $x; ?>')"><?php _e('Insert Email', 'wishlist-member'); ?></a></li>
							<li><a href="javascript:;" onclick="wpm_insertHTML('[username]','<?php echo $x; ?>')"><?php _e('Insert Username', 'wishlist-member'); ?></a></li>
							<li><a href="javascript:;" onclick="wpm_insertHTML('[passwordhint]','<?php echo $x; ?>')"><?php _e('Insert Password Hint', 'wishlist-member'); ?></a></li>
							<li><a href="javascript:;" onclick="wpm_insertHTML('[loginurl]','<?php echo $x; ?>')"><?php _e('Insert Login URL', 'wishlist-member'); ?></a></li>
							<?php
							if (count($wlm_shortcodes)) {
								echo '<li>--------------------------------------------------</li>';
								foreach ($wlm_shortcodes AS $custom_field) {
								?>
									<li><a href="javascript:;" onclick="wpm_insertHTML('<?php echo $custom_field['title']; ?>','<?php echo $x; ?>')"><?php _e($custom_field['value'], 'wishlist-member'); ?></a></li>
								<?php
								}
							}
							?>
						</ul>
					</li>
				</ul>
				<br clear="all" />
				<?php _e('The available Merge Codes can be used to insert personal information for each member into the email.', 'wishlist-member'); ?>
				<p><?php _e('The list of Merge Codes can be accessed using the blue icon to the right of the Email Body field.', 'wishlist-member'); ?></p>
			</td>
		</tr>
	</table>

	<p class="submit">
		<?php $this->Options();
		$this->RequiredOptions();
		?>
		<input type="hidden" name="WishListMemberAction" value="Save" />
		<input type="submit" class="button-primary" value="<?php _e('Save', 'wishlist-member'); ?>" />
	</p>
</form>

<script>
	jQuery(function($){
		$('input[name$=_subject]').each(function(i, o) {
			if($(o).val().match(/^re:/i)) {
				$('body').append('<div class="error"><p><?php _e('Note that adding Re: to the subject line of an email can result in the server flagging it as spam and not sending the email', 'wishlist-member'); ?></p></div>');
			}
		});
	});
</script>
