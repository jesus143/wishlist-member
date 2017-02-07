<?php

class AuthnetARBException extends Exception {}

class AuthnetARB {

	private $login = "";
	private $trans_key = "";
	private $test    = true;

	private $params  = array();
	private $sucess  = false;
	private $error   = true;

	private $xml;
	private $response;
	private $resultCode;
	private $code;
	private $text;
	private $subscrId;
	private $refId;

	public function __construct( $login, $key, $test = false ) {

		$this->login     = trim( $login );
		$this->trans_key = trim( $key );
		if ( ! $this->login || ! $this->trans_key ) {
			throw new AuthnetARBException("You have not configured your Authnet login credentials.");
		}

		$this->test = $test;
		$subdomain = $this->test ? 'apitest' : 'api';
		$this->url = "https://" . $subdomain . ".authorize.net/xml/v1/request.api";
	}

	public function do_apicall( $method, $data ) {
		$this->params['merchantAuthentication'] = array(
			"name" 			 => $this->login,
			"transactionKey" => $this->trans_key,
		);
		$this->params = array_merge( $this->params, $data );
		$xml_data   =  $this->xmlinize( $this->params );

		$this->xml  = "<?xml version='1.0' encoding='utf-8'?>";
		$this->xml .= "<{$method} xmlns='AnetApi/xml/v1/schema/AnetApiSchema.xsd'>";
		$this->xml .= $xml_data;
		$this->xml .= "</{$method}>";
		$this->process();
	}

	public function setParameter($field = "", $value = null) {
		$field = (is_string($field)) ? trim($field) : $field;
		$value = (is_string($value)) ? trim($value) : $value;
		if (!is_string($field))
		{
			throw new AuthnetARBException("setParameter() arg 1 must be a string or integer: " . gettype($field) . " given.");
		}
		if (!is_string($value) && !is_numeric($value) && !is_bool($value) && !is_array($value) )
		{
			throw new AuthnetARBException("setParameter() arg 2 must be a string, integer, boolean or array value: " . gettype($value) . " given.");
		}
		if (empty($field))
		{
			throw new AuthnetARBException("setParameter() requires a parameter field to be named.");
		}
		if ($value === "")
		{
			throw new AuthnetARBException("setParameter() requires a parameter value to be assigned: $field");
		}
		$this->params[$field] = $value;
	}

	public function isSuccessful() {
		return $this->success;
	}

	public function isError() {
		return $this->error;
	}

	public function getResponse() {
		return strip_tags($this->text);
	}

	public function getResponseCode() {
		return $this->code;
	}

	public function getStatus() {
		return $this->substatus;
	}

	public function getSubscriberID() {
		return $this->subscrId;
	}

	public function __toString() {
		if (!$this->params)
		{
			return (string) $this;
		}

		$output  = "";
		$output .= '<table summary="Authnet Results" id="authnet">' . "\n";
		$output .= '<tr>' . "\n\t\t" . '<th colspan="2"><b>Outgoing Parameters</b></th>' . "\n" . '</tr>' . "\n";

		foreach ($this->params as $key => $value)
		{
			$output .= "\t" . '<tr>' . "\n\t\t" . '<td><b>' . $key . '</b></td>';
			$output .= '<td>' . $value . '</td>' . "\n" . '</tr>' . "\n";
		}

		$output .= '</table>' . "\n";
		return $output;
	}

	private function xmlinize( $array_data ) {
		$xml = "";
		foreach( $array_data as $element => $value ) {
			if ( is_array( $value ) && count( $value ) > 0 ) {
				$value = $this->xmlinize( $value );
				$xml .= "<{$element}>{$value}</{$element}>";
			} else {
				$xml .= "<{$element}>{$value}</{$element}>\n";
			}
		}
		return $xml;
	}

	private function process( $retries = 3 ) {
		$count = 0;
		while ($count < $retries)
		{
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $this->url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_HTTPHEADER, Array("Content-Type: text/xml"));
			curl_setopt($ch, CURLOPT_HEADER, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $this->xml);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			$this->response = curl_exec($ch);

			$this->parseResults();

			if ($this->resultCode === "Ok")
			{
				$this->success = true;
				$this->error   = false;
				break;
			}
			else
			{
				$this->success = false;
				$this->error   = true;
				throw new AuthnetARBException($this->text);
				break;
			}
			$count++;
		}
		curl_close($ch);
	}

	//function to parse Authorize.net response
	private function parseResults(){
		$this->refId = $this->substring_between($this->response,'<refId>','</refId>');
		$this->resultCode = $this->substring_between($this->response,'<resultCode>','</resultCode>');
		$this->code = $this->substring_between($this->response,'<code>','</code>');
		$this->text = $this->substring_between($this->response,'<text>','</text>');
		$this->subscrId = $this->substring_between($this->response,'<subscriptionId>','</subscriptionId>');
		$this->substatus = $this->substring_between($this->response,'<status>','</status>');
	}

	//helper function for parsing response
	private function substring_between($haystack,$start,$end){
		if (strpos($haystack,$start) === false || strpos($haystack,$end) === false) {
			return false;
		} else {
			$start_position = strpos($haystack,$start)+strlen($start);
			$end_position = strpos($haystack,$end);
			return substr($haystack,$start_position,$end_position-$start_position);
		}
	}
}
