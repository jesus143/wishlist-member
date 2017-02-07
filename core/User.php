<?php

/**
 * User Class for WishList Member
 * @author Mike Lopez <mjglopez@gmail.com>
 * @package wishlistmember
 *
 * @version $Rev: 3101 $
 * $LastChangedBy: mike $
 * $LastChangedDate: 2016-09-13 11:39:16 -0400 (Tue, 13 Sep 2016) $
 */
if (!defined('ABSPATH'))
	die();

if (!class_exists('WishListMemberUser')) {
	require_once(ABSPATH . '/wp-admin/includes/user.php');

	/**
	 * WishList Member User Class
	 * Keeps all membership information in one place
	 *
	 * @package wishlistmember
	 * @subpackage classes
	 */
	class WishListMemberUser {

		/**
		 * User ID
		 * @var integer
		 */
		var $ID;

		/**
		 * User information
		 * @var WP_User object
		 */
		var $UserInfo;

		/**
		 * Sequential Upgrade setting
		 * @var boolean
		 */
		var $Sequential;

		/**
		 * Membership Levels
		 * @var array
		 */
		var $Levels = array();
		
		/**
		 * Pay Per Posts
		 * @var array
		 */
		var $PayPerPosts = array();

		/**
		 * Pointer to $WishListMemberInstance
		 * @var object
		 */
		var $WL;

		/**
		 * Constructor
		 */
		function __construct($ID, $loadUser = null) {
			global $wpdb, $WishListMemberInstance;

			$this->WL = &$WishListMemberInstance;
			
			/*
			 * if $ID is not numeric then it might be an email address or a username
			 */
			if(!is_numeric($ID)) {
				$x = false;
				if(filter_var($ID, FILTER_VALIDATE_EMAIL)) {
					$x = get_user_by('email', $ID);
				}
				if(!$x) {
					$x = get_user_by('login', $ID);
				}
				if($x) {
					$ID = $x->ID;
				}
			}

			// verify User ID
			$ID+=0;
			$ID = $wpdb->get_var("SELECT `ID` FROM `{$wpdb->users}` WHERE `ID`={$ID}");
			if (!$ID)
				return false;

			// ID verified, save it
			$this->ID = $ID;

			// load user information if requested
			if ($loadUser === true) {
				$this->LoadUser();
			}

			// sequential setting
			$this->Sequential = $this->WL->IsSequential($this->ID);

			$this->LoadLevels();
			
			$ppps = $this->WL->GetUser_PayPerPost($this->ID, true);
			foreach($ppps AS $ppp) {
				$this->PayPerPosts[$ppp->type][] = $ppp->content_id;
				$this->PayPerPosts['_all_'][] = $ppp->content_id;
			}

			return true;
		}

		/**
		 * Loads user information as returned by WP_User object
		 */
		function LoadUser() {
			$this->UserInfo = $this->WL->Get_UserData($this->ID);
		}

		/**
		 * Loads membership levels including their
		 * - Status (Cancelled, Pending, UnConfirmed)
		 * - Timestamp
		 * - Transaction ID
		 */
		function LoadLevels() {
			$wpm_levels = $this->WL->GetOption('wpm_levels');
			$levels = $this->WL->GetMembershipLevels($this->ID);
			$this->Levels = array();
			foreach ($levels AS $level) {
				if ($wpm_levels[$level]) {
					$this->Levels[$level] = new stdClass();
					$this->Levels[$level]->Level_ID = $level;
					$this->Levels[$level]->Name = $wpm_levels[$level]['name'];
					$this->Levels[$level]->Cancelled = $cancelled = $this->WL->LevelCancelled($level, $this->ID);
					$this->Levels[$level]->CancelDate = $this->WL->LevelCancelDate($level, $this->ID);
					$this->Levels[$level]->Pending = $pending = $this->WL->LevelForApproval($level, $this->ID);
					$this->Levels[$level]->UnConfirmed = $unconfirmed = $this->WL->LevelUnConfirmed($level, $this->ID);
					$this->Levels[$level]->Expired = $expired = $this->WL->LevelExpired($level, $this->ID);
					$this->Levels[$level]->ExpiryDate = $this->WL->LevelExpireDate($level, $this->ID);
					$this->Levels[$level]->SequentialCancelled = $sequentialcancelled = $this->WL->LevelSequentialCancelled($level, $this->ID);
					$this->Levels[$level]->Scheduled = (bool) $scheduled = $this->WL->IsLevelScheduled($level, $this->ID);
					$this->Levels[$level]->ScheduleInfo = maybe_unserialize($scheduled);
					$this->Levels[$level]->ParentLevel = $this->WL->LevelParent($level, $this->ID);
					$pending = ($pending) ? true : false;
					$this->Levels[$level]->Active = $active = !($cancelled | $pending | $unconfirmed | $expired | (bool) $scheduled);
					if ($active) {
						$this->Levels[$level]->Status = array(__('Active'));
					} else {
						$statusNames = array();
						if ($unconfirmed)
							$statusNames[] = __('Unconfirmed');
						if ($pending)
							$statusNames[] = __('For Approval');
						if ($cancelled)
							$statusNames[] = __('Cancelled');
						if ($scheduled)
							$statusNames[] = __('Scheduled');
						if ($expired === true)
							$statusNames[] = __('Expired');
						$this->Levels[$level]->Status = $statusNames;
					}
				}
			}

			// timestamps
			$ts = $this->WL->UserLevelTimestamps($this->ID);
			foreach ($ts AS $level => $time) {
				if ($this->Levels[$level])
					$this->Levels[$level]->Timestamp = $time;
			}

			// transaction IDs
			$txns = $this->WL->GetMembershipLevelsTxnIDs($this->ID);
			foreach ($txns AS $level => $txn) {
				if ($this->Levels[$level])
					$this->Levels[$level]->TxnID = $txn;
			}
		}
		
		/**
		 * Adds Level to user obj in RAM.
		 * @param integer $levelID
		 * 
		 */
		function AddLevelobj($level){
			
			//$this->Levels[$level] = new stdClass();
			$this->Levels[$level]->Level_ID = $level;
			$this->Levels[$level]->Name = "Name";
			$this->Levels[$level]->Cancelled = "NULL";
			$this->Levels[$level]->CancelDate = FALSE;
			$this->Levels[$level]->Pending = NULL;
			$this->Levels[$level]->UnConfirmed = NULL;
			$this->Levels[$level]->Expired = FALSE;
			$this->Levels[$level]->ExpiryDate = FALSE;
			$this->Levels[$level]->SequentialCancelled = NULL;
			$this->Levels[$level]->Active = TRUE;
			$this->Levels[$level]->Status =	array(__('Active'));
			$this->Levels[$level]->Timestamp =	"" ;
			$this->Levels[$level]->TxnID =	"";
			
		}

		/**
		 * Adds user to Level
		 * @param integer $levelID
		 * @param string $TransactionID
		 */
		function AddLevel($levelID, $TransactionID) {
			$x = $this->WL->GetMembershipLevels($this->ID);
			$x[] = $levelID;
			$this->WL->SetMembershipLevels($this->ID, $x);

			// transaction id
			$this->WL->SetMembershipLevelTxnID($this->ID, $levelID, $TransactionID);

			// reload levels
			$this->LoadLevels();
		}

		/**
		 * Removes user from Level
		 * @param integer $levelID
		 */
		function RemoveLevel($levelID) {
			$x = array_unique($this->WL->GetMembershipLevels($this->ID));

			// reset level statuses
			$this->UnCancelLevel($levelID);
			$this->ApproveLevel($levelID);
			$this->Confirm($levelID);

			// remove level
			$k = array_search($levelID, $x);
			if ($k !== false)
				unset($x[$k]);

			// save it
			$this->WL->SetMembershipLevels($this->ID, $x);

			// reload levels
			$this->LoadLevels();
		}

		/**
		 * Execute sequential upgrade for user
		 */
		function RunSequentialUpgrade() {
			$this->DoSequential($this->ID);
		}

		/**
		 * Cancel Membership Level
		 * @param integer $levelID
		 */
		function CancelLevel($levelID) {
			$this->Levels[$levelID]->Cancelled = $this->WL->LevelCancelled($levelID, $this->ID, true);
		}

		/**
		 * UnCancel Level
		 * @param integer $levelID
		 */
		function UnCancelLevel($levelID) {
			$this->Levels[$levelID]->Cancelled = $this->WL->LevelCancelled($levelID, $this->ID, false);
		}

		/**
		 * Approve Membership Level
		 * @param integer $levelID
		 */
		function ApproveLevel($levelID) {
			$this->Levels[$levelID]->Pending = $this->WL->LevelForApproval($levelID, $this->ID, false);
		}

		/**
		 * UnApprove Membership Level
		 * @param integer $levelID
		 */
		function UnApproveLevel($levelID) {
			$this->Levels[$levelID]->Pending = $this->WL->LevelForApproval($levelID, $this->ID, true);
		}

		/**
		 * Confirm Membership Level (Used in Email Confirmation)
		 * @param integer $levelID
		 */
		function Confirm($levelID) {
			$this->Levels[$levelID]->UnConfirmed = $this->WL->LevelUnConfirmed($levelID, $this->ID, false);
		}

		/**
		 * Confirm user's membership level registration by hash
		 * @param string $hash Hash Key
		 * @return mixed Level ID on success or FALSE on error
		 */
		function ConfirmByHash($hash) {
			$email = $this->UserInfo->user_email;
			$username = $this->UserInfo->user_login;
			$key = $this->WL->GetAPIKey();
			foreach ($this->Levels AS $levelID => $level) {
				$h = md5("{$email}__{$username}__{$levelID}__{$key}");
				if ($h == $hash && $level->UnConfirmed) {
					$this->Confirm($levelID);
					return $levelID;
				}
			}
			return false;
		}

		/**
		 * UnConfirm Membership Level (Used in Email Confirmation)
		 * @param integer $levelID
		 */
		function UnConfirm($levelID) {
			$this->Levels[$level]->UnConfirmed = $this->WL->LevelUnConfirmed($levelID, $this->ID, true);
		}

		/**
		 * Enable Sequential Upgrade for User
		 */
		function EnableSequential() {
			$this->Sequential = $this->WL->IsSequential($this->ID, true);
		}

		/**
		 * Disable Sequential Upgrade for User
		 */
		function DisableSequential() {
			$this->Sequential = $this->WL->IsSequential($this->ID, false);
		}

		function IsExpired($level) {
			return $this->Levels[$level]->Expired === true;
		}

		function ExpireDate($level) {
			if ($this->Levels[$level]->Expired === false) {
				return false;
			} else {
				
			}
		}

		/**
		 * Executes the "Remove From Level" & "Add To Level" features
		 * 
		 * @param array $new_levels New Levels to which the user was added
		 * @param array $removed_levels Levels that were removed
		 */
		function DoAddRemove($new_levels, $removed_levels) {
			$current_levels = array_keys($this->Levels);
			$new_levels = (array) $new_levels;
			$removed_levels = (array) $removed_levels;
			$wpm_levels = $this->WL->GetOption('wpm_levels');
			$remove = $add = array();
			foreach ( $new_levels AS $level ) {

				if ( isset( $wpm_levels[$level] ) ) { //make sure that level is existing and active

					//get levels to remove or add
					$to_remove = array_keys((array) $wpm_levels[$level]['removeFromLevel']);
					$to_add    = array_keys((array) $wpm_levels[$level]['addToLevel']);
					// // we don't remove the newly added levels
					// $to_remove = array_diff($to_remove, $new_levels);
					// // we don't add the newly removed levels
					// $to_add = array_diff($to_add, $removed_levels);

					foreach ( $to_remove as $tr ) {
						if ( in_array( $tr, $current_levels ) ) {//only remove levels that this user currently have
							$remove[$tr] = $level;
						}
					}

					foreach ( $to_add as $ta ) {
						if ( ! in_array( $ta, $current_levels ) ) {//only add levels that this user does not have
							if ( array_key_exists( $ta, $add ) ) {//if this level is for add already, check level priority
								if ( $wpm_levels[$level]['levelOrder'] > $wpm_levels[$add[$ta]]['levelOrder'] ) {
									$add[$ta] = $level;
								}
							} else {
								$add[$ta] = $level;
							}
						}
					}
				}
			}

			$to_add_levels = array_keys( $add );
			$to_remove_levels = array_keys( $remove );
			if( count( $to_add_levels ) <= 0 && count( $to_remove_levels ) <= 0 ) return; //nothing to do here

			// we merge current levels with levels to be automatically added
			// and then we remove the remainings levels that are to be automatically removed
			$levels = array_unique(array_diff(array_merge($current_levels, $to_add_levels), $to_remove_levels));
			// we update the levels

			$xLevels = array('Levels' => array_unique($levels), 'To_Removed_Levels'=>array_unique($to_remove_levels), 'Metas' => array());
			foreach ( $levels as $key => $lvl ) {
				if ( isset( $add[$lvl] ) ) { //if this level is newly added, we add parent meta
					$xLevels['Metas'][$lvl] = array( array( 'parent_level', $add[$lvl] ) );
				}
			}
			$x = $this->WL->SetMembershipLevels($this->ID, (object) $xLevels);
		}
	}

}
?>
