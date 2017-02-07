<?php
$__index__ = 'paypalpayflow';
$__sc_options__[$__index__] = 'PayPal <!-- 1 -->Payflow';
// $__sc_affiliates__[$__index__] = '';
// $__sc_videotutorial__[$__index__] = wlm_video_tutorial ( 'integration', 'sc', $__index__ );

if (wlm_arrval($_GET, 'cart') == $__index__) {
	if (!$__INTERFACE__) {
		// BEGIN Initialization
		$payflowthankyou = $this->GetOption('payflowthankyou');
		if (!$payflowthankyou) {
			$this->SaveOption('payflowthankyou', $payflowthankyou = $this->MakeRegURL());
		}

		// save POST URL
		if (wlm_arrval($_POST, 'payflowthankyou')) {
			$_POST['payflowthankyou'] = trim(wlm_arrval($_POST, 'payflowthankyou'));
			$wpmx = trim(preg_replace('/[^A-Za-z0-9]/', '', $_POST['payflowthankyou']));
			if ($wpmx == $_POST['payflowthankyou']) {
				if ($this->RegURLExists($wpmx, null, 'payflowthankyou')) {
					echo "<div class='error fade'>" . __('<p><b>Error:</b> stripe Thank You URL (' . $wpmx . ') is already in use by a Membership Level or another Shopping Cart.  Please try a different one.</p>', 'wishlist-member') . "</div>";
				} else {
					$this->SaveOption('payflowthankyou', $payflowthankyou = $wpmx);
					echo "<div class='updated fade'>" . __('<p>Thank You URL Changed.&nbsp; Make sure to update stripe with the same Thank You URL to make it work.</p>', 'wishlist-member') . "</div>";
				}
			} else {
				echo "<div class='error fade'>" . __('<p><b>Error:</b> Thank You URL may only contain letters and numbers.</p>', 'wishlist-member') . "</div>";
			}
		}

		if (isset($_POST['payflowsettings'])) {
			$payflowsettings = $_POST['payflowsettings'];
			$this->SaveOption('payflowsettings', $payflowsettings);
		}

		$payflowthankyou_url = $wpm_scregister . $payflowthankyou;
		$payment_link_format = trim(add_query_arg('ppayflow', ' ', home_url('/')));
		$payflowsettings = $this->GetOption('payflowsettings');

		// END Initialization
	} else {
		// START Interface
		$xposts = $this->GetPayPerPosts(array('post_title', 'post_type'));
		$post_types = get_post_types('', 'objects');

		$level_names = array();
		foreach($wpm_levels as $sku => $level) {
			$level_names[$sku] = $level['name'];
		}

		foreach ($xposts AS $post_type => $posts) {
			foreach ((array) $posts AS $post) {
				$level_names['payperpost-' . $post->ID] = str_replace("'", "", $post->post_title); 
			}
		}

		$paypal_api_settings_variable_name = 'payflowsettings';
		include($this->pluginDir . '/resources/forms/integration.shoppingcart.payflow-form.php');

		// products
		$table_handler_action_prefix = 'wlm_payflow_';
		include_once($this->pluginDir . '/resources/forms/integration.shoppingcart.paypalproducts-formtemplate.php');

		?>

		<style type="text/css">
			#logo-preview img { width: 90px; height: 40px;}
			.sandbox-mode { display: none; }
		</style>
		<script type="text/javascript">
				var level_names = JSON.parse('<?php echo json_encode($level_names); ?>');
				var send_to_editor = function(html) {
					imgurl = jQuery('img', html).attr('src');
					var el = jQuery('#stripe-logo');
					el.val(imgurl);
					tb_remove();
					//also update the img preview
					jQuery('#logo-preview').html('<img src="' + imgurl + '">');
				}

				jQuery(function($) {
					$('.dropmenu').on('click', function(ev) {
						ev.preventDefault();
						$('li.dropme ul').not( $(this).parent()).hide();
						console.log($(this).parent().find('ul'));
					});

					function update_fields(el, tr) {
						if (el.val() == 1) {
							tr.find('.amount').find('input').attr('disabled', true).val('');
							tr.find('.plans').find('select').removeAttr('disabled');
						} else {
							tr.find('.plans').find('select').attr('disabled', true).val('');
							tr.find('.amount').find('input').removeAttr('disabled');
						}
					}

					function update_sandbox_tbl(cb) {
						if(cb.prop('checked')) {
							$('.sandbox-mode').show('slow');
						} else {
							$('.sandbox-mode').hide('slow');
						}

					}

					$('.sandbox_mode').on('change', function(ev) {
						update_sandbox_tbl($(this));
					});


					update_sandbox_tbl($('.sandbox_mode'));
				});
		</script>
		<?php
		include_once($this->pluginDir . '/admin/tooltips/integration.shoppingcart.payflow.tooltips.php');
		// END Interface
	}
}
?>
