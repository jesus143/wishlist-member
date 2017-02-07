<?php

if(extension_loaded('curl') && function_exists('mb_detect_encoding') && !class_exists( 'Stripe', FALSE )) {
 	include_once($this->pluginDir . '/extlib/Stripe/lib/Stripe.php');
}


if (!class_exists('WLM_Stripe_ShortCodes')) {
	class WLM_Stripe_ShortCodes {
		public function __construct() {
			add_action('edit_user_profile', array($this, 'profile_form'));
			add_action('show_user_profile', array($this, 'profile_form'));
			add_action('profile_update', array($this, 'update_profile'), 9, 2);

			add_action('admin_notices', array($this, 'notices'));

			add_shortcode('wlm_stripe_btn', array($this, 'wlm_stripe_btn'));
			add_shortcode('wlm_stripe_linkback', array($this, 'wlm_stripe_linkback'));
			add_shortcode('wlm_stripe_profile', array($this, 'wlm_stripe_profile'));
			add_action('wp_footer', array($this, 'footer'));

			//register tinymce shortcodes
			global $pagenow;
			if(in_array($pagenow, array('post.php', 'post-new.php'))) {
				global $WishListMemberInstance;

				$levels = $WishListMemberInstance->GetOption('wpm_levels');

				$wlm_shortcodes = array();
				$str = __(" Registration Button", "wishlist-member");
				foreach($levels as $i => $l) {
					$wlm_shortcodes[] = array('title' => $l['name'] . $str , 'value' => sprintf("[wlm_stripe_btn sku=%s]", $i));
				}

				$wlm_shortcodes[] = array('title' => __('Profile Page','wishlist-member'), 'value' => '', 'jsfunc' => 'wlmtnmcelbox_vars.stripe_profile_objs');
				if($wlm_shortcodes) {
					$WishListMemberInstance->IntegrationShortcodes['Stripe Integration'] = $wlm_shortcodes;
				}
			}

			$WishListMemberInstance->tinymce_lightbox_files[] = $this->get_view_path('tinymce_lightbox');

		}
		public function get_view_path($handle) {
			global $WishListMemberInstance;
			return sprintf($WishListMemberInstance->pluginDir .'/extlib/wlm_stripe/%s.php', $handle);
		}
		public function profile_form($user) {
			if(!current_user_can('manage_options')) {
				return;
			}

			$user_id = $user;
			if(is_object($user)) {
				$user_id = $user->ID;
			}

			global $WishListMemberInstance;
			global $pagenow;

			$stripeapikey         = trim($WishListMemberInstance->GetOption('stripeapikey'));
			$stripepublishablekey = trim($WishListMemberInstance->GetOption('stripepublishablekey'));

			if(empty($stripeapikey) && empty($stripeapikey)) {
				return;
			}

			if($pagenow == 'profile.php' || $pagenow == 'user-edit.php') {
				$stripe_cust_id = $WishListMemberInstance->Get_UserMeta($user_id, 'stripe_cust_id');
				include $this->get_view_path('stripe_user_profile');
			}
		}
		public function update_profile($user) {
			if(!current_user_can('manage_options')) {
				return;
			}

			$user_id = $user;
			if(is_object($user)) {
				$user_id = $user->ID;
			}

			global $WishListMemberInstance;
			if(isset($_POST['stripe_cust_id'])) {
				$WishListMemberInstance->Update_UserMeta($user_id, 'stripe_cust_id', trim($_POST['stripe_cust_id']));
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



		public function wlm_stripe_btn($atts, $content) {
			$form = new WLM_Stripe_Forms();
			return $form->generate_stripe_form($atts, $content);
		}
		public function footer() {
			global $WishListMemberInstance;
			if(isset($WishListMemberInstance))
				$stripethankyou = $WishListMemberInstance->GetOption('stripethankyou');
			
			$wpm_scregister = get_bloginfo('url') . '/index.php/register/';
			$stripethankyou_url = $wpm_scregister . $stripethankyou;

			$wlmstripevars['cancelmessage'] = __("Are you sure you want to cancel your subscription?", 'wishlist-member');
			$wlmstripevars['nonceinvoices'] = wp_create_nonce('stripe-do-invoices');
			$wlmstripevars['nonceinvoicedetail'] = wp_create_nonce('stripe-do-invoice');
			$wlmstripevars['noncecoupon'] = wp_create_nonce('stripe-do-check_coupon');
			$wlmstripevars['noncecoupondetail'] = wp_create_nonce('stripe-do-get_coupon');
			$wlmstripevars['stripethankyouurl'] = $stripethankyou_url;
			?>
			<script type="text/javascript">
				function get_stripe_vars() {
					return eval( '(' + '<?php echo json_encode($wlmstripevars)?>' +')');
				}
			</script>
			<?php
		}

		public function wlm_stripe_profile($atts) {
			global $WishListMemberInstance;
			global $current_user;

			$stripepublishablekey = $WishListMemberInstance->GetOption('stripepublishablekey');
			$stripethankyou = $WishListMemberInstance->GetOption('stripethankyou');
			$wpm_scregister = get_bloginfo('url') . '/index.php/register/';
			$stripethankyou_url = $wpm_scregister . $stripethankyou;

			if (empty($current_user->ID)) {
				return null;
			}

			$default_atts = array('levels' => '','include_posts' => 'yes' );
			$atts = shortcode_atts( $default_atts, $atts );
			$mlevels = $atts["levels"] == "" ? "all" : $atts["levels"];
			$mlevels = $mlevels != "no" ? ( $mlevels != "all" ? explode( ",", $mlevels ) : $mlevels ) : "no";
			$ppost = $atts["include_posts"] != "no" ? "yes" : "no";

			wp_enqueue_style('wlm-stripe-profile-style', $WishListMemberInstance->pluginURL.'/extlib/wlm_stripe/css/stripe-profile.css', '', $WishListMemberInstance->Version);
			wp_enqueue_style('stripe-paymenttag-style', $WishListMemberInstance->pluginURL.'/extlib/wlm_stripe/css/stripe-paymenttag.css', '', $WishListMemberInstance->Version);
			wp_enqueue_script('stripe-paymenttag', $WishListMemberInstance->pluginURL.'/extlib/wlm_stripe/js/stripe-paymenttag.js', array('jquery'), $WishListMemberInstance->Version, true);
			wp_enqueue_script('leanModal', $WishListMemberInstance->pluginURL.'/extlib/wlm_stripe/js/jquery.leanModal.min.js', array('jquery'), $WishListMemberInstance->Version, true);
			wp_enqueue_script('wlm-stripe-profile', $WishListMemberInstance->pluginURL.'/extlib/wlm_stripe/js/stripe.wlmprofile.js', array('stripe-paymenttag', 'leanModal'), $WishListMemberInstance->Version, true);


			$levels = $WishListMemberInstance->GetMembershipLevels($current_user->ID, null, null, null, true);
			$wpm_levels = $WishListMemberInstance->GetOption('wpm_levels');
			$user_posts	= $WishListMemberInstance->GetUser_PayPerPost("U-".$current_user->ID);



			$txnids = array();

			if ( $mlevels != "no" ) {
				foreach ($wpm_levels as $id => $level) {
					if ( $mlevels != "all" && ! in_array( $id, (array)$mlevels ) ) {
						continue;
					}
					$txn = $WishListMemberInstance->GetMembershipLevelsTxnID($current_user->ID, $id);
					if(empty($txn)) {
						continue;
					}
					$txnids[$id]['txn'] = $txn;
					$txnids[$id]['level'] = $level;
					$txnids[$id]['level_id'] = $id;
					$txnids[$id]['type'] = 'membership';
				}
			}


			if ( $ppost == "yes" ) {
				foreach($user_posts as $u) {
					$p = get_post($u->content_id);
					$id = 'payperpost-'.$u->content_id;
					$txn = $WishListMemberInstance->Get_ContentLevelMeta("U-".$current_user->ID, $u->content_id, 'transaction_id');
					$txnids[$id]['txn'] = $txn;
					$txnids[$id]['level_id'] = $id;
					$txnids[$id]['type'] = 'post';
					$txnids[$id]['level'] = array(
						'name' => $p->post_title
					);
				}
			}





			$wlm_user = new WishListMemberUser($current_user->ID);
			ob_start();
			?>
			<?php if (isset($_GET['status'])): ?>
				<?php if (wlm_arrval($_GET,'status') == 'ok'): ?>
					<p><span class="stripe-success"><?php _e("Profile Updated", "wishlist-member") ?></span></p>
				<?php else: ?>
					<span class="stripe-error"><?php _e("Unable to update your profile, please try again", "wishlist-member") ?></span>
				<?php endif; ?>
			<?php endif; ?>
			<?php
			include $this->get_view_path('profile');
			$str = ob_get_clean();
			$str = preg_replace('/\s+/', ' ', $str);
			return $str;

		}
	}
}
if (!class_exists('WLM_Stripe_Forms')) {

	class WLM_Stripe_Forms {
		protected $forms;
		public function get_view_path($handle) {
			global $WishListMemberInstance;
			return sprintf($WishListMemberInstance->pluginDir .'/extlib/wlm_stripe/%s.php', $handle);
		}
		public function footer() {
			global $current_user;
			global $WishListMemberInstance;

			$stripepublishablekey = $WishListMemberInstance->GetOption('stripepublishablekey');
			$skus                 = array_keys($this->forms);
			$stripe_cust_id       = $WishListMemberInstance->Get_UserMeta($current_user->ID, 'stripe_cust_id');

			foreach($this->forms as $frm) {
				echo $frm;
			}


			?>
<script type="text/javascript">
	Stripe.setPublishableKey('<?php echo $stripepublishablekey ?>');
	
	var stripe_payment_button_status=true;
jQuery(function($) {
<?php

		foreach($skus as $sku) {

				if(is_user_logged_in() && !empty($stripe_cust_id)) {
					echo <<<str
					$('#regform-$sku .regform-form').PopupRegForm({
						skip_all_validations: true
					});
str;
				} else {
					echo <<<str
					$('#regform-$sku .regform-form').PopupRegForm({
						on_validate_success: function(form, fields, ui) {
							ui.find('.regform-waiting').show();
							Stripe.card.createToken({
								number: fields.card_number.val(),
								cvc: fields.cvc.val(),
								exp_month: fields.exp_month.val(),
								exp_year: fields.exp_year.val(),
								name: fields.first_name.val() + " " + fields.last_name.val()
							}, function(status, response) {
								if (response.error) {
									// show the errors on the form
									ui.find('.regform-error').html( '<p>' + response.error.message  + '</p>');
									form.find('.regform-button').prop("disabled", false);
									form.find('.regform-waiting').hide();
								} else {
									var token = response['id'];
									// insert the token into the form so it gets submitted to the server
									form.append("<input type='hidden' name='stripeToken' value='" + token + "'/>");
									
									if ( stripe_payment_button_status == true) {
									stripe_payment_button_status = false;
									form.submit();
									}

								}
							});
							return false;
						}
					});
str;
				}
		}

?>
});
</script>
<?php
		}
		public function load_popup() {
			global $WishListMemberInstance;
			wp_enqueue_script('jquery-fancybox', $WishListMemberInstance->pluginURL.'/js/jquery.fancybox.pack.js', array('jquery'), $WishListMemberInstance->Version, true);
			wp_enqueue_style('jquery-fancybox', $WishListMemberInstance->pluginURL.'/css/jquery.fancybox.css', array(), $WishListMemberInstance->Version);

			wp_enqueue_script('wlm-popup-regform-card-validation', '//js.stripe.com/v2/', array('jquery'), $WishListMemberInstance->Version, true);
			wp_enqueue_script('wlm-popup-regform', $WishListMemberInstance->pluginURL.'/js/wlm.popup-regform.js', array('wlm-popup-regform-card-validation'), $WishListMemberInstance->Version, true);
			wp_enqueue_style('wlm-popup-regform-style', $WishListMemberInstance->pluginURL.'/css/wlm.popup-regform.css', array(), $WishListMemberInstance->Version);

		}

		public function generate_stripe_form($atts, $content) {
			global $WishListMemberInstance;
			$this->load_popup();
			add_action('wp_footer', array($this, 'footer'), 100);

			global $current_user;
			extract(
				shortcode_atts(
					array(
						'sku' => null,
						'amount' => 0,
						'currency' => '',
						'coupon' => 1,
						'showlogin' => 1,
					),
					$atts
				)
			);

			if (empty($sku)) {
				return null;
			}
			$amount   = $amount ? (float) $amount : 0;
			$currency = $currency ? $currency : '';
			$coupon   = (int) $coupon;
			$btn_hash = false;

			$stripeapikey       = $WishListMemberInstance->GetOption('stripeapikey');
			$stripeconnections  = $WishListMemberInstance->GetOption('stripeconnections');
			$stripethankyou     = $WishListMemberInstance->GetOption('stripethankyou');
			$wpm_scregister     = get_site_url() . '/index.php/register/';
			$stripethankyou_url = $wpm_scregister . $stripethankyou;
			$stripesettings     = $WishListMemberInstance->GetOption('stripesettings');
			$wpm_levels         = $WishListMemberInstance->GetOption('wpm_levels');
			$WishListMemberInstance->InjectPPPSettings($wpm_levels);

			//settings
			$settings = $stripeconnections[$sku];
			$amt = $settings['amount'];
			$cur = empty($stripesettings['currency']) ? 'USD' : $stripesettings['currency'];

			if ($settings['subscription']) {
				try {
					Stripe::setApiKey($stripeapikey);
					$plan = Stripe_Plan::retrieve($settings['plan']);
					$amt = number_format($plan->amount / 100, 2);
				} catch (Exception $e) {
					$msg = __("Error %s");
					return sprintf($msg, $e->getMessage());
				}
			} else {
				//override by shorcode attribute
				if ( $amount || $currency ) $btn_hash = true; //lets check if this need hash
				$amt 	  = $amount ? $amount : $amt ;
				$currency = $currency ? $currency : $cur ;
				if ( $btn_hash ) $btn_hash = "{$stripeapikey}-{$amt}-{$currency}";
				$coupon = false; //disable coupon for one time payments.
			}

			$ppp_level  = $WishListMemberInstance->IsPPPLevel($sku);
			$level_name = $wpm_levels[$sku]['name'];

			if($ppp_level) {
				$level_name = $ppp_level->post_title;
			}

			$heading         = empty($stripesettings['formheading']) ? "Register to %level" : $stripesettings['formheading'];
			$heading         = str_replace('%level', $level_name, $heading);

			$btn_label = empty($stripesettings['buttonlabel']) ? "Join %level" : $stripesettings['buttonlabel'];
			$btn_label = str_replace('%level', $level_name, $btn_label);

			$panel_btn_label = empty($stripesettings['panelbuttonlabel']) ? "Pay" : $stripesettings['panelbuttonlabel'];
			$panel_btn_label = str_replace('%level', $level_name, $panel_btn_label);
			$logo            = $stripesettings['logo'];
			$logo            = str_replace('%level', $level_name, $stripesettings['logo']);
			$content         = trim($content);
			ob_start();
			?>

			<?php if (empty($content)) : ?>
				<button class="regform-button go-regform" name="go_regform" value="<?php echo $amt ?>" style="width: auto" id="go-regform-<?php echo $sku ?>" class="" href="#regform-<?php echo $sku ?>"><?php echo $btn_label ?></button>
			<?php else: ?>
				<a id="go-regform-<?php echo $sku ?>" class="go-regform" href="#regform-<?php echo $sku ?>"><?php echo $content ?></a>
			<?php endif; ?>

			<?php $btn = ob_get_clean(); ?>

			<?php
			$additional_class = 'regform-stripe';
			if(!$coupon) {
				$additional_class .= ' nocoupon';
			}
			if(!is_user_logged_in()){
				$path              = sprintf($WishListMemberInstance->pluginDir .'/extlib/wlm_stripe/form_new_fields.php');
				include $path;
				$this->forms[$sku] = wlm_build_payment_form($data, $additional_class);
			} else {
				$stripe_cust_id    = $WishListMemberInstance->Get_UserMeta($current_user->ID, 'stripe_cust_id');
				$path              = sprintf($WishListMemberInstance->pluginDir .'/extlib/wlm_stripe/form_existing_fields.php');
				include $path;
				$this->forms[$sku] = wlm_build_payment_form($data, $additional_class);
			}
			return $btn;
		}

	}

}

$sc = new WLM_Stripe_ShortCodes();

?>
