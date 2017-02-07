<?php

class WLMAuthorizeNetARB {
  private $wlm;
  private $formsettings;
  private $apisettings;
  public $subscriptions;
  private $arb;

  public function __construct() {
    global $WishListMemberInstance;
    if ( empty( $WishListMemberInstance ) ) return;

    $default_form_settings = array(
      "supportemail"         => "",
      "logo"                 => "",
      "formheading"          => "Register to %level",
      "formheadingrecur"     => "Subscribe to %level",
      "formbuttonlabel"      => "Pay %amount",
      "formbuttonlabelrecur" => "Pay %amount",
      "beforetext"           => "",
      "beforetextrecur"      => "",
      "aftertext"            => "",
      "aftertextrecur"       => "",
    );

    $this->wlm           = $WishListMemberInstance;

    //subscriptions
    $this->subscriptions = $this->wlm->GetOption('anetarbsubscriptions');
    $this->subscriptions = $this->subscriptions ? $this->subscriptions : array();

    //api settings
    $this->apisettings   = $this->wlm->GetOption('anetarbsettings');
    $this->apisettings   = $this->apisettings ? $this->apisettings : array();

    //settings of the payment form
    $this->formsettings  = $this->wlm->GetOption('authnet_arb_formsettings');
    $this->formsettings = is_array( $this->formsettings ) && count( $this->formsettings ) > 0 ? $this->formsettings : $default_form_settings;
  }

  public function init(){
    if ( empty( $this->wlm ) ) return;

    add_action('init', array($this, 'wp_init'));
    add_action('admin_init', array($this, 'include_underscorejs'));
    add_shortcode('wlm_authorizenet_arb_btn', array($this, 'anet_arb_btn'));
    add_action('wp_footer', array( $this, 'footer'), 100 );

    add_action('wp_ajax_wlm_anetarb_new-subscription', array($this, 'new_subscription'));
    add_action('wp_ajax_wlm_anetarb_all-subscriptions', array($this, 'get_all_subscriptions'));
    add_action('wp_ajax_wlm_anetarb_save-subscription', array($this, 'save_subscription'));
    add_action('wp_ajax_wlm_anetarb_delete-subscription', array($this, 'delete_subscription'));

    add_action('wishlistmember_arb_sync', array( $this, 'syn_arb') );
    //TinyMCE Editor Buttons
    $this->anet_arb_shortcode_btns();
  }

  public function wp_init() {
    $login   = isset( $this->apisettings['api_login_id'] ) ? $this->apisettings['api_login_id'] : "";
    $key     = isset( $this->apisettings['api_transaction_key'] ) ? $this->apisettings['api_transaction_key'] : "";
    if ( $login && $key ) { //run CRON only if API SETTINGS are set.
      //cron for syncing arb
      $next_schedule = wp_next_scheduled( "wishlistmember_arb_sync" );
      if ( ! $next_schedule ) {
        wp_schedule_event( time(), 'twicedaily', "wishlistmember_arb_sync" );
        //this will be cleared on WLM cron clearing
      } else {
        if ( $next_schedule <= time() ){
          spawn_cron( time() );
        }
      }
    }
  }

  public function load_js() {
    static $loaded = false;
    if ( ! $loaded ) {
      global $WishListMemberInstance;
      wp_enqueue_script('jquery-fancybox', $WishListMemberInstance->pluginURL.'/js/jquery.fancybox.pack.js', array('jquery'), $WishListMemberInstance->Version, true);
      wp_enqueue_style('jquery-fancybox', $WishListMemberInstance->pluginURL.'/css/jquery.fancybox.css', array(), $WishListMemberInstance->Version);
      wp_enqueue_script('wlm-popup-regform-card-validation', 'https://js.stripe.com/v2/', array('jquery'), $WishListMemberInstance->Version, true);
      wp_enqueue_script('wlm-popup-regform', $WishListMemberInstance->pluginURL.'/js/wlm.popup-regform.js', array(), $WishListMemberInstance->Version, true);
      wp_enqueue_style('wlm-popup-regform-style', $WishListMemberInstance->pluginURL.'/css/wlm.popup-regform.css', array(), $WishListMemberInstance->Version);
      $loaded = true;
    }
  }

  public function include_underscorejs() {
    global $WishListMemberInstance;
    if(is_admin() && isset($_GET['page']) && $_GET['page'] == $this->wlm->MenuID && isset($_GET['wl']) && $_GET['wl'] == 'integration') {
      wp_enqueue_script('underscore-wlm', $WishListMemberInstance->pluginURL . '/js/underscore-1.6.min.js', array('underscore'), $WishListMemberInstance->Version);
    }
  }

  public function get_all_subscriptions() {
    echo json_encode( $this->subscriptions );
    die();
  }

