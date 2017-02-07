<style type="text/css">
	i.icon-question-sign {
		font-family: FontAwesome;
		color:#0073aa;
	}
	.desc {
		margin-top: 10px;
	}
</style>

<?php
$forms			 = $this->GetCustomRegForms();
$base_url		 = $this->QueryString( 'action', 'form_id' );
$the_formid		 = $_GET['form_id'];
$the_formname	 = ' Custom Registration Form - ' . date( 'Y-m-d H:i:s' );
foreach ( $forms AS $form ) {
	if ( $form->option_name == $the_formid ) {
		$the_formname		 = $form->option_value['form_name'];
		$the_formfields		 = $form->option_value['fields'];
		$the_formrequired	 = $form->option_value['required'];
	}
}
?>
<?php if ( wlm_arrval( $_GET, 'action' ) == 'edit' ) : ?>
<!--Custom Registration Form Editor-->
	<div style="display:none" id="default_form">
		<?php echo $this->get_legacy_registration_form( $the_formid, '', true ); ?>
	</div>

	<form class="edit_form" onsubmit="return false;">
		<table class="form-table custom-regform-table" style="width:1px;">
			<tr valign="top">
				<th scope="row">Form Name</th>
				<td><input type="text" name="form_name" value="<?php echo $the_formname; ?>" style="width:100%" /></td>
			</tr>
			<tr valign="top">
				<th scope="row" style="background:#eee; border: 1px solid #aaa;text-align:center" colspan="2"><h3 style="margin:0;padding:0">Custom Registration Form Editor</h3></th>
			</tr>
			<tr valign="top">
				<td colspan="2" style="border: 1px solid #aaa;text-align:center">
					Drag and drop the desired fields from the list on the left to customize the registration form.
				</td>
			</tr>
			<tr valign="top">
				<th scope="row" style="background:#eee; border-left: 1px solid #aaa; border-bottom: 1px solid #aaa;">
			<div class="registration_form_choices">
				<h3 onclick="fields_toggle(this, '#div_reg_form_fields')">
					<span>&#9662;</span>
					Standard Fields
				</h3>
				<div id="div_reg_form_fields" class="registration_form_fields">
					<table id="reg_form_fields" class="reg_form_draggables">
						<tr fld_type="field_text"><td>Text</td></tr>
						<tr fld_type="field_textarea"><td>Text Box</td></tr>
						<tr fld_type="field_select"><td>Dropdown List</td></tr>
						<tr fld_type="field_radio"><td>Radio Buttons</td></tr>
						<tr fld_type="field_checkbox"><td>Checkboxes</td></tr>
						<tr fld_type="field_hidden"><td>Hidden Field</td></tr>
					</table>
					<hr />
				</div>
				<h3 onclick="fields_toggle(this, '#div_reg_form_fields_wp')">
					<span>&#9656;</span>
					WordPress Profile Fields
				</h3>
				<div id="div_reg_form_fields_wp" class="registration_form_fields hidden">
					<table id="reg_form_fields_wp" class="reg_form_draggables">
						<tr fld_type="field_wp_firstname"><td>First Name</td></tr>
						<tr fld_type="field_wp_lastname"><td>Last Name</td></tr>
						<tr fld_type="field_wp_nickname"><td>Nickname</td></tr>
						<tr fld_type="field_wp_website"><td>Website</td></tr>
						<tr fld_type="field_wp_aol"><td>AIM</td></tr>
						<tr fld_type="field_wp_yim"><td>Yahoo IM</td></tr>
						<tr fld_type="field_wp_jabber"><td>Jabber / Google Talk</td></tr>
						<tr fld_type="field_wp_biography"><td>Biographical Info</td></tr>
					</table>
					<hr />
				</div>
				<h3 onclick="fields_toggle(this, '#div_reg_form_address_fields')">
					<span>&#9656;</span>
					WishList Member Address Fields
				</h3>
				<div id="div_reg_form_address_fields" class="registration_form_fields hidden">
					<table id="reg_form_address_fields" class="reg_form_draggables">
						<tr fld_type="field_wp_company"><td>Company</td></tr>
						<tr fld_type="field_wp_address1"><td>Address (First Line)</td></tr>
						<tr fld_type="field_wp_address2"><td>Address (Second Line)</td></tr>
						<tr fld_type="field_wp_city"><td>City</td></tr>
						<tr fld_type="field_wp_state"><td>State</td></tr>
						<tr fld_type="field_wp_zip"><td>Zip Code</td></tr>
						<tr fld_type="field_wp_country"><td>Country</td></tr>
					</table>
					<hr />
				</div>
				<h3 onclick="fields_toggle(this, '#div_reg_form_fields_special')">
					<span>&#9656;</span>
					Special Fields
				</h3>
				<div id="div_reg_form_fields_special" class="registration_form_fields hidden">
					<table id="reg_form_fields_special" class="reg_form_draggables">
						<tr fld_type="field_tos"><td>Terms of Service</td></tr>
						<tr fld_type="field_special_header"><td>Section Header</td></tr>
						<tr fld_type="field_special_paragraph"><td>Paragraph Text</td></tr>
					</table>
				</div>
			</div>
			</th>
			<td id="the_form_itself">
			</td>
			<td align="left"></td>
			</tr>
		</table>
		<h2 style="line-height:1px;font-size:1px;width:100%">&nbsp;</h2>
		<p class="submit">
			<input type="button" class="button-primary" value="Save" onclick="save_registration_form(this.form)" />
			<input type="button" class="button-secondary" value="Reset Form" onclick="if (confirm('Are you sure want to reset this registration form?'))
							jQuery('#the_form_itself table.wpm_registration').html(origFormState)" />
		</p>
	</form>

	<div style="display:none">
		<form method="POST" id="regform_submit_data">
			<input type="text" name="WishListMemberAction" value="SaveCustomRegForm" />
			<input type="text" name="form_id" value="<?php echo $the_formid; ?>" />
			<input type="text" name="form_name" value="<?php echo $the_formname; ?>" />
			<input type="text" name="form_fields" value="<?php echo $the_formfields; ?>" />
			<input type="text" name="form_required" value="<?php echo $the_formrequired; ?>" />
			<textarea name="rfdata"></textarea>
		</form>
	</div>

	<table id="edit_form_div" style="display:none">
		<tr class="edit_form_div"><td colspan="3" class="edit_form_container">
				<div class="animatedDiv" style="display:none;width:100%">
					<h3>Edit Field</h3>
					<form onsubmit="return false" class="edit_form">
						<table>
							<tr class="edit_form_label">
								<th scope="row">Label</th>
								<td>
									<input class="label" type="text" name="label" value="" />
									<?php echo $this->Tooltip( "settings-registration-custom-tooltips-label" ); ?>
								</td>
							</tr>
							<tr class="edit_form_name">
								<th scope="row">Name</th>
								<td>
									<input class="name" size="30" type="text" name="name" value="" />
									<?php echo $this->Tooltip( "settings-registration-custom-tooltips-name" ); ?>
								</td>
							</tr>
							<tr class="edit_form_default">
								<th scope="row">Default</th>
								<td>
									<input class="default" type="text" name="default" size="40" value="" />
									<?php echo $this->Tooltip( "settings-registration-custom-tooltips-default" ); ?>
								</td>
							</tr>
							<tr class="edit_form_list">
								<th scope="row">Items</th>
								<td>
									<textarea class="value" name="value" cols="40" rows="4"></textarea>
								</td>
								<td>
									<?php echo $this->Tooltip( "settings-registration-custom-tooltips-checkbox-items" ); ?>
								</td>
							</tr>
							<tr class="edit_form_width">
								<th scope="row">Width</th>
								<td>
									<input class="width" type="text" name="width" value="" size="4" />
									<?php echo $this->Tooltip( "settings-registration-custom-tooltips-width" ); ?>
								</td>
							</tr>
							<tr class="edit_form_height">
								<th scope="row">Height</th>
								<td>
									<input class="height" type="text" name="height" value="" size="4" />
									<?php echo $this->Tooltip( "settings-registration-custom-tooltips-height" ); ?>
								</td>
							</tr>
							<tr class="edit_form_desc">
								<th scope="row">Description</th>
								<td>
									<textarea class="desc" name="desc" cols="40" rows="4" id="description"></textarea>
								</td>
								<td>
									<?php echo $this->Tooltip( "settings-registration-custom-tooltips-description" ); ?>
								</td>
							</tr>
							<tr class="edit_form_required">
								<th scope="row"></th>
								<td>
									<label><input type="checkbox" name="required" class="required" value="1"> <span>Required Field</span></label>
									<?php echo $this->Tooltip( "settings-registration-custom-tooltips-required" ); ?>
								</td>
							</tr>
							<tr class="edit_form_buttons">
								<th scope="row"></th>
								<td class="submit" align="right">
									<a href="javascript:;" onclick="wpm_insertHTML('[wlm_min_passlength]', 'description')"><?php _e( 'Insert Minimum Password Length Mergecode', 'wishlist-member' ); ?></a><br /><br/>
									<div style="float:left">
										<input type="button" class="button buttonSave" value="Close" />
										<?php echo $this->Tooltip( "settings-registration-custom-tooltips-close" ); ?>
									</div>
									<input type="button" class="button buttonClone" value="Clone" />
									<input type="button" class="button buttonDelete" value="Delete" />
									<?php echo $this->Tooltip( "settings-registration-custom-tooltips-clone-delete" ); ?>
								</td>
							</tr>
						</table>
					</form>
				</div>
			</td></tr>
	</table>
