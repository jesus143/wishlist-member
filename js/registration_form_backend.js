/*
 * Custom Registration Form Admin Interface Javascript
 */

/**
 * Original Form State
 */
var origFormState = '';

/**
 * Field Row
 */
var newRowStart='<tr class="li_fld ';
var newRowStart2='">';
var newRowEditLinks='<td class="editLinks"><div class="reqNotice">&nbsp;</div><a href="javascript:;" onclick="edit_field(this)" id="edit_field_link">&#x25bc;</a></td>';
var newRowLast='</tr>';

/**
 * Fields
 */
var fields = new Object();
fields.field_text='<td class="label">Text:</td><td class="fld_div"><input class="fld" type="text" name="text_field" size="20" /><div class="desc"></div></td>';
fields.field_textarea='<td class="label">Text Box:</td><td class="fld_div"><textarea class="fld" name="textbox_field" cols="30" rows="4"></textarea><div class="desc"></div></td>';
fields.field_select='<td class="label">Dropdown List:</td><td class="fld_div"><select class="fld" name="dropdown_field"><option>Option 1</option></select><div class="desc"></div></td>';
fields.field_radio='<td class="label">Radio Buttons:</td><td class="fld_div"><label><input class="fld" type="radio" name="radio_field" value="Radio" /> Radio</label><div class="desc"></div></td>';
fields.field_checkbox='<td class="label">Checkboxes:</td><td class="fld_div"><label><input class="fld" type="checkbox" name="checkbox_field[]" value="Checkbox" /> Checkbox</label><div class="desc"></div></td>';
fields.field_hidden='<td class="label">&nbsp;</td><td class="fld_div"><input class="fld" type="hidden" name="hidden_field" /></td>';

fields.field_wp_firstname='<td class="label">First Name:</td><td class="fld_div"><input class="fld" type="text" name="firstname" size="20" /><div class="desc"></div></td>';
fields.field_wp_lastname='<td class="label">Last Name:</td><td class="fld_div"><input class="fld" type="text" name="lastname" size="20" /><div class="desc"></div></td>';
fields.field_wp_nickname='<td class="label">Nickname:</td><td class="fld_div"><input class="fld" type="text" name="nickname" size="20" /><div class="desc"></div></td>';
fields.field_wp_website='<td class="label">Website:</td><td class="fld_div"><input class="fld" type="url" name="website" size="20" /><div class="desc"></div></td>';
fields.field_wp_aol='<td class="label">AIM:</td><td class="fld_div"><input class="fld" type="text" name="aim" size="20" /><div class="desc"></div></td>';
fields.field_wp_yim='<td class="label">Yahoo IM:</td><td class="fld_div"><input class="fld" type="text" name="yim" size="20" /><div class="desc"></div></td>';
fields.field_wp_jabber='<td class="label">Jabber / Google Talk:</td><td class="fld_div"><input class="fld" type="text" name="jabber" size="20" /><div class="desc"></div></td>';
fields.field_wp_biography='<td class="label">Biographical Info:</td><td class="fld_div"><textarea class="fld" name="biography" cols="30" rows="4"></textarea><div class="desc"></div></td>';

fields.field_wp_company='<td class="label">Company:</td><td class="fld_div"><input class="fld" type="text" name="company" size="30" /><div class="desc"></div></td>';
fields.field_wp_address1='<td class="label">Address (Line 1):</td><td class="fld_div"><input class="fld" type="text" name="address1" size="40" /><div class="desc"></div></td>';
fields.field_wp_address2='<td class="label">Address (Line 2):</td><td class="fld_div"><input class="fld" type="text" name="address2" size="40" /><div class="desc"></div></td>';
fields.field_wp_city='<td class="label">City:</td><td class="fld_div"><input class="fld" type="text" name="city" size="20" /><div class="desc"></div></td>';
fields.field_wp_state='<td class="label">State:</td><td class="fld_div"><input class="fld" type="text" name="state" size="10" /><div class="desc"></div></td>';
fields.field_wp_zip='<td class="label">Zip Code:</td><td class="fld_div"><input class="fld" type="text" name="zip" size="5" /><div class="desc"></div></td>';
fields.field_wp_country='<td class="label">Country:</td><td class="fld_div"><input class="fld" type="text" name="country" size="40" /><div class="desc"></div></td>';

