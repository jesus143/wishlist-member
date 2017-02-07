<?php
/*
 * bbPress Integration for Forum and Category protection
 * Original Author :Peter Indiola
 * Version: $Id:
 */
return; // Hiding this for now
//Check if bbPress is exist and is activated.
if(!is_plugin_active('bbpress/bbpress.php'))return;

$__index__ = 'bbpress';
$__other_options__[$__index__] = 'bbPress';
$__other_affiliates__[$__index__] = '';
$__other_videotutorial__[$__index__] = wlm_video_tutorial ( 'integration', 'other', $__index__ );

if ($_GET['other_integration'] == $__index__) {
  if ($__INTERFACE__) {

      $this->IntegrationActive('integration.other.bbpress.php', true);
      $level_names = array();
      foreach($wpm_levels as $sku => $level) {
        $level_names[$sku] = $level['name'];
      }

      ?>
      <style type="text/css">
      .col-edit { display: none;}
      </style>
        <h2>Manage bbPress Forums and Categories</h2>
        <br/>
        <table class="widefat settings-list">
          <thead>
            <tr>
              <th scope="col" width="300"><?php _e('Name', 'wishlist-member'); ?></th>
              <th scope="col"><?php _e('Protection', 'wishlist-member'); ?></th>
              <th scope="col"><?php _e('Levels', 'wishlist-member'); ?></th>
              <th scope="col"><?php _e('Type', 'wishlist-member'); ?></th>
              <th scope="col"><?php _e('Parent', 'wishlist-member'); ?></th>
              <th scope="col">&nbsp;</th>
              <th scope="col">&nbsp;</th>
            </tr>
          </thead>

          <tbody>
          </tbody>
        </table>
        <p>

        <script type="text/template" id='setting-row'>
          <tr id="setting-<%=obj.id%>">
            <td class="column-title col-info col-name">
              <strong><a class="row-title"><%= obj.name %></a></strong>
            </td>
            <td class="col-info col-recurring">
              <% if(obj.protection == 'Public') { %>
                  Public
              <% } else if (obj.protection == 'Protected') { %>
                  Protected
              <% } else {%>
                  ----
              <% } %>
            </td>
            <td class="col-info col-levels"><%= level_names[obj.level] %></td>
            <td class="col-info col-type"><%=obj.type%></td>
					  <td class="col-info col-parent"><% if(obj.parent == 1) print("Yes"); else print ("No"); %></td>
            <td class="col-info col-sku">
              <%= level_names[obj.sku] %>
            </td>
            <td class="col-edit col-name">
              <strong><a class="row-title"><%= obj.name %></a></strong>
            </td>
            <td class="col-edit col-recurring">
              <select class="form-val" name="protection">
                 <option name="---">---</option>
                 <option <% if(obj.protection == 'Public') print ('selected="selected"') %> name="public">Public</option>
                 <option <% if(obj.protection == 'Protected') print ('selected="selected"') %> name="protected">Protected</option>
              </select>
            </td>
            <td class="col-edit col-currency">
              <select name="sku" class="form-val">
                <optgroup label="Membership Levels">
                  <?php foreach($wpm_levels as $sku => $l): ?>
                  <option <% if(obj.level == '<?php echo $sku?>') print('selected="selected"')%> value="<?php echo $sku?>"><?php echo $l['name']?></option>
                  <?php endforeach; ?>
                </optgroup>
              </select>
            </td>
            </td>
            <td class="col-edit col-type"><%=obj.type%></td>
					  <td class="col-edit col-parent"><% if(obj.parent == 1) print("Yes"); else print ("No"); %></td>
            <td class="col-edit col-data">
            <%=obj.date%>
						<hr/>
						<p class="form-actions">
							<input class="form-val" type="hidden" name="id" value="<%=obj.id%>"/>
							<button class="button-primary save-settings">Save settings</button>
							<button class="button-secondary cancel-edit">Cancel</button>
							<span class="spinner"></span></div>
						</p>
            </td>
            <td>
               <span class="edit"><a href="#" rel="<%=obj.id%>" class="edit-setting">Edit</a></span>
            </td>

          </tr>
        </script>


      <script type="text/javascript">
          var level_names = JSON.parse('<?php echo json_encode($level_names)?>');

          jQuery(function($) {
            $('.dropmenu').on('click', function(ev) {
              ev.preventDefault();
              $('li.dropme ul').not( $(this).parent()).hide();
              console.log($(this).parent().find('ul'));
            });

            function update_fields(el, tr) {
              if (el.val() == 1) {
                tr.find('.amount').find('input').attr('disabled', true).val('');
                tr.find('.plans').find('select').removeAttr('disabled');
              } else {
                tr.find('.plans').find('select').attr('disabled', true).val('');
                tr.find('.amount').find('input').removeAttr('disabled');
              }
            }

            /** table handler **/

            var table_handler = {};


            table_handler.toggle_recurring = function(id) {
              var row = $('#settings-' + id);
              var el = row.find('input[name=recurring]');
              if(el.prop('checked')) {
                row.find('.recurring').show();
                row.find('.onetime').hide();
              } else {
                row.find('.recurring').hide();
                row.find('.onetime').show();
              }
            }
            table_handler.remove_row = function(id) {
              $('#bbsetting-' + id).remove();
              self.table.find('tr').each(function(i, e) {
                $(e).removeClass('alternate');
                if(i % 2 == 0) {
                  $(e).addClass('alternate');
                }
              });
            }

            table_handler.render_row = function(obj) {
              var cnt      = self.table.find('tr').length;
              var template = $("#setting-row").html();
              var str      = _.template(template, {'obj': obj} );
              var el       = $('#setting-' + obj.id);


              if(el.length > 0) {
                el.replaceWith(str);
              } else {
                self.table.find('tbody').eq(0).append(str);
              }

              table_handler.toggle_recurring(obj.id);


              if(cnt % 2 == 0) {
                self.table.find('tr').eq(cnt).addClass('alternate');
              }
            }
            table_handler.end_edit = function(id) {
              $('#setting-' + id).find('td.col-info').show();
              $('#setting-' + id).find('td.col-edit').hide();
            }
            table_handler.edit_row = function(id) {
              $('#setting-' + id).find('td.col-info').hide();
              $('#setting-' + id).find('td.col-edit').show();
            }
            table_handler.fetch = function() {
              $.post(ajaxurl + '?action=wlm_bbpress_all-forums', {}, function(res) {
                var obj = JSON.parse(res);
                for(i in obj) {
                  table_handler.render_row(obj[i]);
                }
              });
            }
            table_handler.edit_setting = function(id) {
              table_handler.edit_row(id);
            }
            table_handler.delete_subscription = function(id) {
              $.post(ajaxurl + '?action=wlm_anetarb_delete-subscription', {id: id}, function(res) {
                table_handler.remove_row(id);
              });
            }
            table_handler.save_subscription = function(id) {
              var row = $('#setting-' + id);
              row.find('.spinner').show();

              var data = {};
              row.find('.form-val').each(function(i, e) {
                var el = $(e);
                data[el.prop('name')] = $(el).is(':checkbox')?  ( $(el).is(':checked')? 1 : 0 )  : el.val();
              });

              $.post(ajaxurl + '?action=wlm_bbpress_save-settings', data, function(res) {
                row.find('.spinner').hide();
                //table_handler.render_row(JSON.parse(res));
                row.find('td.col-info.col-recurring.col-recurring').html(data.protection);
                var levelName = row.find('select[name=sku] option:selected').text();
                row.find('td.col-info.col-levels').html(levelName);
                table_handler.end_edit(id);
                if(res) {
                  $('.wishlist_member_admin').prepend("<div class='updated fade'><p>bbPress Settings Updated!</p></div>");
                  $('.updated').fadeOut(1600, "linear");
                }
              });


            }
            table_handler.new_subscription = function() {
              var data = {
                'name' : $('.new-setting-level option:selected').html(),
                'sku'  : $('.new-setting-level').val()
              };
              $.post(ajaxurl + '?action=wlm_anetarb_new-subscription', data, function(res) {
                var obj = JSON.parse(res);
                var template = $("#setting-row").html();
                table_handler.render_row(obj);
              });
            }
            table_handler.init = function(table) {
              self.table = table;

              $('.new-subscription').on('click', function(ev) {
                ev.preventDefault();
                table_handler.new_subscription();
              });

              $('.settings-list').on('click', '.delete-setting', function(ev) {
                ev.preventDefault();
                table_handler.delete_subscription( $(this).attr('rel'));
              });

              $('.settings-list').on('click', '.edit-setting', function(ev) {
                ev.preventDefault();
                table_handler.edit_setting( $(this).attr('rel'));
              });

              $('.settings-list').on('click', '.save-settings', function(ev) {
                ev.preventDefault();
                var id = $(this).parent().find('input[name=id]').val();
                table_handler.save_subscription(id);
              });

              $('.settings-list').on('click', '.cancel-edit', function(ev) {
                ev.preventDefault();
                var id = $(this).parent().find('input[name=id]').val();
                table_handler.end_edit(id);
              });

              $('.settings-list').on('change', '.col-recurring input', function(ev) {
                ev.preventDefault();
                var id = $(this).parent().parent().find('input[name=id]').val();
                table_handler.toggle_recurring(id);
              });

              table_handler.fetch();
            }



            table_handler.init($('.settings-list'));
            /* end table handler **/
          });
      </script>
      <?php
  }
}
