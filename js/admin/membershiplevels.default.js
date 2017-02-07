function wpm_show_advanced(a) {
	var x = document.getElementById(jQuery(a).attr('wlm-target'));
	var d = x.style.display
	x.style.display = (d == 'none') ? '' : 'none'

	if (a.getElementsByTagName('span')[0]) {
		a.getElementsByTagName('span')[0].innerHTML = (d === 'none') ? '&mdash;' : '+';
	}
	jQuery('.select2').select2({
		allowClear: true,
		minimumResultsForSearch: -1,
		width: function() {return jQuery(this.element[0]).width()},
		dropdownCssClass: 'select2-nowrap',
		dropdownAutoWidth: true
	});
	return false;
}

jQuery(function($) {
	var fixHelper = function(e, ui) {
		ui.children().each(function() {
			$(this).width($(this).width());
		});
		return ui;
	};
	$.inline_editor = function(tbl) {
		var self = this;
		self.tbl = tbl;
		self.current_editor = null;

		self.init = function() {
			tbl.find('.wpm_row_edit').live('click', function(ev) {
				ev.preventDefault();
				var id = $(this).attr('rel');
				self.edit(id);
			});

			tbl.find('.wpm_row_editor_close').live('click', function(ev) {
				ev.preventDefault();
				var id = $(this).attr('data-id');
				self.cancel(id);
			});
			tbl.find('.wpm_row_editor_submit').live('click', function(ev) {
				ev.preventDefault();
				var id = $(this).attr('data-id');
				self.submit(id);
			});

			tbl.find('.wpm_row_delete').live('click', function(ev) {
				ev.preventDefault();
				var id = $(this).attr('rel');
				self.remove(id);
			});

			self.tbl.find("tbody").sortable({
				opacity: 0.6,
				cursor: 'move',
				items: 'tr.wpm_level_row',
				placeholder: 'ui-state-highlight',
				helper: fixHelper,
				over: function(e, ui) {
					//$('.ui-state-highlight').html('<td colspan="5">&nbsp;</td>');
				},
				start: function(e, ui) {
					if (self.current_editor != null) {
						self.cancel(self.current_editor);
					}
				},
				stop: function(e, ui) {
					var tbody = ui.item.parent();
					var data = {
						reorder: {},
						action: 'wlm_reorder_membership_levels'
					};

					tbody.find('tr').each(function(i, el) {
						var row = $(el);
						var id = row.attr('id').split('-')[1];
						data.reorder[id] = i;
					});

					$.post(ajaxurl, data);
				}
			});//.disableSelection();
		}

		self.remove_editor = function(id) {
			var editor = $('#wlmEditRow-' + id);
			editor.hide('fast', function() {
				editor.remove();
				self.current_editor = null;
			});

			var row = $('#wpm_level_row-' + id);
			var title = row.find('.row-title');
			var title_text = title.html();
			title_text.trim();
			if (title_text.charAt(0) == "-") {
				title_text = title_text.replace("-", "+");
			} else if (title_text.charAt(0) != "+") {
				title_text = "+ " + title_text;
			}
			title.html(title_text);
			row.find('.row-edit').addClass("wpm_row_edit");
			row.find('.row-edit').removeClass("link-disabled");
		}
		self.edit = function(id) {

			//ensure only one editor is shown
			if (self.current_editor != null && self.current_editor != id) {
				self.cancel(self.current_editor);
			}

			if (self.current_editor == id) {
				self.cancel(id);
				return;
			}

			var row = $('#wpm_level_row-' + id);
			row.after('<tr class="levels_loading" style="background:#f7fbff"><td colspan="100"><div style="width:150px;margin: 0 auto"><span class="spinner" style="float:left;margin-top:-1px"></span>loading level settings...</div></td></tr>');
			$('.levels_loading .spinner').show();

			var data = {
				'action': 'wlm_form_membership_level',
				'id': id
			}

			$.post(ajaxurl, data, function(res) {
				$('.levels_loading').remove();
				row.after(res);
				row.next().show('slow', function() {
					zc_initialize(row.next().find('.wlmClipButton'));
				});
				self.current_editor = id;
				//reinitialize tooltips
				initialize_tooltip(jQuery);
				//initialize select2 dropdowns
				$('.select2').select2({
					allowClear: true,
					minimumResultsForSearch: -1,
					width: function() {return $(this.element[0]).width()},
					dropdownCssClass: 'select2-nowrap',
					dropdownAutoWidth: true
				});
			});

			var title = row.find('.row-title');
			var title_text = title.html();
			title_text.trim();
			if (title_text.charAt(0) == "+") {
				title_text = title_text.replace("+", "-");
			}
			title.html(title_text);
			row.find('.row-edit').removeClass("wpm_row_edit");
			row.find('.row-edit').addClass("link-disabled");
		}
		self.submit = function(id) {
			var row = $('#wpm_level_row-' + id);
			var form = $('#form-' + id);
			var editor = $('#wlmEditRow-' + id);
			var spinner = editor.find('.spinner');


			spinner.show();
//			$.post('<?php echo admin_url()?>', form.serialize(), function(res) {
			$.post(ajaxurl, form.serialize(), function(res) {

				//immediately show changes to level name and url
				row.find('.row-title').html(form.find('input[name*="name"]').val());
				var url = row.find('a.wpm_regurl').eq(0);
				var urlparts = url.html().split('/register/')
				var newurl = urlparts[0] + '/register/' + form.find('input[name*=url]').val()

				url.attr('href', newurl);
				url.html(newurl);

				row.find('.wlmClipButton').attr('data-clipboard-text', newurl);
				jQuery('.wrap').find('p').after('<div class="updated fade"><p>Membership Levels Updated</p></div>');
				jQuery('.updated').fadeOut(6000);
				self.cancel(id);
			});

		}
		self.cancel = function(id) {
			self.remove_editor(id);
		}
		self.remove = function(id) {
			var row = $('#wpm_level_row-' + id)
			var editor = $('#wlmEditRow-' + id);

			var cont = false;
			cont = confirm('Please confirm you want to delete this Membership Level.')
			if (!cont) {
				return;
			}
			cont = confirm('Are you sure? Deleting a Membership Level cannot be undone.' + "\n" + "\n");
			if (!cont) {
				return;
			}

			data = {
				'action': 'wlm_del_membership_level',
				'id': id
			};
			$.post(ajaxurl, data, function(res) {
				editor.hide('slow');
				row.hide('slow', function() {
					row.remove();
					editor.remove();
				});
			});

		};
		self.init();
	};
	$.inline_editor($('#wpm_membership_levels'));

	$('.wpm_row_editor_close').live('click', function(ev) {
		ev.preventDefault();
		var id = $(this).attr('data-id');
	});
});