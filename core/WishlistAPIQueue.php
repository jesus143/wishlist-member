<?php
/**
 * Plugin Methods Class for WishList Member API Queue
 * @author Fel Jun Palawan <feljunpalawan@gmail.com>
 * @package wishlistmember
 *
 * @version $$
 * $LastChangedBy: mike $
 * $LastChangedDate: 2016-11-29 01:15:31 -0500 (Tue, 29 Nov 2016) $
 */
class WishlistAPIQueue {

	function __construct(){
		global $wpdb;
		$this->TablePrefix = $wpdb->prefix . 'wlm_';
		$this->Table = $this->TablePrefix . 'api_queue';

		//cleanup some old records with error
		$this->remove_old_queue();
	}
	public function add_queue($name,$value,$notes="",$unique=false){
		global $wpdb;
		if($unique) {
			$query = $wpdb->prepare("SELECT `ID` FROM `{$this->Table}` WHERE `name`=%s AND `value`=%s LIMIT 1", $name, $value);
			$unique = $wpdb->get_row($query);
			if($unique) {
				return false;
			}
		}
		$data = array(
			'name' => $name,
			'value' => $value,
			'notes' => $notes,
			'tries' => 0
		);		
		return $wpdb->insert($this->Table, $data);		
	}

	public function get_queue($name,$limit=null,$tries=null,$sort="ID",$date=null){
		global $wpdb;

		$sort = " ORDER BY {$sort} ASC";
		$limit = (int)$limit;
		$limit = $limit != null ? " LIMIT 0,{$limit}":"";
		$where = " WHERE name LIKE '%{$name}%'";

		if($tries != null){
			$where = $where == "" ? " WHERE tries <= {$tries}" : " {$where} AND tries <= {$tries}";
		}

		if($date != null){
			$where = $where == "" ? " WHERE date_added <= '{$date}'" : " {$where} AND date_added <= '{$date}'";
		}

		$query = "SELECT * FROM {$this->Table} {$where} {$sort} {$limit}";
		return $wpdb->get_results($query);
	}

	public function update_queue($id,$data){
		global $wpdb;
		$where = array('ID' => $id);
		return $wpdb->update($this->Table, $data, $where);
	}

	public function delete_queue($id) {
		global $wpdb;
		$wpdb->query($wpdb->prepare("DELETE FROM `{$this->Table}` WHERE `ID`=%d", $id));
	}

	public function remove_old_queue() {
		global $wpdb;
		$wpdb->query( "DELETE FROM `{$this->Table}` WHERE date_added < DATE_SUB(NOW(), INTERVAL 1 WEEK) AND tries > 1" );
	}
}

?>