  public function new_subscription() {
    $subscriptions = $this->subscriptions;
    if(empty($subscriptions)) {
      $subscriptions = array();
    }

    //create an id for this button
    $id = strtoupper(substr(sha1( microtime()), 1, 10));

    $subscription = array(
      'id'            => $id,
      'name'          => $_POST['name'],
      'currency'      => 'USD',
      'amount'        => 10,
      'recurring'     => 0,
      'sku'           => $_POST['sku'],
    );

    $this->subscriptions[$id] = $subscription;
    $this->wlm->SaveOption('anetarbsubscriptions', $this->subscriptions);

    echo json_encode($subscription);
    die();
  }

  public function save_subscription() {
    $id = $_POST['id'];
    $subscription = $_POST;
    if ( isset( $subscription['recurring'] ) && $subscription['recurring'] == 1 ) {
      if ( isset( $subscription['recur_billing_cycle'] ) && $subscription['recur_billing_cycle'] == 0 ) {
        $subscription['recur_billing_cycle'] = "";
      }
      if ( isset( $subscription['trial_billing_cycle'] ) && $subscription['trial_billing_cycle'] == 0 ) {
        $subscription['trial_billing_cycle'] = "";
        if ( isset( $subscription['trial_amount'] ) ) $subscription['trial_amount'] = "";
      }

      if ( isset( $subscription['trial_amount'] ) && $subscription['trial_amount'] == 0 ) {
        $subscription['trial_amount'] = 0;
      }
    }
    $this->subscriptions[$id] = $subscription;
    $this->wlm->SaveOption('anetarbsubscriptions', $this->subscriptions);
    echo json_encode($this->subscriptions[$id]);
    die();
  }

  public function delete_subscription() {
    $id = $_POST['id'];
    unset($this->subscriptions[$id]);
    $this->wlm->SaveOption('anetarbsubscriptions', $this->subscriptions);
    die();
  }

