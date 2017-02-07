<?php
/*
 * Shopping Cart Integration
 * Original Author : Mike Lopez
 * Version: $Id: shoppingcart.php 2935 2015-12-08 08:10:11Z mike $
 */
if (!isset($_GET['cart'])) {
	$_GET['cart'] = $this->GetOption('lastcartviewed');
}
$this->SaveOption('lastcartviewed', $_GET['cart']);

if(wlm_arrval($_POST, 'wlm_save_active_shopping_carts')) {
	$this->SaveOption('ActiveShoppingCarts', $_POST['active_wlm_shopping_carts']);
	printf ('<div class="updated"><p>%s</p></div>', __('Shopping carts updated.', 'wishlist-member'));
}

$sc_files = array();
$active_wlm_shopping_carts = (array) $this->GetOption('ActiveShoppingCarts');

$__integrations__ = glob($this->pluginDir . '/admin/integration.shoppingcart.*.php');
$__INTERFACE__ = false;
$has_active = false;
foreach ((array) $__integrations__ AS $__integration__) {
	include($__integration__);
	$sc_files[basename($__integration__)] = $__sc_options__[$__index__];
	if(in_array(basename($__integration__), $active_wlm_shopping_carts)) {
		$has_active = true;
	}
}
// sort by Name
natcasesort($sc_files);
natcasesort($__sc_options__);

$enabled = array_intersect_key($sc_files, count($active_wlm_shopping_carts) ? array_combine($active_wlm_shopping_carts, $active_wlm_shopping_carts) : array());
$disabled = array_diff($__sc_options__, $enabled);
$enabled = array_intersect($__sc_options__, $enabled);

?>
<style>
	.column-container {
		-webkit-column-count: 2;
		-moz-column-count: 2;
		column-count: 2;

		-webkit-column-gap: 30px;
		-moz-column-gap: 30px;
		column-gap: 30px;

		-webkit-column-rule: 1px solid #aaa;
		-moz-column-rule: 1px solid #aaa;
		column-rule: 1px solid #aaa;
	}
	.column-container label {
		display: block;
		margin: 0 0 0 1.5em;
		padding: .3em .3em 0 .3em;
		text-indent: -1.5em;
	}
</style>
<?php if(!$has_active) printf('<div class="error"><p>%s</p></div>', __('No shopping cart integrations enabled.','wishlist-member')); ?>

<div style="display:<?php echo $has_active ? 'none':'block'; ?>" id="manage-shopping-carts">
	<form method="post">
		<input type="hidden" name="wlm_save_active_shopping_carts" value="1">
		<p style="font-size:1em">
			<strong><?php _e('Enable or disable shopping carts by selecting or deselecting them.', 'wishlist-member'); ?></strong>
		</p>
		<div class="column-container">
		<p style="margin: 0;padding:0">
			<?php foreach($sc_files AS $sc_file => $sc_name) : ?>
				<label><input type="checkbox" id="<?php echo array_search($sc_name, $__sc_options__); ?>" name="active_wlm_shopping_carts[]" value="<?php echo $sc_file; ?>" <?php if(in_array($sc_file, $active_wlm_shopping_carts)) echo 'checked="checked"'; ?>><?php echo $sc_name; ?></label>
			<?php endforeach; ?>
		</p>
		</div>
		<hr>
		<p>
			<input type="submit" class="button button-primary" value="Update Shopping Carts">
		</p>
	</form>
</div>

