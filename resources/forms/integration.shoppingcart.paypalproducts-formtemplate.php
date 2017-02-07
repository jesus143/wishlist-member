<!-- products -->
<?php
	$currencies = array('USD','AUD','BRL','CAD','CZK','DKK','EUR','HKD','HUF','ILS','JPY','MYR','MXN','NOK','NZD','PHP','PLN','GBP','RUB','SGD','SEK','CHF','TWD','THB','TRY');
?>
<div id="setup-products" style="display:none">
	<h2 class="wlm-integration-steps"><?php _e('Set Up Products:','wishlist-member'); ?></h2>
	<br>
	<table class="widefat product-list">
		<thead>
			<tr>
				<th scope="col" width="150"><?php _e('Product Name', 'wishlist-member'); ?></th>
				<th scope="col" width="250"><?php _e('Access', 'wishlist-member'); ?></th>
				<th scope="col" width="75"><?php _e('Recurring', 'wishlist-member'); ?></th>
				<th scope="col" width="75"><?php _e('Currency', 'wishlist-member'); ?></th>
				<th scope="col"><?php _e('Amount', 'wishlist-member'); ?></th>
			</tr>
		</thead>

		<tbody>
		</tbody>

		<tfoot>
			<tr>
				<td colspan="100">
					<i class="icon-spinner icon-spin"></i>
					<em><?php _e('Loading...','wishlist-member'); ?></em>
				</td>
			</tr>
		</tfoot>
	</table>
	<?php include($this->pluginDir . '/resources/lightbox/integration-shortcodes-screenshot.php'); ?>
	<p><?php _e('Select a membership level and click the Add New Product button below to add a new product.','wishlist-member'); ?></p>
	<div style="float:left">
		<select name="sku" class="new-product-level">
			<option value=""><?php _e('Select Access...', 'wishlist-member'); ?></option>
			<optgroup label="Membership Levels">
				<?php foreach($wpm_levels as $sku => $l): ?>
				<option value="<?php echo $sku?>"><?php echo $l['name']?></option>
				<?php endforeach; ?>
			</optgroup>

			<?php foreach ($xposts AS $post_type => $posts) : ?>
			<optgroup label="<?php echo $post_types[$post_type]->labels->name; ?>">
				<?php foreach ((array) $posts AS $post): ?>
				<option value="payperpost-<?php echo $post->ID?>"><?php echo $post->post_title?></option>
				<?php endforeach; ?>
			</optgroup>
			<?php endforeach; ?>
		</select>
		<button href="<?php echo $ppthankyou_url?>?action=new-product" class="button-secondary new-product" disabled="disabled"><?php _e('Add New Product','wishlist-member'); ?></button>
		<span class="spinner"></span>
	</div>
</div>