fields.field_tos='<td class="label">&nbsp;</td><td class="fld_div"><label><input class="fld" type="checkbox" name="terms_of_service" value="I agree to the Terms of Service" /> I agree to the Terms of Service</label><div class="desc">Terms of Service goes here</div></td>';
fields.field_special_header='<td class="label" colspan="2">Section Header</td>';
fields.field_special_paragraph='<td class="fld_div field_special_paragraph" colspan="2"><div class="desc"><p>Text</p></div></td>';
/**
 * Add field to registration form
 */
function regform_add(fld,before){
	newfld = jQuery(newRowStart+fld+newRowStart2+fields[fld]+newRowEditLinks+newRowLast);
	
	if(fld.substring(0,9)=='field_wp_'){
		newfld.addClass('wp_field');
	}
	
	jQuery(before).before(newfld);
	edit_field(newfld.find('#edit_field_link'));
}

/**
 * Clone field
 */
function row_clone(obj){
	var clone = obj.clone();
	obj.after(clone);
	clone.animate({
		'background-color':'lightYellow'
	}, 300, 'linear', function(){
		clone.animate({
			'background-color':'transparent'
		}, 300, 'linear', function(){
			clone.css('background-color','transparent');
		});
	});
}

/**
 * Delete field
 */
function row_delete(obj){
	obj.animate({
		'background-color': '#ff8888'
	}, 300, 'linear', function (){
		obj.animate({
			'background-color': 'transparent'
		}, 300, 'linear', function (){
			obj.next('.edit_form_div').remove();
			obj.remove();
		});
	});
}

/**
 * Save
 */
function save_field(dlg,obj){
	var frm = dlg.find('.edit_form')
	var isTOS = obj.hasClass('field_tos');
	var isHidden = obj.hasClass('field_hidden');
	var bltin = obj.hasClass('systemFld');
	var wp_field = obj.hasClass('wp_field');

	fld=obj.find('.fld');
	if(fld.length<1){
		fld=jQuery('<div><input class="fld" type="hidden" /></div>').find('.fld');
	}
	fldName=jQuery.trim(frm.find('.name').val()).replace(/[^A-Za-z0-9_\[\]]/g,' ').replace(/[ ]+/g,' ').replace(/[ ]/g,'_');
	if(!bltin && !wp_field){
		fld.attr('name',fldName);
	}

	label = frm.find('.label').val();
	if(isTOS){
		label = '&nbsp';
	}
	if(isHidden){
		label = 'h: '+fldName;
	}

	obj.find('.label').html(label);
	val=frm.find('.value').val();
	default_val = frm.find('.default').val();
	
	fldType=fld.attr('type');
	switch(fld.get(0).tagName){
		case 'SELECT':
			fldType='select';
		case 'INPUT':
			switch(fldType){
				case 'checkbox':
				case 'radio':
				case 'select':
					val = val.split("\n");
					if(isTOS){
						val = new Array(val[0]);
					}
					options='';
					for (var i=0;i<val.length;i++){
						v=jQuery.trim(val[i]);
						if(v!=''){
							v=v.split(':',2);
							if(v.length<2){
								v[1]=v[0];
							}
							if(fldType=='select'){
								selected = default_val == v[0] ? ' selected="selected"' : '';
								options+='<option value="'+v[0]+'"'+selected+'>'+jQuery.trim(v[1])+'</option>';
							}else{
								checked = default_val == v[0] ? ' checked="checked"' : '';
								options+='<label><input name="'+fldName+'" class="fld" type="'+fldType+'" value="'+v[0]+'"'+checked+' /> '+jQuery.trim(v[1])+'</label><br />';
							}
						}
					}
					if(fldType=='select'){
						fld.html(options);
					}else{
						obj.find('.fld_div').html(options+'<div class="desc"></div>');
					}
					break;
				default:
					fld.attr('value',default_val);
			}
			break;
		case 'TEXTAREA':
			fld.text(default_val);
			break;

		default:
			fld.val(default_val);
			break;
	}
	var width=Math.abs(frm.find('.width').val());
	var height=Math.abs(frm.find('.height').val());
	if(fld.get(0).tagName=='TEXTAREA'){
		if(width)fld.attr('cols',width);
		if(height)fld.attr('rows',height);
	}else{
		if(width)fld.attr('size',width);
	}

	isRequired=frm.find('.required').attr('checked');
	if(isRequired||isTOS){
		obj.addClass('required');
	}else{
		obj.removeClass('required');
	}
	obj.find('.reqNotice').html(isRequired||isTOS?'required':'&nbsp;');

	var descval = frm.find('.desc').val();

	if(isTOS){
		fldID = 'tos_data_'+fldName;
		obj.find('.desc').attr('id',fldID);

		if(isRequired){
			if(!jQuery(descval).hasClass('tos_content')){
				descval = '<div class="tos_content">'+descval+'</div>';
			}

			tos_label = obj.find('.fld_div label');
			checkbox = tos_label.find('input');
			var t=jQuery.trim(tos_label.text());
			tos_label.html(' <a href="/#TB_inline?inlineId='+fldID+'" class="thickbox">'+t+'</a>');
			tos_label.prepend(checkbox);
			obj.addClass('lightbox_tos');
		}else{
			obj.removeClass('lightbox_tos');
		}
	}

	obj.find('.desc').html(descval);
}

