<h2><?php _e('Settings &raquo; Setup Wizard', 'wishlist-member'); ?></h2>
<?php
$pages = get_pages('exclude=' . implode(',', $this->ExcludePages(array(), true)));
$levels = $this->GetOption('wpm_levels');

$system_pages = array(
	'non_members_error_page_internal'	=> $this->GetOption('non_members_error_page_internal'),
	'membership_cancelled_internal'		=> $this->GetOption('membership_cancelled_internal'),
	'wrong_level_error_page_internal'	=> $this->GetOption('wrong_level_error_page_internal'),
	'after_login_internal'				=> $this->GetOption('after_login_internal'),
	'after_registration_internal'		=> $this->GetOption('after_registration_internal')
);

?>
<style type="text/css">

    #wlmwizard {
        margin-top: 5px;
        border: 1px solid #DADADA;
        border-bottom: none;
    }
    .wlmwizard-step {
        margin-top: -1px;
        background-color: #FFFFFF;
        border-top: 1px solid #DADADA;

    }
    .wlmwizard-step p{
		margin-left: 10px;
    }
    .wlmwizard-step .navigate {
        margin-top: 2em;
        margin-left: 10px;
    }
    .wlmwizard-step .addlevel {
        margin-left: 10px;
    }
    #wlmwizard .info {
        background-color: #F1F1F1;
        border-color: #DFDFDF;
        border-bottom: 1px solid #DADADA;
    }


    .wlmwizard-step .wlmwizard-title {
        padding-left: 10px;
        padding-top: 7px;
        height: 24px;
        line-height: 1em;
        border-bottom: 1px solid #DADADA;
        background-color:#F1F1F1;

    }
    .wlmwizard-title span {
        font-size: 1.1em;
    }
    .wlmwizard-step-contentwrap {
        margin-top: 1em;
        margin-bottom: 1em;
    }
    .wlmwizard-form {
        vertical-align: bottom;
        width: 50%;
        padding-left: 1em;
        padding-right: 5em;
    }
    .wlmwizard-instruction {
        vertical-align: bottom;
        padding-right: 1em;
        padding-left: 5em;
        width: 49.9%;
    }
    .wlmwizard-separator {
        width:1px;
        border-left: 1px solid #DADADA;
    }
    .wlmwizard-instruction hr, .wlmwizard-form hr {
        background-color: #DADADA;
    }
    .wlmwizard-complete {
        color: green;
    }
    .wlmwizard-error {
        padding-left: 8px;
    }
    .wlmwizard-incomplete, .wlmwizard-error {
        color: red;
    }
</style>

<?php if (wlm_arrval($_GET,'saved') == '1'): ?>
	<div id="wlmwizard" style="padding-bottom: 0px;">
		<div class="info">
			<table>
				<tr>
					<td class="wlmwizard-form" style="vertical-align: text-top">
						<h3 style="text-align: center"><?php _e('Congratulations, your setup is complete!', 'wishlist-member'); ?></h3>
						<p style="text-align: center"><?php _e('What would you like to do now?', 'wishlist-member'); ?></p>
						<p style="text-align: center">
							<a href="admin.php?page=<?php echo $this->MenuID ?>"><?php _e('Return to WishList Member Dashboard', 'wishlist-member'); ?></a>
						</p>
						<p style="text-align: center">- OR - </p>
						<p style="text-align: center">
							<a href="http://customers.wishlistproducts.com/wishlist-member-tutorials/" target="_blank"><?php _e('Click Here to View Video Tutorials', 'wishlist-member'); ?></a>
						</p>
					</td>
					<td class="wlmwizard-separator">&nbsp;</td>
					<td class="wlmwizard-instruction">
						<p><strong><?php _e('Next Steps...', 'wishlist-member'); ?></strong></p>
						<p><?php _e('The basic settings are now in place for this WishList Member powered site.', 'wishlist-member'); ?></p>
						<p><?php _e('Some recommended next steps are adding content to the error pages and redirect pages that were created during the set up wizard process.', 'wishlist-member'); ?></p>
						<p><?php _e('Viewing the available tutorial videos is also encouraged as they explain and run through many of the WishList Member features and functionality.', 'wishlist-member'); ?></p>
						<p><?php _e('<a href="http://customers.wishlistproducts.com/wishlist-member-tutorials/" target="_blank">Click Here to View Video Tutorials</a>', 'wishlist-member'); ?></p>
					</td>
				</tr>
			</table>
		</div>
	</div>
