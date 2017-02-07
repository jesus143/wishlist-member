<?php
$custom_types = get_post_types(array('_builtin' => false), 'objects');
if ($custom_types):
	?>
	<p><?php _e('Allow WishList Member to manage the protection for the selected Custom Post Types:', 'wishlist-member'); ?></p>
	<blockquote>
		<form method="post">
			<table class="widefat" style="width:350px">
				<thead>
					<tr>
						<th class="">
							<input type="checkbox" onclick="jQuery('.checkboxes').attr('checked',this.checked)" />
						</th>
						<th><?php _e('Custom Post Type', 'wishlist-member'); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($custom_types AS $custom_type) : 
					
						// // Don't show BBPress Topics or Replies
						// if($custom_type->description == 'bbPress Topics' || $custom_type->description == 'bbPress Replies')	
						// 	continue;
					?>
						<tr class="<?php echo $alt++ % 2 ? '' : 'alternate'; ?>" >
							<th class="check-column">
								&nbsp;&nbsp;<input class="checkboxes" <?php
				if ($this->PostTypeEnabled($custom_type->name)) {
					echo " checked='checked' ";
				}
				?> type="checkbox" name="protected_custom_post_types[]" id="custom_post_type_<?php echo $custom_type->name; ?>" value="<?php echo $custom_type->name; ?>" /></th>
							<td><label for="custom_post_type_<?php echo $custom_type->name; ?>"><?php echo $custom_type->labels->name; ?></label></td>
						</tr>
	<?php endforeach; ?>
				</tbody>
			</table>
			<p class="submit">
				<?php
				echo '<!-- ';
				$this->Option('protected_custom_post_types');
				echo ' -->';
				$this->Options();
				$this->RequiredOptions();
				?>
				<input type="hidden" name="WishListMemberAction" value="Save" />
				<input type="submit" class="button-primary" value="<?php _e('Save', 'wishlist-member'); ?>" />
			</p>
		</form>
	</blockquote>
	<?php
else:
	?>
	<p><?php _e('There are no custom post types found', 'wishlist-member'); ?></p>
<?php
endif;
?>