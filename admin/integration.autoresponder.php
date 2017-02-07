<?php
/*
 * Autoresponder Interface
 * Original Author : Mike Lopez
 * Version: $Id: integration.autoresponder.php 3138 2016-10-18 11:15:00Z mike $
 */

$__integrations__ = glob($this->pluginDir . '/admin/integration.autoresponder.*.php');
$__INTERFACE__ = false;
foreach ((array) $__integrations__ AS $__integration__) {
	include($__integration__);
}

$data = $this->GetOption('Autoresponders');
if (wlm_arrval($_POST,'saveAR') == 'saveAR' && $_POST['ar']) {
	if ( isset( $_POST['migrate_mcar'] ) ) { //mailchimp migrate to v3 backup old settings
		$this->SaveOption('Autoresponders_mcbackup', $data);
	}
	$data[$data['ARProvider']] = $_POST['ar'];
	$this->SaveOption('Autoresponders', $data);
	echo "<div class='updated fade'>" . __('<p>Your autoresponder settings have been updated.</p>', 'wishlist-member') . "</div>";
} elseif (isset($_POST['ARProvider'])) {
	$data['ARProvider'] = $_POST['ARProvider'];
	$this->SaveOption('Autoresponders', $data);
	echo "<div class='updated fade'>" . __('<p>Your autoresponder provider has been changed.</p>', 'wishlist-member') . "</div>";
}

if($data['ARProvider'] == 'aweber') {
	echo '<div class="error">';
	echo '<form method="post">';
	echo '<p style="color:red;font-weight:bold">Important: The regular AWeber integration is now deprecated. It is strongly recommended that the AWeber API Integration be used instead.</p>';
	echo '</form>';
	echo '</div>';
}
if($data['ARProvider'] == 'getresponse') {
	echo '<div class="error">';
	echo '<form method="post">';
	echo '<p style="color:red;font-weight:bold">Important: The regular GetResponse integration is now deprecated. It is strongly recommended that the GetResponse API Integration be used instead.</p>';
	echo '</form>';
	echo '</div>';
}

?>

<form method="post">
	<table class="form-table">
		<tr>
			<td scope="row" colspan="3" style="padding-left:0">
				<p><?php _e('Integrate 3rd party AutoResponder services with WishList Member.', 'wishlist-member'); ?></p>
			</td>
			<td>
			<?php if (!empty($__ar_videotutorial__[$data['ARProvider']])): ?>
					<p class="alignright" style="margin-top:0"><a href="<?php echo $__ar_videotutorial__[$data['ARProvider']]; ?>" target="_blank"><?php _e('Watch Integration Video Tutorial', 'wishlist-member'); ?></a></p>
			<?php endif; ?>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row" style="font-weight:bold;"><?php _e('AutoResponder Provider', 'wishlist-member'); ?></th>
			<td style="white-space:nowrap" width="1">
				<select name="ARProvider">
					<option value=""><?php _e('None', 'wishlist-member'); ?></option>
					<?php
					// sort by Name
					natcasesort($__ar_options__);

					// Generic integration always goes last
					if (isset($__ar_options__['generic'])) {
						$x = $__ar_options__['generic'];
						unset($__ar_options__['generic']);
						$__ar_options__['generic'] = $x;
					}

					// display dropdown options
					$provider_name = '';
					foreach ((array) $__ar_options__ AS $key => $value) {
						$selected = ($data['ARProvider'] == $key) ? ' selected="true" ' : '';
						echo '<option value="' . $key . '"' . $selected . '>' . $value . '</option>';
						if($selected) $provider_name = $value;
					}
					?>
				</select> <?php echo $this->Tooltip("integration-autoresponder-tooltips-AR-Provider"); ?>
			</td>
			<td>
				<p class="submit" style="margin:0;padding:0"><input type="submit" class="button-secondary" value="<?php _e('Set AutoResponder Provider', 'wishlist-member'); ?>" /></p>
			</td>
			<td style="text-align:right">
				<?php if (isset($__ar_affiliates__[$data['ARProvider']])): ?>
					<a href="<?php echo $__ar_affiliates__[$data['ARProvider']]; ?>" target="_blank"><?php printf(__('Learn more about %1$s', 'wishlist-member'), $__ar_options__[$data['ARProvider']]); ?></a>
				<?php endif; ?>
			</td>
		</tr>
	</table>
	<hr />
	<br />
</form>
<?php
$__INTERFACE__ = true;
foreach ((array) $__integrations__ AS $__integration__) {
	include($__integration__);
}
include_once($this->pluginDir . '/admin/tooltips/integration.autoresponder.tooltips.php');
?>
