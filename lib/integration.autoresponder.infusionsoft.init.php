<?php
/*
 * Infusionsoft Autoresponder Integration Init
 * Original Author : Fel Jun Palawan
 */
if ( ! class_exists('WLM_Infusionsoft') ) {
	include_once( $this->pluginDir . '/extlib/wlm-infusionsoft.php' );
}
if (!class_exists('WLM_AUTORESPONDER_INFUSIONSOFT_INIT')) {

	class WLM_AUTORESPONDER_INFUSIONSOFT_INIT {
		private $machine_name = "";
		private $api_key      = "";
		private $ifsdk        = NULL;
		private $log          = false;

		function __construct() {
			global $WishListMemberInstance;

			//make sure that WLM active and infusiosnsoft connection is set
			if ( isset( $WishListMemberInstance ) && class_exists( 'WLM_Infusionsoft' ) ) {
				$this->machine_name = $WishListMemberInstance->GetOption('auto_ismachine');
				$this->api_key      = $WishListMemberInstance->GetOption('auto_isapikey');
				$this->log          = $WishListMemberInstance->GetOption('auto_isenable_log');
				$this->machine_name = $this->machine_name ? $this->machine_name : "";
				$this->api_key      = $this->api_key ? $this->api_key : "";

				$apilogfile = false;
				if ( $this->log ) {
	                $dirsep             = DIRECTORY_SEPARATOR; //directory seperator
	                $date_now           = date('m-d-Y');
	                $apilogfile         = $WishListMemberInstance->pluginDir .$dirsep ."ifs_logs_{$date_now}.csv";
				}

				if ( $this->api_key && $this->machine_name ) {
					$this->ifsdk            = new WLM_Infusionsoft( $this->machine_name, $this->api_key, $apilogfile );
				}
			}
		}

	    function load_hooks() {
			if ( $this->ifsdk ) {
				add_action('wishlistmember_user_registered', array($this, 'NewUserTagsHookQueue'),99,2);
				add_action('wishlistmember_add_user_levels', array($this, 'AddUserTagsHookQueue'),10,3);
				add_action('wishlistmember_pre_remove_user_levels', array($this, 'RemoveUserTagsHookQueue'),99,2);
				add_action('wishlistmember_cancel_user_levels', array($this, 'CancelUserTagsHookQueue'),99,2);
				add_action('delete_user', array($this, 'DeleteUserHookQueue'),9,1);

				//check if this settings is handled by shopping card integration of infusionsoft
				global $WishListMemberInstance;
				if ( ! $WishListMemberInstance->GetOption('ismachine') || ! $WishListMemberInstance->GetOption('isapikey') ) {
					add_action('edit_user_profile', array($this, 'ProfileForm'));
					add_action('show_user_profile', array($this, 'ProfileForm'));
					add_action('profile_update', array($this, 'UpdateProfile'), 9, 2);
				}
			}
	    }

		function ProfileForm( $user ) {
			global $WishListMemberInstance, $pagenow;
			if ( ! isset( $WishListMemberInstance ) ) return;
			if ( ! current_user_can( 'manage_options' ) ) { return; }
			$user_id = $user;
			if(is_object($user)) {
				$user_id = $user->ID;
			}
			if ( $pagenow != 'profile.php' && $pagenow != 'user-edit.php') return;

			$contactid = $WishListMemberInstance->Get_UserMeta( $user_id, "wlminfusionsoft_contactid" );
			echo '<h3>Infusionsoft Info</h3>';
			echo '<table class="form-table">';
			echo '<tbody>';
			echo 	'<tr>';
			echo 		'<th><label for="wlminfusionsoft_contactid">Infusionsoft Contact ID</label></th>';
			echo 		'<td>';
			echo 			'<input type="text" name="wlminfusionsoft_contactid" id="wlminfusionsoft_contactid" value="' .$contactid .'" class="regular-text">';
			echo 		'</td>';
			echo 	'</tr>';
			echo '</tbody>';
			echo '</table>';
		}

		function UpdateProfile($user) {
			global $WishListMemberInstance;
			if ( ! isset( $WishListMemberInstance ) ) return;
			if ( ! current_user_can( 'manage_options' ) ) { return; }
			$user_id = $user;
			if(is_object($user)) {
				$user_id = $user->ID;
			}

			if(isset($_POST['wlminfusionsoft_contactid'])) {
				$WishListMemberInstance->Update_UserMeta($user_id, 'wlminfusionsoft_contactid', (int) trim($_POST['wlminfusionsoft_contactid']));
			}
		}

		function generateContactId( $uid ) {
			if ( !$this->ifsdk || !$this->ifsdk->is_api_connected() ) return null;

			global $WishListMemberInstance;
			$contactid = get_user_meta( $uid, "wlifcon_contactid", true ); //wlmis contactid

			if ( !$contactid ) {
				$user_info = get_userdata( $uid );
				if ( ! $user_info ) return null;

				$email     = $user_info->user_email;
				if ( $email && filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
					$contactid = $this->ifsdk->get_contactid_by_email( $email );
					if ( !$contactid ) {
						$user = array(
							'Email' => $email,
							'FirstName' =>$user_info->user_firstname,
							'LastName' => $user_info->user_lastname
						);
						$contactid = $this->ifsdk->create_contact( $user, "Added Via WLM INF AR Integration API." );
					}
				} else {
					return null;
				}
			}

			if ( $contactid ) {
				$WishListMemberInstance->Update_UserMeta( $uid, "wlminfusionsoft_contactid", $contactid );
			} else {
				$contactid = false; //make sure that contactid is false
			}

			return $contactid;
		}

		function processTags( $levels, $action, $contactid = null, $uid = null ) {
			if ( !$this->ifsdk || !$this->ifsdk->is_api_connected() ) return array("errstr"=>"Unable to process tags. No API Connection.","errno"=>1);

			global $WishListMemberInstance;
			$levels = (array) $levels;
			if ( count( $levels ) <= 0 ) return array("errstr"=>"No Levels Found","errno"=>1);//no levels, no need to continue

			if ( !$contactid ) {
				$contactid = $WishListMemberInstance->Get_UserMeta( $uid, "wlminfusionsoft_contactid" );
				//get the contactid if not set
				if ( !$contactid ) {
					$contactid = $this->generateContactId( $uid );
					if ( $contactid === null ) {
						 return array("errstr"=>"Theres a problem with userid, email or api connection.","errno"=>1);
					}
				}
			}

			if ( $contactid ) {

				if( $action == 'new' || $action == 'add' ){
					$istags_add_app = $WishListMemberInstance->GetOption('auto_istags_add_app');
					$istags_add_rem = $WishListMemberInstance->GetOption('auto_istags_add_rem' );
				}elseif( $action == 'remove'){
					$istags_add_app = $WishListMemberInstance->GetOption('auto_istags_remove_app');
					$istags_add_rem = $WishListMemberInstance->GetOption('auto_istags_remove_rem' );
				}elseif( $action == 'cancel'){
					$istags_add_app = $WishListMemberInstance->GetOption('auto_istags_cancelled_app');
					$istags_add_rem = $WishListMemberInstance->GetOption('auto_istags_cancelled_rem' );
				}elseif( $action == 'delete'){
					$istags_add_app = $WishListMemberInstance->GetOption('auto_istags_remove_app');
					$istags_add_rem = $WishListMemberInstance->GetOption('auto_istags_remove_rem' );
				}

				if ( $istags_add_app ) $istags_add_app = maybe_unserialize( $istags_add_app );
				else $istags_add_app = array();

				if ( $istags_add_rem ) $istags_add_rem = maybe_unserialize( $istags_add_rem );
				else $istags_add_rem = array();

				//add the tags for each level
				foreach( (array) $levels as $level ) {
					//add the contact to a tag/group
					if ( isset( $istags_add_app[$level] ) ) {
						foreach ( $istags_add_app[$level] as $k=>$val ) {
							$ret = $this->ifsdk->tag_contact( $contactid, $val );
							if ( isset( $ret["errno"] ) ) return $ret;
						}
					}

					//remove the contact from tag/group
					if ( isset( $istags_add_rem[$level] ) ) {
						foreach ( $istags_add_rem[$level] as $k=>$val ) {
							$ret = $this->ifsdk->untag_contact( $contactid, $val );
							if ( isset( $ret["errno"] ) ) return $ret;
						}
					}
				}

			}else{
				return array("errstr"=>"No Contact ID","errno"=>1);
			}

			return true; //success
		}

		function ifarAddQueue($data,$process=true){
			$WishlistAPIQueueInstance = new WishlistAPIQueue;
			$qname = "infusionsoftar_" .time();
			$data = maybe_serialize($data);
			$WishlistAPIQueueInstance->add_queue($qname,$data,"For Queueing");
			if($process){
				$this->ifarProcessQueue();
			}
		}

		function ifarProcessQueue($recnum = 10,$tries = 5){
			if ( !$this->ifsdk || !$this->ifsdk->is_api_connected() ) return;
			$WishlistAPIQueueInstance = new WishlistAPIQueue;
			$last_process = get_option("WLM_InfusionsoftARAPI_LastProcess");
			$current_time = time();
			$tries = $tries > 1 ? (int)$tries:5;
			$error = false;
			//lets process every 10 seconds
			if(!$last_process || ($current_time - $last_process) > 10){
				$queues = $WishlistAPIQueueInstance->get_queue("infusionsoftar",$recnum,$tries,"tries,name");
				foreach($queues as $queue){
					$data = maybe_unserialize($queue->value);
					if($data['action'] == 'new'){
						$res = $this->NewUserTagsHook($data['uid'],$data['data']);
					}elseif($data['action'] == 'add'){
						$res = $this->AddUserTagsHook($data['uid'],$data['addlevels']);
					}elseif($data['action'] == 'remove'){
						$res = $this->RemoveUserTagsHook($data['uid'],$data['removedlevels']);
					}elseif($data['action'] == 'cancel'){
						$res = $this->CancelUserTagsHook($data['uid'],$data['cancellevels']);
					}elseif($data['action'] == 'delete'){
						$res = $this->DeleteUserTagsHook($data['contactid'],$data['levels']);
					}

					if(isset($res['errstr'])){
						$res['error'] = strip_tags($res['errstr']);
						$res['error'] = str_replace(array("\n", "\t", "\r"), '',$res['error']);
						$d = array(
							'notes'=> "{$res['errno']}:{$res['error']}",
							'tries'=> $queue->tries + 1
							);
						$WishlistAPIQueueInstance->update_queue($queue->ID,$d);
						$error = true;
					}else{
						$WishlistAPIQueueInstance->delete_queue($queue->ID);
						$error = false;
					}
				}
				//save the last processing time
				if($error){
					$current_time = time();
					if($last_process){
						update_option("WLM_InfusionsoftARAPI_LastProcess",$current_time);
					}else{
						add_option("WLM_InfusionsoftARAPI_LastProcess",$current_time);
					}
				}
			}
		}


		//FOR NEW USERS
		function NewUserTagsHookQueue($uid=null,$udata=null){
			$data = array(
				"uid"=>$uid,
				"action"=>"new",
				"data"=>$udata
			);

			// Part of the Fix for issue where Add To levels aren't being processed.
			$user = get_userdata( $uid );
			if(strpos($user->user_email,"temp_") !== false && strlen($user->user_email) == 37 && strpos($user->user_email,"@") === false) {
				// Don't add the data into the queue if it's from a temp account
			} else {
				$this->ifarAddQueue($data);
			}

		}

		function NewUserTagsHook( $uid=null, $data=null ) {
			$tempacct = $data['email'] == 'temp_' . md5( $data['orig_email'] );
			if ( $tempacct ) return; //if temp account used by sc, do not process
			$levels    = (array) $data['wpm_id'];

			return $this->processTags( $levels, 'new', null, $uid );
		}

		//WHEN ADDED TO LEVELS
		function AddUserTagsHookQueue($uid, $addlevels = ''){
			$data = array(
				"uid"=>$uid,
				"action"=>"add",
				"addlevels"=>$addlevels
			);

			// Fix for issue where Add To levels aren't being processed.
			// If the data is from a temp account then add it to the queue API and don't process it for now.
			$user = get_userdata( $uid );
			if(strpos($user->user_email,"temp_") !== false && strlen($user->user_email) == 37 && strpos($user->user_email,"@") === false) {
				$this->ifarAddQueue($data, 0);	
			} elseif(isset($_POST['SendMail'])) {
				// This elseif condition fixes the issue where members who are added via
				// WLM API aren't being processed by the Infusionsoft Autoresponder Integration.
				$this->ifarAddQueue($data, 0);
			} else {
				$this->ifarAddQueue($data);	
			}
		}

		function AddUserTagsHook( $uid, $newlevels = '' ) {
			$user = get_userdata( $uid );
			if ( !$user ) return array("errstr"=>"Invalid User ID.","errno"=>1);
			if(strpos($user->user_email,"temp_") !== false && strlen($user->user_email) == 37 && strpos($user->user_email,"@") === false) return;

			$levels = (array) $newlevels;
			return $this->processTags( $levels, 'add', null, $uid );
		}

		//WHEN REMOVED FROM LEVELS
		function RemoveUserTagsHookQueue($uid, $removedlevels = ''){
			//lets check for PPPosts
			$levels = (array) $removedlevels;
			foreach ( $levels as $key => $level ) {
				if ( strrpos( $level,"U-" ) !== false ) {
    				unset( $levels[$key] );
    			}
			}
			if ( count( $levels ) <= 0 ) return;

			$data = array(
				"uid"=>$uid,
				"action"=>"remove",
				"removedlevels"=>$removedlevels
			);
			$this->ifarAddQueue($data);		
		}

		function RemoveUserTagsHook( $uid, $removedlevels = '' ){
			$user = get_userdata( $uid );
			if ( !$user ) return array("errstr"=>"Invalid User ID.","errno"=>1);
			if(strpos($user->user_email,"temp_") !== false && strlen($user->user_email) == 37 && strpos($user->user_email,"@") === false) return;

			$levels = (array) $removedlevels;
			return $this->processTags( $levels, 'remove', null, $uid );
		}

		//WHEN CANCELLED FROM LEVELS
		function CancelUserTagsHookQueue($uid, $cancellevels = ''){
			//lets check for PPPosts
			$levels = (array) $cancellevels;
			foreach ( $levels as $key => $level ) {
				if ( strrpos( $level,"U-" ) !== false ) {
    				unset( $levels[$key] );
    			}
			}
			if ( count( $levels ) <= 0 ) return;

			$data = array(
				"uid"=>$uid,
				"action"=>"cancel",
				"cancellevels"=>$cancellevels
			);
			$this->ifarAddQueue($data);
		}

		function CancelUserTagsHook( $uid, $removedlevels = '' ){
			$user = get_userdata( $uid );
			if ( !$user ) return array("errstr"=>"Invalid User ID.","errno"=>1);
			if(strpos($user->user_email,"temp_") !== false && strlen($user->user_email) == 37 && strpos($user->user_email,"@") === false) return;

			$levels = (array) $removedlevels;
			return $this->processTags( $levels, 'cancel', null, $uid );
		}

		//WHEN DELETED FROM LEVELS
		function DeleteUserHookQueue( $uid ) {
			if ( !$this->ifsdk || !$this->ifsdk->is_api_connected() ) return;

			global $WishListMemberInstance;
			$levels = $WishListMemberInstance->GetMembershipLevels( $uid );
			foreach( $levels as $key => $lvl ) {
				if( strpos($lvl, 'U-') !== false ) {
					unset( $levels[$key] );
				}
			}
			if ( ! is_array( $levels ) || count( $levels ) <= 0 ) return; //lets return if no level was found

			$contactid = $WishListMemberInstance->Get_UserMeta( $uid, "wlminfusionsoft_contactid" );

			if ( ! $contactid ) { //if no contactid

				$user_info = get_userdata( $uid );
				if ( ! $user_info ) return; //invalid user
				$email = $user_info->user_email;

				if ( ! $contactid ) {
					if ( $email && filter_var( $email, FILTER_VALIDATE_EMAIL ) ){
						$contactid = $this->ifsdk->get_contactid_by_email( $email );
						//since we are deleting the user, we wont be adding it on IFS also
					}
				}
				if ( ! $contactid ) {
					$contactid = get_user_meta( $uid, "wlifcon_contactid", true ); //wlmis contactid
				}
			}
			if ( ! $contactid ) return; //lets return if no level was found

			$data = array(
				"uid"=>$uid,
				"contactid"=>$contactid,
				"action"=>"delete",
				"levels"=>$levels
			);

			$this->ifarAddQueue($data);

			return;
		}

		function DeleteUserTagsHook( $contactid, $levels = array() ) {
			$levels = (array) $levels;
			return $this->processTags( $levels, 'remove', $contactid, null );
		}

	}
}

$ar = new WLM_AUTORESPONDER_INFUSIONSOFT_INIT();
$ar->load_hooks();