<?php if(!$has_active) return; ?>
<form method="get">
	<table class="form-table">
		<?php
		parse_str($this->QueryString('cart'), $fields);
		foreach ((array) $fields AS $field => $value) {
			echo "<input type='hidden' name='{$field}' value='{$value}' />";
		}
		?>
		<tr>
			<td width="1" scope="row" style="padding-left:0">
				<p style="text-align:justify"><?php _e('Select an available Shopping Cart from the dropdown list below to view instructions and set up the corresponding integration.', 'wishlist-member'); ?></p>
			</td>
			<td style="text-align:right;white-space:nowrap">
				<?php if (!empty($__sc_videotutorial__[wlm_arrval($_GET,'cart')])): ?>
					<p class="alignright" style="margin-top:0"><a href="<?php echo $__sc_videotutorial__[wlm_arrval($_GET,'cart')]; ?>" target="_blank"><?php _e('Watch Integration Video Tutorial', 'wishlist-member'); ?></a></p>
				<?php endif; ?>
			</td>
		</tr>
		<tr>
			<td width="1" style="white-space:nowrap; padding-left:0;">
				<select name="cart">
					<option value=""><?php _e('-- Select a Shopping Cart --', 'wishlist-member'); ?></option>
					<?php
					// Generic integration always goes last
					if (isset($__sc_options__['generic'])) {
						$x = $__sc_options__['generic'];
						unset($__sc_options__['generic']);
						$__sc_options__['generic'] = $x;
					}

					// display dropdown options
					$provider_name = '';
					printf('<optgroup label="%s">', __('Enabled', 'wishlist-member'));
					foreach ((array) $enabled AS $key => $value) {
						$selected = (wlm_arrval($_GET,'cart') == $key) ? ' selected="true" ' : '';
						echo '<option value="' . $key . '"' . $selected . '>' . $value . '</option>';
						if($selected) $provider_name = $value;
					}
					echo '</optgroup>';
					printf('<optgroup label="%s">', __('Disabled', 'wishlist-member'));
					foreach ((array) $disabled AS $key => $value) {
						$selected = (wlm_arrval($_GET,'cart') == $key) ? ' selected="true" ' : '';
						echo '<option value="' . $key . '"' . $selected . '>' . $value . ' (' . __('Disabled', 'wishlist-member') . ')</option>';
						if($selected) $provider_name = $value;
					}
					echo '</optgroup>';
					?>
				</select> <?php echo $this->Tooltip("shoppingcart-tooltips-Select-shoppingcart-System"); ?> 
				&nbsp; <input type="submit" class="button-secondary" value="<?php _e('Set Shopping Cart', 'wishlist-member'); ?>" />

				<a style="text-decoration:none; margin-left:5px; font-size:1.5em; vertical-align:middle; line-height: 0" href="#TB_inline?&width=500&height=400&inlineId=manage-shopping-carts" class="thickbox" title="<?php echo $x = __('Enable/Disable Shopping Carts', 'wishlist-member'); ?>"><i class="icon-gear"></i></a>	

			</td>
			<td style="text-align:right;white-space:nowrap">
				<?php if (isset($__sc_affiliates__[wlm_arrval($_GET,'cart')])): ?>
					<a href="<?php echo $__sc_affiliates__[wlm_arrval($_GET,'cart')]; ?>" target="_blank"><?php printf(__('Learn more about %1$s', 'wishlist-member'), $__sc_options__[wlm_arrval($_GET,'cart')]); ?></a>
				<?php endif; ?>
			</td>
		</tr>
	</table>
</form>
<hr />

<?php
	if(in_array($__sc_options__[wlm_arrval($_GET, 'cart')], $disabled)) {
		?>
		<script>
			function wlm_enable_sc(scid) {
				jQuery('#manage-shopping-carts input#'+scid).prop('checked', true);
				jQuery('#manage-shopping-carts form').submit();
			}
		</script>
		<?php
		printf('<div class="error"><p>%s | <a href="#" onclick="wlm_enable_sc(\'%s\')">%s</a></p></div>', __('Notice: This integration is inactive.', 'wishlist-member'), wlm_arrval($_GET, 'cart'), __('Enable', 'wishlist-member'));
	}
?>
<blockquote>
	<?php
	$__INTERFACE__ = true;
	foreach ((array) $__integrations__ AS $__integration__) {
		include($__integration__);
	}

	?>
</blockquote>

<?php
add_thickbox();
include_once($this->pluginDir . '/admin/tooltips/shoppingcart.tooltips.php');
?>