<script type="text/template" id='product-row'>
	<tr id="product-<%=obj.id%>">
		<td class="column-title col-info col-name">
			<strong><a class="row-title"><%= obj.name %></a></strong>
			<div class="row-actions">
				<span class="edit"><a href="#" rel="<%=obj.id%>" class="edit-product">Edit</a> | </span>
				<span class="delete"><a href="#" rel="<%=obj.id%>" class="delete-product">Delete</a></span>
			</div>
		</td>
		<td class="col-info col-sku">
			<?php if($table_handler_action_prefix == 'wlm_paypalps_') : ?>
				<a style="float: right; margin-left:1em;" href="#TB_inline&width=750&height=400&inlineId=paypal-payment-link" datalink="<%=obj.id%>" title="<%= obj.name %> Payment Form" class="thickbox" onclick="wlm_show_link(this, true)"><i class="icon-code"></i></a>
			<?php endif; ?>
			<a style="float: right; margin-left:1em;" href="#TB_inline&width=750&height=110&inlineId=paypal-payment-link" datalink="<?php echo $payment_link_format ?><%=obj.id%>" title="<%= obj.name %> Payment Link" class="thickbox" onclick="wlm_show_link(this, false)"><i class="icon-link icon-flip-horizontal"></i></a>

			<i class="icon-fixed-width icon-<%= obj.sku.search(/^payperpost-/) ? 'group' : 'file' %>" style="opacity:0.6"></i>

			<%= jQuery('select[name=sku] option[value='+obj.sku+']').html() %>
		</td>
		<td class="col-info col-recurring"><% if(obj.recurring == 1) print("YES"); else print ("NO"); %></td>
		<td class="col-info col-currency"><%=obj.currency%></td>
		<td class="col-info col-amount"><% if(obj.recurring == 1) print(Number(obj.recur_amount).toFixed(2)); else print(Number(obj.amount).toFixed(2)); %></td>


		<td class="col-edit col-name">
			<input class="form-val"  style="width:100%" type="text" name="name" value="<%= obj.name %>"/>
		</td>
		<td class="col-edit col-sku">
			<select name="sku" class="form-val" style="width:100%">
				<optgroup label="Membership Levels">
					<?php foreach($wpm_levels as $sku => $l): ?>
					<option <% if(obj.sku == '<?php echo $sku?>') print('selected="selected"')%> value="<?php echo $sku?>"><?php echo $l['name']?></option>
					<?php endforeach; ?>
				</optgroup>

				<?php foreach ($xposts AS $post_type => $posts) : ?>
				<optgroup label="<?php echo $post_types[$post_type]->labels->name; ?>">
					<?php foreach ((array) $posts AS $post): ?>
					<option <% if(obj.sku == 'payperpost-<?php echo $post->ID?>') print('selected="selected"')%> value="payperpost-<?php echo $post->ID?>"><?php echo $post->post_title?></option>
					<?php endforeach; ?>
				</optgroup>
				<?php endforeach; ?>
			</select>
		</td>
		<td class="col-edit col-recurring">
			<input type="checkbox" class="form-val"  name="recurring" value="1" <% if(obj.recurring == 1) print('checked=checked') %>/>
		</td>
		<td class="col-edit col-currency">
			<select class="form-val" name="currency">
				<?php foreach($currencies as $c): ?>
				<option <% if(obj.currency == '<?php echo $c?>') print ('selected="selected"') %> name="<?php echo $c?>"><?php echo $c?></option>
				<?php endforeach; ?>
			</select>
		</td>
		<td class="col-edit col-amount">
			<div class="recurring">
				<table style="margin: 0 0 0 auto">
					<tr>
						<td width="160"><?php _e('Recurring Amount:', 'wishlist-member'); ?></td>
						<td width="1"><input class="form-val" type="text" name="recur_amount" value="<%=obj.recur_amount%>"/> <br/></td>
					</tr>
					<tr>
						<td><?php _e('Billing Cycle:', 'wishlist-member'); ?></td>
						<td>
							<select class="form-val" name="recur_billing_frequency">
							<?php for($i=0; $i<30; $i++): ?>
								<option <% if(obj.recur_billing_frequency == '<?php echo $i+1?>') print ('selected="selected"') %> value="<?php echo $i+1?>"><?php echo $i+1?></option>
							<?php endfor; ?>
							</select>

							<select class="form-val" name="recur_billing_period">
								<option <% if(obj.recur_billing_period == 'Day') print ('selected="selected"') %> value="Day">Day</option>
								<option <% if(obj.recur_billing_period == 'Week') print ('selected="selected"') %> value="Week">Week</option>
								<option <% if(obj.recur_billing_period == 'Month' || !obj.recur_billing_period) print ('selected="selected"') %> value="Month">Month</option>
								<option <% if(obj.recur_billing_period == 'Year') print ('selected="selected"') %> value="Year">Year</option>
							</select>
						</td>
					</tr>
					<tr>
						<td><?php _e('Stop After:', 'wishlist-member'); ?></td>
						<td>
							<select class="form-val" name="recur_billing_cycles">
							<?php for($i=0; $i<52; $i++): ?>
								<option <% if(obj.recur_billing_cycles == '<?php echo $i+1?>') print ('selected="selected"') %> value="<?php echo $i+1?>"><?php echo $i ? $i+1 . ' ' .($i==1 ? __('cycle', 'wishlist-member') : __('cycles', 'wishlist-member')): __('Never', 'wishlist-member'); ?></option>
							<?php endfor; ?>
							</select>
						</td>
					</tr>
					<?php if($table_handler_action_prefix != 'wlm_payflow_') : ?>
					<tr>
						<td><?php _e('Trial Period:', 'wishlist-member'); ?></td>
						<td>
							<label>
								<input type="checkbox" class="form-val trial"  name="trial" value="1" <% if(obj.trial == 1) print('checked=checked') %>/>
								Enable
							</label>
						</td>
					</tr>
					<?php endif; ?>
					<tr class="trial-row">
						<td><?php _e('Trial Period Amount:', 'wishlist-member'); ?></td>
						<td>
							<input class="form-val" type="text" name="trial_amount" value="<%=obj.trial_amount%>"/> <br/>
						</td>
					</tr>
					<tr class="trial-row">
						<td><?php _e('Define Trial Period:', 'wishlist-member'); ?></td>
						<td>
							<select class="form-val" name="trial_recur_billing_frequency">
							<?php for($i=0; $i<30; $i++): ?>
								<option <% if(obj.trial_recur_billing_frequency == '<?php echo $i+1?>') print ('selected="selected"') %> value="<?php echo $i+1?>"><?php echo $i+1?></option>
							<?php endfor; ?>
							</select>

							<select class="form-val" name="trial_recur_billing_period">
								<option <% if(obj.trial_recur_billing_period == 'Day') print ('selected="selected"') %> value="Day">Day</option>
								<option <% if(obj.trial_recur_billing_period == 'Week') print ('selected="selected"') %> value="Week">Week</option>
								<option <% if(obj.trial_recur_billing_period == 'Month') print ('selected="selected"') %> value="Month">Month</option>
								<option <% if(obj.trial_recur_billing_period == 'Year') print ('selected="selected"') %> value="Year">Year</option>
							</select>
						</td>
					</tr>

					<?php if($table_handler_action_prefix == 'wlm_paypalps_') : ?>
					<tr class="trial-row">
						<td><?php _e('2nd Trial Period:', 'wishlist-member'); ?></td>
						<td>
							<label>
								<input type="checkbox" class="form-val trial2"  name="trial2" value="1" <% if(obj.trial2 == 1) print('checked=checked') %>/>
								Enable
							</label>
						</td>
					</tr>

					<tr class="trial-row trial2-row">
						<td><?php _e('2nd Trial Period Amount:', 'wishlist-member'); ?></td>
						<td>
							<input class="form-val" type="text" name="trial2_amount" value="<%=obj.trial2_amount%>"/> <br/>
						</td>
					</tr>
					<tr class="trial-row trial2-row">
						<td><?php _e('Define 2nd Trial Period:', 'wishlist-member'); ?></td>
						<td>
							<select class="form-val" name="trial2_recur_billing_frequency">
							<?php for($i=0; $i<30; $i++): ?>
								<option <% if(obj.trial2_recur_billing_frequency == '<?php echo $i+1?>') print ('selected="selected"') %> value="<?php echo $i+1?>"><?php echo $i+1?></option>
							<?php endfor; ?>
							</select>

							<select class="form-val" name="trial2_recur_billing_period">
								<option <% if(obj.trial2_recur_billing_period == 'Day') print ('selected="selected"') %> value="Day">Day</option>
								<option <% if(obj.trial2_recur_billing_period == 'Week') print ('selected="selected"') %> value="Week">Week</option>
								<option <% if(obj.trial2_recur_billing_period == 'Month') print ('selected="selected"') %> value="Month">Month</option>
								<option <% if(obj.trial2_recur_billing_period == 'Year') print ('selected="selected"') %> value="Year">Year</option>
							</select>
						</td>
					</tr>
					<?php endif; ?>
					<?php if($table_handler_action_prefix == 'wlm_paypalec_') : ?>
					<tr>
						<td><?php _e('Max Failed Payments:','wishlist-member'); ?></td>
						<td>
							<input class="form-val" type="text" name="max_failed_payments" value="<%=obj.max_failed_payments?obj.max_failed_payments:1%>"/> <br/>
						</td>
					</tr>
					<?php endif; ?>
				</table>
			</div>
			<div class="onetime">
				<input class="form-val" type="text" name="amount" value="<%=obj.amount%>"/>
			</div>
			<br>
			<p class="form-actions">
				<input class="form-val" type="hidden" name="id" value="<%=obj.id%>"/>
				<span class="spinner" style="float:none;margin-right:1px"></span>
				<button class="button-primary save-product"><?php _e('Save','wishlist-member'); ?></button>
				&nbsp;
				<button class="button-secondary cancel-edit"><?php _e('Cancel','wishlist-member'); ?></button>
			</p>

		</td>
	</tr>
