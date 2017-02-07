<?php
class EwayRecurWebserviceClient extends EwayWebServiceClient {
	protected $gateway;
	protected $eway_customer_id;
	protected $eway_username;
	protected $eway_password;

	protected $sandbox = false;


	public function __construct($eway_customer_id, $eway_username, $eway_password, $sandbox) {
		$this->gateway = $gateway;
		$this->eway_customer_id = $eway_customer_id;
		$this->eway_username = $eway_username;
		$this->eway_password = $eway_password;
		$this->sandbox = $sandbox;


		$eway_wl_test_gateway = 'https://www.eway.com.au/gateway/rebill/test/manageRebill_test.asmx?WSDL';
		$eway_ws_live_gateway = 'https://www.eway.com.au/gateway/rebill/manageRebill.asmx?WSDL';


		$this->gateway = $eway_ws_live_gateway;
		if($sandbox) {
			$this->gateway = $eway_wl_test_gateway;
		}
	}
	/**
	 * Executes a request
	 * @param type $method
	 * @param type $params k,v pairs where v's are raw(not url encoded)
	 */
	public function call($method, $params = array()) {
		$params = is_array($params)? $params : array();
		$methods = array(
			'CreateRebillCustomer' => array(
				'customerTitle'     => '',
				'customerFirstName' => '',
				'customerLastName'  => '',
				'customerAddress'   => '',
				'customerSuburb'    => '',
				'customerState'     => '',
				'customerCompany'   => '',
				'customerPostCode'  => '',
				'customerCountry'   => '',
				'customerEmail'     => '',
				'customerFax'       => '',
				'customerPhone1'    => '',
				'customerPhone2'    => '',
				'customerRef'       => '',
				'customerJobDesc'   => '',
				'customerComments'  => '',
				'customerURL'       => ''
			),
			'CreateRebillEvent' => array(
				'RebillCustomerID'   => '',
				'RebillInvRef'       => '',
				'RebillInvDes'       => '',
				'RebillCCName'       => '',
				'RebillCCNumber'     => '',
				'RebillCCExpMonth'   => '',
				'RebillCCExpYear'    => '',
				'RebillInitAmt'      => '',
				'RebillInitDate'     => '',
				'RebillRecurAmt'     => '',
				'RebillStartDate'    => '',
				'RebillInterval'     => '',
				'RebillIntervalType' => '',
				'RebillEndDate'      => ''
			),
			'QueryTransactions' => array(
				'RebillCustomerID' => '',
				'RebillID'         => '',
			),
			'DeleteRebillEvent' => array(
				'RebillCustomerID' => '',
				'RebillID'         => '',
			),
			'DeleteRebillCustomer' => array(
				'RebillCustomerID' => '',
			),
			'Transaction24HourReportByInvoiceReference' => array(
				'ewayCustomerInvoiceRef' => ''
			)
		);

		$req = new nusoap_client($this->gateway, TRUE);
		$headers = <<<head
		<eWAYHeader xmlns="http://www.eway.com.au/gateway/rebill/manageRebill">
			<eWAYCustomerID>$this->eway_customer_id</eWAYCustomerID>
				<Username>$this->eway_username</Username>
				<Password>$this->eway_password</Password>
		</eWAYHeader>
head;
		$req->setHeaders($headers);

		if(!isset($methods[$method])) {
			throw new Exception("This method is not yet implemented");
		}

		$params = array_merge($methods[$method], $params);
		$tmp = "";
		foreach($params as $k => $v) {
			$tmp .= sprintf('<%s>%s</%s>', $k, $v, $k);
		}

		$body = <<<body
		<$method xmlns="http://www.eway.com.au/gateway/rebill/manageRebill">
		$tmp
		</$method>


body;
		$result = $req->call($method, $body);
		return $result;
	}
}