<?php

ini_set('display_errors', 0);
include dirname(__FILE__).'/../EwayWebserviceClient.php';
include dirname(__FILE__).'/../EwayRecurWebserviceClient.php';

$svc = new EwayRecurWebserviceClient(87654321, 'test@eway.com.au', 'test123', true);
//var

// $params = array(
//     'customerTitle' => '',
//     'customerFirstName' => 'Erwin',
//     'customerLastName' => 'Atuli',
//     'customerAddress' => '',
//     'customerSuburb' => '',
//     'customerState' => '',
//     'customerCompany' => '',
//     'customerPostCode' => '',
//     'customerCountry' => '',
//     'customerEmail' => '',
//     'customerFax' => '',
//     'customerPhone1' => '',
//     'customerPhone2' => '',
//     'customerRef' => '',
//     'customerJobDesc' => '',
//     'customerComments' => '',
//     'customerURL' => '',
// );
// echo "<pre>";
// $res = $svc->call("CreateRebillCustomer", $params);
// var_dump($res);
// echo "</pre>";

$params = array(
	'RebillCustomerID' => '60092306',
	'RebillID'         => '70101756',
);


$res = $svc->call("QueryTransactions", $params);
$rebills = $res['QueryTransactionsResult']['rebillTransaction'];

$last_trans = $rebills[0];
foreach($rebills as $r) {
	if($r['Status'] == 'Future') {
		break;
	}
	$last_trans = $r;
}

if($last_trans['Status'] == 'Failed') {
	//deactivate
}