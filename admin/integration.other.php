<?php
/*
 * Other Integration
 * Original Author : Mike Lopez
 * Version: $Id: integration.other.php 2813 2015-07-29 14:30:25Z mike $
 */
if (!isset($_GET['other_integration'])) {
	$_GET['other_integration'] = $this->GetOption('lastother_integrationviewed');
}
$this->SaveOption('lastother_integrationviewed', $_GET['other_integration']);
$__integrations__ = glob($this->pluginDir . '/admin/integration.other.*.php');
$__INTERFACE__ = false;
foreach ((array) $__integrations__ AS $__integration__) {
	include($__integration__);
}
?>
<form method="get">
	<table class="form-table">
		<?php
		parse_str($this->QueryString('other_integration'), $fields);
		foreach ((array) $fields AS $field => $value) {
			echo "<input type='hidden' name='{$field}' value='{$value}' />";
		}
		?>
		<tr>
			<td scope="row" colspan="3" style="padding-left:0">
				<?php
					if (!isset($__other_options__[wlm_arrval($_GET,'other_integration')])) {
						echo '<p>';
						_e('Select an option to configure from the dropdown below.', 'wishlist-member');
						echo '</p>';
					}
				?>
			</td>
			<td>
			<?php if (!empty($__other_videotutorial__[wlm_arrval($_GET,'other_integration')])): ?>
				<p class="alignright" style="margin-top:0"><a href="<?php echo $__other_videotutorial__[wlm_arrval($_GET,'other_integration')]; ?>" target="_blank"><?php _e('Watch Integration Video Tutorial', 'wishlist-member'); ?></a></p>
			<?php endif; ?>
			</td>
		</tr>
		<tr>
			<th scope="row"><?php _e('Select System', 'wishlist-member'); ?></th>
			<td width="1" style="white-space:nowrap">
				<select name="other_integration">
					<option value=""><?php _e('None', 'wishlist-member'); ?></option>
					<?php
					// sort by Name
					natcasesort($__other_options__);

					// Generic integration always goes last
					if (isset($__other_options__['generic'])) {
						$x = $__other_options__['generic'];
						unset($__other_options__['generic']);
						$__other_options__['generic'] = $x;
					}

					// display dropdown options
					$provider_name = '';
					foreach ((array) $__other_options__ AS $key => $value) {
						$selected = (wlm_arrval($_GET,'other_integration') == $key) ? ' selected="true" ' : '';
						echo '<option value="' . $key . '"' . $selected . '>' . $value . '</option>';
						if($selected) $provider_name = $value;
					}
					?>

			</td>
			<td>
				<p class="submit" style="margin:0;padding:0"><input type="submit" class="button-secondary" value="<?php _e('Select', 'wishlist-member'); ?>" /></p>
			</td>
			<td style="text-align:right">
				<?php if (isset($__other_affiliates__[wlm_arrval($_GET,'other_integration')])): ?>
					<a href="<?php echo $__other_affiliates__[wlm_arrval($_GET,'other_integration')]; ?>" target="_blank"><?php printf(__('Learn more about %1$s', 'wishlist-member'), $__other_options__[wlm_arrval($_GET,'other_integration')]); ?></a>
				<?php endif; ?>
			</td>
		</tr>
	</table>
</form>
<hr />
<?php
$__INTERFACE__ = true;
foreach ((array) $__integrations__ AS $__integration__) {
	include($__integration__);
}


?>
