// initial variables
var wlm_post_users = [];
var wlm_current_ppp_content_id = 0;
var wlm_post_users_update = false;
var wlm_bulk_ids = [];

// onload stuff
jQuery(function() {
	/**
	 * Edit Protection Settings link handler
	 */
	jQuery('.wlm-edit-protection').click(function(e) {
		e.preventDefault();
		wlm_manage_content_hide_edit();
		var row = jQuery(this).parents('tr');
		var cols = row.find('td').length;
		var edit = row.clone();
		var actions = row.find('.row-actions');
		edit.html('<td></td><td colspan="' + cols + '"><a class="button alignleft" id="wlm-cancel-button">Cancel</a><a class="button button-primary alignright" id="wlm-save-button">Update Protection</a><span class="spinner"></span></td>');
		actions.removeClass('row-actions').addClass('x-row-actions').css('display', 'none');

		var post_users = row.find('span.wlm-payperpost-users');

		var protection = row.find('.wlm-protection');
		var select_options = [
			{id: 'Unprotected', text: 'Unprotected'},
			{id: 'Protected', text: 'Protected'}
		];
		if (!wlm_no_inherit) {
			select_options.push({id: 'Inherited', text: 'Inherited'});
		}
		protection.select2({
			data: select_options,
			minimumResultsForSearch: -1,
			width: '100%'
		});
		protection.select2('val', protection.text());

		protection.change(function(e){
			row.find('.wlm-levels').select2('readonly',e.val == 'Inherited');
		});

		var payperpost = row.find('.wlm-payperpost');
		payperpost.select2({
			data: [
				{id: 'Disabled', text: 'Disabled'},
				{id: 'Paid', text: 'Paid'},
				{id: 'Free', text: 'Free'}
			],
			minimumResultsForSearch: -1,
			width: '100%'
		});
		payperpost.select2('val', payperpost.text());

		var pppusers = row.find('.wlm-payperpost-users');
		if(pppusers.text()) {
			pppusers.after('<button class="button wlm-payperpost-users-button" data-id="'+pppusers.attr('id')+'">'+pppusers.text()+'</button>');
			pppusers.hide();
			jQuery('.wlm-payperpost-users-button').click(wlm_pppusers);
		}

		var levels = row.find('.wlm-levels');
		var wlm_levels_x = wlm_levels;
		var immutable = levels.attr('data-immutable').split(',') + '';
		jQuery.each(wlm_levels_x, function(index, value) {
			if(immutable.indexOf(value.id) >= 0) {
				wlm_levels_x[index]['locked'] = true;
			}
		})
		levels.select2({
			data: wlm_levels_x,
			minimumResultsForSearch: -1,
			multiple: true,
			closeOnSelect: false,
			width: '100%'
		});
		levels.select2('val', levels.attr('data-keys').split(','));

		levels.select2('readonly',protection.val() == 'Inherited');

		var force_download = row.find('.wlm-forcedownload');
		force_download.select2({
			data: [
				{id: 'Yes', text: 'Yes'},
				{id: 'No', text: 'No'},
			],
			minimumResultsForSearch: -1,
			multiple: false,
			closeOnSelect: false,
			width: '100%'
		});
		force_download.select2('val', force_download.text());

		edit.addClass('wlm-edit-row');
		row.after(edit);
		edit.find('#wlm-cancel-button').click(function() {
			wlm_manage_content_hide_edit();
		});
		edit.find('#wlm-save-button').click(function() {
			edit.find('.spinner').show();
			var data = {
				action: 'wlm_update_protection',
				protection: row.find('.wlm-protection').select2('val'),
				payperpost: (['categories', 'folders', 'attachment'].indexOf(wlm_content_type) + 1) || wlm_manage_comments ? '' : row.find('.wlm-payperpost').select2('val'),
				forcedownload: 'folders' === wlm_content_type ? row.find('.wlm-forcedownload').select2('val') : '',
				levels: row.find('.wlm-levels').select2('val'),
				content_type: wlm_content_type,
				manage_comments: wlm_manage_comments ? 1 : 0,
				content_id: row.find('.bulk-checkbox').attr('value')
			};
			if(jQuery.isArray(wlm_post_users_update)) {
				data.post_users = wlm_post_users_update;
				data.post_users.push(0); // make sure that jquery sends our array
				wlm_post_users_update = false;
			}

			jQuery.post(ajaxurl, data, function(r) {
				if (!r.success) {
					return;
				}

				protection.text(r.data.protection);
				payperpost.text(r.data.payperpost);
				force_download.text(r.data.forcedownload);
				levels.html(r.data.levels);
				levels.attr('data-keys', r.data.level_keys);
				levels.attr('data-immutable', r.data.immutable);

				if(typeof r.data.post_users !== 'undefined') {
					post_users.text(r.data.post_users);
					wlm_post_users[data.content_id] = undefined;
					wlm_current_ppp_content_id = 0;
				}

				row.find('i.wlm_padlock').attr('class', r.data.padlock === 1 ? 'wlm_padlock icon-lock fa fa-lock' : 'wlm_padlock icon-unlock fa fa-unlock');
				wlm_manage_content_hide_edit();
				wlm_display_message('Protection settings updated for "'+jQuery('tr.wlm-content-'+data.content_id).attr('data-content-title')+'"');
			}, 'json');
		});
	});

	/**
	 * bulk action handler
	 */
	jQuery('#doaction, #doaction2').click(function() {
		var selectID = jQuery(this).attr('id') === 'doaction' ? 'select[name=action]' : 'select[name=action2]';
		var the_button = jQuery(this);
		var action = jQuery(selectID).val();
		if(action == '-1') {
			alert('Please select an action');
			return;
		}
		if(action != '-1') {
			var the_ids = [];
			jQuery('.bulk-checkbox:checked').each(function() {
				the_ids.push(jQuery(this).val());
			});
			if(the_ids.length < 1) {
				alert('You must select at least one item from the table.');
				return;
			}
			wlm_bulk_ids = the_ids;
			if(action == 'pppusers') {
				wlm_pppusers(-1);
			} else {

				var val = jQuery('#select-actions-extras select.'+action).val();
				if(val == '' || val == null) {
					alert(jQuery('#select-actions-extras select.'+action).attr('data-error'));
					return;
				}

				if(!confirm('Are you sure?')) {
					return;
				}
				
				the_button.attr('disabled', 'disabled');
				the_button.after('<span class="the_button_spinner spinner"></span>');


				jQuery.ajax(
					ajaxurl,
					{
						data: {
							action: 'wlm_contenttab_bulk_action',
							bulk_action: action,
							bulk_action_value: val,
							content_ids: the_ids,
							content_type: wlm_content_type,
							manage_comments: wlm_manage_comments ? 1 : 0
						},
						type: 'POST',
						dataType: 'json',
					}
				).done(function(r) {
					if(r.success) {
						switch(action) {
							case 'force_download':
								wlm_display_message(r.msg);
								for(key in r.data) {
									jQuery('span#wlm-forcedownload-'+key).text(r.data[key]);
									wlm_blink_cell('tr.wlm-content-'+key+' td.force_download', '#99FFCC');
								}
							break;
							case 'ppp':
								wlm_display_message(r.msg);
								for(key in r.data) {
									jQuery('span#wlm-payperpost-'+key).text(r.data[key]);
									wlm_blink_cell('tr.wlm-content-'+key+' td.wlm_payperpost', '#99FFCC');
								}
							break;
							case 'protection':
								wlm_display_message(r.msg);
								for(key in r.data) {
									jQuery('span#wlm-protection-'+key).text(r.data[key].label);
									row_id = 'tr.wlm-content-'+key;
									jQuery(row_id+' i.wlm_padlock').attr('class', r.data[key].padlock === 1 ? 'wlm_padlock icon-lock fa fa-lock' : 'wlm_padlock icon-unlock fa fa-unlock');
									if(typeof r.data[key].new_levels != 'undefined') {
										jQuery(row_id+' span.wlm-levels').html(r.data[key].new_levels);
										jQuery(row_id+' span.wlm-levels').attr('data-keys',r.data[key].new_level_keys);
									}
									wlm_blink_cell(row_id+' td.wlm_protection', '#99FFCC');
								}
							break;
							case 'add_levels':
							case 'remove_levels':
								wlm_display_message(r.msg);
								for(key in r.data) {
									row_id = 'tr.wlm-content-'+key;
									if(typeof r.data[key].new_levels != 'undefined') {
										jQuery(row_id+' span.wlm-levels').html(r.data[key].new_levels);
										jQuery(row_id+' span.wlm-levels').attr('data-keys',r.data[key].new_level_keys);
										jQuery(row_id+' span.wlm-levels').attr('data-immutable',r.data[key].immutable);
									}
									wlm_blink_cell(row_id+' td.wlm_levels', '#99FFCC');
								}
							break;
						}
					}
					the_button.removeAttr('disabled');
					jQuery('.the_button_spinner').remove();
				});
			}
		}
	});

	/**
	 * bulk action extras handler
	 */
	jQuery('select[name=action],select[name=action2]').change(function() {
		var other = jQuery(this).attr('name') == 'action' ? 'action2' : 'action';
		jQuery('select[name='+other+']').val('-1');
		jQuery('#select-actions-extras select').select2('val', '');
		jQuery('#select-actions-extras').detach().insertAfter(this);
		jQuery('.select-actions-extras').hide();
		var action = jQuery(this).val();
		switch(action) {
			case 'protection':
			case 'add_levels':
			case 'remove_levels':
			case 'ppp':
			case 'force_download':
				jQuery('#select-actions-extras .'+action).show();
			break;
		}
	});
	jQuery('#select-actions-extras select').select2({width:'copy', minimumResultsForSearch: -1, closeOnSelect: false});
	jQuery('select[name=action], select[name=action2]').select2({width:'210px', minimumResultsForSearch: -1});
	jQuery('#doaction, #doaction2').before('<span> </span>'); // span needed to add space between select2 and button

	jQuery('.wlm-folder').click(function() {

		$folder_id = jQuery(this).attr('id');

		// show the lightbox
		jQuery('#wlm-folder-box-'+$folder_id).WishListLightBox({'autoopen': true,'oncancel':function() {

		}});

	});

	/**
	 * ppp user search handler
	 */
	jQuery('#wlm-pppusers-box select[name=search_by]').select2({width:'copy', minimumResultsForSearch: -1});
	jQuery('#wlm-pppusers-box select[name=search_by]').change(function() {
		jQuery('#wlm-pppusers-box .wlm_search_by_field').hide();
		jQuery('#wlm-pppusers-box #wlm_search_'+jQuery(this).val()).show();
	});
	jQuery('#wlm-pppusers-box select[name=search_by_level]').select2({width:'copy', minimumResultsForSearch: -1, closeOnSelect: false});
	jQuery('form#wlm-ppp-user-search').submit(function(event){
		event.preventDefault();
		var form     = jQuery(this);
		var searchby = form.find('select[name=search_by]').val();
		var value    = form.find('*[name=search_'+searchby+']').val();

		if(typeof value === 'string') {
			value = value.trim();
		}

		if(!value) {
			alert('You have not provided an item to search for.');
			return;
		}

		form.find('input[type=submit]').attr('disabled','disabled');
		form.find('span.spinner').show();

		data = {
			action: 'wlm_user_search',
			search_by: searchby,
			search: value,
			return_raw: 1
		}

		jQuery.post(ajaxurl, data, function(r){
			wlm_ppp_users_table_search_results.empty();
			wlm_ppp_users_table_queue.find('tr').removeClass('search-result');
			var noout = true;
			if(r.success && r.data.length) {
				jQuery.each(r.data, function(idx){
					var user    = r.data[idx]
					var intable = wlm_ppp_users_table_queue.find('tr#wlm-ppp-users-'+user.ID);
					if(intable.length < 1) {
						var html          = jQuery('<tr id="wlm-ppp-users-'+user.ID+'"><td>'+user.display_name+'</td><td>'+user.user_email+'<br>'+user.user_login+'</td></tr>');
						var radioshtml    = '<label><input type="radio" name="pppuser['+user.ID+']" data-id="U-'+user.ID+'" value="1">Y</label> &nbsp; <label><input type="radio" name="pppuser['+user.ID+']" data-id="U-'+user.ID+'" value="0">N</label>';
						var bulkradiohtml = ' &nbsp; <label><input type="radio" name="pppuser['+user.ID+']" data-id="U-'+user.ID+'" value="-1">&mdash;</label>';

						if(wlm_current_ppp_content_id == '-1') {
							var radios = jQuery('<td>'+radioshtml+'</td>');
						} else{
							var radios = jQuery('<td><input type="button" data-id="'+user.ID+'" value="Add &raquo;" class="button" style="float:right"></td>');
						}

						radios.find('input').click(function() {
							wlm_ppp_users_table_queue.find('tr.notice-row').remove();
							var uid = jQuery(this).attr('data-id');
							if(wlm_current_ppp_content_id == '-1') {
								var radios = jQuery(radioshtml+bulkradiohtml);
							} else {
								var radios = jQuery(radioshtml);
							}

							if(jQuery(this).val() === '0') {
								jQuery(radios).find('input[value=0]').attr('checked', 'checked');
							} else {
								jQuery(radios).find('input[value=1]').attr('checked', 'checked');
							}

							radios = jQuery(radios);
							var row = jQuery(jQuery(this).parents('tr')[0]);
							var col = jQuery(jQuery(this).parents('td')[0]);
							wlm_ppp_users_table_queue.prepend(row);
							wlm_blink_cell(row.find('td'));
							wlm_pppusers_table_styles();

							col.empty().append(radios);

							wlm_ppp_table_height();
						});
						wlm_ppp_users_table_search_results.append(html.append(radios));
						noout = false;
					} else {
						noout = false;
						intable.addClass('search-result');
						wlm_ppp_users_table_queue.prepend(intable);
					}
				});
			}
			if(noout){
				wlm_ppp_users_table_search_results.append('<tr class="notice-row"><td colspan="100" align="center"><em>No search results founds</em></td></tr>');
			}
			wlm_ppp_users_table_search_results.css('display','block');
			wlm_ppp_table_height();
			wlm_pppusers_table_styles();
			form.find('input[type=submit]').removeAttr('disabled');
			form.find('span.spinner').hide();
		}, 'json');

	});


	// recompute ppp table height on window resize
	jQuery(window).resize(function() {
		wlm_ppp_table_height();
	});

	// more initial values
	wlm_ppp_users_table = jQuery('.wlm-ppp-users-table');
	wlm_ppp_users_table_queue = jQuery('.wlm-ppp-users-table tbody.queue');
	wlm_ppp_users_table_search_results = jQuery('.wlm-ppp-users-table tbody.search_results');
});

