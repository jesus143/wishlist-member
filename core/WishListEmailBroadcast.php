<?php
/**
 * Plugin Methods Class for WishList Member Email Broadcast
 * @author Fel Jun Palawan <feljunpalawan@gmail.com>
 * @package wishlistmember
 *
 * @version $$
 * $LastChangedBy: mike $
 * $LastChangedDate: 2016-04-12 09:36:46 -0400 (Tue, 12 Apr 2016) $
 */
class WishListEmailBroadcast {

	function __construct ( ) {

		global $wpdb;
		$this->TablePrefix = $wpdb->prefix . 'wlm_';
		$this->Table = $this->TablePrefix . 'emailbroadcast';
		$this->QueueTable = $this->TablePrefix . 'email_queue';
	}

	/**
	 * Save Email Broadcast
	 * @global object $wpdb
	 * @param string $subject
	 * @param string $msg
	 * @param string $signature
	 * @param string $recipients
	 * @param string $mlevel
	 * @param string $sent_as
	 */
	function save_broadcast (  $subject, $msg, $footer, $send_to, $mlevel, $sent_as, $otheroptions ) {
		global $wpdb;
		$table = $this->Table;
		$q = $wpdb->prepare("INSERT INTO $table(subject,text_body,footer,send_to,mlevel,sent_as,status,otheroptions) VALUES('%s','%s','%s','%s','%s','%s','Queueing','%s')", $subject, $msg, $footer, $send_to, $mlevel, $sent_as, $otheroptions);

		if ($wpdb->query($q)) {
			$ret = $wpdb->get_results("SELECT LAST_INSERT_ID( ) as LAST_INSERT_ID ");
			return $ret[0]->LAST_INSERT_ID;
		} else {
			return false;
		}
	}

	/**
	 * Get Email Broadcast
	 * @global object $wpdb
	 * @param int $id
	 */
	function get_broadcast ( $id ) {
		global $wpdb;
		$id = (int) $id;
		$table = $this->Table;
		$q = $wpdb->prepare("SELECT * FROM {$table} WHERE id=%d",$id);
		return $wpdb->get_row($q);
	}

	/**
	 * Get All Email Broadcast
	 * @global object $wpdb
	 * @param boolean $count	 
	 * @param string $start
	 * @param string $per_page
	 * @param string $order	 	 
	 */
	function get_all_broadcast ( $start = "", $per_page = "", $order = "" ) {
		global $wpdb;
		$table = $this->Table;
		$start = (int) $start;
		$per_page = (int) $per_page;
		$limit = "";
		if($per_page > 0){
			$limit = "LIMIT %d,%d";
		}
		$order = $order == "" ? "date_added" : $order;
		$order_query = "ORDER BY {$order} DESC";

		$q = $wpdb->prepare("SELECT * FROM $table {$order_query} {$limit}",array($start,$per_page));
		return $wpdb->get_results($q);
	}

	/**
	 * Get All Unsync Email Broadcast (from old email broadcast)
	 * @global object $wpdb
	 * @param boolean $count	 
	 * @param string $start
	 * @param string $per_page
	 * @param string $order	 	 
	 */
	function get_unsync_broadcast () {
		global $wpdb;
		$table = $this->Table;
		// $q = $wpdb->prepare("SELECT * FROM {$table} WHERE ( recipients != '' AND total_queued <= 0 ) OR ( failed_address IS NOT NULL AND failed_address != '' )");
		$q = "SELECT * FROM {$table} WHERE ( recipients != '' AND total_queued <= 0 ) OR ( failed_address IS NOT NULL AND failed_address != '' )";
		return $wpdb->get_results($q);
	}

	/**
	 * Count all email broadcast
	 * @global object $wpdb	 
	 */
	function count_broadcast ( ) {
		global $wpdb;
		$table = $this->Table;

		return $wpdb->get_var("SELECT COUNT(*) FROM {$table}");
	}

