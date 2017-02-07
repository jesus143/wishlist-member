<?php
$fields = array(
	'nonce' => array(
		'type'  => 'hidden',
		'name'  => 'nonce',
		'label' => '',
		'value' => wp_create_nonce('stripe-do-charge'),
		'class' => ''
	),
	'stripe_action' => array(
		'type'  => 'hidden',
		'name'  => 'stripe_action',
		'label' => '',
		'value' => 'charge',
		'class' => ''
	),
	'charge_type' => array(
		'type'  => 'hidden',
		'name'  => 'charge_type',
		'label' => '',
		'value' => 'existing',
		'class' => '',
	),
	'subscription' => array(
		'type'  => 'hidden',
		'name'  => 'subscription',
		'label' => '',
		'value' => $settings['subscription'],
		'class' => ''
	),
	'redirect_to' => array(
		'type'  => 'hidden',
		'name'  => 'redirect_to',
		'label' => '',
		'value' => get_permalink(),
		'class' => ''
	),
	'sku' => array(
		'type'  => 'hidden',
		'name'  => 'sku',
		'label' => '',
		'value' => $sku,
		'class' => ''
	),
	//name fields
	'first_name' => array(
		'type'        => 'text',
		'name'        => 'first_name',
		'label'       => __('First Name', 'wishlist-member'),
		'placeholder' => __('First Name', 'wishlist-member'),
		'value'       => $current_user->first_name,
	),
	'last_name' => array(
		'type'        => 'text',
		'name'        => 'last_name',
		'label'       => __('Last Name', 'wishlist-member'),
		'placeholder' => __('Last Name', 'wishlist-member'),
		'value'       => $current_user->last_name,
	),
	'email' => array(
		'type'        => 'text',
		'name'        => 'email',
		'label'       => __('Email', 'wishlist-member'),
		'placeholder' =>  __('Email', 'wishlist-member'),
		'value'       => $current_user->user_email,
	),
	'coupon' => array(
		'type'        => $coupon ? 'text' : 'none',
		'name'        => 'coupon',
		'label'       =>  __('Coupon Code', 'wishlist-member'),
		'placeholder' => __('Coupon Code', 'wishlist-member'),
		'class'       => 'stripe-coupon',
		'value'       => "",
	),
	//card fields
	'cc_number' => array(
		'type'        => 'card',
		'name'        => 'cc_number',
		'label'       => __('Card Number:', "wishlist-member"),
		'placeholder' => "●●●● ●●●● ●●●● ●●●●",
		'value'       => "",
	),
	'cc_expmonth' => array(
		'type'        => 'card',
		'name'        => 'cc_expmonth',
		'label'       => __('Expires:', "wishlist-member"),
		'placeholder' => "",
		'value'       => "",
	),
	'cc_expyear' => array(
		'type'        => 'card',
		'name'        => 'cc_expyear',
		'label'       => __('Expires:', "wishlist-member"),
		'placeholder' => "",
		'value'       => "",
	),
	'cc_cvc' => array(
		'type'        => 'card',
		'name'        => 'cc_cvc',
		'label'       => __('Code:', "wishlist-member"),
		'placeholder' => "",
		'value'       => "",
	)
);

//if amount or currency was overriden, lets put a hash
//this will insure that amount is not rigged
if ( $btn_hash ) {
	$fields['btn_hash'] = array(
		'type'  => 'hidden',
		'name'  => 'btn_hash',
		'label' => '',
		'value' => wp_create_nonce( $btn_hash ),
		'class' => ''
	);
	$fields['custom_amount'] = array(
		'type'  => 'hidden',
		'name'  => 'custom_amount',
		'label' => '',
		'value' => $amt,
		'class' => ''
	);
	$fields['custom_currency'] = array(
		'type'  => 'hidden',
		'name'  => 'custom_currency',
		'label' => '',
		'value' => $currency,
		'class' => ''
	);
}

if(!empty($stripe_cust_id)) {
	unset($fields['first_name']);
	unset($fields['last_name']);
	unset($fields['email']);
	unset($fields['cc_number']);
	unset($fields['cc_expmonth']);
	unset($fields['cc_expyear']);
	unset($fields['cc_cvc']);

}

$data['fields']             = $fields;
$data['heading']            = $heading;
$data['panel_button_label'] = $panel_btn_label ." " . $currency ." ". $amt;
$data['form_action']        = $stripethankyou_url;
$data['id']                 = $sku;
$data['logo']               = $logo;
$data['showlogin']          = (bool) $showlogin;
?>
<!--
<?php if (isset($_GET['status']) && $_GET['status'] == 'fail') echo sprintf(__("<br/>If you continue to have trouble registering, please contact <em><a style='color: red' href='mailto:%s'>%s</a></em>"), $stripesettings['supportemail'], $stripesettings['supportemail']) ?>
-->