/*
 * content protection
 */

// hides the manage content editor
function wlm_manage_content_hide_edit() {
	jQuery('.wlm-edit-row').remove();
	jQuery('.x-row-actions').removeClass('x-row-actions').addClass('row-actions').css('display', '');
	jQuery('.wlm-protection').select2('destroy');
	jQuery('.wlm-levels').select2('destroy');
	jQuery('.wlm-payperpost').select2('destroy');
	jQuery('.wlm-forcedownload').select2('destroy');
	jQuery('.wlm-payperpost-users').show();
	jQuery('.wlm-payperpost-users-button').remove();
}

/*
 * folder protection
 */
function wlm_parent_folder_edit() {
	jQuery('.parent_folder_noedit').toggle();
	jQuery('.parent_folder_edit').toggle();
	return false;
}

function wlm_confirm_change_parent_folder(f) {
	i = jQuery(f).find('input[name=parentFolder]');
	if (i.value() === i.attr('data-original')) {
		return true;
	}

	return confirm('Any protected folders you currently have will lose protection if you continue.\n\nAre you sure you want to continue?');
}

function wlm_confirm_autoconfigure(f) {
	var i = new Array();
	var button_text = jQuery(f).find('input[type=submit]').val().toLowerCase();
	jQuery(f).find('ol.wlm-folder-autoconfig-actions li').each(function(index, li) {
		i.push((index + 1) + ". " + jQuery(li).text())
	});

	return confirm('The following actions will be performed automatically if you choose to continue:\n\n' + i.join('\n\n') + '\n\nAre you sure you want to ' + button_text + ' folder protection?');
}

