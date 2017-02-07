<?php
/**
 * A wrapper to OneshoppingCart API to facilitate 
 * cancellation
 * requires OneShopAPI to be loaded first
 */
class WLMOneShopApi {
	/**
	 * 
	 */
	private $default_record_count = 100;
	private $api;
	public function __construct($merchant_id, $merchant_key, $api_uri) {
		$this->api = new OneShopAPI($merchant_id, $merchant_key, $api_uri);
	}
	public function doc_to_xml($doc) {
		$xml = new DOMDocument();
		$xml->loadXML($doc);
		return $xml;
	}
	public function is_error($api_response) {
		$success = $api_response->getElementsByTagName('Response')->item(0)->getAttribute('success');
		if($success == true) {
			return false;
		}
		return true;
	}
	private function has_more_records($api_response) {
		$next = $api_response->getElementsByTagName('NextRecordSet');
		$next = $next->item(0);
		return !empty($next);
	}
    public function get_order_by_id($oid, $include_upsell_orders = false) {
        $this->api->ClearApiParameters();
        $o_doc = $this->api->GetOrderById($oid);
        if(!$o_doc) {
        	return array();
        }
        $o_doc = $this->doc_to_xml($o_doc);
        $status = $o_doc->getElementsByTagName('OrderStatusType')->item(0)->nodeValue;
        $client_id = $o_doc->getElementsByTagName('ClientId')->item(0)->nodeValue;
        $product_id = $o_doc->getElementsByTagName('ProductId')->item(0)->nodeValue;
        $sku = $o_doc->getElementsByTagName('Sku')->item(0)->nodeValue;
        $rid = $o_doc->getElementsByTagName('RecurringOrderId')->item(0)->nodeValue;
        $date = $o_doc->getElementsByTagName('OrderDate')->item(0)->nodeValue;
        $order = array(
            'id'            => $oid,
            'status'        => strtolower($status),
            'client_id'     => $client_id,
            'product_id'    => $product_id,
			'sku'			=> $sku,
			'recurring_id'  => $rid,
			'date'          => $date,
        );
		
		if($include_upsell_orders){
			$order['upsells'] = array();
			$upsells = $o_doc->getElementsByTagName('UpsellOrders');
			if($upsells->length){
				$upsells = $upsells->item(0)->getElementsByTagName('Orders');
				for($i=0;$i<$upsells->length;$i++){
					$sku = $this->get_order_by_id($upsells->item($i)->nodeValue);
					$sku = $sku['sku'];
					$order['upsells'][]=$sku;
				}
			}
		}
		
        return $order;
    }
    /**
     * @param $stardate mm/dd/yyyy or mm/dd/yyyy hh:mm:ss
     * @param $enddate mm/dd/yyyy or mm/dd/yyyy hh:mm:ss
     */
	public function get_orders($stardate = null, $enddate = null, $order_ids_only = false) {
		$this->api->ClearApiParameters();
		$orders = array();
		$has_more = true;
		$rec_count = $this->default_record_count;
		$rec_offst = 0;
		
		while($has_more) {			

            if($stardate != null) {
                $this->api->AddApiParameter("LimitStartDate", $stardate);
            }
            if($enddate != null) {
                $this->api->AddApiParameter("LimitEndDate", $enddate);			
            }
			$this->api->AddApiParameter("LimitCount", $rec_count);
			$this->api->AddApiParameter("LimitOffset", $rec_offst);
			$this->api->AddApiParameter("SortOrder", 'ASC');			
			$doc = $this->api->GetOrdersList();
			if(!$doc) {
				break;
			}
			$doc = $this->doc_to_xml($doc);			
			
			$o = $doc->getElementsByTagName("Order");
			for($i=0; $i<$o->length; $i++) {
				$oid = $o->item($i)->nodeValue;
                //retrieve the corresponding order
                if($order_ids_only) {
                	$orders[] = $oid;
                } else {
	                $order = $this->get_order_by_id($oid);
	                $order_reference = sprintf('%s-%s', $order['product_id'], $order['client_id']);
	                $orders[$order_reference] = $order;
                }
			}

			if(!$this->has_more_records($doc)) {
				break;
			}
			
			$rec_count = $doc->getElementsByTagName("LimitCount")->item(0)->nodeValue;
			$rec_offst = $doc->getElementsByTagName("LimitOffset")->item(0)->nodeValue;
		}
		return $orders;
	}
    public function get_product_by_id($pid) {
        $this->api->ClearApiParameters();
        //get the product detail
        $pdoc = $this->api->GetProductById($pid);
        $pdoc = $this->doc_to_xml($pdoc);
        $sku = $pdoc->getElementsByTagName("ProductSku");
        $sku = $sku->item(0)->nodeValue;
        //not linked to any membership level
        //so we don't care ^_^
        if(empty($sku)) {
            return false;
        }
        $product  = array(
            'id'	=> $pid,
            'sku'	=> $sku
        );
        return $product;
    }
	public function get_products() {
		$this->api->ClearApiParameters();
		$products = array();
		$has_more = true;
		$rec_count = $this->default_record_count;
		$rec_offset = 0;
		
		while($has_more) {
			$this->api->AddApiParameter("LimitCount", $rec_count);
			$this->api->AddApiParameter("LimitOffset", $rec_offst);
			$doc = $this->api->GetProductsList();
			$doc = $this->doc_to_xml($doc);
			
			$p = $doc->getElementsByTagName("Product");
			for($i=0; $i<$p->length; $i++) {
				$pid = $p->item($i)->nodeValue;
                $product = $this->get_product_by_id($pid);
                if($product === false) {
                    continue;
                }
                $products[] = $product;
			}
			
			if(!$this->has_more_records($doc)) {
				break;
			}
			
			$rec_count = $doc->getElementsByTagName("LimitCount")->item(0)->nodeValue;
			$rec_offst = $doc->getElementsByTagName("LimitOffset")->item(0)->nodeValue;
		}
		return $products;
	}
}
