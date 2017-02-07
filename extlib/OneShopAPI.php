<?php

# OneShopAPI: PHP wrapper class for MCSSL.com API
if(!class_exists('OneShopAPI') ) {
	class OneShopAPI {
		var $_merchantId = "";
		var $_merchantKey = "";
		var $_apiUri = "";
		var $_apiParameters;

		function OneShopAPI($merchantId, $merchantKey, $apiUri) {
			$this->_merchantId = $merchantId;
			$this->_merchantKey = $merchantKey;
			$this->_apiUri = $apiUri;
		}
		# This method is used to add parameters to the
		# $_apiParameters array. This array is used by the
		# CreateRequestString() method.
		function AddApiParameter($parameterKey, $parameterValue) {
			# If $parameterKey exists, NULL it out and set the new value
			if (@array_key_exists($parameterKey, $this->_apiParameters)) {
				$this->_apiParameters[$parameterKey] = NULL;
			}
			$this->_apiParameters[$parameterKey] = $parameterValue;
		}


		# This method clears the $_apiParameters array
		# so it can be reused.
		function ClearApiParameters() {
			$this->_apiParameters = NULL;
		}

		# This method uses the curl object to make
		# a POST request to the api and return the response
		# from the API
		function SendHttpRequest($uri, $request_body) {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $uri);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $request_body);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('X-POST_DATA_FORMAT: xml'));
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); # TODO - SET THIS TO true FOR PRODUCTION
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			$data = curl_exec($ch);
			curl_exec($ch);
			$err = curl_error($ch);
			curl_close($ch);
			if ($err)
				return $err;
			return $data;
		}

		# This method will call the SendHttpRequest method
		# after appending the proper information to the uri
		# and creating the request body
		function ApiRequest($path, $parameters = "") {
			$uri = $this->_apiUri."/API/".$this->_merchantId.$path;
			$request_body = $this->CreateRequestString();
			$result = $this->SendHttpRequest($uri, $request_body);

			return($result);
		}

		# This method will take a properly formatted api uri
		# and create the response body then call the http request method
		function XLinkApiRequest($xlink, $parameters = "") {
			$request_body = $this->CreateRequestString();
			$result = $this->SendHttpRequest($xlink, $request_body);
			return($result);
		}

		function CreateRequestString() {
			$request_body = "<Request><Key>".$this->_merchantKey."</Key>".$this->ParseApiParameters($this->_apiParameters)."</Request>";
			return $request_body;
		}

		function ParseApiParameters($parameters) {
			$request_payload = "";
			if ((!empty($parameters)) && (is_array($parameters))) {
				foreach($parameters as $key => $value) {
					if (!is_array($value)) {
						$request_payload .= ("<".$key.">".$value."</".$key.">\r\n");
					}
					else {
						$request_payload .= "<".$key.">\r\n";
						$request_payload .= $this->create_request($value);
						$request_payload .= "</".$key.">\r\n";
					}
				}
			}
			return $request_payload;
		}

		function GetOrdersList() {
			return($this->ApiRequest("/ORDERS/LIST"));
		}

		function GetOrderById($orderId) {
			return($this->ApiRequest("/ORDERS/" . $orderId . "/READ"));
		}

		function GetRecurringOrderById($orderId) {
			return($this->ApiRequest("/RECURRINGORDERS/" . $orderId . "/READ"));
		}

		function GetProductsList() {
			return($this->ApiRequest("/PRODUCTS/LIST"));
		}

		function GetProductById($productId) {
			return($this->ApiRequest("/PRODUCTS/" . $productId . "/READ"));
		}

		function GetClientsList() {
			return($this->ApiRequest("/CLIENTS/LIST"));
		}

		function GetClientById($clientId) {
			return($this->ApiRequest("/CLIENTS/". $clientId ."/READ"));
		}

		function GetErrorsList() {
			return($this->ApiRequest("/ERRORS/LIST"));
		}

		function GetAvailableApiMethods() {
			return($this->ApiRequest(""));
		}
	}
}
?>