<?php
/*
 * Blacklist Members
 */
?>
<h2><?php _e('Members &raquo; Blacklist', 'wishlist-member'); ?></h2>
<p><?php _e('Specific email addresses and IP addresses can be prevented from registering by adding them to the Blacklists below.', 'wishlist-member'); ?></p>
<?php if (wlm_arrval($_GET,'append') || $_GET['eappend']): ?>
	<script type="text/javascript">
		window.onload=function(){
			document.getElementById('blacklistform').submit();
		}
	</script>
<?php endif; ?>
<form method="post" id="blacklistform" action="?<?php echo $this->QueryString('append', 'eappend'); ?>">
	<table class="form-table">
		<tr valign="top">
			<th scope="row"><?php _e('Email BlackList', 'wishlist-member'); ?><p><?php _e('Enter the email addresses to Blacklist. One email address per line.</p><p>Example:</p>user@domain.com<br />*@domain.com<br />*.com', 'wishlist-member'); ?></th>
		<td><textarea name="<?php $this->Option('blacklist_email'); ?>" cols="40" rows="10" style="float:left;margin-right:10px"><?php echo trim($this->OptionValue(true) . "\n" . $_GET['eappend']); ?></textarea>
			<?php echo $this->Tooltip("members-blacklist-tooltips-Members-Email-BlackList"); ?>
		</td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php _e('IP BlackList', 'wishlist-member'); ?><p><?php _e('Enter the IP addresses to Blacklist. One IP address per line.</p><p>Example:</p>192.168.0.1<br />192.168.0.*<br />192.168.*', 'wishlist-member'); ?></th>
		<td><textarea name="<?php $this->Option('blacklist_ip'); ?>" cols="40" rows="10" style="float:left;margin-right:10px"><?php echo trim($this->OptionValue(true) . "\n" . $_GET['append']); ?></textarea>
			<?php echo $this->Tooltip("members-blacklist-tooltips-Members-IP-BlackList"); ?>

		</td>
		</tr>
	</table>
	<table class="form-table">
		<tr valign="top">
			<th scope="row" colspan="2"><b><?php _e('Blacklist Messages', 'wishlist-member'); ?></b> <?php echo $this->Tooltip("members-blacklist-tooltips-Blacklist-Messages"); ?></th>
		</tr>
		<tr valign="top">
			<th scope="row" class="WLRequired"><?php _e('Email Blacklist', 'wishlist-member'); ?></th>
			<td><input type="text" name="<?php $this->Option('blacklist_email_message', true); ?>" size="60" value="<?php $this->OptionValue(false, 'Your email address is blacklisted.'); ?>" /></td>
		</tr>
		<tr valign="top">
			<th scope="row" class="WLRequired"><?php _e('IP Blacklist', 'wishlist-member'); ?></th>
			<td><input type="text" name="<?php $this->Option('blacklist_ip_message', true); ?>" size="60" value="<?php $this->OptionValue(false, 'Your IP address is blacklisted.'); ?>" /></td>
		</tr>
		<tr valign="top">
			<th scope="row" class="WLRequired"><?php _e('Email and IP Blacklist', 'wishlist-member'); ?></th>
			<td><input type="text" name="<?php $this->Option('blacklist_email_ip_message', true); ?>" size="60" value="<?php $this->OptionValue(false, 'Your email and IP addresses are blacklisted.'); ?>" /></td>
		</tr>
	</table>
	<p class="submit">
		<?php $this->Options();
		$this->RequiredOptions();
		?>
		<input type="hidden" name="WishListMemberAction" value="Save" />
		<input type="submit" class="button-primary" value="<?php _e('Save Settings', 'wishlist-member'); ?>" />
	</p>
</form>
