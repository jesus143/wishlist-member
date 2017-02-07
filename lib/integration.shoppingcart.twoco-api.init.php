<?php

require_once dirname(__FILE__) . '/../lib/integration.shoppingcart.twoco-api.php';

class WishListtwoco_apiIntegrationInit {
	private $forms;

	public function __construct() {

	}

}

class WLM_TwoCo_Api_ShortCodes {
	protected $folder = 'wlm_twoco_api';
	public function __construct() {
		add_shortcode('wlm_twoco_api_btn', array($this, 'wlm_twoco_api_btn'));
		
		//register tinymce shortcodes

		//hook after the regform resources are already loaded
		add_action('wp_footer', array($this, 'footer'));
		global $pagenow;
		if(in_array($pagenow, array('post.php', 'post-new.php'))) {
			global $WLMTinyMCEPluginInstanceOnly;
			global $WishListMemberInstance;

			$levels = $WishListMemberInstance->GetOption('wpm_levels');

			$wlm_shortcodes = array();
			$str = __(" Registration Button", "wishlist-member");
			foreach($levels as $i => $l) {
				$wlm_shortcodes[] = array('title' => $l['name'] . $str , 'value' => sprintf("[wlm_twoco_api_btn sku=%s]", $i));
			}
			if($wlm_shortcodes) {
				$WishListMemberInstance->IntegrationShortcodes['2Checkout Payment API Integration'] = $wlm_shortcodes;
			}
		}

	}
	public function get_view_path($handle) {
		global $WishListMemberInstance;
		return sprintf($WishListMemberInstance->pluginDir .'/extlib/'.$this->folder.'/%s.php', $handle);
	}
	public function profile_form($user) {
		$user_id = $user;
		if(is_object($user)) {
			$user_id = $user->ID;
		}

		global $WishListMemberInstance;
		global $pagenow;
		if($pagenow == 'profile.php' || $pagenow == 'user-edit.php') {
			$stripe_cust_id = $WishListMemberInstance->Get_UserMeta($user_id, 'stripe_cust_id');
			include $this->get_view_path('stripe_user_profile');
		}
	}
	public function update_profile($user) {
		$user_id = $user;
		if(is_object($user)) {
			$user_id = $user->ID;
		}
		if(current_user_can('manage_options')) {
			global $WishListMemberInstance;
			if(isset($_POST['stripe_cust_id'])) {
				$WishListMemberInstance->Update_UserMeta($user_id, 'stripe_cust_id', trim($_POST['stripe_cust_id']));
			}
		}
	}
	public function notices() {
		if(extension_loaded('curl')) {
			return;
		}

		if($_GET['page'] == 'WishListMember' && $_GET['wl'] =='integration') {
			$msg = '<div class="error fade"><p>';
			$msg .= __('<strong>WishList Member Notice:</strong> The <strong>Stripe</strong> integration will not work properly. Please enable <strong>Curl</strong>.', 'wishlist-member');
			$msg .= '</p></div>';
			echo $msg;
		}
	}