/*
 * pay per post protection
 */

function wlm_load_pppusers(content_id, reload) {
	if(content_id == '-1'){
		wlm_pppusers_show_table(content_id);
		return;
	}
	if(typeof wlm_post_users[content_id] === 'undefined' || reload) {
		// ajax call to get all post users
		jQuery.ajax(
			ajaxurl,
			{
				data: {
					action: 'wlm_get_ppp_users',
					post_id: content_id
				},
				type: 'POST',
				async: true,
				dataType: 'json',
			}
		).done(function(r){
			if(r.success) {
				wlm_post_users[content_id] = r.data;
				if(!reload) {
					wlm_pppusers_show_table(content_id);
				}
			}
		});
	} else {
		if(!reload){
			wlm_pppusers_show_table(content_id);
		}
	}
}

function wlm_pppusers_show_table(content_id) {
	if(content_id != wlm_current_ppp_content_id) {
		wlm_current_ppp_content_id = content_id;
		// prepare the table
		wlm_ppp_users_table_search_results.empty();
		wlm_ppp_users_table_queue.empty();

		jQuery('#wlm-ppp-queue-update-button').val('Continue...');
		if(content_id == '-1') {
			jQuery('#wlm-ppp-queue-update-button').val('Update Pay Per Post Users');
		} else if(wlm_post_users[content_id].length) {
			jQuery.each(wlm_post_users[content_id], function(idx, user) {
				var html   = jQuery('<tr id="wlm-ppp-users-'+user.ID+'"><td>'+user.display_name+'</td><td>'+user.user_email+'<br>'+user.user_login+'</td></tr>');
				var radios = jQuery('<td><label><input type="radio" name="pppuser['+user.ID+']" data-id="U-'+user.ID+'" value="1" checked="checked">Y</label> &nbsp; <label><input type="radio" name="pppuser['+user.ID+']" value="0">N</label></td>');
				wlm_ppp_users_table_queue.append(html.append(radios));
			});
		} else {
			wlm_ppp_users_table_queue.append('<tr class="notice-row"><td colspan="100" align="center"><em>No Pay Per Post Users assigned to this content.</em></td></tr>')
		}
		wlm_ppp_users_table_search_results.empty().append('<tr class="notice-row"><td colspan="100" align="center"><em>Use the form above to search for Users.</em></td></tr>')
	}
	wlm_pppusers_table_styles();

	// show the lightbox
	jQuery('#wlm-pppusers-box').WishListLightBox({'autoopen': true,'oncancel':function() {
		wlm_current_ppp_content_id = 0;
	}});

	wlm_ppp_table_height();
}

