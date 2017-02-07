<?php
include_once($this->pluginDir . '/lib/integration.shoppingcart.paypalcommon.php');

class WlmpaypalpayflowInit {
	private $forms;
	private $wlm;
	private $products;

	public function load_popup() {
		global $WishListMemberInstance;
		wp_enqueue_script('jquery-fancybox', $WishListMemberInstance->pluginURL.'/js/jquery.fancybox.pack.js', array('jquery'), $WishListMemberInstance->Version, true);
		wp_enqueue_style('jquery-fancybox', $WishListMemberInstance->pluginURL.'/css/jquery.fancybox.css', array(), $WishListMemberInstance->Version);
		wp_enqueue_script('wlm-popup-regform-card-validation', 'https://js.stripe.com/v2/', array('jquery'), $WishListMemberInstance->Version, true);
		wp_enqueue_script('wlm-popup-regform', $WishListMemberInstance->pluginURL.'/js/wlm.popup-regform.js', array('wlm-popup-regform-card-validation'), $WishListMemberInstance->Version, true);
		wp_enqueue_style('wlm-popup-regform-style', $WishListMemberInstance->pluginURL.'/css/wlm.popup-regform.css', array(), $WishListMemberInstance->Version);

	}
	public function __construct() {
		add_action('admin_init', array($this, 'use_underscore'));
		add_shortcode( 'wlm_payflow_btn', array($this, 'paypalpayflowbtn'));
		add_action('wp_footer', array($this, 'footer'), 100);
		add_filter( 'the_content', array($this, 'shortlink_processor') );

		$this->paypalpayflow_shortcode_btns();


		add_action('wp_ajax_wlm_payflow_new-product', array($this, 'new_product'));
		add_action('wp_ajax_wlm_payflow_all-products', array($this, 'get_all_products'));
		add_action('wp_ajax_wlm_payflow_save-product', array($this, 'save_product'));
		add_action('wp_ajax_wlm_payflow_delete-product', array($this, 'delete_product'));
		add_action('wp_ajax_wlm_payflow_get-product-form', array($this, 'paypal_form'));

		global $WishListMemberInstance;

		if(empty($WishListMemberInstance)) {
			return;
		}
		$this->wlm      = $WishListMemberInstance;
		$this->products = $WishListMemberInstance->GetOption('paypalpayflowproducts');
	}

	public function shortlink_processor($content) {
		static $called = false;
		if($called) return $content;
		$called = true;
		if(!empty($_GET['ppayflow'])) {
			echo 555555;
			$ppayflow = $_GET['ppayflow'];
			echo sprintf('<div style="display:none">%s</div>', do_shortcode( sprintf('[wlm_payflow_btn sku="%s"]', $ppayflow )));
			echo <<<STRING
			<script>
				jQuery(function($) {
					window.location.hash='regform-{$ppayflow}';
				});
			</script>
STRING;
		}
		return $content;
	}

	public function footer() {
		foreach((array) $this->forms as $f) {
			echo $f;
		}
		if(!empty($this->forms) && is_array($this->forms)) :
	?>
		<script type="text/javascript">
		jQuery(function($) {
		<?php
				$skus = array_keys($this->forms);
				foreach($skus as $sku) {
					echo sprintf("$('#regform-%s .regform-form').PopupRegForm();", $sku);
				}
		?>
		});
		</script>
	<?php
		endif;
	}
	public function use_underscore() {
		global $WishListMemberInstance;
		if(is_admin() && isset($_GET['page']) &&  $_GET['page'] == $WishListMemberInstance->MenuID && isset($_GET['wl']) && $_GET['wl'] == 'integration') {
			wp_enqueue_script('underscore-wlm', $WishListMemberInstance->pluginURL . '/js/underscore-1.6.min.js', array('underscore'), $WishListMemberInstance->Version);
		}
	}

