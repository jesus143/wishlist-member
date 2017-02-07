
//for processing email broadcast
function wpm_processEmailBroadcast(id){
		
		var data = {
			WishListMemberAction: 'EmailBroadcast',
			EmailBroadcastAction: 'ProcessBroadcast',
			BroadcastID: id
		}

		jQuery.ajax({
			type: 'POST',
			url: admin_main_js.wlm_broacast_url,
			data: data,
			success: function( response ) {
	      		if ( ! isNaN(response) ) {
		      		jQuery(".broadcast-tq-" + id ).html( response );
		      		var q_cnt = jQuery(".emails-in-queue-cnt" ).html();
		      		if( ! isNaN( q_cnt ) ) {
		      			q_cnt = parseInt( q_cnt ) + parseInt( response );
		      			jQuery(".emails-in-queue-cnt" ).html( q_cnt );
		      		}
		    	} else {
		    		alert( response );
		    	}
		    },
			error: function() {
		    	alert("An error occured while trying to queue your emails.");
		    },
		});
}

function wpm_insertHTML(html, field) {
	var o = document.getElementById(field);
	try {
		if (o.selectionStart || o.selectionStart === 0) {
			o.focus();
			var os = o.selectionStart;
			var oe = o.selectionEnd;
			var np = os + html.length;
			o.value = o.value.substring(0, os) + html + o.value.substring(oe, o.value.length);
			o.setSelectionRange(np, np);
		} else if (document.selection) {
			o.focus();
			sel = document.selection.createRange();
			sel.text = html;
		} else {
			o.value += html;
		}
	} catch (e) {
	}
}

function wpm_selectAll(chk, table, className) {
	if (!className)
		className = 'check-column';
	table = document.getElementById(table);
	var th = table.getElementsByTagName('th');
	for (var i = 0; i < th.length; i++) {
		if (th[i].className == className && th[i].scope == 'row') {
			try {
				th[i].getElementsByTagName('input')[0].checked = chk.checked;
			} catch (e) {
			}
		}
	}
}