function wlm_pppusers(content_id) {
	if(typeof content_id === 'object') {
		var ppp_button = jQuery(this);
		var content_id = ppp_button.attr('data-id').match(/[0-9]+$/)[0];
	}

	wlm_post_users_update = false;

	jQuery("#wlm-ppp-modal-backdrop").show();

	// pre-load post users
	wlm_load_pppusers(content_id);


}

function wlm_prepare_ppp_queue() {

	if(wlm_current_ppp_content_id == '-1') {

		if(!confirm('Are you sure you want to update the Pay Per Post Users for all selected content?')) {
			return;
		}

		jQuery('.wlm-ppp-queue-update-button.spinner').show();
		jQuery('#wlm-ppp-queue-update-button').attr('disabled', 'disabled');
		var ppp_add    = [];
		var ppp_remove = [];
		jQuery('.wlm-ppp-queue input[type=radio]:checked').each(function() {
			var id = String(jQuery(this).attr('data-id')).match(/[0-9]+$/)[0];
			switch(jQuery(this).val()) {
				case '0':
					ppp_remove.push(id);
				break;
				case '1':
					ppp_add.push(id);
				break;
			}
		});
		jQuery.ajax(
			ajaxurl,
			{
				data: {
					action: 'wlm_contenttab_bulk_action',
					bulk_action: 'pppusers',
					bulk_action_value: '',
					content_ids: wlm_bulk_ids,
					content_type: wlm_content_type,
					manage_comments: wlm_manage_comments ? 1 : 0,
					ppp_add: ppp_add,
					ppp_remove: ppp_remove
				},
				type: 'POST',
				dataType: 'json',
			}
		).done(function(r) {
			jQuery('a.media-modal-close').click();
			wlm_display_message(r.msg);
			for(var idx in r.data) {
				jQuery('span#wlm-payperpost-users-'+idx).text(r.data[idx]);
				wlm_blink_cell('tr.wlm-content-'+idx+' td.wlm_payperpost_users');
			}
			jQuery('.wlm-ppp-queue-update-button.spinner').hide();
			jQuery('#wlm-ppp-queue-update-button').removeAttr('disabled');
		});
	} else {
		wlm_post_users_update = [];
		jQuery('#wlm-pppusers-box input:checked[value=1]').each(function(idx){
			wlm_post_users_update.push(jQuery(this).attr('data-id'));
		});
		jQuery('tr.wlm-content-'+wlm_current_ppp_content_id+ ' button.wlm-payperpost-users-button').text(wlm_post_users_update.length);
		jQuery('tr.wlm-content-'+wlm_current_ppp_content_id+ ' span.wlm-payperpost-users').text(wlm_post_users_update.length);

		jQuery('#wlm-pppusers-box').WishListLightBox().close_modal();
	}
}

