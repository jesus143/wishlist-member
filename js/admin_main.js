(function ( $ ) {

	// WishListLighBox plugin with support for tabs
	// See membereship levels advanced search for usage
	$.fn.WishListLightBox = function( options ) {
		// This is the easiest way to have default options.
		var settings = $.extend({
			// These are the defaults.
			trigger  : null,
			autoopen : false
		}, options );

		var self = this;

		self.backdrop = self.next('.media-modal-backdrop');

		self.open_modal = function() {
			self.backdrop.show();
			self.show();
			self.focus();
			$('body').css('overflow','hidden');
		}
		self.close_modal = function() {
			self.backdrop.hide();
			self.hide();
			$('body').css('overflow','auto');
		}

		self.open_tab = function (tab) {
			self.find('.media-router a').removeClass('active');
			self.find('a[href='+tab+']').addClass('active');
			self.find('.panel').hide();
			$(tab).show();
		}

		self.oncancel = function(settings) {
			if('function' === typeof settings.oncancel) {
				settings.oncancel();
			}
		}
		self.init = function(settings) {
			if(settings.trigger != null) {
				settings.trigger.on('click', function(ev) {
					self.open_modal();
				});
			}

			// we need to set a tab index. Otherwise this
			// div will not recieve focus, thus will not fire
			// keydown events

			self.attr('tabindex', 0);

			// handle tab clicks
			self.on('click', '.media-router a',  function(ev) {
				self.open_tab( $(this).attr('href'));
			});

			// handle close click
			self.on('click', '.media-modal-close', function(ev) {
				self.close_modal();
				self.oncancel(settings);
			});

			// clicking outside the form closes the box
			self.backdrop.on('click', function(ev) {
				self.close_modal();
				self.oncancel(settings);
			});

			// escape closes the box
			self.on('keydown', function(event) {
				if ( 27 === event.which ) {
					self.close_modal();
					self.oncancel(settings);
				}
			});

			var tab = $(location).attr('hash');
			// open the correct tab if needed
			if($(tab).length > 0 && $.contains( self, $(tab))) {
				self.open_tab(tab);
			} else {
				self.find('.media-router a').get(0).click();
			}

			if(settings.autoopen) {
				self.open_modal();
			}
		}
		self.init(settings);
		return self;
	}

}(jQuery));