function wpm_clone_level(f) {
	var srcid = f.clonefrom.value;
	var dstid = f.doclone.value;
	if (f.doclone.checked) {
		var data = {
			action: 'wlm_form_membership_level',
			id: srcid
		}

		jQuery.post(ajaxurl, data, function(res) {
			var src = jQuery(res).find('form').get(0);
			f['wpm_levels[' + dstid + '][isfree]'].checked = src['wpm_levels[' + srcid + '][isfree]'].checked;
			f['wpm_levels[' + dstid + '][role]'].selectedIndex = src['wpm_levels[' + srcid + '][role]'].selectedIndex;
			f['wpm_levels[' + dstid + '][loginredirect]'].selectedIndex = src['wpm_levels[' + srcid + '][loginredirect]'].selectedIndex;
			f['wpm_levels[' + dstid + '][afterregredirect]'].selectedIndex = src['wpm_levels[' + srcid + '][afterregredirect]'].selectedIndex;
			f['wpm_levels[' + dstid + '][allpages]'].checked = src['wpm_levels[' + srcid + '][allpages]'].checked;
			f['wpm_levels[' + dstid + '][allcategories]'].checked = src['wpm_levels[' + srcid + '][allcategories]'].checked;
			f['wpm_levels[' + dstid + '][allposts]'].checked = src['wpm_levels[' + srcid + '][allposts]'].checked;
			f['wpm_levels[' + dstid + '][allcomments]'].checked = src['wpm_levels[' + srcid + '][allcomments]'].checked;
			f['wpm_levels[' + dstid + '][expire]'].value = src['wpm_levels[' + srcid + '][expire]'].value;
			f['wpm_levels[' + dstid + '][calendar]'].selectedIndex = src['wpm_levels[' + srcid + '][calendar]'].selectedIndex;
			f['wpm_levels[' + dstid + '][expire]'].disabled = src['wpm_levels[' + srcid + '][expire]'].disabled;
			f['wpm_levels[' + dstid + '][calendar]'].disabled = src['wpm_levels[' + srcid + '][calendar]'].disabled;
			f['wpm_levels[' + dstid + '][noexpire]'].checked = src['wpm_levels[' + srcid + '][noexpire]'].checked;
			f['wpm_levels[' + dstid + '][disableexistinglink]'].checked = src['wpm_levels[' + srcid + '][disableexistinglink]'].checked;
			f['wpm_levels[' + dstid + '][registrationdatereset]'].checked = src['wpm_levels[' + srcid + '][registrationdatereset]'].checked;
			f['wpm_levels[' + dstid + '][uncancelonregistration]'].checked = src['wpm_levels[' + srcid + '][uncancelonregistration]'].checked;
			f['wpm_levels[' + dstid + '][requirecaptcha]'].checked = src['wpm_levels[' + srcid + '][requirecaptcha]'].checked;
			f['wpm_levels[' + dstid + '][requireemailconfirmation]'].checked = src['wpm_levels[' + srcid + '][requireemailconfirmation]'].checked;
			f['wpm_levels[' + dstid + '][requireadminapproval]'].checked = src['wpm_levels[' + srcid + '][requireadminapproval]'].checked;
			jQuery(f['wpm_levels[' + dstid + '][removeFromLevel][]']).val(jQuery(src['wpm_levels[' + srcid + '][removeFromLevel][]']).val());
			jQuery(f['wpm_levels[' + dstid + '][addToLevel][]']).val(jQuery(src['wpm_levels[' + srcid + '][addToLevel][]']).val());
			jQuery(f['wpm_levels[' + dstid + '][removeFromLevel][]']).select2("val", jQuery(src['wpm_levels[' + srcid + '][removeFromLevel][]']).val());
			jQuery(f['wpm_levels[' + dstid + '][addToLevel][]']).select2("val", jQuery(src['wpm_levels[' + srcid + '][addToLevel][]']).val());
		});
	} else {
		f['wpm_levels[' + dstid + '][isfree]'].checked = false;
		f['wpm_levels[' + dstid + '][role]'].selectedIndex = 0;
		f['wpm_levels[' + dstid + '][loginredirect]'].selectedIndex = 0;
		f['wpm_levels[' + dstid + '][afterregredirect]'].selectedIndex = 0;
		f['wpm_levels[' + dstid + '][allpages]'].checked = false;
		f['wpm_levels[' + dstid + '][allcategories]'].checked = false;
		f['wpm_levels[' + dstid + '][allposts]'].checked = false;
		f['wpm_levels[' + dstid + '][allcomments]'].checked = false;
		f['wpm_levels[' + dstid + '][expire]'].value = '';
		f['wpm_levels[' + dstid + '][calendar]'].selectedIndex = 0;
		f['wpm_levels[' + dstid + '][expire]'].disabled = false
		f['wpm_levels[' + dstid + '][calendar]'].disabled = false;
		f['wpm_levels[' + dstid + '][noexpire]'].checked = false;
		f['wpm_levels[' + dstid + '][disableexistinglink]'].checked = false;
		f['wpm_levels[' + dstid + '][registrationdatereset]'].checked = false;
		f['wpm_levels[' + dstid + '][uncancelonregistration]'].checked = false;
		f['wpm_levels[' + dstid + '][requirecaptcha]'].checked = false;
		f['wpm_levels[' + dstid + '][requireemailconfirmation]'].checked = false;
		f['wpm_levels[' + dstid + '][requireadminapproval]'].checked = false;
	}
}

function wpm_showHideLevels(o) {
	var s = o.selectedIndex;
	var d = document.getElementById('levels');
	jQuery('.wpm_action_options').hide();

	switch (jQuery(o).val()) {
		case 'wpm_change_membership':
			jQuery('#move_to_date, #levels').show();
			break;
		case 'wpm_add_membership':
			jQuery('#add_to_date, #levels').show();
			break;
		case 'wpm_del_membership':
			jQuery('#remove_to_date, #levels').show();
			break;
		case 'wpm_cancel_membership':
			jQuery('#cancel_date, #levels').show();
			break;
		case 'wpm_uncancel_membership':
		case 'wpm_confirm_membership':
		case 'wpm_unconfirm_membership':
		case 'wpm_approve_membership':
		case 'wpm_unapprove_membership':
			jQuery('#levels').show();
			break;
		case 'wpm_add_payperposts':
		case 'wpm_del_payperposts':
			jQuery('#wpm_payperposts').show();
			break;
	}
}