function wlm_pppusers_table_styles() {

	if(wlm_current_ppp_content_id == '-1') {
		var title = wlm_bulk_ids.length == 1 ? ' Post' : ' Posts';
		title = wlm_bulk_ids.length + title + ' (Bulk Update)';
		jQuery('#wlm-pppusers-box').addClass('bulk-update');
	} else {
		var title = jQuery('tr.wlm-content-'+wlm_current_ppp_content_id).attr('data-content-title');
		jQuery('#wlm-pppusers-box').removeClass('bulk-update');
	}

	jQuery('#wlm-pppusers-box .media-frame-title h3 em').text(title);

	wlm_ppp_users_table_search_results.find('tr:odd').removeClass('alternate');
	wlm_ppp_users_table_search_results.find('tr:even').addClass('alternate');

	wlm_ppp_users_table_queue.find('tr:odd').removeClass('alternate');
	wlm_ppp_users_table_queue.find('tr:even').addClass('alternate');

}

function wlm_ppp_table_height() {
	var height = jQuery('#wlm-pppusers-box .media-frame-content').height();
	var max_height = height - wlm_ppp_users_table.position().top - wlm_ppp_users_table.find('th').height() - 40;
	wlm_ppp_users_table_queue.css({'max-height':max_height,'height':max_height});
	wlm_ppp_users_table_search_results.css({'max-height':max_height,'height':max_height});
}

function wlm_blink_cell(id) {
	jQuery(id).toggleClass('wlm-ajax-green', true, 100);
	jQuery(id).toggleClass('wlm-ajax-green', false, 3000);
}

function wlm_display_message(msg) {
	jQuery('.updated').hide();
	var x = jQuery('<div class="updated"><p>'+msg+'</p></div>');
	jQuery('.wrap h2:first').after(x);
	//x.delay(3000).slideUp(400, function() {x.remove();});

}