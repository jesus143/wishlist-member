<?php

ini_set('display_errors', 0);
include dirname(__FILE__).'/../EwayWebserviceClient.php';
include dirname(__FILE__).'/../Eway24WebserviceClient.php';

$svc = new Eway24WebserviceClient(87654321, 'test@eway.com.au', 'test123', true);

$params = array(
    'ewayCustomerInvoiceRef' => '138433888562',
);
echo "<pre>";
$res = $svc->call("Transaction24HourReportByInvoiceReference", $params);
var_dump($res);
echo "</pre>";



