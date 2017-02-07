<?php
include_once($this->pluginDir . '/lib/integration.shoppingcart.paypalcommon.php');

class WlmPaypalProInit {
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
		add_shortcode( 'wlm_paypalpro_btn', array($this, 'paypalprobtn'));
		add_action('wp_footer', array($this, 'footer'), 100);
		add_filter( 'the_content', array($this, 'shortlink_processor') );

		$this->paypalpro_shortcode_btns();


		add_action('wp_ajax_wlm_paypalpro_new-product', array($this, 'new_product'));
		add_action('wp_ajax_wlm_paypalpro_all-products', array($this, 'get_all_products'));
		add_action('wp_ajax_wlm_paypalpro_save-product', array($this, 'save_product'));
		add_action('wp_ajax_wlm_paypalpro_delete-product', array($this, 'delete_product'));

		global $WishListMemberInstance;

		if(empty($WishListMemberInstance)) {
			return;
		}
		$this->wlm      = $WishListMemberInstance;
		$this->products = $WishListMemberInstance->GetOption('paypalproproducts');

	}

	public function shortlink_processor($content) {
		static $called = false;
		if($called) return $content;
		$called = true;
		if(!empty($_GET['pppro'])) {
			$pppro = $_GET['pppro'];
			echo sprintf('<div style="display:none">%s</div>', do_shortcode( sprintf('[wlm_paypalpro_btn sku="%s"]', $pppro )));
			echo <<<STRING
			<script>
				jQuery(function($) {
					window.location.hash='regform-{$pppro}';
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
		if(is_admin() && isset($_GET['page']) && $_GET['page'] == $WishListMemberInstance->MenuID && isset($_GET['wl']) && $_GET['wl'] == 'integration') {
			wp_enqueue_script('underscore-wlm', $WishListMemberInstance->pluginURL . '/js/underscore-1.6.min.js', array('underscore'), $WishListMemberInstance->Version);
		}
	}

	public function paypalprobtn( $atts, $content) {
		global $WishListMemberInstance, $wlm_paypal_buttons;
		$this->load_popup();
		$products   = $WishListMemberInstance->GetOption('paypalproproducts');
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

		$settings              = $WishListMemberInstance->GetOption('paypalprothankyou_url');
		$paypalprothankyou     = $WishListMemberInstance->GetOption('paypalprothankyou');
		$wpm_scregister        = get_bloginfo('url') . '/index.php/register/';
		$paypalprothankyou_url = $wpm_scregister . $paypalprothankyou;
		include $WishListMemberInstance->pluginDir .'/extlib/wlm_paypal/form_new_fields.php';
		$this->forms[$sku] = wlm_build_payment_form($data);
		if($imgbtn) {
			$btn = sprintf('<a id="go-regform-%s" class="wlm-paypal-button go-regform" href="#regform-%s">%s</a>', $sku, $sku, $btn);
		} else {
			$btn = sprintf('<button id="go-regform-%s" class="wlm-paypal-button go-regform" href="#regform-%s">%s</button>', $sku, $sku, $btn);
		}
		return $btn;
	}
	public function paypalpro_shortcode_btns() {
		global $pagenow;
		if(in_array($pagenow, array('post.php', 'post-new.php'))) {
			global $WishListMemberInstance;
			$products = $WishListMemberInstance->GetOption('paypalproproducts');
			if(is_array($products) && count($products)) {
				$WishListMemberInstance->IntegrationShortcodes[] = array('title' => __('PayPal Pro Integration','wishlist-member') , 'value' => '', 'jsfunc' => 'wlmtnmcelbox_vars.show_paypalpro_inserter_lightbox');
			}
		}
	}


	//ajax methods

	public function delete_product() {
		$id = $_POST['id'];
		unset($this->products[$id]);
		$this->wlm->SaveOption('paypalproproducts', $this->products);
	}
	public function save_product() {

		$id = $_POST['id'];
		$product = $_POST;
		$this->products[$id] = $product;
		$this->wlm->SaveOption('paypalproproducts', $this->products);
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
			'checkout_type' => 'direct-charge'
		);

		$this->products[$id] = $product;
		$this->wlm->SaveOption('paypalproproducts', $this->products);

		echo json_encode($product);
		die();
	}
}


$wlm_paypalpro_init = new WlmPaypalProInit();

