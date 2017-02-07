<?php
/*
 * Export Members
 */
?>
<h2><?php _e('Members &raquo; Export Members', 'wishlist-member'); ?></h2>
<p><?php _e('Export Members as a .CSV file by selecting the appropriate Membership Level(s) and settings below.', 'wishlist-member'); ?></p>
<form method="post" id="export-form">
	<table class="form-table">
		<tr valign="top">
			<th scope="row"><?php _e('Membership Level', 'wishlist-member'); ?></th>
			<td>
				<select data-placeholder="Select a level..." multiple="multiple" class="select_mlevels" name="wpm_to[]" >
					<option class="select_all" value="select_all" >Select All</option>
					<?php foreach ($wpm_levels as $id => $level): ?>
                    <option value="<?php echo $id; ?>"><?php echo $level['name']; ?></option>
					<?php endforeach; ?>
					<option value="nonmember">Non-Members</option>
				</select> <?php echo $this->Tooltip("members-export-tooltips-Export-Members"); ?>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row">
				<?php _e('Additional Options', 'wishlist-member'); ?>
			</th>
			<td>
				<label><input type="checkbox" name="full_data_export" value="1" /> <?php _e('Export Full Data', 'wishlist-member'); ?></label><br />
				<label><input type="checkbox" name="include_password" value="1" /> <?php _e('Include Password (Encrypted)', 'wishlist-member'); ?></label><br />
				<label><input type="checkbox" name="include_inactive" value="1" /> <?php _e('Include Inactive Members', 'wishlist-member'); ?></label>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row">
				<?php _e('Member Portion', 'wishlist-member'); ?>
			</th>
			<td>
				<input type="text" onkeypress='return event.charCode >= 48 && event.charCode <= 57' name="per_page" value="1000"/> <?php echo $this->Tooltip("members-export-tooltips-default-export-per-chunk"); ?>
				<input type="hidden" class="current_page" name="current_page" value="0"/>
				<input type="hidden" name="nonce" value="<?php echo wp_create_nonce('export-chunked' . microtime());?>"/>
				<input type="hidden" name="tempname" value="<?php echo tempnam(sys_get_temp_dir(), 'export-chunked-' . $nonce);?>" />
			</td>
		</tr>
		<tr valign="top" class="export-status" style="display:none;">
			<th scope="row">
				<?php _e('Export Status', 'wishlist-member'); ?>
			</th>
			<td>
				<div class="export-progress">
					Exported <span class="export-low"></span> to <span class="export-high"></span> of <span class="export-total"></span>
					<p class="warning"><?php _e("Please do not close your browser while the export is ongoing", "wishlist-member")?></p>
				</div>
				<div class="export-done">
					Export complete.
				</div>
			</td>
		</tr>
	</table>
	<p class="submit">
		<input type="hidden" name="WishListMemberAction" value="ExportMembersChunked" />
	</p>
</form>

<input type="submit" class="start-export button-primary" value="<?php _e('Export Members', 'wishlist-member'); ?>" />

<style type="text/css">
.warning {
	color: red;
}
.export-done {
	display:none;
	color: green;
}
</style>
<script type="text/javascript">
	jQuery(document).ready(function(){

	jQuery('.select_mlevels').chosen({width:'40%'});

	jQuery('.select_mlevels').chosen().change(function(){
		$str_selected = jQuery(this).val();
		if($str_selected != null){
			$pos = $str_selected.lastIndexOf("select_all");
			if($pos >= 0){
				jQuery(this).find('option').each(function() {
					if(jQuery(this).val() == "select_all"){
						jQuery(this).prop("selected",false);
					}else{
						jQuery(this).prop("selected","selected");
					}
					jQuery(this).trigger("liszt:updated");
				});

			}
		}
	});

	jQuery(function($) {
		var exporting = false;
		var form = $('#export-form');

		var export_members = function() {
			exporting = true;
			$('.export-done').hide();
			$('.export-progress').show();
			$.post(form.prop('action'), form.serialize(), function(res) {
				response = JSON.parse(res);
				$('.export-total').html(response.total);
				$('.export-low').html( response.current_page * response.per_page+1);
				$('.export-high').html( (response.current_page * response.per_page) + response.exported );
				$('.current_page').val( Number($('.current_page').val()) + 1);

				if(response.error) {
					$('.export-status').hide();
					return alert(response.error);
				}
				if(response.has_more) {
					$('.export-status').show();
					export_members();
				} else {
					//$('.export-status').hide();
					$('.export-done').show();
					$('.export-progress').hide();
					exporting = false;
					form.submit();
				}
			});
		}

		$('.start-export').on('click', function() {
			$('.current_page').val(0);
			export_members();
		});



		window.addEventListener("beforeunload", function (e) {
			if(exporting == true) {
				var confirmationMessage = "<?php _e("Leaving this page will cancel the current export", "wishlist-member")?>";
				(e || window.event).returnValue = confirmationMessage;
				return confirmationMessage;
			}
		});
	});
});
</script>