<?php else: ?>
	<h2 style="font-size:18px;width:100%"><?php _e('Setup Wizard', 'wishlist-member'); ?></h2>


	<form method="post">
		<div id="wlmwizard">
			<div class="wlmwizard-error"></div>
			<!-- Start step 1 -->
			<div class="wlmwizard-step" id="wizard-step-1">
				<div class="wlmwizard-title">
					<span>Step 1 - Create Membership Levels</span>
					<div class="" style="float: right"><span class="wlmwizard-incomplete" id="wlmwizard-complete-1">[INCOMPLETE]</span> &nbsp;</div>
				</div>
				<div class="wlmwizard-step-contentwrap">
					<table>
						<tr>
							<td class="wlmwizard-form">
								<table class="form-table levelsform">
									<tr>
										<th><?php _e('Membership Level', 'wishlist-member'); ?></th>
									</tr>
									<?php if(!empty($levels)): ?>
									<?php foreach($levels as $i => $l): ?>
									<tr>
										<td>
											<input type="text" name="membership_levels[<?php echo $i?>]" value="<?php echo $l['name']?>" size="60"/>
										</td>
									</tr>
									<?php endforeach; ?>
									<?php else: ?>
									<tr>
										<td>
											<input type="text" name="membership_levels[]" value="" size="60"/>
										</td>
									</tr>
									<?php endif; ?>
								</table>
								<p class="addlevel">
									<a href="" class="button wlmwizard-btn-addlevel"> <?php _e('Add Another Membership Level', 'wishlist-member'); ?></a>
								</p>
								<p class="navigate">
									<a href="" class="button wlmwizard-btn-next"> <?php _e('Next', 'wishlist-member'); ?></a>
								</p>
							</td>
							<td class="wlmwizard-separator">&nbsp;</td>
							<td class="wlmwizard-instruction" style="vertical-align: middle;">
								<p>
									<span><strong>Instructions</strong></span>
								</p>
								<p>
									WishList Member uses the Membership Levels you create as the foundation for controlling access to each element of your site. 
									In this step you will create your first Membership Level. 
								</p>
								<p>
									<span><strong>NOTE:</strong> You can edit or adjust your Membership Level names later.</span><br/>
									<!-- <a href="#">Video Tutorial</a> -->
								</p>
							</td>
						</tr>

					</table>
				</div>
			</div>
			<!-- End step 1 -->
			<div class="wlmwizard-step" id="wizard-step-2">
				<div class="wlmwizard-title">
					<span> Step 2 - Assign Error Pages</span>
					<div class="" style="float: right"><span class="wlmwizard-incomplete" id="wlmwizard-complete-2">[INCOMPLETE]</span> &nbsp;</div>
				</div>
				<div class="wlmwizard-step-contentwrap">
					<table>
						<tr>
							<td class="wlmwizard-form">
								<p><?php _e('Select Non-Member page:', 'wishlist-member'); ?></p>
								<p>
									<select name="non_members_error_page_internal"">
										<option value=""></option>
										<?php foreach ($pages AS $page): ?>
											<?php $selected = $page->ID == $system_pages['non_members_error_page_internal']? 'selected="selected"' : null ?>
											<option <?php echo $selected?> value="<?php echo $page->ID ?>"><?php echo $page->post_title ?></option>
										<?php endforeach; ?>
									</select>
									&nbsp;&nbsp;&nbsp;
									<strong>-OR-</strong>
									&nbsp;&nbsp;
									<?php $checked = empty($system_pages['non_members_error_page_internal'])? 'checked="checked"' : null ?>
									&nbsp;<input <?php echo $checked?> type="radio" name="autocreate_non_members_error_page_internal"/>&nbsp;
									Create it For Me
								</p>
								<hr/>
							</td>
							<td class="wlmwizard-separator">&nbsp;</td>
							<td class="wlmwizard-instruction">
								<p>
									<span><strong>Instructions</strong></span>
								</p>
								<p>
									<strong> Non-Member Error Page</strong> - This page will appear to anyone who is not a part of any Membership Level when trying to access protected content.  This page will also appear to Members who are not logged in.
								</p>
								<hr/>
							</td>
						</tr>
						<tr>
							<td class="wlmwizard-form">
								<p><?php _e('Select Cancellation page:', 'wishlist-member'); ?></p>
								<p>
									<select name="membership_cancelled_internal">
										<option value=""></option>
										<?php foreach ($pages AS $page): ?>
											<?php $selected = $page->ID == $system_pages['membership_cancelled_internal']? 'selected="selected"' : null ?>
											<option <?php echo $selected?> value="<?php echo $page->ID ?>"><?php echo $page->post_title ?></option>
										<?php endforeach; ?>
									</select>
									&nbsp;&nbsp;&nbsp;
									<strong>-OR-</strong>
									&nbsp;&nbsp;
									<?php $checked = empty($system_pages['membership_cancelled_internal'])? 'checked="checked"' : null ?>
									&nbsp;<input <?php echo $checked?> type="radio" name="autocreate_membership_cancelled_internal"/>&nbsp;
									Create it For Me
								</p>
								<hr/>
							</td>
							<td class="wlmwizard-separator">&nbsp;</td>
							<td class="wlmwizard-instruction">
								<p>
									<strong>Cancellation Page</strong> - This page will appear when a Member attempts to login after their Membership Level has been cancelled.
								</p>
								<hr/>
							</td>
						</tr>
						<tr>
							<td class="wlmwizard-form">
								<p><?php _e('Select Wrong Member error page:', 'wishlist-member'); ?></p>
								<p>
									<select name="wrong_level_error_page_internal">
										<option value=""></option>
										<?php foreach ($pages AS $page): ?>
											<?php $selected = $page->ID == $system_pages['wrong_level_error_page_internal']? 'selected="selected"' : null ?>
											<option <?php echo $selected?> value="<?php echo $page->ID ?>"><?php echo $page->post_title ?></option>
										<?php endforeach; ?>
									</select>
									&nbsp;&nbsp;&nbsp;
									<strong>-OR-</strong>
									&nbsp;&nbsp;
									<?php $checked = empty($system_pages['wrong_level_error_page_internal'])? 'checked="checked"' : null ?>
									&nbsp;<input <?php echo $checked?> type="radio" name="autocreate_wrong_level_error_page_internal"/>&nbsp;
									Create it For Me
								</p>
								<p class="navigate">
									<a href="" class="button wlmwizard-btn-back"> <?php _e('Previous', 'wishlist-member'); ?></a>
									<a href="" class="button wlmwizard-btn-next"> <?php _e('Next', 'wishlist-member'); ?></a>
								</p>
							</td>
							<td class="wlmwizard-separator">&nbsp;</td>
							<td class="wlmwizard-instruction">
								<p>
									<strong>Wrong Member Error Page</strong> - This page will appear to Members who attempt to access content that is not assigned to their Membership Level(s).
								</p>
								<p>
									<!--<a href="#">Video Tutorial</a> |-->
									<?php echo $this->Tooltip("settings-wizard-tooltips-error-pages"); ?>
								</p>
							</td>
						</tr>
					</table>
				</div>
			</div>

			<div class="wlmwizard-step"  id="wizard-step-3">
				<div class="wlmwizard-title">
					<span>Step 3 - Protection Settings</span>
					<div class="" style="float: right"><span class="wlmwizard-incomplete" id="wlmwizard-complete-3">[INCOMPLETE]</span> &nbsp;</div>
				</div>
				<div class="wlmwizard-step-contentwrap">
					<table>
						<tr>
							<td class="wlmwizard-form">
								<p><?php _e('Automatically protect all posts/pages?', 'wishlist-member'); ?></p>
								<p>
									<?php $val = $this->GetOption('default_protect'); ?>
									<label><input type="radio" name="default_protect" value="1" <?php if($val == "1") echo 'checked="checked"'?>> Yes</label>&nbsp;
									<label><input type="radio" name="default_protect" value="0" <?php if($val == "0") echo 'checked="checked"'?>> No</label>
								</p>
								<hr/>
							</td>
							<td class="wlmwizard-separator"></td>
							<td class="wlmwizard-instruction">
								<p>
									<span><strong>Instructions</strong></span>
								</p>
								<p>
									<strong>Automatic Protection</strong> -  If this option is set to Yes, then all posts and pages will be protected by default. If this option is set to No, then all posts and pages will be unprotected by default.
									<br><br>
									<strong> Note </strong>: Posts and pages will be protected or unprotected by default based on this setting but they can also be protected or unprotected within the WishList Member settings if desired.
								</p>
								<hr/>
							</td>
						</tr>
						<tr>
							<td class="wlmwizard-form" style="vertical-align: top;">
								<p><?php _e('Turn hide/show protection on?', 'wishlist-member'); ?></p>
								<p>
									<?php $val = $this->GetOption('only_show_content_for_level'); ?>
									<label><input type="radio" name="only_show_content_for_level" value="1" <?php if($val == "1") echo 'checked="checked"'?>> Yes</label>&nbsp;
									<label><input type="radio" name="only_show_content_for_level" value="0" <?php if($val == "0") echo 'checked="checked"'?>> No</label>
								</p>

								<p class="navigate">
									<a href="" class="button wlmwizard-btn-back"> <?php _e('Previous', 'wishlist-member'); ?></a>
									<a href="" class="button wlmwizard-btn-next"> <?php _e('Next', 'wishlist-member'); ?></a>
								</p>
							</td>
							<td class="wlmwizard-separator"></td>
							<td class="wlmwizard-instruction">
								<p>
									<strong>Hide/Show Protection</strong> - Also know as “Only show content for each Membership Level” in the WishList Member settings.
									<br><br>
									Protected content (menu items, posts titles in blog rolls, etc.) will not be visible to Non-Member when this option is set to Yes.
									<br><br>
									Protected content (menu items, posts titles in blog rolls, etc.) will be visible to Non-Members when this option is set to No. But the content will not be accessible when clicked to view by Non-Members and they will be redirected to the Non-Members error page.

								</p>
								<p>
									<!--<a href="#">Video Tutorial</a> |-->
									<?php echo $this->Tooltip("settings-wizard-tooltips-protection"); ?>
								</p>

							</td>
						</tr>
					</table>
				</div>
			</div>
			<div class="wlmwizard-step"  id="wizard-step-3">
				<div class="wlmwizard-title">
					<span>Step 4 - Member Redirect Pages</span>
					<div class="" style="float: right"><span class="wlmwizard-incomplete" id="wlmwizard-complete-4">[INCOMPLETE]</span> &nbsp;</div>
				</div>
				<div class="wlmwizard-step-contentwrap">
					<table>
						<tr>
							<td class="wlmwizard-form">
								<p><?php _e('Select the After Registration page that Members will see immediately after registration.', 'wishlist-member'); ?></p>
								<p>
									<select name="after_registration_internal">
										<option value=""></option>
										<?php foreach ($pages AS $page): ?>
											<?php $selected = $page->ID == $system_pages['after_registration_internal']? 'selected="selected"' : null ?>
											<option <?php echo $selected?> value="<?php echo $page->ID ?>"><?php echo $page->post_title ?></option>
										<?php endforeach; ?>
									</select>

									<strong>-OR-</strong>

									<?php $checked = empty($system_pages['after_registration_internal'])? 'checked="checked"' : null ?>
									&nbsp;<input <?php echo $checked?> type="radio" name="autocreate_after_registration_internal"/>&nbsp;
									Create it For Me
								</p>
								<hr/>
							</td>
							<td class="wlmwizard-separator"></td>
							<td class="wlmwizard-instruction">
								<p>
									<span><strong>Instructions</strong></span>
								</p>
								<p>
									<strong>After Registration Page</strong> - Members are redirected to a specific page immediately after registration. A default After Registration page can be set here.  Each Membership Level will direct to this After Registration page.<br><br>
									The After Registration page can be set to a unique page for each Membership Level in the Levels tab of WishList Member if desired.<br><br>
									<strong>Note</strong>: The After Registration page will only be seen by the Member once as it will only be viewed after the Member registers.
						</p>
						<hr/>
						</td>
						</tr>
						<tr>
							<td class="wlmwizard-form" style="vertical-align: top;">
								<p><?php _e('Select the After Login page that Members will see immediately after logging in.', 'wishlist-member'); ?></p>
								<p>
									<select name="after_login_internal">
										<option value=""></option>
										<?php foreach ($pages AS $page): ?>
											<?php $selected = $page->ID == $system_pages['after_login_internal']? 'selected="selected"' : null ?>
											<option <?php echo $selected?> value="<?php echo $page->ID ?>"><?php echo $page->post_title ?></option>
										<?php endforeach; ?>
									</select>

									<strong>-OR-</strong>

									<?php $checked = empty($system_pages['after_login_internal'])? 'checked="checked"' : null ?>
									&nbsp;<input <?php echo $checked?> type="radio" name="autocreate_after_login_internal"/>&nbsp;
									Create it For Me</p>
								<p class="navigate">
									<a href="" class="button wlmwizard-btn-back"> <?php _e('Previous', 'wishlist-member'); ?></a>
								</p>
								<p>

								</p>
							</td>
							<td class="wlmwizard-separator"></td>
							<td class="wlmwizard-instruction">
								<p>
									<strong>After Login Page</strong> - Members are redirected to the set After Login page each time they login. <br><br>
									This After Login page can be set to a unique page for each Membership Level in the Levels tab of WishList Member if desired.
								</p>
								<p>
									<!--<a href="#">Video Tutorial</a> |-->
									<?php echo $this->Tooltip("settings-wizard-tooltips-redirect"); ?>
								</p>
								<p class="submit" style="float:right">
									<input type="hidden" name="WishListMemberAction" value="WizardSetup">
									<input type="submit" value="<?php _e("Save This Setup", 'wishlist-member'); ?>" class="button-primary" name="Submit">
								</p>
							</td>
						</tr>

					</table>
				</div>
			</div>
		</div>
	</form>

	<div id="add-level-template" style="display:none">
		<table>
		<tr>
			<td>
				<input type="text" name="membership_levels[]" value="" size="60"/>
				<a href='#' class='wlmwizard-btn-removelevel'>remove</a>
			</td>
		</tr>
		</table>
	</div>

	<script type="text/javascript">
		jQuery(function($) {
			function clear_error_ui() {
				var err_ui = $('#wlmwizard').children(':first');
				err_ui.html("");
			}
			function show_error(msg) {
				var err_ui = $('#wlmwizard').children(':first');
				err_ui.html("");
				err_ui.append("<p style=\"margin-top: 2px\" class=\"error fade\"><strong>"+msg+"</strong></p>");
			}
			var quickadd_levels = {
				table: null,
				init: function(el, btn) {
					var t = this;
					t.table = el
					btn.live('click', function(ev) {
						ev.preventDefault();
						t.add_level();
					});

					t.table.find('a').live('click', function(ev) {
						ev.preventDefault();
						t.remove_level(this);
					});

				},
				add_level: function() {
					var t = this.table;
					var r = $('#add-level-template table tbody').html();
					$(r).appendTo(t);
				},
				remove_level: function(o) {
					$(o).parent().parent().remove();
				}
			}

			var handlers = {
				handle_step_1: function(ui) {
					quickadd_levels.init($('.levelsform'), $('.wlmwizard-btn-addlevel'));
				},
				handle_step_2: function(ui) {
					var s = ui.find('select');
					s.live('change', function(ev) {
						var i = $(this);
						var tr = i.parents('tr');
						if(i.val() == "") {
							tr.find('input[type=radio]').attr('checked', true);
						} else {
							tr.find('input[type=radio]').removeAttr('checked');
						}
					});

					var r = ui.find('input[type=radio]');
					r.live('change', function(ev) {
						var i = $(this);
						var tr = i.parents('tr');
						if(i.attr('checked')) {
							tr.find('select').val("");
						}
					});
				},
				handle_step_3: function(ui) {
				},
				handle_step_4: function(ui) {
					handlers.handle_step_2(ui);
				}
			}
			var validators = {
				validate_step_1: function(ui) {
					var els = ui.find('input[type=text]');
					if(els.length <= 0) {
						return false;
					}

					var err = true;
					els.each(function(i, inp) {
						if($(this).val() == "") {
							show_error("Please make sure that all membership levels have names");
							err = false;
							return false;
						}
					});
					return err;
				},
				validate_step_2: function(ui) {
					return true;
				},
				validate_step_3: function(ui) {
					return true;
				}
			}
			var wlmwizard = {
				step: 0,
				frames: null,
				init: function(el) {
					var t = this;
					t.frames = el.find('.wlmwizard-step-contentwrap');

					t.frames.hide();
					t.frames.eq(0).show();
					//attach the events to the navigators
					el.find('.wlmwizard-btn-back').live('click', function(ev) {
						ev.preventDefault();
						t.prev();
						return false;
					});
					el.find('.wlmwizard-btn-next').live('click', function(ev) {
						ev.preventDefault();
						t.next();
						return false;
					});

					$.each(t.frames, function(i,el) {
						var fn = "handle_step_" + (i+1);
						if(typeof handlers[fn] != 'undefined') {
							handlers[fn]($(el));
						}
					});
				},
				next: function() {
					var v = validators;
					var t = this;
					var cur_pos = t.step;
					var err_ui = t.frames.parent('div').children(":first");
					var status = v['validate_step_' + (cur_pos + 1)](t.frames.eq(cur_pos), err_ui);

					if(status == false) {
						t.complete($('#wlmwizard-complete-' + (t.step + 1)), false);
						return;
					}
					clear_error_ui();
					//change incomplete to complete
					t.complete($('#wlmwizard-complete-' + (t.step + 1)), true);

					t.step = t.step + 1;
					t.frames.eq(cur_pos).slideUp(600, function(){
						t.frames.eq(t.step).slideDown(300);
					});
				},
				prev: function() {
					var t = this;
					var cur_pos = t.step;
					t.step = t.step - 1;
					t.frames.eq(cur_pos).slideUp(600, function(){
						t.frames.eq(t.step).slideDown(300);
					});

				},
				save: function() {
					var t = this;
				},
				validate: function() {
					var t = this;
				},
				complete: function(e, k) {
					e.removeClass('wlmwizard-complete');
					e.removeClass('wlmwizard-incomplete');
					if(k == true) {
						e.html('[COMPLETE]');
						e.addClass('wlmwizard-complete')
					} else {
						e.html('[INCOMPLETE]');
						e.addClass('wlmwizard-incomplete')
					}
				}
			}
			wlmwizard.init($('#wlmwizard'));

		});
	</script>
<?php endif; ?>