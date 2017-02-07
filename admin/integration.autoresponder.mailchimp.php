<?php
/*
 * MailChimp Autoresponder API
 * Original Author : Fel Jun Palawan
 * Version: $Id: integration.autoresponder.mailchimp.php 3151 2016-11-29 06:15:31Z mike $
 */

/*
  GENERAL PROGRAM NOTES: (This script was based on Mike's Autoresponder integrations.)
  Purpose: This is the UI part of the code. This is displayed as the admin area for MailChimp Integration in WishList Member Dashboard.
  Location: admin/
  Calling program : integration.autoresponder.php
  Logic Flow:
  1. integration.autoresponder.php displays this script (integration.autoresponder.mailchimp.php)
  and displays current or default settings
  2. on user update, this script submits value to integration.autoresponder.php, which in turn save the value
  3. after saving the values, integration.autoresponder.php call this script again with $wpm_levels contains the membership levels and $data contains the MailChimp Integration settings for each membership level.
 */

$__index__ = 'mailchimp';
$__ar_options__[$__index__] = 'MailChimp';
$__ar_videotutorial__[$__index__] = wlm_video_tutorial ( 'integration', 'ar', $__index__ );
?>

<?php if ($data['ARProvider'] == $__index__): ?>
<?php if ($__INTERFACE__): ?>
	<?php
		if ( isset( $_POST['migrate_mcar'] ) ) {
			echo "<div class='updated fade'>" . __('<p>Migration to MailChimp API v3.0 Integration Successful.</p>', 'wishlist-member') . "</div>";
		}

		//make sure WLM_AUTORESPONDER_AWEBERAPI_INIT class is loaded
		//integration inits does not load when you just switch from one integration to another
		if ( !class_exists('WLM_AUTORESPONDER_MAILCHIMP_INIT') ) {
			require_once($this->pluginDir . '/lib/integration.autoresponder.mailchimp.init.php');
		}
	?>
	<?php if ( isset( $data[$__index__]['api_v3'] ) || trim( $data[$__index__]['mcapi'] ) == "" ) : ?>
		<?php include_once( $this->pluginDir . '/extlib/mailchimp/mailchimp-admin-v3.php' ); ?>
	<?php else : ?>
		<?php include_once( $this->pluginDir . '/extlib/mailchimp/mailchimp-admin-old.php' ); ?>
	<?php endif; ?>

<?php include_once($this->pluginDir . '/admin/tooltips/integration.autoresponder.mailchimp.tooltips.php'); ?>
<?php endif; ?>
<?php endif; ?>
