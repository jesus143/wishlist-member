<?php
include_once($this->pluginDir . '/lib/integration.shoppingcart.paypalcommon.php');

class WlmpaypalpsInit {
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
		add_shortcode( 'wlm_paypalps_btn', array($this, 'paypalpsbtn'));
		add_action('wp_footer', array($this, 'footer'), 100);

		$this->paypalps_shortcode_btns();


		add_action('wp_ajax_wlm_paypalps_new-product', array($this, 'new_product'));
		add_action('wp_ajax_wlm_paypalps_all-products', array($this, 'get_all_products'));
		add_action('wp_ajax_wlm_paypalps_save-product', array($this, 'save_product'));
		add_action('wp_ajax_wlm_paypalps_delete-product', array($this, 'delete_product'));
		add_action('wp_ajax_wlm_paypalps_get-product-form', array($this, 'paypal_form'));

		global $WishListMemberInstance;

		if(empty($WishListMemberInstance)) {
			return;
		}
		$this->wlm      = $WishListMemberInstance;
		$this->products = $WishListMemberInstance->GetOption('paypalpsproducts');
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

	public function paypalpsbtn( $atts, $content) {
		global $WishListMemberInstance, $wlm_paypal_buttons;

		$atts                 = extract( shortcode_atts( array( 'sku'=> null, 'btn' => null ), $atts ) );
		$paypalpsthankyou     = $WishListMemberInstance->GetOption('ppthankyou');
		$blogurl              = get_bloginfo('url');
		$wpm_scregister       = $blogurl . '/index.php/register/';
		$paypalpsthankyou_url = $wpm_scregister . $paypalpsthankyou;

		$btn = trim($btn);

		if(!empty($wlm_paypal_buttons[$btn])) {
			$btn = $wlm_paypal_buttons[$btn];
		}

		if(!$btn) {
			if($product['recurring']) {
				$btn = 'https://www.paypalobjects.com/webstatic/en_AU/i/buttons/btn_paywith_primary_m.png';
			} else {
				$btn = 'https://www.paypalobjects.com/webstatic/en_US/i/buttons/buy-logo-medium.png';
			}
		}

		$link = $paypalpsthankyou_url . '?pid='.$sku;

		if(filter_var($btn, FILTER_VALIDATE_URL) === false) {
			$pattern = '<button onclick="window.location=\'%s\'" class="wlm-paypal-button">%s</button>';
		} else {
			$pattern = '<a href="%s"><img src="%s" border="0" style="border:none" class="wlm-paypal-button"></a>';
		}

		$btn = sprintf($pattern, $link, $btn);

		return $btn;

	}
	public function paypalps_shortcode_btns() {
		global $pagenow;
		if(in_array($pagenow, array('post.php', 'post-new.php'))) {
			global $WishListMemberInstance;
			$products = $WishListMemberInstance->GetOption('paypalpsproducts');
			if(is_array($products) && count($products)) {
				$WishListMemberInstance->IntegrationShortcodes[] = array('title' => __('PayPal Payments Standard Integration','wishlist-member') , 'value' => '', 'jsfunc' => 'wlmtnmcelbox_vars.show_paypalps_inserter_lightbox');
			}
		}
	}


	//ajax methods

	public function delete_product() {
		$id = $_POST['id'];
		unset($this->products[$id]);
		$this->wlm->SaveOption('paypalpsproducts', $this->products);
	}
	public function save_product() {

		$id = $_POST['id'];
		$product = $_POST;
		$this->products[$id] = $product;
		$this->wlm->SaveOption('paypalpsproducts', $this->products);
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
		$this->wlm->SaveOption('paypalpsproducts', $this->products);

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
		$paypalpsthankyou     = $WishListMemberInstance->GetOption('ppthankyou');
		$blogurl              = get_bloginfo('url');
		$wpm_scregister       = $blogurl . '/index.php/register/';
		$paypalpsthankyou_url = $wpm_scregister . $paypalpsthankyou;
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
		$the_fields['notify_url']    = $paypalpsthankyou_url;
		$the_fields['return']        = $paypalpsthankyou_url;
		$the_fields['currency_code'] = $product['currency'];
		$the_fields['charset'] 		 = 'utf-8';

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

global $wlm_paypalps_init;
$wlm_paypalps_init = new WlmpaypalpsInit();