	public function paypalpayflowbtn( $atts, $content) {
		global $WishListMemberInstance, $wlm_paypal_buttons;
		$this->load_popup();
		$products   = $WishListMemberInstance->GetOption('paypalpayflowproducts');
		$wpm_levels = $WishListMemberInstance->GetOption('wpm_levels');
		$atts       = extract( shortcode_atts( array( 'sku'=> null, 'btn' => null ), $atts ) );
		$product    = $products[$sku];
		$content    = trim($content);
		$btn        = trim($btn);

		if(!$btn) {
			$btn = $content;
		}

		if(!empty($wlm_paypal_buttons[$btn])) {
			$btn = $wlm_paypal_buttons[$btn];
		}

		$imgbtn = false;
		if($btn) {
			if(filter_var($btn, FILTER_VALIDATE_URL)) {
				$btn = sprintf('<img border="0" style="border:none" class="wlm-paypal-button" src="%s">', $btn);
				$imgbtn = true;
			}
		}

		$panel_button_label = 'Pay %amount %waiting';
		if($product['recurring']) {
			$amt = nl2br(wlm_paypal_create_description($product, false));
		} else {
			$amt = sprintf('%0.2f %s', $product['amount'], $product['currency']);
		}

		$settings              = $WishListMemberInstance->GetOption('paypalpayflowthankyou_url');
		$paypalpayflowthankyou     = $WishListMemberInstance->GetOption('payflowthankyou');
		$wpm_scregister        = get_bloginfo('url') . '/index.php/register/';
		$paypalpayflowthankyou_url = $wpm_scregister . $paypalpayflowthankyou;

		// Use the thank you url varialbe used in Paypal Pro so we don't change much of the code
		$paypalprothankyou_url = $paypalpayflowthankyou_url;
		include $WishListMemberInstance->pluginDir .'/extlib/wlm_paypal/form_new_fields.php';
		$this->forms[$sku] = wlm_build_payment_form($data);
		if($imgbtn) {
			$btn = sprintf('<a id="go-regform-%s" class="wlm-paypal-button go-regform" href="#regform-%s">%s</a>', $sku, $sku, $btn);
		} else {
			$btn = sprintf('<button id="go-regform-%s" class="wlm-paypal-button go-regform" href="#regform-%s">%s</button>', $sku, $sku, $btn);
		}
		return $btn;

	}
	public function paypalpayflow_shortcode_btns() {
		global $pagenow;
		if(in_array($pagenow, array('post.php', 'post-new.php'))) {
			global $WishListMemberInstance;
			$products = $WishListMemberInstance->GetOption('paypalpayflowproducts');
			if(is_array($products) && count($products)) {
				$WishListMemberInstance->IntegrationShortcodes[] = array('title' => __('PayPal Payflow Integration','wishlist-member') , 'value' => '', 'jsfunc' => 'wlmtnmcelbox_vars.show_paypalpayflow_inserter_lightbox');
			}
		}
	}


	//ajax methods

	public function delete_product() {
		$id = $_POST['id'];
		unset($this->products[$id]);
		$this->wlm->SaveOption('paypalpayflowproducts', $this->products);
	}
	public function save_product() {

		$id = $_POST['id'];
		$product = $_POST;
		$this->products[$id] = $product;
		$this->wlm->SaveOption('paypalpayflowproducts', $this->products);
		echo json_encode($this->products[$id]);
		die();
	}

	public function get_all_products() {
		$products = $this->products;
		echo json_encode($products);
		die();
	}

	public function new_product() {
		$products = $this->products;
		if(empty($products)) {
			$products = array();
		}

		//create an id for this button
		$id = strtoupper(substr(sha1( microtime()), 1, 10));

		$product = array(
			'id'            => $id,
			'name'          => $_POST['name'] . ' Product',
			'currency'      => 'USD',
			'amount'        => 10,
			'recurring'     => 0,
			'sku'           => $_POST['sku'],
			'checkout_type' => 'payments-standard'
		);

		$this->products[$id] = $product;
		$this->wlm->SaveOption('paypalpayflowproducts', $this->products);

		echo json_encode($product);
		die();
	}