	public function load_popup() {
		global $WishListMemberInstance;
		wp_enqueue_script('jquery-fancybox', $WishListMemberInstance->pluginURL.'/js/jquery.fancybox.pack.js', array('jquery'), $WishListMemberInstance->Version, true);
		wp_enqueue_style('jquery-fancybox', $WishListMemberInstance->pluginURL.'/css/jquery.fancybox.css', array(), $WishListMemberInstance->Version);
		wp_enqueue_script('wlm-popup-regform-card-validation', 'https://js.stripe.com/v2/', array('jquery'), $WishListMemberInstance->Version, true);
		wp_enqueue_script('wlm-popup-regform', $WishListMemberInstance->pluginURL.'/js/wlm.popup-regform.js', array('wlm-popup-regform-card-validation'), $WishListMemberInstance->Version, true);
		wp_enqueue_style('wlm-popup-regform-style', $WishListMemberInstance->pluginURL.'/css/wlm.popup-regform.css', array(), $WishListMemberInstance->Version);
	
		global $WishListMemberInstance;
		$twocheckoutapisettings    = $WishListMemberInstance->GetOption('twocheckoutapisettings');
		//Check if Sandbox is enabled
		if($twocheckoutapisettings['twocheckoutapi_sandbox'])
			$twoco_url = 'sandbox.2checkout.com';
		else
			$twoco_url = 'www.2checkout.com';
		
		wp_enqueue_script('wlm-api-2co-min', 'https://'.$twoco_url.'/checkout/api/2co.min.js', array(), $WishListMemberInstance->Version, true);
		wp_enqueue_script('wlm-api-2co-publickey', 'https://'.$twoco_url.'/checkout/api/script/publickey/'.$twocheckoutapisettings['twocheckoutapi_publishable_key'].'', array(), $WishListMemberInstance->Version, true);
	}
	public function wlm_twoco_api_btn($atts, $content) {
		
		global $WishListMemberInstance;
		global $current_user;
		

		global $current_user;
		$class = empty($regform_cust_id)? 'regform-form' : null;

		$regform_cust_id = 0;

		global $WishListMemberInstance;
		$twocheckoutapisettings    = $WishListMemberInstance->GetOption('twocheckoutapisettings');

	
		$this->load_popup();
		extract(shortcode_atts(array(
					'sku' => null,
						), $atts));

		if (empty($sku)) {
			return null;
		}

		
		echo '
<script type="text/javascript">

 var skutouse = "";

  function successCallback(data) {
 var skutouse = document.getElementById("hiddensku").value;
    var myForm = document.getElementById("myCCForm-"+skutouse);
    myForm.token.value = data.response.token.token;
    myForm.submit();        
  }

  function errorCallback(data) {
	 var skutouse = document.getElementById("hiddensku").value;
	 var myForm = document.getElementById("myCCForm-"+skutouse);
	 myForm.submit();        
  }

  function retrieveToken(skutouse) {
	var hiddensku = document.getElementById("hiddensku");
	hiddensku.value = skutouse;
    TCO.requestToken(successCallback, errorCallback, "myCCForm-"+skutouse);
  }

</script>
';	
		
		$wpm_levels               = $WishListMemberInstance->GetOption('wpm_levels');
		$twoco_apisettings             = $WishListMemberInstance->GetOption('twocheckoutapisettings');
		$twoco_apisettings['skip_cvc'] = true;
		extract($twoco_apisettings);


		$ppp_level    = $WishListMemberInstance->IsPPPLevel($sku);
		$level_name   = $wpm_levels[$sku]['name'];

		if($ppp_level) {
			$level_name = $ppp_level->post_title;
		}



		$btn_label       = empty($buttonlabel) ? "Join %level" : $buttonlabel;
		$btn_label       = str_replace('%level', $level_name, $btn_label);
		$panel_btn_label = empty($twoco_apisettings['panelbuttonlabel']) ? "Pay" : $twoco_apisettings['panelbuttonlabel'];
		$panel_btn_label = str_replace('%level', $level_name, $panel_btn_label);
		$settings        = $connections[$sku];
		$amt             = $settings['rebill_init_amount'];
		$currency        = empty($twoco_apisettings['currency'])? 'USD' : $twoco_apisettings['currency'];
		$wpm_scregister  = get_site_url() . '/index.php/register/';
		$thankyouurl     = $wpm_scregister . $WishListMemberInstance->GetOption('twocheckoutapithankyouurl');


		ob_start();
		?>
		<?php if (empty($content)) : ?>
			<button class="regform-button go-regform" style="width: auto" id="go-regform-<?php echo $sku ?>" class="" href="#regform-<?php echo $sku ?>"><?php echo $btn_label ?></button>
		<?php else: ?>
			<a id="go-regform-<?php echo $sku ?>" class="go-regform" href="#regform-<?php echo $sku ?>"><?php echo $content ?></a>
		<?php endif; ?>

		<input type="hidden" id="hiddensku" value="">	
			
		<?php
		$btn = ob_get_clean();
		ob_start();
		?>


		<?php
		if(!is_user_logged_in()){
			//retrieve fields
			$path = sprintf($WishListMemberInstance->pluginDir .'/extlib/'.$this->folder.'/form_new_fields.php');
			include $path;
			$this->forms[$sku] = wlm_build_payment_form($data);
		} else {
			global $current_user;
			$path = sprintf($WishListMemberInstance->pluginDir .'/extlib/'.$this->folder.'/form_existing_fields.php');
			include $path;
			$this->forms[$sku] = wlm_build_payment_form($data);
		}
		//include $this->get_view_path('form_css');
		?>

		<?php
		return $btn;
	}

	public function footer() {
		foreach((array) $this->forms as $f) {
			echo $f;
		}
?>
<script type="text/javascript">
jQuery(function($) {
<?php
	if(!empty($this->forms) && is_array($this->forms)) {
		$skus = array_keys($this->forms);
		foreach($skus as $sku) {
			if(is_user_logged_in()) {
				echo sprintf("
						$('#regform-%s .regform-form').PopupRegForm({
						validate_first_name: false,
						validate_last_name: false,
						validate_email: false,
						validate_cvc: false
						});", $sku);
			} else {
				echo sprintf("$('#regform-%s .regform-form').PopupRegForm({validate_cvc: false});", $sku);
			}
		}
	}
?>
});
</script>
<?php
	}

}



$sc = new WLM_TwoCo_Api_ShortCodes();
$twoco_api_init = new WishListtwoco_apiIntegrationInit();
?>
