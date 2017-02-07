<?php

/*
 * Ontraport (Office Auto Pilot) Autoresponder Integration Functions
 * Original Author : Ronaldo Reymundo
 * Version: $Id: integration.autoresponder.ontraport.php 2813 2015-07-29 14:30:25Z mike $
 */

//$__classname__ = 'WLM_AUTORESPONDER_ONTRAPORT';
//$__optionname__ = 'ontraport';
//$__methodname__ = 'ontraport_subscribe';


if (!class_exists('WLM_AUTORESPONDER_ONTRAPORT')) {

	class WLM_AUTORESPONDER_ONTRAPORT {


		private $wlm;
		private $ontraport_appid;
		private $ontraport_key;
	       
	    function set_wlm($wlm) {
			$this->wlm = $wlm;
		}

	  	function set_appid($appid = '') {
			 $this->ontraport_appid = $appid;
		}

	  	function set_key($key = '') {
			 $this->ontraport_key = $key;
		}

	    /**
	     * Send a HTTP request to the API
	     *
	     * @param string $api_method The API method to be called
	     * @param string $http_method The HTTP method to be used (GET, POST, PUT, DELETE, etc.)
	     * @param array $data Any data to be sent to the API
	     * @return string An XML-formatted response
	     **/
	    private function sendRequest($api_method, $http_method = 'GET', $data = null)
	    {
	        // Set the request type and construct the POST request
	        $postdata = "appid=".$this->ontraport_appid."&key=".$this->ontraport_key."&return_id=1";
	        $postdata .= '&reqType='.$api_method;
	        $postdata .= '&data='.$data;

	        // Set request
	        $request_url = 'https://api.ontraport.com/cdata.php';

	        // Debugging output
	        $this->debug = array();
	        $this->debug['HTTP Method'] = $http_method;
	        $this->debug['Request URL'] = $request_url;

	        // Create a cURL handle
	        $ch = curl_init();

	        // Set the request
	        curl_setopt($ch, CURLOPT_URL, $request_url);

	        // Do not ouput the HTTP header
	        curl_setopt($ch, CURLOPT_HEADER, false);

	        // Save the response to a string
	        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	        // Send data as PUT request
	        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $http_method);

	        // This may be necessary, depending on your server's configuration
	        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

	        // Send data
	        if (!empty($postdata)) {

	            curl_setopt($ch, CURLOPT_POST, true);
	            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Length: '.strlen($postdata)));
	            curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);

	            // Debugging output
	            $this->debug['Posted Data'] = $postdata;

	        }

	        // Execute cURL request
	        $curl_response = curl_exec($ch);

	        // Save CURL debugging info
	        $this->debug['Curl Info'] = curl_getinfo($ch);

	        // Return parsed response
	        return $curl_response;
	    }

	    public function ontraport_subscribe($that, $ar, $wpm_id, $email, $unsub = false) {

				if (!$unsub) {
					$options = $that->GetOption('Autoresponders');
					
					$this->set_appid($options['ontraport']['app_id']);
					$this->set_key($options['ontraport']['api_key']);

					if($options['ontraport']['addenabled'][$wpm_id] == 'yes') {

						// Set format for sequences
						$sequences = '*/*';
						foreach( (array)$options['ontraport']['sequences'][$wpm_id] as $sequence) {
							$sequences.= $sequence.'*/*';
						}

						// Set format to add tags
						foreach( (array)$options['ontraport']['tags'][$wpm_id] as $tag) {
							$tags.= $tag.'*/*';
						}

						// Construct contact data in XML format
						$data = <<<STRING
						<contact>
						<Group_Tag name="Contact Information">
						<field name="First Name">{$that->OrigPost['firstname']}</field>
						<field name="Last Name">{$that->OrigPost['lastname']}</field>
						<field name="E-Mail">{$that->OrigPost['email']}</field>
						</Group_Tag>
						<Group_Tag name="Sequences and Tags">
						<field name="Contact Tags" type="numeric">{$tags}</field>
						<field name="Sequences">{$sequences}</field>
						</Group_Tag>
						</contact>
STRING;

				        // Encoded data
				        $data = urlencode(urlencode($data));

				        // Send Request
				        $this->sendRequest('add', 'POST', $data);
					}
					
				} else {
					// If unsub Do nothing, for now.
				}
		}

		/**
	     * Fetches the list of Tags
	     *
	     * @return Returns the ID and tags name in array form
	     **/
		public function ontraport_fetch_tags() {
			$result = new SimpleXMLElement($this->sendRequest('pull_tag', 'POST', $data));

			$data = array();
			$count = 0;
			foreach($result as $val) {
				$data[$this->get_xml_attribute($val, 'id')] = (string)$result->tag[$count];
				$count++;
			}

			return $data;
		}


		/**
	     * Fetches the list of Sequences
	     *
	     * @return Returns the ID and sequence name in array form
	     **/
		public function ontraport_fetch_sequences() {
			$result = new SimpleXMLElement($this->sendRequest('fetch_sequences', 'POST', $data));

			$data = array();
			$count = 0;
			foreach($result as $val) {
				$data[$this->get_xml_attribute($val, 'id')] = (string)$result->sequence[$count];
				$count++;
			}

			return $data;
		}

		// Gets ID of the sequence/tags returned by fetch_tags or fetch_sequence API call
		public function get_xml_attribute($object, $attribute)
		{
		    if(isset($object[$attribute]))
		        return (string) $object[$attribute];
		}

	}
}