/**
 * Edit field
 */
function edit_field(obj,bltin){
	obj = jQuery(obj).parent().parent();

	var wp_field = obj.hasClass('wp_field');

	var dlg = obj.next('.edit_form_div');
	if(!dlg.length){
		dlg = jQuery('#edit_form_div tr.edit_form_div').clone();
		obj.after(dlg);
		frm = dlg.find('.edit_form');
		frm.get(0).reset();
		frm.find('.label').val(obj.find('.label').html());
		fld =obj.find('.fld');
		if(fld.length < 1){
			fld = jQuery('<div><input class="fld" type="hidden" /></div>').find('.fld');
		}
		frm.find('.name').val(fld.attr('name'));
		fldType=fld.attr('type');
		isfldList=false;
		isInput=false;
		switch(fld.get(0).tagName){
			case 'SELECT':
				fldType='select';
			case 'INPUT':
				switch(fldType){
					case 'select':
						dlg.find('a.help[rel="#settings-registration-custom-tooltips-checkbox-items"]').attr('rel','#settings-registration-custom-tooltips-select-items');
					case 'radio':
						dlg.find('a.help[rel="#settings-registration-custom-tooltips-checkbox-items"]').attr('rel','#settings-registration-custom-tooltips-radio-items');
					case 'checkbox':
						isfldList=true;
						value = '';
						options=fldType=='select'?fld.find('option'):fld;
						for(var i =0;i<options.length;i++){
							opt=jQuery(options[i]);
							v=opt.attr('value');
							t=jQuery.trim(fldType=='select'?opt.html():opt.parent().text());
							value+=(v!=t?v+':'+t:v)+'\n';
						}
						frm.find('.value').val(jQuery.trim(value));
						
						if(fldType=='select'){
							frm.find('.default').val(obj.find('.fld option:selected').val());
						}else{
							frm.find('.default').val(obj.find('input.fld:checked').val());
						}

						break;
					default:
						isInput=true;
						frm.find('.width').val(fld.attr('size'));
						frm.find('.default').val(fld.val());
				}
				break;
			case 'TEXTAREA':
				frm.find('.width').val(fld.attr('cols'));
				frm.find('.height').val(fld.attr('rows'));
				frm.find('.default').val(fld.val());
				break;
			default:
				frm.find('.default').val(fld.val());
		}

		var isButton = fld.hasClass('button');
		var isTOS = obj.hasClass('field_tos');
		var isHidden = obj.hasClass('field_hidden');
		var isHeader = obj.hasClass('field_special_header');
		var isParagraph = obj.hasClass('field_special_paragraph');

		frm.find('.edit_form_default th').html(isButton?'Value':'Default');
		frm.find('.edit_form_required').css('display',bltin||isButton||isHidden||isHeader||isParagraph?'none':'');
		frm.find('.edit_form_label').css('display',isButton||isTOS||isHidden||isParagraph?'none':'');
		frm.find('.edit_form_desc').css('display',isButton||isHidden||isHeader?'none':'');
		frm.find('.edit_form_default').css('display',fld.attr('type')=='password'||isTOS||isHeader||isParagraph?'none':'');
		frm.find('.edit_form_list').css('display',isfldList?'':'none');
		frm.find('.edit_form_width').css('display',isfldList||isButton||isTOS||isHidden||isHeader||isParagraph?'none':'');
		frm.find('.edit_form_height').css('display',isfldList||isInput||isButton||isTOS||isHidden||isHeader||isParagraph?'none':'');
		frm.find('.edit_form_name').css('display',isHeader||isParagraph?'none':'');

		if(isHeader){
			frm.find('.edit_form_label th').html('Header Text');
			frm.find('.label').attr('size',50);
			dlg.find('a.help[rel="#settings-registration-custom-tooltips-label"]').attr('rel','#settings-registration-custom-tooltips-header-text');
		}
		if(isParagraph){
			frm.find('.edit_form_desc th').html('Paragraph HTML');
			frm.find('.desc').attr('rows',6);
			dlg.find('a.help[rel="#settings-registration-custom-tooltips-description"]').attr('rel','#settings-registration-custom-tooltips-paragraph-text');
		}


		if(bltin || wp_field){
			frm.find('.edit_form_name input[name=name]').attr('readonly','readonly');
		}

		if(!isButton && !isTOS && !isHidden && (obj.hasClass('required') || bltin)){
			frm.find('.required').attr('checked',true);
		}else if(isTOS && obj.hasClass('lightbox_tos')){
			frm.find('.required').attr('checked',true);
		}else{
			frm.find('.required').removeAttr('checked');
		}

		frm.find('.desc').val(jQuery.trim(obj.find('.desc').html()));

		if(isTOS){
			frm.find('.edit_form_list th').html('Text for Checkbox');
			frm.find('.edit_form_desc th').html('HTML Code for Terms of Service');
			frm.find('.edit_form_required td label span').html('Show Terms of Service in LightBox');
			frm.find('textarea.value').attr('rows',1);
			frm.find('textarea.desc').attr('rows',10);
			dlg.find('a.help[rel="#settings-registration-custom-tooltips-checkbox-items"]').attr('rel','#settings-registration-custom-tooltips-tos-label');
			dlg.find('a.help[rel="#settings-registration-custom-tooltips-description"]').attr('rel','#settings-registration-custom-tooltips-tos-content');
			dlg.find('a.help[rel="#settings-registration-custom-tooltips-required"]').attr('rel','#settings-registration-custom-tooltips-tos-lightbox');
		}

		obj.addClass('li_edit')

		dlg.find('.buttonSave').click(function(){
			dlg_toggle(dlg,obj);
		});
		if(bltin){
			dlg.find('.buttonClone').remove();
			dlg.find('.buttonDelete').remove();
			dlg.find('a.help[rel="#settings-registration-custom-tooltips-clone-delete"]').remove();
		}else{
			dlg.find('.buttonClone').click(function(){
				dlg_toggle(dlg,obj,true);
				row_clone(obj);
			});
			dlg.find('.buttonDelete').click(function(){
				if(confirm('Are you sure you want to delete this row?')){
					dlg_toggle(dlg,obj,true);
					row_delete(obj);
				}
			});
		}
	}

	dlg_toggle(dlg,obj);
}