function wpm_doConfirm(f) {

	var double_confirm = new Array(
			  'wpm_delete_member'
			  // 'wpm_delete_member',
			  // 'wpm_change_membership',
			  // 'wpm_del_membership',
			  // 'wpm_del_payperposts',
			  // 'wpm_clear_scheduled'
			  );

	if (f.wpm_action.selectedIndex == 0) {
		alert('No action selected');
		return;
	}
	var a = f.wpm_action.options[f.wpm_action.selectedIndex].text;
	var l = f.wpm_membership_to.options[f.wpm_membership_to.selectedIndex].text;
	if (f.wpm_membership_to.value != '-') {
		a += ' ' + l;
	}
	a = "Action: \"" + a + "\"";
	var c1 = "Are you sure you want to execute the\nfollowing action for the selected users?\n\n" + a;
	var c2 = "Are you REALLY sure you want to execute the\nfollowing action for the selected users?\n\n" + a;
	
	if (double_confirm.indexOf(f.wpm_action.value) > -1) {
		if (confirm(c1) && confirm(c2)) {
			f.submit();
		}
	} else {
		if (confirm(c1)) {
			f.submit();
		}
	}
}

function wlm_user_search(url, instructions, fxn) {
	var search_by = jQuery.trim(jQuery('#wlm_user_search_by').val());
	switch (search_by) {
		case 'by_level':
			var search = jQuery('#wlm_level_search_input').val();
			break;
		default:
			var search = jQuery.trim(jQuery('#user_search_input').val());
	}
	var outdiv = jQuery('#wlm_user_search_ajax_output');
	if (search == '' || search == null) {
		outdiv.html('<p style="color:red">No search entry specified</p>');
		return;
	}
	var data = {
		action: 'wlm_user_search',
		url: url,
		search: search,
		search_by: search_by
	}
	jQuery.post(ajaxurl, data, function(response) {
		response = jQuery.trim(response);
		if (response == '') {
			outdiv.html('<p style="color:red">No users found</p>');
		} else {
			outdiv.html('<p>' + instructions + '</p>' + response);
			fxn();
		}
	});
}


function zc_compat_initialize() {
	ZeroClipboard.setMoviePath(wlm_zeroclip.path);
	jQuery("a.wlmClipButton").each(function() {
		var clip = new ZeroClipboard.Client();
		clip.setHandCursor(true);
		clip.glue(this);

		var code = jQuery(this).attr("title");
		clip.setText(code);

		clip.addEventListener('complete', function(client, args) {
			alert("Copied text to clipboard:\n" + args);
		});
	});
}
function zc_initialize(el) {
	ZeroClipboard.setDefaults({moviePath: wlm_zeroclip.path});
	jQuery(el).each(function(i, e) {
		var clip = new ZeroClipboard();
		clip.glue(jQuery(e).get(0));
		clip.on('complete', function(c, args) {
			alert("Copied text to clipboard:\n" + args.text);
		});
	})
}

if (typeof ZeroClipboard !== "undefined") {
	jQuery(document).ready(function($) {
		var ver = ZeroClipboard.version.split('.', 2);
		if (ver[1] == 2) {
			zc_initialize($('a.wlmClipButton'));
		} else {
			zc_compat_initialize();
		}
	});
}

jQuery(document).ready(function() {
	jQuery('.if_edit_tag_level').click(function() {
		var next_tr = jQuery(this).parent().parent().next();
		if (jQuery(this).hasClass("ifshow")) {
			next_tr.show();
			next_tr.find(".chzn-select").chosen({width:'300px'});
			jQuery(this).removeClass("ifshow");
			jQuery(this).addClass("ifhide");
			jQuery(this).html("[-] Hide Level Tag Settings");
		} else {
			next_tr.hide();
			jQuery(this).removeClass("ifhide");
			jQuery(this).addClass("ifshow");
			jQuery(this).html("[+] Edit Level Tag Settings");
		}

	});
});

jQuery(document).ready(function() {
	jQuery('.ifauto_tag_tab').click(function() {
		var holder = jQuery(this).parent().parent();
		holder.find("li").each(function() {
			jQuery(this).removeClass("current");
		});
		jQuery(this).parent().addClass("current");
		jQuery(".ifauto_tagging_holder").removeClass("hidden");
		if ( ! jQuery(".ifauto_sequence_holder").hasClass("hidden") ) {
			jQuery(".ifauto_sequence_holder").addClass("hidden");
		}
		jQuery("#ifauto_current_tab").val("tag");
	});
	jQuery('.ifauto_sequence_tab').click(function() {
		var holder = jQuery(this).parent().parent();
		holder.find("li").each(function() {
			jQuery(this).removeClass("current");
		});
		jQuery(this).parent().addClass("current");
		jQuery(".ifauto_sequence_holder").removeClass("hidden");
		if ( ! jQuery(".ifauto_tagging_holder").hasClass("hidden") ) {
			jQuery(".ifauto_tagging_holder").addClass("hidden");
		}
		jQuery("#ifauto_current_tab").val("sequence");
	});
});