jQuery(document).ready(function($) {
	//for loading wlm feeds on dashboard
	jQuery(function($) {
		data = {
			action: 'wlm_feeds'
		}
		$.ajax({
			type: 'POST',
			url: admin_main_js.wlm_feed_url,
			data: data,
			success: function(response) {
				if($.trim(response) != ""){
					$('.wlrss-widget').html(response);
					$('#wlrss-postbox').show();
				}
			}
		});
	});

	//for email broadcast
	jQuery("#send-mail-queue").click(function(){
		var container = jQuery("#send-mail-queue-modal");
		var mails = [];
		var mailcount = 0;
		jQuery("#send-mail-queue-modal").modal({
	                overlayCss:{
	                	backgroundColor:'#222'
	            	},
	                containerCss:{
	                	backgroundColor:'#fff',
	                	border:'1px solid #ccc',
	                	padding:'8px',
	                	width: '400px'
	                },
	                onClose: function () {
						jQuery.modal.close();
						window.location.assign(document.URL);
					}
		});
		//get the number of emails in queue
		var data = {
			WishListMemberAction: 'EmailBroadcast',
			EmailBroadcastAction: 'GetEmailQueue'
		}

		jQuery.ajax({
			type: 'POST',
			url: admin_main_js.wlm_broacast_url,
			data: data,
			success: function(response) {
				mails = jQuery.parseJSON(response);
				if ( mails && mails.length > 0 ) {
					mailcount = mails.length;
					container.find(".email-queue-count-holder").html(mailcount);
					container.find(".modal-sent-percent").html("0%");

					var sent = 0;
					var failed = 0;
					var pcnt = 0;
					jQuery.each( mails, function( key, value ) {

						data = {
							WishListMemberAction: 'EmailBroadcast',
							EmailBroadcastAction: 'SendEmailQueue',
							QueueID: value
						}

						jQuery.ajax({
							type: 'POST',
							url: admin_main_js.wlm_broacast_url,
							data: data,
							success: function(response) {

								if(response == true || response == "true" ){
									sent++;
								}else{
									failed++;
								}
							},
							error:function() {
								failed++;
							},
							complete:function(){
								pcnt = (sent + failed) / mailcount;
								pcnt = pcnt * 100;
								pcnt = Math.round(pcnt);
								container.find(".modal-sent-count").html(sent);
								container.find(".modal-failed-count").html(failed);
								container.find(".modal-sent-percent").html(pcnt +"%");
								if(pcnt >= 100){
									container.find(".simplemodal-close").show();
									container.find(".waiting-msg").hide();
								}
							}
						});
					});
				}else{
					container.find(".email-queue-count-holder").html("0");
					container.find(".simplemodal-close").show();
					container.find(".waiting-msg").hide();
					alert("No emails found in queue.");
				}
			},
			error:function() {
				container.find(".email-queue-count-holder").html("-error-");
				container.find(".simplemodal-close").show();
				container.find(".waiting-msg").hide();
				alert("An error occured while processing the email queue. Please refresh the page and try again.");
			}
		});

	});

	//for email broadcast
	jQuery(".requeue-failed-emails").click(function(){
		var container = jQuery("#requeue-failed-modal");
		var broadcastid = parseInt( jQuery(this).attr('broadcastid') );
		var subject = jQuery(this).attr('subject');

		if ( ! broadcastid ) {
			alert("Invalid Broadcast ID. Please refresh the page and try again.");
			return false;
		}

		jQuery("#requeue-failed-modal").modal({
					overlayClose: true,
					autoResize:true,
					autoPosition: true,
					minWidth: 350,
					position: ["10%",""],
	                overlayCss:{
	                	backgroundColor:'#222'
	            	},
	                containerCss:{
	                	backgroundColor:'#fff',
	                	border:'1px solid #ccc',
	                	padding:'0px 8px',
	                },
					onShow: function (dialog) {
						var modal = this;
						dialog.container.css("height", "auto");
						jQuery.modal.setPosition();
					},
	                onClose: function () {
						container.find(".modal-container").html("Retrieving failed emails...");
						container.find(".modal-title").html("Failed Emails");
						jQuery.modal.close();
					}
		});

		container.find(".modal-title").html("Failed Emails for <strong>" +subject +"</strong>");
		container.find(".requeue-failed-button").hide();
		container.find(".remove-failed-email").hide();
		container.find(".select-all-emails").hide();

		container.find(".requeue-failed-button").attr("href","javascript:void(0);");
		//get the failed of emails
		var data = {
			WishListMemberAction: 'EmailBroadcast',
			EmailBroadcastAction: 'GetFailedEmails',
			BroadcastID: broadcastid,
		}
		jQuery.ajax({
			type: 'POST',
			url: admin_main_js.wlm_broacast_url,
			data: data,
			success: function(response) {
				mails = jQuery.parseJSON(response);
				txt = "";
				if ( mails && Object.keys(mails).length > 0 ) {
					jQuery.each( mails, function( key, value ) {
						txt += "<tr><td><input type='checkbox' name='failedids[]' class='failedids' value='" +key +"' /></td><td>" +value +"</td></tr>";
					});
					container.find(".modal-container").html("<table class='widefat' style='border:none;'>" +txt +"</select>");
					container.find(".remove-failed-email").attr("href",admin_main_js.wlm_broacast_url +"&action=removefailed&id=" +broadcastid);

					container.find(".requeue-failed-button").show();
					container.find(".remove-failed-email").show();
					container.find(".select-all-emails").show();

				} else {
					alert("No emails found in queue.");
					container.find(".modal-container").html("Retrieving failed emails...");
					container.find(".modal-title").html("Failed Emails");
					jQuery.modal.close();
				}
			},
			error:function() {
				alert("An error occured while processing the email queue. Please refresh the page and try again.");
				container.find(".modal-container").html("Retrieving failed emails...");
				container.find(".modal-title").html("Failed Emails");
			}
		});
	});

	jQuery(".requeue-failed-button").click(function(){
		var container = jQuery("#requeue-failed-modal");
		var failedids = [];
		container.find('.failedids:checkbox').each( function() {
			if ( $(this).is(':checked') ) {
				failedids.push($(this).val());
			}
		});
		if ( failedids.length <= 0 ) {
			alert("No email selected to be requeued.");
			return false;
		}

		container.find(".modal-container").html("Requeuing selected emails. Please wait...");
		container.find(".requeue-failed-button").hide();
		container.find(".remove-failed-email").hide();
		container.find(".select-all-emails").hide();
		//get the failed of emails
		var data = {
			WishListMemberAction: 'EmailBroadcast',
			EmailBroadcastAction: 'RequeueFailedEmails',
			FailedIDs: failedids.join(),
		}
		jQuery.ajax({
			type: 'POST',
			url: admin_main_js.wlm_broacast_url,
			data: data,
			success: function(response) {
				if ( response == "" ) {
					jQuery.modal.close();
					alert("Selected email where added to your queue.");
					window.location.assign(document.URL);
				} else {
					alert(response);
					window.location.assign(document.URL);
				}
			},
			error:function() {
				alert("An error occured while queueing the emails. Please refresh the page and try again.");
				window.location.assign(document.URL);
			}
		});
	});

	jQuery(".select-all-failed-emails").click(function(){
		var container = jQuery("#requeue-failed-modal");
		var status = $(this).is(':checked');
		container.find(':checkbox').each( function() {
		  $(this).attr('checked', status);
		});
	});

});