/**
 * hide / show our field editing dialog
 */
function dlg_toggle(dlg,obj,noAnimation){
	var speed = noAnimation ? 0 : 300;
	save_field(dlg,obj);
	dlg.find('.animatedDiv').toggle(speed ,function(){
		var e=jQuery(this);
		if(e.css('display')=='none'){
			dlg.remove();
			obj.removeClass('li_edit');
		}
	});
	initialize_tooltip(jQuery);
}

/**
 * save the registration form
 */
function save_registration_form(f){
	var fields='';
	var required='';
	var fldName='';

	jQuery('#the_form_itself .buttonSave').click();
	// form name
	var form_name = f.form_name.value;
	if(!form_name){
		form_name = 'Custom Form - '+(+(new Date()));
	}

	f = jQuery('#regform_submit_data').get(0);
	f.reset();
	f.form_name.value=form_name;
	jQuery.each(jQuery('#the_form_itself table.wpm_registration tr.li_fld:not(.systemFld)'),function(i,row){
		row = jQuery(row);
		fldName = row.find('.fld').attr('name');
		
		if(typeof fldName != 'undefined'){
			fields+=','+fldName.split('[')[0];
			if(row.hasClass('required')){
				required+=','+fldName;
			}
		}
	});
	f.form_fields.value=fields.substring(1);
	f.form_required.value=required.substring(1);

	jQuery('#the_form_itself :password').removeAttr('value');;
	var form = jQuery('#the_form_itself').clone();
	form.find('.edit_form_div').remove();
	form.find('.editLinks').remove();
	form.find('*').removeAttr('style');
	form.find('*').removeClass('li_edit');

	jQuery(f.rfdata).val(form.html());
	f.submit();
}