  public function anet_arb_btn($atts, $content) {
    $atts           = extract( shortcode_atts( array( 'sku'=> null ), $atts ) );
    $button_id      = $sku ? $sku : false;
    if ( ! $button_id ) return ""; //if button id is not present in the shortcode

    $subscriptions      = $this->subscriptions;

    $subscription       = isset( $subscriptions[ $button_id ] ) ? $subscriptions[ $button_id ] : false;
    if ( ! $subscription ) return ""; //if id is not valid

    //the real sku
    $sku = isset( $subscription['sku'] ) ? $subscription['sku'] : false;
    if ( ! $sku ) return ""; //invalid level

    global $current_user;

    //load the js files
    $this->load_js();

    $wpm_levels          = $this->wlm->GetOption('wpm_levels');
    $formsettings        = $this->formsettings;
    $formsettings        = is_array( $formsettings ) ? $formsettings : array();

    $anetarbthankyou     = $this->wlm->GetOption('anetarbthankyou');
    $wpm_scregister      = get_bloginfo('url') . '/index.php/register/';
    $anetarbthankyou_url = $wpm_scregister . $anetarbthankyou;

    $frequency        = isset( $subscription['recur_billing_frequency'] ) ? (int) $subscription['recur_billing_frequency'] : "";
    $period           = isset( $subscription['recur_billing_period'] ) ? $subscription['recur_billing_period'] : "";
    $recur_cycle      = isset( $subscription['recur_billing_cycle'] ) ? (int) $subscription['recur_billing_cycle'] : 0;
    $trial_cycle      = isset( $subscription['trial_billing_cycle'] ) ? (int) $subscription['trial_billing_cycle'] : 0;
    $trial_amount     = isset( $subscription['trial_amount'] ) ? (float)$subscription['trial_amount'] : 0;
    $total_cycle      = $recur_cycle  + $trial_cycle;

    $amount              = $subscription['recurring'] ? (float) $subscription['recur_amount'] : (float) $subscription['amount'];
    $currency            = $subscription['currency'] ? $subscription['currency'] : "";
    $level_name          = $wpm_levels[$sku]['name'];

    $supportemail        = isset( $formsettings['supportemail'] ) && !empty( $formsettings['supportemail'] ) ? $formsettings['supportemail'] : "";
    $logo                = isset( $formsettings['logo'] ) && !empty( $formsettings['logo'] ) ? $formsettings['logo'] : false;
    $display_address        = isset( $formsettings['display_address'] ) && !empty( $formsettings['display_address'] ) ? true : false;

    if ( $subscription['recurring'] ) {
      $formheading       = isset( $formsettings['formheadingrecur'] ) && !empty( $formsettings['formheadingrecur'] ) ? $formsettings['formheadingrecur'] : false;
      $formbuttonlabel   = isset( $formsettings['formbuttonlabelrecur'] ) && !empty( $formsettings['formbuttonlabelrecur'] ) ? $formsettings['formbuttonlabelrecur'] : false;
      $beforetext        = isset( $formsettings['beforetextrecur'] ) && !empty( $formsettings['beforetextrecur'] ) ? $formsettings['beforetextrecur'] : false;
      $aftertext         = isset( $formsettings['aftertextrecur'] ) && !empty( $formsettings['aftertextrecur'] ) ? $formsettings['aftertextrecur'] : false;
    } else {
      $formheading       = isset( $formsettings['formheading'] ) && !empty( $formsettings['formheading'] ) ? $formsettings['formheading'] : false;
      $formbuttonlabel   = isset( $formsettings['formbuttonlabel'] ) && !empty( $formsettings['formbuttonlabel'] ) ? $formsettings['formbuttonlabel'] : false;
      $beforetext        = isset( $formsettings['beforetext'] ) && !empty( $formsettings['beforetext'] ) ? $formsettings['beforetext'] : false;
      $aftertext         = isset( $formsettings['aftertext'] ) && !empty( $formsettings['aftertext'] ) ? $formsettings['aftertext'] : false;
    }

    $card_types = array(
      'Visa'       => 'Visa',
      'MasterCard' => 'MasterCard',
      'Discover'   => 'Discover',
      'Amex'       => 'American Express',
      'Diners Club'=> 'Diners Club',
      'JCB'        => 'JCB',
    );
    $formsettings['credit_cards'] = isset( $formsettings['credit_cards'] ) ? $formsettings['credit_cards'] : array("Visa","MasterCard","Discover","Amex");
    $formsettings['credit_cards'] = count( $formsettings['credit_cards'] ) > 0 ? $formsettings['credit_cards'] : array("Visa","MasterCard","Discover","Amex");
    foreach ( $card_types as $key => $value ) {
      if ( ! in_array( $key, $formsettings['credit_cards'] ) ) {
         unset( $card_types[$key] );
      }
    }

    //prepare codes value
    $codes = array(
      "level"        => $level_name,
      "amount"       => $amount,
      "frequency"    => $frequency ? $frequency : 0,
      "period"       => $period,
      "cycle"        => $recur_cycle ? $recur_cycle : "Unlimited",
      "trial_cycle"  => $trial_cycle ? $trial_cycle : "",
      "trial_amount" => $trial_cycle ? ($trial_amount ? $trial_amount : 0) : "",
      "total_cycle"  => $recur_cycle ? $total_cycle : "Unlimited",
      "currency"     => $currency,
      "supportemail" => $supportemail,
    );

    //prepare form data
    include $this->wlm->pluginDir .'/extlib/wlm_authorizenet_arb/form_new_field.php';

    $this->forms[$button_id] = wlm_build_payment_form( $data );

    return sprintf('<a id="go-regform-%s" class="go-regform" href="#regform-%s">%s</a>', $button_id, $button_id, $content );
  }

  public function process_form_codes( $str, $codes = array() ) {
      foreach( $codes as $code => $value ) {
        $str = str_replace("%{$code}", $value, $str );
      }
      return $str;
  }

  public function footer() {
    $js = "";
    foreach((array) $this->forms as $sku => $f ) {
      $js .= " $('#regform-{$sku} .regform-form').PopupRegForm();\n";
      echo $f;
    }
    $js = "\n jQuery(function($) { \n{$js} });\n";
    $js = "\n<script type='text/javascript'> {$js} </script>\n";
    echo $js;
  }

  public function anet_arb_shortcode_btns(){
    global $pagenow;
    if(in_array($pagenow, array('post.php', 'post-new.php'))) {
      global $WLMTinyMCEPluginInstanceOnly;
      global $WishListMemberInstance;

      $subscriptions  = $this->subscriptions;
      $wlm_shortcodes = array();

      foreach((array) $subscriptions as $id => $p) {
        if ( $p['recurring'] ) {
          $buy_now_str    = __("Subscribe Now", "wishlist-member");
          $title = "{$p['name']} (" .strtoupper($p['currency'])  .$p['init_amount'] ." & " .$p['recur_amount'] ."/" .$p['recur_billing_frequency'] .strtoupper( substr( $p['recur_billing_period'], 0, 1 ) ) .")";
          $code  = array('title' => $title, 'value' => sprintf("[wlm_authorizenet_arb_btn sku=%s]%s[/wlm_authorizenet_arb_btn]", $id, $buy_now_str));
          array_unshift( $wlm_shortcodes, $code );
        } else {
          $buy_now_str    = __("Buy Now", "wishlist-member");
          $title = "{$p['name']} (" .strtoupper($p['currency'])  .$p['amount'] .")";
          $code = array('title' => $title , 'value' => sprintf("[wlm_authorizenet_arb_btn sku=%s]%s[/wlm_authorizenet_arb_btn]", $id, $buy_now_str));
          array_push( $wlm_shortcodes, $code );
        }
      }

      if ( $wlm_shortcodes ) {
	      $WishListMemberInstance->IntegrationShortcodes['Authorize.Net (ARB) Integration'] = $wlm_shortcodes;
      }
    }
  }

