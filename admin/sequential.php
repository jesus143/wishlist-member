<?php if ( $show_page_menu ) : ?>
	<?php
	return;
endif;
?>
<?php
$wpm_levels = $this->GetOption( 'wpm_levels' );
// sort membership levels according to Level Order
$this->SortLevels( $wpm_levels, 'a', 'levelOrder' );
$err_levels = $_POST ? $_POST['err_levels'] : array();
$this->set_timezone_to_wp();
?>
<h2><?php _e( 'Sequential Upgrade', 'wishlist-member' ); ?></h2>
<br />
<?php
if(isset($_POST['sequential_err_msg'])) {
	echo '<div class="error fade"> '.$_POST['sequential_err_msg'].'<br><br></div>';
}
?>
<form method="post">
	<table class="widefat wlm_sequential">
		<thead>
			<tr>
				<th scope="row" style="line-height:20px;"><?php _e( 'Membership Level', 'wishlist-member' ); ?></th>
				<th scope="row" style="line-height:20px;"><?php _e( 'Method', 'wishlist-member' ); ?> <?php echo $this->Tooltip( "sequential-tooltips-Method" ); ?></th>
				<th scope="row" style="line-height:20px;"><?php _e( 'Upgrade To', 'wishlist-member' ); ?> <?php echo $this->Tooltip( "sequential-tooltips-Upgrade-To" ); ?></th>
				<th scope="row" style="line-height:20px;"><?php _e( 'Schedule', 'wishlist-member' ); ?> <?php echo $this->Tooltip( "sequential-tooltips-After" ); ?></th>
				<th scope="row" style="line-height:20px;text-align:right"><?php _e( 'Status', 'wishlist-member' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ( ( array ) $wpm_levels AS $key => $level ): ?>
				<?php
				$level_error = '';
				if ( ! empty( $err_levels[$key] ) ) {
					$level_error                 = ' ' . implode( ' ', array_unique( $err_levels[$key] ) ) . ' ';
					$level['upgradeMethod']      = $_POST['upgradeMethod'][$key];
					$level['upgradeTo']          = $_POST['upgradeTo'][$key];
					$level['upgradeSchedule']    = $_POST['upgradeSchedule'][$key];
					$level['upgradeAfter']       = $_POST['upgradeAfter'][$key];
					$level['upgradeAfterPeriod'] = $_POST['upgradeAfterPeriod'][$key];
					$level['upgradeOnDate']      = strtotime( $_POST['upgradeOnDate'][$key] );
				}
				?>
				<tr class="<?php echo $level_error;
			echo $alt ++ % 2 ? '' : 'alternate';
				?>">
					<td style="line-height:2em;"><b><?php echo $level['name']; ?></b></td>
					<td class="wlm_sequential_upgrade_method">
						<select name="upgradeMethod[<?php echo $key; ?>]" class="select2" style="width:130px;" >
							<?php
							if ( ! $level['upgradeTo'] || ! $level['upgradeMethod'] || ($level['upgradeSchedule'] == 'ondate' && $level['upgradeOnDate'] < 1) || ($level['upgradeMethod'] == 'MOVE' && ! (( int ) $level['upgradeAfter']) && empty( $level['upgradeSchedule'] )) ) {
								?>
								<option value="0"><?php _e( 'Select Method', 'wishlist-member' ); ?></option>
								<?php
							} else {
								?>
								<option value="inactive"><?php _e( 'Inactive', 'wishlist-member' ); ?></option>
								<?php
							}
							?>

							<option value="MOVE" <?php $this->Selected( 'MOVE', $level['upgradeMethod'] ); ?>><?php _e( 'Move', 'wishlist-member' ); ?></option>
							<option value="ADD" <?php $this->Selected( 'ADD', $level['upgradeMethod'] ); ?>><?php _e( 'Add', 'wishlist-member' ); ?></option>

						</select>
					</td>
					<td class="wlm_sequential_upgrade_to">
						<select name="upgradeTo[<?php echo $key; ?>]" class="select2" style="width: 150px;">
							<option value="0"><?php _e( 'Select Level', 'wishlist-member' ); ?></option>
							<?php foreach ( ( array ) array_keys( ( array ) $wpm_levels ) AS $k ): if ( $k != $key ): ?>
									<option value="<?php echo $k; ?>" <?php $this->Selected( $k, $level['upgradeTo'] ); ?>><?php echo $wpm_levels[$k]['name']; ?></option>
									<?php
								endif;
							endforeach;
							?>
						</select>
					</td>
					<td style="line-height:2em" class="wlm_sequential_upgrade_schedule">
						<?php
						if ( $level['upgradeSchedule'] == 'ondate' ) {
							$ondate_hidden = '';
						} else {
							$ondate_hidden = ' style="display:none"';
						}
						?>
						<select class="upgrade_schedule_select" data-select-key="<?php echo $key; ?>" name="upgradeSchedule[<?php echo $key; ?>]" onchange="wlm_select_sequpgrade_schedule(this.value, '<?php echo $key; ?>')" >
							<option value=""><?php _e( 'After', 'wishlist-member' ); ?></option>
							<option value="ondate" <?php $this->Selected( 'ondate', $level['upgradeSchedule'] ); ?>><?php _e( 'On', 'wishlist-member' ); ?></option>
						</select>
						<span id="sequpgrade_schedule_<?php echo $key; ?>">
							<span class="sequpgrade_schedule sequpgrade_schedule_" style="display:none">
								<input type="number" name="upgradeAfter[<?php echo $key; ?>]" value="<?php echo ( int ) $level['upgradeAfter']; ?>" style="width:50px;" size="3" min="0" />
								<select name="upgradeAfterPeriod[<?php echo $key; ?>]" class="select2" style="width:100px;" >
									<option value=""><?php _e( 'Day/s', 'wishlist-member' ); ?></option>
									<option value="weeks" <?php $this->Selected( 'weeks', $level['upgradeAfterPeriod'] ); ?>><?php _e( 'Week/s', 'wishlist-member' ); ?></option>
									<option value="months" <?php $this->Selected( 'months', $level['upgradeAfterPeriod'] ); ?>><?php _e( 'Month/s', 'wishlist-member' ); ?></option>
									<option value="years" <?php $this->Selected( 'years', $level['upgradeAfterPeriod'] ); ?>><?php _e( 'Year/s', 'wishlist-member' ); ?></option>
								</select>
							</span>
							<span class="sequpgrade_schedule sequpgrade_schedule_ondate" style="display:none">
								<input class="seq_upgrade_ondate" name="upgradeOnDate[<?php echo $key; ?>]" value="<?php echo $level['upgradeOnDate'] ? date( 'm/d/Y h:i a', $level['upgradeOnDate'] ) : ''; ?>" size="24" placeholder="mm/dd/yyyy hh:mm" />
							</span>
						</span>
					</td>
					<td style="line-height:2em;text-align:right">
						<?php
						if ( ! $level['upgradeTo'] || ! $level['upgradeMethod'] || ($level['upgradeSchedule'] == 'ondate' && $level['upgradeOnDate'] < 1) || ($level['upgradeMethod'] == 'MOVE' && ! (( int ) $level['upgradeAfter']) && empty( $level['upgradeSchedule'] )) ) {
							_e( 'Inactive', 'wishlist-member' );
						} else {
							_e( 'Active', 'wishlist-member' );
						}
						?>
					</td>
				</tr>
<?php endforeach; ?>
		</tbody>
	</table>
	<p class="submit">
		<input type="hidden" name="WishListMemberAction" value="SaveSequential" />
		<input type="submit" class="button-primary" value="Save Sequential Upgrade" />
	</p>
</form>
<h2 style="font-size:18px;width:100%;border:none;"><?php _e( 'Advanced Settings:', 'wishlist-member' ); ?></h2>
<p><?php _e( 'Sequential Upgrades are automatically triggered when a member signs in to their account. If you would like to set your system to trigger upgrades without requiring a member to sign in, you must create a Cron Job on your server.', 'wishlist-member' ); ?></p>
<p>
	<?php
	$link = $this->GetMenu( 'settings' );
	printf( __( '<a href="%1$s" target="_blank">Click Here</a> for instructions on how to set-up a Cron Job for WishList Member.', 'wishlist-member' ), $link->URL . '&mode2=cron' );
	?>
</p>
<script>

	jQuery(document).ready(function($) {
     	jQuery('.select2').select2({
     		allowClear: true,
			minimumResultsForSearch: -1,
			width: 'copy',
			dropdownCssClass: 'select2-nowrap',
			dropdownAutoWidth: true
		});
    });

	function wlm_select_sequpgrade_schedule(sched, seqkey) {
		seqid = "sequpgrade_schedule_" + seqkey;
		jQuery('#' + seqid + " .sequpgrade_schedule").hide();
		jQuery('#' + seqid + " .sequpgrade_schedule_" + sched).show();
	}
	jQuery(function() {
		jQuery(".seq_upgrade_ondate").datetimepicker(
				{
					dateFormat: "mm/dd/yy",
					timeFormat: "hh:mm tt"
				}
		);
		jQuery('select.upgrade_schedule_select').each(function(c, o) {
			wlm_select_sequpgrade_schedule(jQuery(o).attr('value'), jQuery(o).attr('data-select-key'));
		});
	});

</script>
<?php
include_once($this->pluginDir . '/admin/tooltips/sequential.tooltips.php');
?>