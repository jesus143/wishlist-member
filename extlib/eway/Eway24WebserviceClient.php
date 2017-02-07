<?php
class Eway24WebserviceClient extends EwayWebServiceClient {
    protected $gateway;
    protected $eway_customer_id;
    protected $eway_username;
    protected $eway_password;
    public function __construct($eway_customer_id, $eway_username,
            $eway_password, $sandbox) {
        $this->gateway = $gateway;
        $this->eway_customer_id = $eway_customer_id;
        $this->eway_username = $eway_username;
        $this->eway_password = $eway_password;

        // the 24 web service gateways
    	$eway_24_test_gateway = 'https://www.eway.com.au/gateway/services/Test/TransactionReportService.asmx?WSDL';
    	$eway_24_live_gateway = 'https://www.eway.com.au/gateway/services/TransactionReportService.asmx?WSDL';
    	$this->gateway = $eway_24_live_gateway;
    	if($sandbox) {
    		$this->gateway = $eway_24_test_gateway;
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
            'Transaction24HourReportByInvoiceReference' => array(
                'ewayCustomerInvoiceRef' => ''
            )
        );

        $req = new nusoap_client($this->gateway, TRUE);
        $headers = <<<head
        <eWAYHeader xmlns="https://www.eway.com.au/gateway/services/TransactionReportService.asmx/">
            <eWAYCustomerID>$this->eway_customer_id</eWAYCustomerID>
            <UserName>$this->eway_username</UserName>
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
        <$method  xmlns="https://www.eway.com.au/gateway/services/TransactionReportService.asmx/">
        $tmp
        </$method>

body;
        $result = $req->call($method, $body);
        //echo '<h2>Request</h2><pre>' . htmlspecialchars($req->request, ENT_QUOTES) . '</pre>';
        //echo '<h2>Response</h2><pre>' . htmlspecialchars($req->response, ENT_QUOTES) . '</pre>';
        return $result;
    }
}