<?php else : ?>
	<!--List Custom Registration Forms-->

	<div style="width: 750px;">
		<p style="text-align: left;margin-right:10px;">
			<a href="?<?php echo $base_url; ?>&action=edit"><?php _e( 'Create New Form', 'wishlist-member' ); ?></a>
		</p>
		<?php if ( count( $forms ) ): ?>
			<table class="widefat">
				<col />
				<col align="right" width="100" />
				<thead>
					<tr>
						<th>Form Name</th>
						<th>Actions</th>
					</tr>
				</thead>
				<tbody>
					<?php
					foreach ( $forms AS $form ) {
						$form_id = $form->option_name;
						extract( $form->option_value );
						?>
						<tr class="<?php echo $alt ++ % 2 ? '' : 'alternate'; ?>">
							<td><?php echo $form_name; ?></td>
							<td style="white-space:nowrap">
								<a href="?<?php echo $base_url; ?>&action=edit&form_id=<?php echo $form_id; ?>">Edit</a>
								&nbsp;
								<a onclick="do_wlm_reg_form_action('CloneCustomRegForm', '<?php echo $form_id; ?>');" href="javascript:;">Clone</a>
								&nbsp;
								&nbsp;
								&nbsp;
								<a style="color:red;" onclick="if (confirm('Are you sure you want to delete this form?')) {
														do_wlm_reg_form_action('DeleteCustomRegForm', '<?php echo $form_id; ?>')
													}" href="javascript:;">Delete</a>
							</td>
						</tr>
						<?php
					}
					?>
				</tbody>
			</table>
			<form method="POST" id="wlm_action_form">
				<input type="hidden" name="WishListMemberAction" value="" />
				<input type="hidden" name="form_id" />
			</form>
		</div>
	<?php endif; ?>
<?php endif; ?>