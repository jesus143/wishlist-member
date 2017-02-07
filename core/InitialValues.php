<?php

/*
 * Initial data saved to database when WishList Member is first activated
 * Makes it easier to manage this
 * by Mike Lopez
 */

$WishListMemberInitialData = array(
	'rss_hide_protected'                         => 1,
	'wpm_levels'                                 => array(),
	'pending_period'                             => '',
	'rss_secret_key'                             => md5( microtime() ),
	'disable_rss_enclosures'                     => 1,
	'reg_cookie_timeout'                         => 600,
	'admin_approval_shoppingcart_reg'            => 0,
	'payperpost_ismember'                        => '0',
	'protect_after_more'                         => '1',
	'auto_insert_more'                           => '0',
	'private_tag_protect_msg'                    => __( '<i>[Content protected for [level] members only]</i>', 'wishlist-member' ),
	'reverse_private_tag_protect_msg'            => __( '<i>[Content not available for [level] members ]</i>', 'wishlist-member' ),
	'notify_admin_of_newuser'                    => '1',
	'members_can_update_info'                    => '1',
	'unsub_notification'                         => '1',
	'incomplete_notification'                    => '1',
	'incomplete_notification_first'              => '1',
	'incomplete_notification_add'                => '3',
	'incomplete_notification_add_every'          => '24',
	'expiring_notification'                      => '1',
	'expiring_notification_days'                 => '3',
	'show_linkback'                              => '1',
	'unsubscribe_expired_members'                => '0',
	'dont_send_reminder_email_when_unsubscribed' => '0',
	'redirect_existing_member'                   => '0',
	'prevent_ppp_deletion'                       => '1',
	'password_hinting'                           => '0',
	'enable_short_registration_links'            => '0',
	'enable_login_redirect_override'             => '1',
	'enable_retrieve_password_override'          => 1,
	'disable_legacy_reg_shortcodes'          	 => '0',
	'disable_legacy_private_tags'          		 => '0',
	'email_per_hour'                             => WLMDEFAULTEMAILPERHOUR,
	'email_memory_allocation'                    => WLMMEMORYALLOCATION,
	'WLM_ContentDrip_Option'                     => '',
	'file_protection_ignore' => 'jpg, jpeg, png, gif, bmp, css, js',
	'mask_passwords_in_emails'                => '1',
	/* welcome email */
	'register_email_subject'                  => __( 'Congrats - You are registered!', 'wishlist-member' ),
	'register_email_body'                     => __( 'Dear [firstname],

You have successfully registered as one of our [memberlevel] members.

Please keep this information safe as it contains your username and password.

Your Membership Info:
U: [username]
P: [password]

Login URL: [loginurl]

Be sure to drop by the site as we are continuously adding to the members area.

To your success!', 'wishlist-member' ),
	/* lost information email */
	'lostinfo_email_subject'                  => __( 'Your membership password reset request', 'wishlist-member' ),
	'lostinfo_email_message'                  => __( 'Dear [firstname],

Our records show that you recently asked to reset the password for your account.

Your current information is:
Username: [username]
Membership: [memberlevel]

As a security measure all passwords are encrypted in our database and cannot be retrieved. However, you can easily reset it.

To reset your password visit the following URL, otherwise just ignore this email and your membership info will remain the same.

[reseturl]

Thanks again!', 'wishlist-member' ),
	/* confirmation email */
	'confirm_email_subject'                   => __( 'Please confirm your registration', 'wishlist-member' ),
	'confirm_email_message'                   => __( 'Hi [firstname]

Thank You for registering for [memberlevel]

Your registration must be confirmed before it is active.

Confirm by visiting the link below:

[confirmurl]

Once your account is confirmed you will be able to login with the following details.

Your Membership Info:
U: [username]
P: [password]

Login URL: [loginurl]

Please keep this information safe, it is the only email that will include your username and password.

** These login details will only give you proper access after the registration has been confirmed.

Thank You.', 'wishlist-member' ),
	/* registration require admin approval email */
	'requireadminapproval_email_subject'      => __( 'Registration requires admin approval', 'wishlist-member' ),
	'requireadminapproval_email_message'      => __( 'Hi [firstname]

Thank You for registering for [memberlevel]

Your registration must be approved first by the admin before your status can be active.

Once your account is approved you will be able to login with the following details.

Your Membership Info:
U: [username]
P: [password]

Login URL: [loginurl]

Please keep this information safe, it is the only email that will include your username and password.

These login details will only give you proper access when the admin has approved your registration.

Thank You.', 'wishlist-member' ),
	/* registration admin approval email */
	'registrationadminapproval_email_subject' => __( 'Registration admin approval', 'wishlist-member' ),
	'registrationadminapproval_email_message' => __( 'Hi [firstname]

Your registration is now approved by the admin.

Please use the login details were sent in your initial registration email.

Thank You.', 'wishlist-member' ),
	/* new member notification sent to admin */
	'newmembernotice_email_subject'           => __( 'A New Member has Registered', 'wishlist-member' ),
	'newmembernotice_email_message'           => __( 'A new member has registered with the following info:

First Name: [firstname]
Last Name: [lastname]
Email: [email]
Membership Level: [memberlevel]
Username: [username]

Thank you.', 'wishlist-member' ),
	/* a member unsubscribe notification sent to admin */
	'unsubscribe_notice_email_subject'        => __( 'Member has Unsubscribed', 'wishlist-member' ),
	'unsubscribe_notice_email_message'        => __( 'A member has unsubscribed with the following info:

First Name: [firstname]
Last Name: [lastname]
Email: [email]
Username: [username]

Thank you.', 'wishlist-member' ),
	/* a member unsubscribe notification sent to admin */
	'incnotification_email_subject'           => __( 'Please Complete Your Registration', 'wishlist-member' ),
	'incnotification_email_message'           => __( 'Hi,

Thank you for registering for [memberlevel]

Complete your registration by visiting the link below:

[incregurl]

Thank you.', 'wishlist-member' ),
	/* expiring member email notification sent */
	'expiringnotification_email_subject'      => __( 'Expiring Membership Subscription Reminder', 'wishlist-member' ),
	'expiringnotification_email_message'      => __( 'Hi [firstname],

Your Membership Subscription for [memberlevel] is about to expire on [expirydate].

Thank you.', 'wishlist-member' ),
	/* password hint email notification sent */
	'password_hint_email_subject'             => __( 'Your Password Hint', 'wishlist-member' ),
	'password_hint_email_message'             => __( 'Hi [firstname] [lastname],

Your Password Hint is:

[passwordhint]

Click the link below to login
[loginurl]

Thank you.', 'wishlist-member' ),
	/* Registration Instructions (New Members) */
	'reg_instructions_new'                    => __( '<p>To complete your registration, please select one of the two options:</p>
<ol>
<li>Existing members, please <a href="[existinglink]">click here</a>.</li>
<li>New members, please fill in the form below to complete<br />your <b>[level]</b> application.</li>
</ol>', 'wishlist-member' ),
	/* Registration Instructions with Existing Link disabled (New Members) */
	'reg_instructions_new_noexisting'         => __( '<p>Please fill in the form below to complete your <b>[level]</b> registration.</p>', 'wishlist-member' ),
	/* Registration Instructions for Existing Members */
	'reg_instructions_existing'               => __( '<p>To complete your registration, please select one of the two options:</p>
<ol>
<li>New members, please <a href="[newlink]">click here</a>.</li>
<li>Existing members, please fill in the form below to complete<br />your <b>[level]</b> application.</li>
</ol>', 'wishlist-member' ),
	/* Sidebar Widget CSS */
	'sidebar_widget_css'                      => '/* The Main Widget Enclosure */
.WishListMember_Widget{ }',
	/* Login Merge Code CSS Enclosure */
	'login_mergecode_css'                     => '/* The Main Login Merge Code Enclosure */
.WishListMember_LoginMergeCode{ }',
	/* Registration Form CSS */
	'reg_form_css'                            => '/* CSS Code for the Registration Form */

/* The Main Registration Form Table */
.wpm_registration{
	clear:both;
	padding:0;
	margin:10px 0;
}
.wpm_registration td{
	text-align:left;
}
/*CSS for Existing Members Login Table*/
.wpm_existing{
	clear:both;
	padding:0;
	margin:10px 0;
}
/* CSS for Registration Error Messages */
p.wpm_err{
	color:#f00;
	font-weight:bold;
}

/* CSS for custom message sent to registration url */
p.wlm_reg_msg_external {
	border: 2px dotted #aaaaaa;
	padding: 10px;
	background: #fff;
	color: #000;
}

/* CSS Code for the Registration Instructions Box */

/* The Main Instructions Box */
div#wlmreginstructions{
	background:#ffffdd;
	border:1px solid #ff0000;
	padding:0 1em 1em 1em;
	margin:0 auto 1em auto;
	font-size:1em;
	width:450px;
	color:#333333;
}

/* Links displayed in the Instructions Box */
#wlmreginstructions a{
	color:#0000ff;
	text-decoration:underline;
}

/* Numbered Bullets in the Instructions Box */
#wlmreginstructions ol{
	margin:0 0 0 1em;
	padding:0 0 0 1em;
	list-style:decimal;
	background:none;
}

/* Each Bullet Entry */
#wlmreginstructions li{
	margin:0;
	padding:0;
	background:none;
}',
	'closed_comments_msg'                     => __( 'You are not allowed to view comments on this post.', 'wishlist-member' ),
);