</script>

<script>
jQuery(function($) {
	/** table handler **/

	var table_handler = {};

	table_handler.toggle_recurring = function(id) {
		var row = jQuery('#product-' + id);
		var el = row.find('input[name=recurring]');
		if(el.prop('checked')) {
			row.find('.recurring').show();
			row.find('.onetime').hide();
		} else {
			row.find('.recurring').hide();
			row.find('.onetime').show();
		}
	}
	table_handler.toggle_trial = function(id) {
		var row = jQuery('#product-' + id);
		var el = row.find('input.trial');
		if(el.prop('checked')) {
			row.find('.trial-row').show();
			table_handler.toggle_trial2(id);
		} else {
			row.find('.trial-row').hide();
		}
	}
	table_handler.toggle_trial2 = function(id) {
		var row = jQuery('#product-' + id);
		var el = row.find('input.trial2');
		if(el.prop('checked')) {
			row.find('.trial2-row').show();
		} else {
			row.find('.trial2-row').hide();
		}
	}
	table_handler.remove_row = function(id) {
		jQuery('#product-' + id).remove();
		self.table.find('tr').each(function(i, e) {
			jQuery(e).removeClass('alternate');
			if(i % 2 == 0) {
				jQuery(e).addClass('alternate');
			}
		});
		table_handler.toggle_header();
	}

	table_handler.render_row = function(obj) {
		var cnt      = self.table.find('tr').length;
		var template = jQuery("#product-row").html();
		var str      = _.template(template, {'obj': obj} );
		var el       = jQuery('#product-' + obj.id);


		if(el.length > 0) {
			el.replaceWith(str);
		} else {
			self.table.find('tbody').eq(0).append(str);
		}

		table_handler.toggle_recurring(obj.id);
		table_handler.toggle_trial(obj.id);
		table_handler.toggle_trial2(obj.id);

		if(cnt % 2 == 0) {
			self.table.find('tr').eq(cnt).addClass('alternate');
		}

		table_handler.toggle_header();
	}
	table_handler.end_edit = function(id) {
		jQuery('#product-' + id).find('td.col-info').show();
		jQuery('#product-' + id).find('td.col-edit').hide();
	}
	table_handler.edit_row = function(id) {
		jQuery('#product-' + id).find('td.col-info').hide();
		jQuery('#product-' + id).find('td.col-edit').show();
	}
	table_handler.fetch = function() {
		jQuery.post(ajaxurl + '?action=<?php echo $table_handler_action_prefix; ?>all-products', {}, function(res) {
			var obj = JSON.parse(res);
			for(i in obj) {
				table_handler.render_row(obj[i]);
			}
			self.table.find('tfoot td').html('<em>No products to display. Please add a product below.</em>')
		});
	}
	table_handler.edit_product = function(id) {
		table_handler.edit_row(id);
	}
	table_handler.delete_product = function(id) {
		if(confirm('Are you sure you want to delete this product?')) {
			jQuery('#product-' + id + ' td').toggleClass('wlm-ajax-red', true, 1000);
			jQuery.post(ajaxurl + '?action=<?php echo $table_handler_action_prefix; ?>delete-product', {id: id}, function(res) {
				table_handler.remove_row(id);
			});
		}
	}
	table_handler.save_product = function(id) {
		var row = jQuery('#product-' + id);
		row.find('.spinner').css('visibility','visible');

		var data = {};
		row.find('.form-val').each(function(i, e) {
			var el = jQuery(e);
			data[el.prop('name')] = jQuery(el).is(':checkbox')?  ( jQuery(el).is(':checked')? 1 : 0 )  : el.val();
		});


		jQuery.post(ajaxurl + '?action=<?php echo $table_handler_action_prefix; ?>save-product', data, function(res) {
			row.find('.spinner').css('visibility','hidden');
			var obj = JSON.parse(res);
			table_handler.render_row(obj);
			table_handler.end_edit(id);
			jQuery('#product-'+obj.id).toggleClass('wlm-ajax-green', true, 100);
			jQuery('#product-'+obj.id).toggleClass('wlm-ajax-green', false, 3000);
		});
	}
	table_handler.new_product = function() {
		var data = {
			'name' : jQuery('.new-product-level option:selected').html(),
			'sku'  : jQuery('.new-product-level').val()
		};
		jQuery('.new-product').next('.spinner').css('visibility','visible');
		jQuery('.new-product').attr('disabled','disabled');
		jQuery.post(ajaxurl + '?action=<?php echo $table_handler_action_prefix; ?>new-product', data, function(res) {
			var obj = JSON.parse(res);
			var template = jQuery("#product-row").html();
			table_handler.render_row(obj);
			jQuery('.new-product').next('.spinner').css('visibility','hidden');
			jQuery('.new-product').removeAttr('disabled');
			jQuery('#product-'+obj.id).toggleClass('wlm-ajax-green', true, 100);
			jQuery('#product-'+obj.id).toggleClass('wlm-ajax-green', false, 3000);
			table_handler.edit_product(obj.id);
		});
	}
	table_handler.init = function(table) {
		self.table = table;

		jQuery('.new-product').on('click', function(ev) {
			ev.preventDefault();
			table_handler.new_product();
		});

		jQuery('.product-list').on('click', '.delete-product', function(ev) {
			ev.preventDefault();
			table_handler.delete_product( jQuery(this).attr('rel'));
		});

		jQuery('.product-list').on('click', '.edit-product', function(ev) {
			ev.preventDefault();
			table_handler.edit_product( jQuery(this).attr('rel'));
		});

		jQuery('.product-list').on('click', '.save-product', function(ev) {
			ev.preventDefault();
			var id = jQuery(this).parent().find('input[name=id]').val();
			table_handler.save_product(id);
		});

		jQuery('.product-list').on('click', '.cancel-edit', function(ev) {
			ev.preventDefault();
			var id = jQuery(this).parent().find('input[name=id]').val();
			table_handler.end_edit(id);
		});

		jQuery('.product-list').on('change', '.col-recurring input', function(ev) {
			ev.preventDefault();
			var id = jQuery(this).parent().parent().find('input[name=id]').val();
			table_handler.toggle_recurring(id);
		});

		jQuery('.product-list').on('change', '.trial', function(ev) {
			ev.preventDefault();
			var id = jQuery(this).parents('tr[id^=product-]').find('input[name=id]').val();
			table_handler.toggle_trial(id);
		});

		jQuery('.product-list').on('change', '.trial2', function(ev) {
			ev.preventDefault();
			var id = jQuery(this).parents('tr[id^=product-]').find('input[name=id]').val();
			table_handler.toggle_trial2(id);
		});


		table_handler.fetch();

		table_handler.toggle_header();
	}

	table_handler.toggle_header = function() {
		if(self.table.find('tbody tr').length < 1) {
			self.table.find('thead').hide();
			self.table.find('tfoot').show();
			jQuery('.integration-shortcodes-screenshot').hide();
		} else {
			self.table.find('thead').show();
			self.table.find('tfoot').hide();
			jQuery('.integration-shortcodes-screenshot').show();
		}
	}

	table_handler.init(jQuery('.product-list'));

	/* end table handler **/
});

