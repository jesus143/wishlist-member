jQuery(document).ready(function($) {
	$("#datepicker, #dp_add_level, #dp_move_level, #dp_remove_level, #to_date, #from_date").datepicker();
        $("#datepicker, #dp_add_level, #dp_move_level, #dp_remove_level").datepicker('option', 'minDate', 0 );
	$("input[id^=dp_add_level], input[id^=dp_move_level], input[id^=dp_remove_level]").val('##/##/####');
	$("input[id^=dp_add_level], input[id^=dp_move_level], input[id^=dp_remove_level]").change(
		function(){
			var selected = $(this).val();
			var question ="Sorry, the date you have selected has already passed.";
			var currentD = new Date();
			var currentDate = $.datepicker.formatDate('mm/dd/yy', new Date(currentD));

		if (new Date(selected).getTime()  < new Date(currentDate).getTime() ) {

			if(confirm(question) ){				
				$(this).val('##/##/####');
			
		}
	}
});
	$('#search-levels, #save-search, #filter_dates, #filter_status, #filter_sequential, select[name=howmany], #wpm_id').select2({
		allowClear: true,
		minimumResultsForSearch: -1,
		width: 'copy',
		dropdownCssClass: 'select2-nowrap',
		dropdownAutoWidth: true
	});
	$('select[name=wpm_membership_to], select[name=wpm_action]').select2({
		allowClear: true,
		width: 'copy',
		dropdownCssClass: 'select2-nowrap',
		dropdownAutoWidth: true
	});
	jQuery('#wpm_payperposts_to').select2({
		allowClear: true,
		dropdownCssClass: 'select2-nowrap',
		dropdownAutoWidth: true,
		ajax: {
			type: 'POST',
			url: ajaxurl,
			dataType: "jsonp",
			quietMillis: 100,
			data: function(term, page) {
				return {
					action: 'wlm_payperpost_search',
					search: '%' + term + '%',
					page: page,
					page_limit: 15
				}
			},
			results: function(data, page) {
				var more = (page * 15) < data.total;
				return {results: data.posts, more: more};
			}

		},
		formatResult: function(data) {
			return data.post_title;
		},
		formatSelection: function(data) {
			return data.post_title;
		},
		id: function(data) {
			return data.ID;
		}
	});

	jQuery("#filter_dates").change(function() {
		if ($(this).attr('selected', true).val() != '') {
			jQuery("#date_ranges").show("fast");
		} else {
			jQuery("#date_ranges").hide("fast");
		}
	});

	jQuery("#save_search").click(function() {
		jQuery("#save_searchname").toggle(this.checked);
	});

	$('#update-filters').click(function() {
		var q = '&howmany=' + $('select[name=howmany]').val() + '&show_latest_reg=' + $('input[name=show_latest_reg]').attr('checked')
		var url = redir_url + q;
		window.location.href = url;
		return false;

	});

	$('.remove-saved-search').on('click', function(e) {
		e.preventDefault();
		var item = $(this).attr('rel');
		var row = $(this).parent().parent();
		if(confirm('Are you sure you want to delete the selected saved search?')){
			$.post(ajaxurl, {option_name: item, action:'wlm_delete_saved_search'}, function(){

			});
			row.hide('slow');
		}
	});

	$('a.unschedule').on('click', function(e) {
		e.preventDefault();
		var schedule_type = $(this).attr('data-schedule-type');
		var level = $(this).attr('data-level-id');
		var user = $(this).attr('data-user-id');
		var row = (schedule_type == 'remove' || schedule_type == 'cancel') ? $(this).parents('blockquote') : $(this).parents('tr');
		var level_name = $('select[name=wpm_membership_to] option[value='+level+']').text();

		var type = '';
		switch(schedule_type) {
			case 'remove': type = 'REMOVAL'; break;
			case 'add': type = 'ADD'; break;
			case 'move': type = 'MOVE'; break;
			case 'cancel': type = 'CANCELLATION'; break;
		}

		if(confirm('Are you sure you want to UNSCHEDULE the scheduled ' + type + ' for the membership level "' + level_name + '"?')) {
			$.post(ajaxurl, {"schedule_type": schedule_type, "level": level, "user": user, action: 'wlm_unschedule_single'});
			row.css('background', '#ff5050');
			row.find('td, th').css('background', '#ff5050');
			row.fadeOut(500);
		}
	});
});