  public function syn_arb(){
    //allow sync at least every 1 hour only
    $logs = $this->wlm->GetOption('auhtnetarb_sync_log');
    if ( $logs && is_array( $logs ) ) {
      $previous = isset( $logs['start'] ) ? $logs['start'] : "";
      $previous = strtotime( $previous );
      $now      = time();
      $diff     = $now - $previous;
      $day      = 60 * 60;
      if ( $diff < $day  ) {
        $msg = "Cannot sync now. " .($day - $diff) ." second/s left";
        return array( "end" => date("Y-m-d H:i:s"), "message" => $msg, "count" => 0 );
      }
    }

    wlm_set_time_limit(0); //override max execution time
    if ( ! class_exists( 'AuthnetARB' ) ) {
      include $this->wlm->pluginDir .'/extlib/wlm_authorizenet_arb/authnet_arb.php';
    }
    $counter = 0;
    $message = "";
    $login   = isset( $this->apisettings['api_login_id'] ) ? $this->apisettings['api_login_id'] : "";
    $key     = isset( $this->apisettings['api_transaction_key'] ) ? $this->apisettings['api_transaction_key'] : "";
    $sandbox = isset( $this->apisettings['sandbox_mode'] ) ? $this->apisettings['sandbox_mode'] : 0;
    $sandbox = $sandbox ? true : false;

    $txnids  = $this->wlm->GetOption('auhtnetarb_transaction_ids');

    if ( empty( $txnids ) || ! $txnids || ! is_array( $txnids ) ) {
      $txnids = $this->get_arb_txnids();
      $this->wlm->SaveOption('auhtnetarb_transaction_ids', $txnids );
    }

    //initial the log since it was called
    $sync_start = date("Y-m-d H:i:s");
    $log = array( "count"=> 0, "message" => "ARB Sync started.", "start"=> $sync_start, "end" => "" );
    $this->wlm->SaveOption('auhtnetarb_sync_log', $log );

    try {
      $this->arb = new AuthnetARB( $login, $key, $sandbox );
      foreach ( $txnids as $key=>$txnid ) {
        list( $market, $subid ) = explode( '-', $txnid, 2 );

        $stat = $this->get_subscription_status( $subid );

        $_POST['sctxnid'] = "arb-" . $subid;
        switch ( $stat ) {
          case 'active':
          case 'expired':
            $this->wlm->ShoppingCartReactivate();
            break;
          case 'suspended':
          case 'canceled':
          case 'terminated':
            $this->wlm->ShoppingCartDeactivate();
            break;
          default:
            # we do nothing, it might be an error when doing api call
            break;
        }
        unset($txnids[$key]);
        $this->wlm->SaveOption('auhtnetarb_transaction_ids', $txnids );
        $counter++;
      }
      $message = "Synced successfully.";
    } catch (Exception $e) {
      $message = $e->getMessage();
    }

    //update the log
    $log = array( "count"=> $counter, "message" => $message, "start"=> $sync_start, "end" => date("Y-m-d H:i:s") );
    $this->wlm->SaveOption('auhtnetarb_sync_log', $log );

    return $log;
  }

  private function get_subscription_status( $subid ) {
      $status = false;
      if ( ! $this->arb ) return false;
      try {
        $this->arb->do_apicall("ARBGetSubscriptionStatusRequest", array( "subscriptionId" =>$subid ) );
        if ( $this->arb->isSuccessful() ) {
          $status = strtolower( $this->arb->getStatus() );
        }
      }catch (Exception $e) {
        $status = 'invalid';
      }
      return $status;
  }

  private function get_arb_txnids() {
    global $wpdb;
    $qwhere = "WHERE uo.`option_value` LIKE 'arb-%'";
    $qjoin = "LEFT JOIN `{$this->wlm->Tables->userlevels}` AS ul ON uo.`userlevel_id` = ul.`ID`";
    $query = "SELECT uo.`option_value` as option_value  FROM `{$this->wlm->Tables->userlevel_options}` AS uo {$qjoin} {$qwhere}";
    return $wpdb->get_col( $query );
  }
}

$wlm_aurthorizenet_arb_init = new WLMAuthorizeNetARB();
$wlm_aurthorizenet_arb_init->init();
