<?php
/*
 * Pay Per Post SKUs Table
 * Original Author : Mike Lopez
 */
?>
<?php if (isset($ppph2)) : ?>
	<h2 class="small"><?php echo $ppph2; ?></h2>
<?php else : ?>
	<h2 class="small"><?php _e('Pay Per Post SKUs', 'wishlist-member'); ?></h2>
<?php endif; ?>

<?php if (isset($pppdesc)) : ?>
	<p><?php echo $pppdesc; ?></p>
<?php else : ?>
	<p><?php _e('The Pay Per Post SKUs specify the post that should be connected to each transaction.', 'wishlist-member'); ?></p>
<?php endif; ?>

<?php
if (!isset($ppptitle_header)) {
	$ppptitle_header = __('Post Title', 'wishlist-member');
}
if (!isset($pppsku_header)) {
	$pppsku_header = __('SKU', 'wishlist-member');
}
if (!isset($pppsku_text)) {
	$pppsku_text = '%s';
}

$xposts = $this->GetPayPerPosts(array('post_title', 'post_type'));
$post_types = get_post_types('', 'objects');
?>
<?php foreach ($xposts AS $post_type => $posts) : ?>
	<?php if(count($posts)) : ?>
	<form method="post">
	<h3 class="wlm-integration"><?php echo $post_types[$post_type]->labels->name; ?></h3>
	<table class="widefat">
		<thead>
			<tr>
				<th scope="col"><?php echo $ppptitle_header; ?></th>
				<th scope="col"><?php echo $pppsku_header; ?></th>
				<th scope="col">&nbsp;</th>
			</tr>
		</thead>
		<tbody>
			<?php $alt = 0; foreach ($posts AS $post) : $sku = 'payperpost-' . $post->ID;?>
				<tr class="<?php echo $alt++ % 2 ? '' : 'alternate'; ?>" >
					<td width="35%"><b><?php echo $post->post_title; ?></b></td>
					<td width="35%"><u style="font-size:1.2em"><?php printf($pppsku_text,$sku); ?></u></td>
					<td><a class="if_edit_tag_level ifshow" href="javascript:void(0);">[+] Edit Level Tag Settings</a></td>
				</tr>
				<tr class="<?php echo $alt++ % 2 ? '' : 'alternate'; ?> hidden" id="wpm_level_row_<?php echo $sku; ?>">					
					<td style="z-index:0;overflow:visible;">
						<p><b>When Added:</b></p>
						<p>
							Apply Tags:<br />
							<select name="istagpp_add_app<?php echo $sku; ?>[]" data-placeholder='<?php echo $tag_placeholder; ?>' style="width:300px;" class='chzn-select' multiple="multiple" >
								<?php
								foreach ($isTagsCategory as $catid => $name) {
									if (isset($isTags[$catid]) && count($isTags[$catid]) > 0) {
										asort($isTags[$catid]);
										echo "<optgroup label='{$name}'>";
										foreach ($isTags[$catid] as $id => $data) {
											$selected = "";
											if (isset($istagspp_add_app[$sku]) && in_array($data['Id'], $istagspp_add_app[$sku])) {
												$selected = "selected='selected'";
											}
											echo "<option value='{$data['Id']}' {$selected}>{$data['Name']}</option>";
										}
										echo "</optgroup>";
									}
								}
								?>
							</select>
						</p>
						<p>
							Remove Tags:<br />
							<select name="istagpp_add_rem<?php echo $sku; ?>[]" data-placeholder='<?php echo $tag_placeholder; ?>' style="width:300px;" class='chzn-select' multiple="multiple" >
								<?php
								foreach ($isTagsCategory as $catid => $name) {
									if (isset($isTags[$catid]) && count($isTags[$catid]) > 0) {
										asort($isTags[$catid]);
										echo "<optgroup label='{$name}'>";
										foreach ($isTags[$catid] as $id => $data) {
											$selected = "";
											if (isset($istagspp_add_rem[$sku]) && in_array($data['Id'], $istagspp_add_rem[$sku])) {
												$selected = "selected='selected'";
											}

											echo "<option value='{$data['Id']}' {$selected}>{$data['Name']}</option>";
										}
										echo "</optgroup>";
									}
								}
								?>
							</select>
						</p>								
					</td>
					<td style="z-index:0;overflow:visible;">
						<p><b>When Removed:</b></p>
						<p>
							Apply Tags:<br />
							<select name="istagpp_remove_app<?php echo $sku; ?>[]" data-placeholder='<?php echo $tag_placeholder; ?>' style="width:300px;" class='chzn-select' multiple="multiple" >
								<?php
								foreach ($isTagsCategory as $catid => $name) {
									if (isset($isTags[$catid]) && count($isTags[$catid]) > 0) {
										asort($isTags[$catid]);
										echo "<optgroup label='{$name}'>";
										foreach ($isTags[$catid] as $id => $data) {
											$selected = "";
											if (isset($istagspp_remove_app[$sku]) && in_array($data['Id'], $istagspp_remove_app[$sku])) {
												$selected = "selected='selected'";
											}

											echo "<option value='{$data['Id']}' {$selected}>{$data['Name']}</option>";
										}
										echo "</optgroup>";
									}
								}
								?>
							</select>
						</p>
						<p>
							Remove Tags:<br />
							<select name="istagpp_remove_rem<?php echo $sku; ?>[]" data-placeholder='<?php echo $tag_placeholder; ?>' style="width:300px;" class='chzn-select' multiple="multiple" >
								<?php
								foreach ($isTagsCategory as $catid => $name) {
									if (isset($isTags[$catid]) && count($isTags[$catid]) > 0) {
										asort($isTags[$catid]);
										echo "<optgroup label='{$name}'>";
										foreach ($isTags[$catid] as $id => $data) {
											$selected = "";
											if (isset($istagspp_remove_rem[$sku]) && in_array($data['Id'], $istagspp_remove_rem[$sku])) {
												$selected = "selected='selected'";
											}
											echo "<option value='{$data['Id']}' {$selected}>{$data['Name']}</option>";
										}
										echo "</optgroup>";
									}
								}
								?>
							</select>
						</p>			
					</td>
					<td style="z-index:0;overflow:visible;">&nbsp;</td>
				</tr>				
			<?php endforeach; ?>
		</tbody>
	</table>
	<p style="text-align:right;">
		<input type="submit" class="button-secondary" name="update_tags_pp" value="<?php _e('Update Tags Settings', 'wishlist-member'); ?>" />
	</p>	
	</form>
	<?php echo $ppp_table_end; ?>
	<?php endif; ?>
<?php endforeach; ?>