<?php

if (!class_exists('WishListWidget')) {
	class WishListWidget extends WP_Widget {

		function __construct() {
			parent::__construct('WishListWidget', 'WishList Member', array('description' => 'WishList Member.'));	
		}
	 
		// widget main
		function widget($args = array(), $instance = array()) {
			
			global $WishListMemberInstance;

			// If $instance is false then this means that the widget is being called directly by other functions
			// We then set the $instance

			$is_active = is_active_widget( false, false, 'wishlistwidget', true);

			if(!$instance) {
				// If there are active WLM widgets, we get the settings of the first one
				if($is_active) {

					$wlm_widget_settings = get_option( 'widget_wishlistwidget' );

					foreach( (array) $wlm_widget_settings as $setting) {

						$instance['wpm_widget_hiderss'] = $setting['wpm_widget_hiderss'];
						$instance['wpm_widget_hideregister'] = $setting['wpm_widget_hideregister'];
						$instance['wpm_widget_nologinbox'] = $setting['wpm_widget_nologinbox'];
						$instance['wpm_widget_hidelevels'] = $setting['wpm_widget_hidelevels'];
						$instance['wpm_widget_fieldwidth'] = $setting['wpm_widget_fieldwidth'];

						break;
					}
				} else {
					// If no WLM widget active then set default settings.
					$instance['wpm_widget_hiderss'] = 0;
					$instance['wpm_widget_hideregister'] = 0;
					$instance['wpm_widget_nologinbox'] = 0;
					$instance['wpm_widget_hidelevels'] = 0;
					$instance['wpm_widget_fieldwidth'] = 0;
				}
			}

			extract($args);
			$wpm_levels = $WishListMemberInstance->GetOption('wpm_levels');
			$wpm_current_user = wp_get_current_user();
			if ($instance['wpm_widget_nologinbox'] != 1 || $wpm_current_user->ID) {
				if (!wlm_arrval($_GET, 'reg')) {

					$output = '';
					if (!$return) {
						echo $before_widget . $before_title;
						if ($wpm_current_user->ID) {
							if (isset($args["title"]))
								echo $args["title"];
							else
								echo $instance['title'];
						}else {
							if (isset($args["title2"]))
								echo $args["title2"];
							else
								echo $instance['title2'];
						}
						echo $after_title;
						echo "<div id='wlmember_loginwidget'>";
					}
					if ($wpm_current_user->ID) {
						$name = $wpm_current_user->first_name;
						if (!$name
						)
							$name = $wpm_current_user->user_nicename;
						if (!$name
						)
							$name = $wpm_current_user->user_login;
						$output.='<p>' . trim(sprintf(__('Welcome %1$s', 'wishlist-member'), $name)) . '</p>';
						$levels = $WishListMemberInstance->GetMembershipLevels($wpm_current_user->ID, null, null, null, true);
						$inactivelevels = $WishListMemberInstance->GetMemberInactiveLevels($wpm_current_user->ID);
						sort($levels); // <- we sort the levels
						if (!$instance['wpm_widget_hidelevels']) {
							$clevels = count($levels);

							if ($clevels) {
								//	$output.=__("&raquo; Level", "&raquo; Levels", $clevels, 'wishlist-member');
								if ($clevels == 1) {
									$output.=__("&raquo; Level", 'wishlist-member');
								} else {
									$output.=__("&raquo; Levels", 'wishlist-member');
								}

								$output.=': ';
								if ($clevels > 1)
									$output.='<br /><div id="" style="margin-left:1em">';
								$morelevels = false;
								$maxmorelevels = $return ? 1000000000 : 2;
								for ($i = 0; $i < $clevels; $i++) {
									if ($i > $maxmorelevels && !$morelevels) {
										$output.='<div id="wlm_morelevels" style="display:none">';
										$morelevels = true;
									}
									if ($clevels > 1
									)
										$output.='&middot; ';
									$strike = '';
									if (in_array($levels[$i], $inactivelevels)) {
										$output.='<strike>';
										$strike = '</strike>';
									}
									$output.=$wpm_levels[$levels[$i]]['name'];
									$output.=$strike;
									$output.='<br />';
								}
								if ($morelevels) {
									$output.='</div>';
									$output.='&middot; <label style="cursor:pointer;" onclick="wlmml=document.getElementById(\'wlm_morelevels\');wlmml.style.display=wlmml.style.display==\'none\'?\'block\':\'none\';this.innerHTML=wlmml.style.display==\'none\'?\'' . __('More levels', 'wishlist-member') . ' <small>&nabla;</small>\':\'' . __('Less levels', 'wishlist-member') . ' <small>&Delta;</small>\';this.blur()">' . __('More levels', 'wishlist-member') . ' <small>&nabla;</small></label>';
								}
								if ($clevels > 1)
									$output.='</div>';
							}
						}

						if ($WishListMemberInstance->GetOption('members_can_update_info')) {
							$output.='&raquo; <a href="' . get_bloginfo('wpurl') . '/wp-admin/profile.php">' . __('Membership Details', 'wishlist-member') . '</a><br />';
						}
						if ($instance['wpm_widget_hiderss'] != 1) {
							$output.='&raquo; <a href="' . get_bloginfo('rss2_url') . '">' . __('RSS Feed', 'wishlist-member') . '</a><br />';
						}
						if (function_exists('wp_logout_url')) {
							// $logout = wp_logout_url(get_bloginfo('url'));
							$logout = wp_logout_url();
							if ( $WishListMemberInstance->GetOption('enable_logout_redirect_override') ) {
								$logout = wp_nonce_url(site_url('wp-login.php?action=logout', 'login'), 'log-out');
							 }; 
						} else {
							// $logout = wp_nonce_url(site_url('wp-login.php?action=logout&redirect_to=' . urlencode(get_bloginfo('url')), 'login'), 'log-out');
							$logout = wp_nonce_url(site_url('wp-login.php?action=logout', 'login'), 'log-out');
						}
						$output.='&raquo; <a href="' . $logout . '">' . __('Logout', 'wishlist-member') . '</a><br />';
						if ($return)
							return $output;
						echo $output;
					}else {
						$register = $WishListMemberInstance->GetOption('non_members_error_page_internal');
						$register = $register ? get_permalink($register) : $WishListMemberInstance->GetOption('non_members_error_page');
						$widget_fieldwidth = (int) $instance['wpm_widget_fieldwidth'];
						$login_url = esc_url(site_url( 'wp-login.php', 'login_post' ));
						if (!$widget_fieldwidth
						)
							$widget_fieldwidth = 15;

						echo '<form method="post" action="' . $login_url . '"><p>' . __('You are not currently logged in.', 'wishlist-member') . '</p>';
						echo '<span class="wlmember_loginwidget_input_username_holder"><label>' . __('Username', 'wishlist-member') . ':</label><br /><input class="wlmember_loginwidget_input_username"  type="text" name="log" size="' . $widget_fieldwidth . '" /></span><br />';
						echo '<span class="wlmember_loginwidget_input_password_holder"><label>' . __('Password', 'wishlist-member') . ':</label><br /><input class="wlmember_loginwidget_input_password" type="password" name="pwd" size="' . $widget_fieldwidth . '" /></span><br />';
						echo '<span class="wlmember_loginwidget_input_checkrememberme_holder"><input  class="wlmember_loginwidget_input_checkrememberme" type="checkbox" name="rememberme" value="forever" /> <label>' . __('Remember Me', 'wishlist-member') . '</label></span><br />';
						echo '<span class="wlmember_loginwidget_input_submit_holder"><input class="wlmember_loginwidget_input_submit" type="submit" name="wp-submit" value="' . __('Login', 'wishlist-member') . '" /></span><br /><br />';
						if ($instance['wpm_widget_hideregister'] != 1) {
							echo '<span class="wlmember_loginwidget_link_register_holder">&raquo; <a href="' . $register . '">' . __('Register', 'wishlist-member') . '</a></span><br />';
						}
						echo '<span class="wlmember_loginwidget_link_lostpassword_holder">&raquo; <a href="' . wp_login_url() . '?action=lostpassword">' . __('Lost your Password?', 'wishlist-member') . '</a></span>';
						if($WishListMemberInstance->GetOption('enable_login_redirect_override'))
							$redirect = !empty($_GET['wlfrom']) ? esc_attr(stripslashes($_GET['wlfrom'])) : 'wishlistmember';
						else 
							$redirect = '';
						echo '<input type="hidden" name="wlm_redirect_to" value="' . $redirect . '" />';
						echo '<input type="hidden" name="redirect_to" value="' . $redirect . '" /></form>';
					}
					if (!$return) {
						echo "</div>";
						echo $after_widget;
					}
				}
			}
				
		}
		
		function form($instance) {

			$title = ! empty( $instance['title'] ) ? $instance['title'] : '';
			$title2 = ! empty( $instance['title2'] ) ? $instance['title2'] : '';
			$rsschecked = $instance['wpm_widget_hiderss'] ? ' checked="checked" ' : '';
			$registerchecked = $instance['wpm_widget_hideregister'] ? ' checked="checked" ' : '';
			$nologinboxchecked = $instance['wpm_widget_nologinbox'] ? ' checked="checked" ' : '';
			$hidelevelschecked = $instance['wpm_widget_hidelevels'] ? ' checked="checked" ' : '';
			$widget_fieldwidth = (int) $instance['wpm_widget_fieldwidth'];
			if (!$widget_fieldwidth
			)
				$widget_fieldwidth = 15;

			?>
			<p>
				<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title when logged in::' ); ?></label> 
				<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
			</p>
			<p>
				<label for="<?php echo $this->get_field_id( 'title2' ); ?>"><?php _e( 'Title when logged out::' ); ?></label> 
				<input class="widefat" id="<?php echo $this->get_field_id( 'title2' ); ?>" name="<?php echo $this->get_field_name( 'title2' ); ?>" type="text" value="<?php echo esc_attr( $title2 ); ?>">
			</p>
			<?php
				echo '<p><b>' . __('Advanced Options', 'wishlist-member') . '</b></p>';
			?>
			<p>
				<input id="<?php echo $this->get_field_id( 'wpm_widget_hiderss' ); ?>" name="<?php echo $this->get_field_name( 'wpm_widget_hiderss' ); ?>" type="checkbox" value="1" <?php echo $rsschecked; ?> >
				<label for="<?php echo $this->get_field_id( 'wpm_widget_hiderss' ); ?>"><?php _e('Hide RSS Link', 'wishlist-member'); ?></label>
			</p>
			<p>
				<input id="<?php echo $this->get_field_id( 'wpm_widget_hideregister' ); ?>" name="<?php echo $this->get_field_name( 'wpm_widget_hideregister' ); ?>" type="checkbox" value="1" <?php echo $registerchecked; ?> >
				<label for="<?php echo $this->get_field_id( 'wpm_widget_hideregister' ); ?>"><?php _e('Hide Register Link', 'wishlist-member'); ?></label>
			</p>
			<p>
				<input id="<?php echo $this->get_field_id( 'wpm_widget_nologinbox' ); ?>" name="<?php echo $this->get_field_name( 'wpm_widget_nologinbox' ); ?>" type="checkbox" value="1" <?php echo $nologinboxchecked; ?> >
				<label for="<?php echo $this->get_field_id( 'wpm_widget_nologinbox' ); ?>"><?php _e('Only display if member is logged in', 'wishlist-member'); ?></label>
			</p>

				<input id="<?php echo $this->get_field_id( 'widget_hidelevels' ); ?>" name="<?php echo $this->get_field_name( 'wpm_widget_hidelevels' ); ?>" type="checkbox" value="1" <?php echo $hidelevelschecked; ?> >
				<label for="<?php echo $this->get_field_id( 'widget_hidelevels' ); ?>"><?php _e('Hide membership levels', 'wishlist-member'); ?></label>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id( 'wpm_widget_fieldwidth' ); ?>"><?php _e('Width of Login Fields', 'wishlist-member'); ?></label> <br />
				<input id="<?php echo $this->get_field_id( 'wpm_widget_fieldwidth' ); ?>" name="<?php echo $this->get_field_name( 'wpm_widget_fieldwidth' ); ?>" size="4" type="text" value="<?php echo esc_attr( $widget_fieldwidth ); ?>">
			</p>
			<?php
		}

		public function update( $new_instance, $old_instance ) {
			$instance = array();
			$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
			$instance['title2'] = ( ! empty( $new_instance['title2'] ) ) ? strip_tags( $new_instance['title2'] ) : '';

			$instance['wpm_widget_hiderss'] = ( ! empty( $new_instance['wpm_widget_hiderss'] ) ) ? strip_tags( $new_instance['wpm_widget_hiderss'] ) : '';
			$instance['wpm_widget_hideregister'] = ( ! empty( $new_instance['wpm_widget_hideregister'] ) ) ? strip_tags( $new_instance['wpm_widget_hideregister'] ) : '';
			$instance['wpm_widget_nologinbox'] = ( ! empty( $new_instance['wpm_widget_nologinbox'] ) ) ? strip_tags( $new_instance['wpm_widget_nologinbox'] ) : '';
			$instance['wpm_widget_hidelevels'] = ( ! empty( $new_instance['wpm_widget_hidelevels'] ) ) ? strip_tags( $new_instance['wpm_widget_hidelevels'] ) : '';
			$instance['wpm_widget_fieldwidth'] = ( ! empty( $new_instance['wpm_widget_fieldwidth'] ) ) ? strip_tags( $new_instance['wpm_widget_fieldwidth'] ) : '';

			return $instance;
		}
	}
}