<h2 style="font-size:18px;width:100%"><?php _e('Step 1. Select Membership Level', 'wishlist-member'); ?></h2>
<?php
/*
 * Registration Page
 */
$regpage_before = $this->GetOption('regpage_before');
$regpage_after = $this->GetOption('regpage_after');
$regpage_form = $this->GetOption('regpage_form');
if (isset($_POST['wlm_regpage_before'])) {
	$regpage_before[wlm_arrval($_POST,'level')] = stripslashes(wlm_arrval($_POST,'wlm_regpage_before'));
	$regpage_after[wlm_arrval($_POST,'level')] = stripslashes(wlm_arrval($_POST,'wlm_regpage_after'));
	$regpage_form[wlm_arrval($_POST,'level')] = $_POST['wlm_regpage_form'];
	$this->SaveOption('regpage_before', $regpage_before);
	$this->SaveOption('regpage_after', $regpage_after);
	$this->SaveOption('regpage_form', $regpage_form);
	echo "<div class='updated fade'>" . __('<p>Registration Form Customization Settings Saved.</p>', 'wishlist-member') . "</div>";
}
?>
<form method="get">
	<table class="form-table">
		<?php
		parse_str($this->QueryString('level'), $fields);
		foreach ((array) $fields AS $field => $value) {
			echo "<input type='hidden' name='{$field}' value='{$value}' />";
		}
		$wpm_levels = $this->GetOption('wpm_levels');
		?>
		<tr>
			<th scope="row"><?php _e('Membership Level', 'wishlist-member'); ?></th>
			<td  >
				<select name="level" onchange="this.form.submit()">
					<option value="">---</option>
					<?php
					foreach ((array) $wpm_levels AS $levelid => $level) {
						$selected = wlm_arrval($_GET,'level') == $levelid ? ' selected="true" ' : '';
						echo "<option value='{$levelid}'{$selected}>{$level[name]}</option>";
					}
					?>
					<option value="payperpost"<?php if (wlm_arrval($_GET,'level') == 'payperpost') echo ' selected="true"'; ?>>Pay Per Post</option>
				</select><?php echo $this->Tooltip("settings-registration-tooltips-Membership-Level"); ?>
			</td>
			<td>
				<noscript><p class="submit" style="margin:0;padding:0"><input type="submit" class="button-secondary" value="<?php _e('Select Membership Level', 'wishlist-member'); ?>" /></p></noscript>
			</td>
		</tr>
	</table>
</form>
<?php if (isset($wpm_levels[wlm_arrval($_GET,'level')]) || wlm_arrval($_GET,'level') == 'payperpost'): ?>
	<h2 style="font-size:18px;width:100%"><?php _e('Step 2. Edit Registration Form Code for the Selected Membership Level', 'wishlist-member'); ?></h2>
	<form method="post">
		<input type="hidden" name="level" value="<?php echo $_GET['level']; ?>" />
		<table class="form-table">
			<tr valign="top">
				<th scope="row"><?php _e('HTML Code to insert BEFORE the Registration Form', 'wishlist-member'); ?> <?php echo $this->Tooltip("settings-registration-tooltips-HTML-Code-to-insert-BEFORE-the-Registration-Form"); ?></th>
				<td width="1"><textarea cols="60" rows="10" name="wlm_regpage_before"><?php echo $regpage_before[wlm_arrval($_GET,'level')]; ?></textarea></td>
				<td align="left"></td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e('Choose Registration Form', 'wishlist-member'); ?>
				<td>

					<select name="wlm_regpage_form">
						<option value=""><?php _e('Default', 'wishlist-member'); ?></option>
						<?php
						$forms = $this->GetCustomRegForms();

						foreach ($forms AS $form) {
							$selected = $regpage_form[wlm_arrval($_GET,'level')] == $form->option_name ? ' selected="true" ' : '';
							$form_name = $form->option_value['form_name'];
							$form_name = trim($form_name);
							echo "<option value='{$form->option_name}' {$selected}>{$form_name}</option>";
						}
						?>
					</select>
					&nbsp &nbsp
					<a href="?<?php echo $regpage_base_url; ?>&mode2=custom&action=edit"><?php _e('Create a New Registration Form', 'wishlist-member'); ?></a>
				</td>
				<td align="left"></td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e('HTML Code to insert AFTER the Registration Form', 'wishlist-member'); ?> <?php echo $this->Tooltip("settings-registration-tooltips-HTML-Code-to-insert-AFTER-the-Registration-Form"); ?></th>
				<td width="1"><textarea cols="60" rows="10" name="wlm_regpage_after"><?php echo $regpage_after[wlm_arrval($_GET,'level')]; ?></textarea></td>
				<td align="left"></td>
			</tr>
		</table>
		<p class="submit">
			<input type="submit" class="button-primary" value="<?php _e('Save Settings', 'wishlist-member'); ?>" />
		</p>
	</form>
<?php endif; ?>