	public function paypal_form() {
		echo $this->paypal_link($_POST['product_id'], true);
		exit;
	}

	public function paypal_link($product_id, $return_as_html_form = false) {
		global $WishListMemberInstance;

		if(empty($this->products[$product_id])) {
			return '';
		}

		$product = $this->products[$product_id];

		$sandbox              = (int) $WishListMemberInstance->GetOption('ppsandbox');
		$paypalpayflowthankyou     = $WishListMemberInstance->GetOption('ppthankyou');
		$blogurl              = get_bloginfo('url');
		$wpm_scregister       = $blogurl . '/index.php/register/';
		$paypalpayflowthankyou_url = $wpm_scregister . $paypalpayflowthankyou;
		$paypalcmd            = $product['recurring'] ? '_xclick-subscriptions' : '_xclick';
		$formsubmit           = $sandbox ? 'https://www.sandbox.paypal.com/cgi-bin/webscr' : 'https://www.paypal.com/cgi-bin/webscr';
		$paypalemail          = $WishListMemberInstance->GetOption($sandbox ? 'ppsandboxemail' : 'ppemail');

		$thefields                   = array();
		$the_fields['cmd']           = $paypalcmd;
		$the_fields['business']      = $paypalemail;
		$the_fields['item_name']     = $product['name'];
		$the_fields['item_number']   = $product['sku'];
		$the_fields['no_note']       = '1';
		$the_fields['no_shipping']   = '1';
		$the_fields['rm']            = '2';
		$the_fields['bn']            = 'WishListProducts_SP';
		$the_fields['cancel_return'] = $blogurl;
		$the_fields['notify_url']    = $paypalpayflowthankyou_url;
		$the_fields['return']        = $paypalpayflowthankyou_url;
		$the_fields['currency_code'] = $product['currency'];

		$button = '';

		if($product['recurring']) {
			$button = 'https://www.paypalobjects.com/webstatic/en_AU/i/buttons/btn_paywith_primary_m.png';
			$period = strtoupper(substr($product['recur_billing_period'], 0, 1));
			$trialperiod = strtoupper(substr($product['trial_recur_billing_period'], 0, 1));
			$trial2period = strtoupper(substr($product['trial2_recur_billing_period'], 0, 1));

			if($product['trial']) {
				$the_fields['a1'] = $product['trial_amount'];
				$the_fields['p1'] = $product['trial_recur_billing_frequency'];
				$the_fields['t1'] = $trialperiod;
				if($product['trial2']) {
					$the_fields['a2'] = $product['trial2_amount'];
					$the_fields['p2'] = $product['trial2_recur_billing_frequency'];
					$the_fields['t2'] = $trial2period;
				}
			}

			$the_fields['a3'] = $product['recur_amount'];
			$the_fields['p3'] = $product['recur_billing_frequency'];
			$the_fields['t3'] = $period;
			$the_fields['src'] = '1';

			if($product['recur_billing_cycles'] > 1) {
				$the_fields['srt'] = $product['recur_billing_cycles'];
			}

		} else {
			$button = 'https://www.paypalobjects.com/webstatic/en_US/i/buttons/buy-logo-medium.png';
			$the_fields['amount'] = $product['amount'];
		}

		if($return_as_html_form) {
			foreach($the_fields AS $fname => $fvalue) {
				$fvalue = sprintf("<input type='hidden' name='%s' value='%s'>", $fname, htmlentities($fvalue, ENT_QUOTES));
				$the_fields[$fname] = $fvalue;
			}
			return sprintf("<form method='post' action='%s' target='_top'>\n%s\n<input type='image' src='%s' alt='Pay with PayPal'>\n</form>", $formsubmit, implode("\n", $the_fields), $button);
		} else {
			$the_fields = http_build_query($the_fields);
			return $formsubmit . '?' . $the_fields;
		}
	}
}

global $wlm_payflow_init;
$wlm_payflow_init = new WlmpaypalpayflowInit();

