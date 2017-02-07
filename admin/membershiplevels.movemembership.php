<?php
/*
 * Membership Levels -> Move Membership Levels
 */

// count non-members
$wpdb = &$GLOBALS['wpdb'];
$nonmembers = $this->NonMemberCount();
?>
<p class="alignright" style="margin-top:0"><a href="<?php echo wlm_video_tutorial ( 'levels', 'moveadd' ); ?>" target="_blank"><?php _e('Watch Video Tutorial', 'wishlist-member'); ?></a></p>
<br />
<p><?php _e('All Members within a Membership Level can be Moved/Added to a selected Membership Level using the Move/Add method below.', 'wishlist-member'); ?></p>
<table class="widefat">
	<thead>
		<tr>
			<th scope="col"><?php _e('Membership Level', 'wishlist-member'); ?></th>
			<th scope="col" style="text-align:center"><?php _e('Members', 'wishlist-member'); ?> <?php echo $this->Tooltip("membershiplevels-movemembership-tooltips-Members-Heading"); ?></th>
			<th scope="col" style="white-space:nowrap"><?php _e('Move/Add Members', 'wishlist-member'); ?> <?php echo $this->Tooltip("membershiplevels-movemembership-tooltips-Move-Add-Heading"); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php $alt = 0;
		echo '<form method="post">';
		?>
		<?php
		$disabled = $nonmembers ? '' : ' disabled="disabled" ';
		?>
		<tr class="<?php echo $alt++ % 2 ? '' : 'alternate'; ?>">
			<td><span style="font-size:1.2em;font-weight:bold"><em><?php _e('Non-Members', 'wishlist-member'); ?></em></span></td>
			<td style="text-align:center"><?php echo $nonmembers; ?></td>
			<td>
<?php if (empty($disabled)) : ?>
					<input type="hidden" name="WishListMemberAction" value="MoveMembership" />
					<input type="hidden" name="wpm_from" value="NONMEMBERS" />
					<?php endif; ?>
				<select name="wpm_to" style="width:155px">
					<option>--- Select a Level ---</option>
					<?php foreach ((array) $wpm_levels AS $xid => $lvl): ?>
						<option value="<?php echo $xid; ?>"><?php echo $lvl['name']; ?></option>
<?php endforeach; ?>
				</select> <?php echo $this->Tooltip("membershiplevels-movemembership-tooltips-Move-Membership-Levels"); ?>
				<input <?php echo $disabled; ?> type="submit" name="wpm_move" class="button-secondary" value="Add" onclick="return confirm('Are you sure you want to ADD all non-members members to '+this.form.wpm_to[this.form.wpm_to.selectedIndex].text)" />
			</td>
		</tr>
		<?php echo '</form>'; ?>
		<?php $wpmls = $wpm_levels;
		foreach ((array) $wpm_levels AS $id => $level): echo '<form method="post">';
			?>
			<tr class="<?php echo $alt++ % 2 ? '' : 'alternate'; ?>">
				<?php
				$disabled = $level['count'] ? '' : ' disabled="disabled" ';
				if (empty($level['count'])) $level['count'] = 0;
				?>
				<td><span style="font-size:1.2em;font-weight:bold"><?php echo $level['name']; ?></span></td>
				<td style="text-align:center"><?php echo $level['count']; ?></td>
				<td width="10" style="white-space:nowrap">
					<?php if (empty($disabled)) : ?>
						<input type="hidden" name="WishListMemberAction" value="MoveMembership" />
						<input type="hidden" name="wpm_from" value="<?php echo $id; ?>" />
						<?php endif; ?>
					<select name="wpm_to" style="width:155px">
						<option>--- Select a Level ---</option>
						<?php foreach ((array) $wpmls AS $xid => $lvl): if ($xid != $id): ?>
								<option value="<?php echo $xid; ?>"><?php echo $lvl['name']; ?></option>
		<?php endif;
	endforeach;
	?>
					</select> <?php echo $this->Tooltip("membershiplevels-movemembership-tooltips-Move-Membership-Levels"); ?>
					<input <?php echo $disabled; ?> type="submit" name="wpm_add" class="button-secondary" value="Add" onclick="return confirm('Are you sure you want to ADD all <?php echo htmlentities(addslashes($level['name'])) ?> members to '+this.form.wpm_to[this.form.wpm_to.selectedIndex].text)" />
					<input <?php echo $disabled; ?> type="submit" name="wpm_move" class="button-secondary" value="Move" onclick="return confirm('Are you sure you want to MOVE all <?php echo htmlentities(addslashes($level['name'])) ?> members to '+this.form.wpm_to[this.form.wpm_to.selectedIndex].text)" />
				</td>
			</tr>
	<?php echo '</form>';
endforeach;
?>
	</tbody>
</table>