/**
 * submit the form
 */
function do_wlm_reg_form_action(action,form_id){
	var f =jQuery('#wlm_action_form').get(0);
	f.WishListMemberAction.value = action;
	f.form_id.value=form_id;
	f.submit();
}

/**
 * the drag/drop placeholder
 */
function placeholder_helper(e,ui){
	jQuery('.ui-state-highlight').html('<td colspan="3">&nbsp;</td>');
}

function fields_toggle(obj, div){
	obj = jQuery(obj);
	div = jQuery(div);
	
	div.is(':visible') ? obj.find('span').html('&#9656;') : obj.find('span').html('&#9662;');
	div.slideToggle(150,'linear');
}


/**
 * jQuery initialize
 */
jQuery(document).ready(function(){

	/**
	 * save the default form
	 */
	var default_form = jQuery('#default_form table.wpm_registration').clone();
	/**
	 * remove "garbage" edit links from default form
	 */
	default_form.find('td.editLinks').remove();
	/**
	 * add edit links to default_form
	 */
	jQuery.each(default_form.find('tr.li_fld'), function(index, element) {
		element = jQuery(element);
		if(element.hasClass('systemFld')){
			element.append('<td class="editLinks"><div class="reqNotice">required</div><a href="javascript:;" onclick="edit_field(this,true)">&#x25bc;</a></td>');
		}else{
			element.append(newRowEditLinks);
			element.find('.reqNotice').html(element.hasClass('required')?'required':'&nbsp;');
		}
	});
	/**
	 * add edit links to submit button
	 */
	default_form.find('tr.li_submit').append('<td class="editLinks"><div class="reqNotice">&nbsp;</div><a href="javascript:;" onclick="edit_field(this,true)">&#x25bc;</a></td>');
	jQuery('#the_form_itself').html(default_form);

	/**
	 * save original form state
	 */
	origFormState=jQuery('#the_form_itself').html();

	/**
	 * make our form fields sortable
	 */
	jQuery("#the_form_itself table.wpm_registration").sortable({
		items:'tr.li_fld',
		handle:'td.label',
		placeholder:'ui-state-highlight',
		over:function(e,ui){
			placeholder_helper(e,ui);
		},
		stop:function(e,ui){
			if(!ui.item.hasClass('li_fld')){
				fld=ui.item.attr('fld_type');
				regform_add(fld,ui.item);
				ui.item.remove();
			}
		},
		helper:function(e,ui){
			var dlg=ui.next('.edit_form_div');
			if(dlg.length){
				dlg_toggle(dlg,ui,true);
			}
			return jQuery('<tr style="background:#ccc;border:1px solid #eee; border-radius:5px"><td>'+ui.find('td.label').text()+'</td></tr>').width(196).height(32);
		},
		cursor:'pointer',
		cursorAt:[98,16]
	});

	/**
	 * make our form objects draggable
	 */
	jQuery(".reg_form_draggables tr").draggable({
		connectToSortable: "#the_form_itself table.wpm_registration",
		placeholder:'ui-state-highlight',
		helper:'clone',
		cursor:'pointer',
		cursorAt:[98,16]
	});

});