function wlm_show_link(obj, form) {
	if(form) {
		data = {
			action     : 'wlm_paypalps_get-product-form',
			product_id : jQuery(obj).attr('datalink')
		};
		jQuery('#paypal-payment-link-text').hide();
		jQuery('#paypal-payment-form-text').val("Loading...").show();
		jQuery.post(ajaxurl, data, function(result) {
			jQuery('#paypal-payment-form-text').val(result);
			setTimeout(function(){
				document.getElementById('paypal-payment-form-text').focus();
			}, 200);
		});
	} else {
		jQuery('#paypal-payment-form-text').hide();
		jQuery('#paypal-payment-link-text').val(jQuery(obj).attr('datalink')).show();
		setTimeout(function(){
			document.getElementById('paypal-payment-link-text').focus();
		}, 200);
	}
}

function wlm_pp_link_focus(obj) {
	obj.select();
	jQuery('.paypal-payment-link-span').show()
}

function wlm_pp_link_blur(obj) {
	jQuery('.paypal-payment-link-span').hide()
}
</script>
<div id="paypal-payment-link" style="display:none;">
	<p style="text-align:center">
		<input id="paypal-payment-link-text" type="text" onblur="wlm_pp_link_blur(this)" onfocus="wlm_pp_link_focus(this);" onmouseup="return false;" style="font-size:1.2em; padding: .5em; width:100%; text-align:center; display:none;" value="" readonly="readonly">
		<textarea id="paypal-payment-form-text" type="text" onblur="wlm_pp_link_blur(this)" onfocus="wlm_pp_link_focus(this);" onmouseup="return false;" style="font-size:1.2em; padding: .5em; width:100%; height:330px; display:none;" value="" readonly="readonly"></textarea>
		<br>
		<span class="paypal-payment-link-span" style="display: none; font-size:1.2em; font-weight:bold"><?php _e('Press','wishlist-member'); ?> <?php echo strpos($_SERVER['HTTP_USER_AGENT'], 'Mac OS X') ? 'Command' : 'Ctrl'; ?>-C <?php _e('to copy','wishlist-member'); ?></span>
	</p>
</div>