	/**
	 * Update Email Broadcast
	 * @global object $wpdb
	 * @param int $id
	 * @param array $data
	 */
	function update_broadcast ( $id, $data ) {
		global $wpdb;
		$id = (int) $id;
		$table = $this->Table;
		foreach($data as $field=>$d){
			$str .= ", {$field}='{$d}'";
		}
		$str = trim($str,",");
		$query = "UPDATE {$table} SET {$str} WHERE id={$id}";
		return $wpdb->query($query);
	}

	/**
	 * Delete Email Broadcasts
	 * @param array $id
	 * @return boolean	 
	 */	
	function delete_broadcast ( $ids ) {
		global $wpdb;
		$ids = implode(",",(array)$ids);
		$table = $this->Table;
		return $wpdb->query("DELETE FROM {$table} WHERE id IN ({$ids})");
	}

	/**
	 * Add To Email Queue
	 * @global object $wpdb
	 * @param int $broadcastid	 
	 * @param int $userid
	 * @return boolean
	 */
	function add_email_queue ( $broadcastid, $userid ) {
		global $wpdb;
		$table = $this->QueueTable;
		$q = $wpdb->prepare("INSERT INTO $table(broadcastid,userid) VALUES('%d','%d')", $broadcastid, $userid);
		if ($wpdb->query($q)) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Bulk Add To Email Queue
	 * @global object $wpdb
	 * @param array $fields	 
	 * @param array $data
	 * @return boolean
	 */
	function bulk_add_email_queue ( $fields, $data ) {
		global $wpdb;
		$table = $this->QueueTable;
		$values = array();
		foreach( $data as $val ) {
			$values[] = "('" .implode("','", $val) ."')";
		}
		$values = implode(",", $values);
		$fields = implode(",", $fields);
		$q = "INSERT INTO {$table}({$fields}) VALUES {$values}";
		if ($wpdb->query($q)) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Get Email Queue
	 * @global object $wpdb
	 * @param int $broadcastid	 
	 * @param boolean $include_fail
	 * @param boolean $count		 
	 */
	function get_email_queue ( $broadcastid = null, $include_fail=false, $include_paused = false, $limit = 0 ) {
		global $wpdb;
		$limit = (int) $limit;
		$where = array();

		if( is_numeric ( $broadcastid ) ) {
			$where[] = "q.broadcastid=%d";
		}

		if(!$include_fail){
			$where[] = "q.failed = 0";
		}

		if(!$include_paused){
			$where[] = "b.status = 'Queued'";
		}

		if($limit > 0){
			$limit = " LIMIT {$limit}";
		}else{
			$limit = "";
		}

		$where = implode(" AND ", $where);
		if($where != ""){
			$where = "WHERE {$where}";
		}

		$join = "LEFT JOIN {$this->Table} AS b ON b.id=q.broadcastid";
		$fields = "q.id AS id,q.broadcastid AS broadcastid,q.userid as userid,b.subject AS subject, b.text_body AS text_body,b.footer AS footer,b.send_to AS send_to,b.sent_as AS sent_as";
		$q = $wpdb->prepare("SELECT {$fields} FROM {$this->QueueTable} AS q {$join} {$where} {$limit}", $broadcastid);
		return $wpdb->get_results($q);
	}


	/**
	 * Get Email Queue
	 * @param int $id
	 * @param array $value
	 */
	function get_email_queue_by_id ( $id ) {
		global $wpdb;
		$join = "LEFT JOIN {$this->Table} AS b ON b.id=q.broadcastid";
		$fields = "q.id AS id,q.broadcastid AS broadcastid,q.userid as userid,b.subject AS subject, b.text_body AS text_body,b.footer AS footer,b.send_to AS send_to,b.sent_as AS sent_as";
		$q = $wpdb->prepare("SELECT {$fields} FROM {$this->QueueTable} AS q {$join} WHERE q.id =%d", $id);
		return $wpdb->get_row($q);
	}

	/**
	 * Count Email Queue
	 * @global object $wpdb
	 * @param int $broadcastid	 
	 * @param boolean $include_fail
	 * @param boolean $count		 
	 */
	function count_email_queue ( $broadcastid = null, $include_fail=false, $include_paused = false ) {
		global $wpdb;
		$table = $this->QueueTable;
		$where = array();
		if( is_numeric ( $broadcastid ) ) {
			$where[] = "broadcastid=%d";
		}
		if(!$include_fail){
			$where[] = "failed = 0";
		}
		if(!$include_paused){
			$where[] = "b.status = 'Queued'";
		}
		$where = implode(" AND ", $where);
		if($where != ""){
			$where = "WHERE {$where}";
		}
		$join = "LEFT JOIN {$this->Table} AS b ON b.id=q.broadcastid";
		$q = $wpdb->prepare("SELECT COUNT(*) FROM {$table} AS q {$join} {$where}", $broadcastid);
		return $wpdb->get_var($q);
	}

	/**
	 * Get Failed Email Queue
	 * @global object $wpdb
	 * @param int $broadcastid
	 * @param boolean $count		 
	 */
	function get_failed_queue ( $broadcastid = null ) {
		global $wpdb;
		$table = $this->QueueTable;
		$where = array();
		if( is_numeric ( $broadcastid ) ) {
			$where[] = "broadcastid=%d";
		}
		$where[] = "failed>0";
		$where = implode(" AND ", $where);
		if($where != ""){
			$where = "WHERE {$where}";
		}

		$q = $wpdb->prepare("SELECT ee.id AS id, ee.broadcastid AS broadcastid, ee.userid AS userid, ee.failed AS failed, u.user_email AS user_email FROM {$table} AS ee LEFT JOIN {$wpdb->prefix}users u ON u.ID = ee.userid {$where}", $broadcastid);
		return $wpdb->get_results($q);
	}

	/**
	 * Get Failed Email Queue
	 * @global object $wpdb
	 * @param int $broadcastid
	 * @param boolean $count		 
	 */
	function count_failed_queue ( $broadcastid = null ) {
		global $wpdb;
		$table = $this->QueueTable;
		$where = array();
		if( is_numeric ( $broadcastid ) ) {
			$where[] = "broadcastid=%d";
		}
		$where[] = "failed>0";
		$where = implode(" AND ", $where);
		if($where != ""){
			$where = "WHERE {$where}";
		}
		$q = $wpdb->prepare("SELECT COUNT(*) FROM {$table} {$where}", $broadcastid);
		return $wpdb->get_var($q);
	}

	/**
	 * Delete Email Broadcast Queue
	 * @param array $id
	 * @return boolean	 
	 */	
	function delete_email_queue ( $ids ) {
		global $wpdb;
		$ids = implode(",",(array)$ids);
		$table = $this->QueueTable;
		return $wpdb->query("DELETE FROM {$table} WHERE id IN ({$ids})");
	}

	/**
	 * Delete Email Broadcast Queue
	 * @param array $id
	 * @return boolean	 
	 */	
	function purge_broadcast_queues ( $broadcastid, $failed_only = true ) {
		global $wpdb;
		$table = $this->QueueTable;
		$q = "";
		if ( $failed_only ) {
			$q = " AND failed > 0";
		}
		return $wpdb->query("DELETE FROM {$table} WHERE  broadcastid = {$broadcastid} {$q}");
	}

	/**
	 * Fail/Unfail Email Broadcast Queue
	 * @global object $wpdb
	 * @param int $id
	 * @param array $value
	 */
	function fail_email_queue ( $ids, $value=1 ) {
		global $wpdb;
		$ids = implode(",",(array)$ids);
		$table = $this->QueueTable;
		$value = (int) $value;
		return $wpdb->query("UPDATE {$table} SET failed = {$value} WHERE id IN ({$ids})");
	}

	function requeue_email ( $broadcastid ) {
		global $wpdb;
		$table = $this->QueueTable;
		$value = (int) $value;
		return $wpdb->query("UPDATE {$table} SET failed = 0 WHERE broadcastid = {$broadcastid}");
	}

	/**
	 * check if old stats is missing
	 */
	function check_stats_missing () {
		global $wpdb;
		//check if the column exist
		$query = "SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND ( COLUMN_NAME='failed_address' OR COLUMN_NAME='recipients' ) AND TABLE_NAME='{$this->Table}'";
		$res = $wpdb->get_results($query);
		return count( $res ) > 1 ? true : false;
	}

}

?>