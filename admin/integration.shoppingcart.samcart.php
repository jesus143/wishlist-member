<?php
/*
 * SamCart Shopping Cart Integration
 * Original Author : Mike Lopez
 * Version: $Id: integration.shoppingcart.samcart.php 2813 2015-07-29 14:30:25Z mike $
 */

$__index__ = 'samcart';
$__sc_options__[$__index__] = 'SamCart';
$__sc_videotutorial__[$__index__] = wlm_video_tutorial ( 'integration', 'sc', $__index__ );

if (wlm_arrval($_GET,'cart') == $__index__) {
	if ($__INTERFACE__) {
		// START Interface
		?>
		<h2 class="wlm-integration-steps"><?php _e('Step 1. Configure API Credentials:','wishlist-member'); ?></h2>
		
		<h3>Blog URL</h3>
		<blockquote>
		<p>
			<?php _e('Copy the Blog URL below and paste it into SamCart in the following section:<br><strong>Settings &raquo; Membership Portal Integration &raquo; WishList Member</strong>','wishlist-member'); ?>
		</p>
		<input type="text" value="<?php echo admin_url(); ?>" size="60" readonly="readonly" onclick="this.select()" />
		</blockquote>
		
		<h3>API Key</h3>
		<blockquote>
		<p>
			<?php _e('Copy the API Key below and paste it into SamCart in the following section:<br><strong>Settings &raquo; Membership Portal Integration &raquo; WishList Member</strong>','wishlist-member'); ?>
		</p>
		<input type="text" name="<?php $this->Option('WLMAPIKey'); ?>" value="<?php $this->OptionValue(false, md5(microtime())); ?>" size="60" readonly="readonly" onclick="this.select()" />
		<p><em>Note: The API Key can be changed if needed in WishList Member in the following section: <br> <strong> Settings &raquo; Configuration &raquo; Miscellaneous </strong></em></p>
		</blockquote>

		<h2 class="wlm-integration-steps"><?php _e('Step 2. The SamCart tutorial video explains additional settings to be configured within SamCart. <br> Please view this video tutorial for more information.','wishlist-member'); ?></h2>
		<p>
			<a href="http://go.wlp.me/wlm:2-9:vid:integration-sc-samcart" target="_blank"> <?php _e('Click Here to view SamCart Video Tutorial.','wishlist-member'); ?> </a>
		</p>
		<?php
	}
}
?>
