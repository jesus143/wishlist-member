<?php

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class WLM_Manage_Content_Table extends WP_List_Table {

	var $content_type;
	var $manage_comments;

	function __construct() {
		//Set parent defaults
		parent::__construct( array(
				'singular' => 'content', //singular name of the listed records
				'plural'   => 'content', //plural name of the listed records
				'ajax'     => false        //does this table support ajax?
			) );
		$_GET['show'] = isset($_GET['show']) ? $_GET['show'] : '';
		switch ( $_GET['show'] ) {
		case 'pages':
			$this->content_type = 'page';
			break;
		case 'posts':
		case '':
		case null:
			$this->content_type = 'post';
			break;
		case 'files':
			$this->content_type = 'attachment';
			break;
		default:
			$this->content_type = $_GET['show'];
		}

		$this->manage_comments = isset( $_GET['manage_comments'] );

	}

	function has_items() {
		if($this->search_box_called) {
			return true;
		}
		return parent::has_items();
	}

	function search_box($text, $input_id) {
		$this->search_box_called = true;
		parent::search_box($text, $input_id);
		$this->search_box_called = false;
	}

	function column_default( $item, $column_name ) {
		switch ( $column_name ) {
		case 'post_date':
			list( $post_date ) = explode( ' ', str_replace( '-', '/', $item[$column_name] ) );

			return preg_replace('/\d{2}(\d+)\/(\d+)\/(\d+)/', '\2/\3/\1', $post_date);
		case 'wlm_padlock' :
			return '';
		case 'wlm_protection':
			$class_and_id = 'class="wlm-protection" id="wlm-protection-' . $item['ID'] . '"';
			if ( $item[$column_name][1] ) {
				$text = 'Inherited';
			} elseif ( $item[$column_name][0] ) {
				$text = 'Protected';
			} else {
				$text = 'Unprotected';
			}
			return '<span ' . $class_and_id . '>' . $text . '</span>';
		case 'wlm_levels':
			$class_and_id = 'class="wlm-levels" id="wlm-levels-' . $item['ID'] . '"';
			$data         = implode( ',', array_keys( $item[$column_name] ) );
			$immutable    = implode( ',', (array) $item['wlm_levels_immutable'] );
			$names        = implode( ', ', $item[$column_name] );
			if ( ! trim( $names ) ) {
				$names = '&nbsp;&mdash;';
			}
			return '<span ' . $class_and_id . ' data-keys="' . $data . '"" data-immutable="' . $immutable . '">' . $names . '</span>';
		case 'wlm_payperpost':
			$class_and_id = 'class="wlm-payperpost" id="wlm-payperpost-' . $item['ID'] . '"';
			if ( ! $item[$column_name][0] ) {
				$text = 'Disabled';
			} elseif ( $item[$column_name][1] ) {
				$text = 'Free';
			} else {
				$text = 'Paid';
			}
			return '<span ' . $class_and_id . '>' . $text . '</span>';
		case 'wlm_payperpost_users':
			$class_and_id = 'class="wlm-payperpost-users" id="wlm-payperpost-users-' . $item['ID'] . '"';
			return '<span ' . $class_and_id . '>' . $item[$column_name] . '</span>';
		case 'post_categories':
			if ( $this->content_type == 'categories' ) {
				$parent = get_term( $item['parent'], $item['taxonomy'] );
				return $parent->name;
			} elseif ( is_post_type_hierarchical( $this->content_type ) or $this->content_type == 'attachment' ) {
				$parent = get_post( $item['post_parent'] );
				return $parent->post_title;
			} else {
				$post_categories = get_the_category( $item['ID'] );
				foreach ( $post_categories as &$post_category ) {
					$post_category = $post_category->name;
				}
				unset( $post_category );
				return implode( ', ', $post_categories );
			}
		case 'taxonomy':
			return $item[$column_name];
		case 'writable':
			return $item[$column_name] ? 'Yes' : 'No';
		case 'htaccess':
			if ( $item['htaccess_exists'] ) {
				if ( $item['htaccess_writable'] ) {
					return __( 'Ok', 'wishlist-member' );
				} else {
					return sprintf( '<span style="color:red">%s</span>', __( 'Not Writable', 'wishlist-member' ) );
				}
			} else {
				return sprintf( '<span style="color:red">%s</span>', __( 'Not Found', 'wishlist-member' ) );
			}
		case 'files':
			return count( glob( $item['full_path'] . '/*' ) ) - count( glob( $item['full_path'] . '/*', GLOB_ONLYDIR ) );
		case 'force_download':
			$text = $item[$column_name] ? 'Yes' : 'No';
			$class_and_id = 'class="wlm-forcedownload" id="wlm-forcedownload-' . $item['ID'] . '"';
			return '<span ' . $class_and_id . '>' . $text . '</span>';
		default:
			return print_r( $item, true ); //Show the whole array for troubleshooting purposes
		}
	}

	function column_post_title( $item ) {

		if(isset($_GET['show']) && $_GET['show'] == 'categories') {
			$view = sprintf( '<a href="%1$s" target="_blank" class="wlm_view" id="wlm-view-%2$d">%3$s</a>', get_category_link($item['ID']), $item['ID'], __( 'View', 'wishlist-member' ) );
		} else if(isset($_GET['show']) && $_GET['show'] == 'folders') {
			$view = sprintf( '<a href="%1$s" class="wlm-folder" id="%2$s">%3$s</a>', '#', $item['ID'], __( 'View files', 'wishlist-member' ) );
		} else {
			$view = sprintf( '<a href="%1$s" target="_blank" class="wlm_view" id="wlm-view-%2$d">%3$s</a>', get_permalink($item['ID']), $item['ID'], __( 'View', 'wishlist-member' ) );
		}

		$actions = array(
			'edit' => sprintf( '<a href="#" class="wlm-edit-protection" id="wlm-edit-protection-%1$d">%2$s</a>', $item['ID'], __( 'Edit', 'wishlist-member' ) ),
			'view' => $view,
		);
		if ( $item['deep'] && $this->content_type != 'attachment' ) {
			$post_title = str_repeat( '&mdash; ', $item['deep'] ) . $item['post_title'];
		} else {
			$post_title = $item['post_title'];
		}

		if(!in_array($this->content_type, array('categories','folders'))) {
			if($item['post_status'] !='publish' && $item['post_status'] != 'inherit') {
				$post_title .= ' - <em>'
				.($item['post_status'] == 'future' ? 'Scheduled' : ucwords($item['post_status']))
				.'</em>';
			}
		}

		$padlock = $item['wlm_protection'][0] ? 'icon-lock fa fa-lock' : 'icon-unlock fa fa-unlock';

		return sprintf( '<i class="wlm_padlock %3$s" style="white-space:nowrap"></i>&nbsp;&nbsp;%1$s %2$s', $post_title, $this->row_actions( $actions ), $padlock );
	}

	function column_cb( $item ) {
		return sprintf(
			'<input class="bulk-checkbox" type="checkbox" name="%1$s[]" value="%2$s" />',
			/* $1%s */ $this->_args['singular'], //Let's simply repurpose the table's singular label ("movie")
			/* $2%s */ $item['ID']                //The value of the checkbox should be the record's id
		);
	}

	function get_columns() {
		switch ( $this->content_type ) {
		case 'categories':
			$columns = array(
				'cb'              => '<input type="checkbox" />',
				'post_title'      => 'Name',
				'wlm_padlock'     => '',
				'wlm_protection'  => 'Protection',
				'wlm_levels'      => 'Levels',
				'post_categories' => 'Parent',
				'taxonomy'        => 'Type',
			);
			break;
		case 'folders':
			$columns = array(
				'cb'             => '<input type="checkbox" />',
				'post_title'     => 'Name',
				'wlm_padlock'    => '',
				'wlm_protection' => 'Protection',
				'wlm_levels'     => 'Levels',
				'writable'       => 'Writable',
				'htaccess'       => '.htaccess',
				'files'          => 'Files',
				'force_download' => 'Force Download',
			);
			break;
		default:
			$columns = array(
				'cb'                   => '<input type="checkbox" />',
				'post_title'           => 'Name',
				'wlm_padlock'          => '',
				'wlm_protection'       => 'Protection',
				'wlm_levels'           => 'Levels',
				'wlm_payperpost'       => 'Per User Access',
				'wlm_payperpost_users' => 'Post Users',
				'post_categories'      => 'Categories',
				'post_date'            => 'Date',
			);
			if ( is_post_type_hierarchical( $this->content_type ) or $this->content_type == 'attachment' ) {
				$columns['post_categories'] = 'Parent';
			}
			if ( $this->content_type == 'attachment' ) {
				unset( $columns['wlm_payperpost'] );
				unset( $columns['wlm_payperpost_users'] );
			}
			if ( $this->manage_comments ) {
				unset( $columns['wlm_payperpost'] );
				unset( $columns['wlm_payperpost_users'] );
				unset( $columns['post_categories'] );
			}
		}
		return $columns;
	}

	function get_sortable_columns() {
		if ( $this->content_type == 'folders' ) {
			return array();
		}
		$sortable_columns = array(
			'post_title' => array( 'post_title', false ), //true means it's already sorted
			'post_date'  => array( 'post_date', true ),
		);
		return $sortable_columns;
	}

	function get_bulk_actions() {
		$action = array(
			'protection'     => __('Edit Protection Status', 'wishlist-member'),
			'add_levels'     => __('Add Levels', 'wishlist-member'),
			'remove_levels'  => __('Remove Levels', 'wishlist-member'),
		);

		if(post_type_exists($this->content_type) && $this->content_type != 'files' && !$this->manage_comments) {
			$action['ppp']      = __('Edit Pay Per Post Status', 'wishlist-member');
			$action['pppusers'] = __('Manage Pay Per Post Users', 'wishlist-member');
		}

		if($this->content_type == 'folders') {
			$action['force_download'] = __('Edit Force Download Status', 'wishlist-member');
		}
		return $action;
	}

	function bulk_actions( $which = '' ) {
		if ( is_null( $this->_actions ) ) {
			$no_new_actions = $this->_actions = $this->get_bulk_actions();
			/**
			 * Filter the list table Bulk Actions drop-down.
			 *
			 * The dynamic portion of the hook name, `$this->screen->id`, refers
			 * to the ID of the current screen, usually a string.
			 *
			 * This filter can currently only be used to remove bulk actions.
			 *
			 * @since 3.5.0
			 *
			 * @param array $actions An array of the available bulk actions.
			 */
			$this->_actions = apply_filters( "bulk_actions-{$this->screen->id}", $this->_actions );
			$this->_actions = array_intersect_assoc( $this->_actions, $no_new_actions );
			$two = '';
		} else {
			$two = '2';
		}

		if ( empty( $this->_actions ) )
			return;

		echo "<label for='bulk-action-selector-" . esc_attr( $which ) . "' class='screen-reader-text'>" . __( 'Select bulk action' ) . "</label>";
		echo "<select name='action$two' id='bulk-action-selector-" . esc_attr( $which ) . "'>\n";
		echo "<option value='-1' selected='selected'>" . __( 'Select an Action' ) . "</option>\n";

		foreach ( $this->_actions as $name => $title ) {
			$class = 'edit' == $name ? ' class="hide-if-no-js"' : '';

			echo "\t<option value='$name'$class>$title</option>\n";
		}

		echo "</select>\n";

		submit_button( __( 'Apply' ), 'action', false, false, array( 'id' => "doaction$two" ) );
		echo "\n";
	}

	function get_table_classes() {
		$classes   = parent::get_table_classes();
		$classes[] = 'wlm-manage-content-table';
		return $classes;
	}

	function single_row( $item ) {
		static $row_class = '';
		static $prev_tax  = '';

		$row_class = strpos( $row_class, 'alternate' ) === false ? 'alternate' : '';

		if ( $this->content_type == 'categories' ) {
			if ( $prev_tax && $prev_tax != $item['taxonomy'] ) {
				$row_class .= ' wlm-next-taxonomy';
			}
			$prev_tax = $item['taxonomy'];
		}

		$row_class .= ' wlm-content-' . $item['ID'];

		printf( '<tr class="%s" data-content-id="%d" data-content-title="%s">', $row_class, $item['ID'], htmlentities($item['post_title'], ENT_QUOTES) );
		$this->single_row_columns( $item );
		echo '</tr>';
	}

	/**
	 *
	 *
	 * @global \WishListMember $WishListMemberInstance
	 */
	function prepare_items() {
		global $WishListMemberInstance;

		if(isset($_GET['s_perpage'])) {
			$WishListMemberInstance->SaveOption('content-tab-perpage', $per_page = wlm_arrval($_GET, 's_perpage'));
		} else {
			$per_page = (int) $WishListMemberInstance->GetOption('content-tab-perpage') + 0;
		}

		if(!in_array($per_page, array(15, 30, 50, 100, 200))) {
			$per_page = 15;
			$WishListMemberInstance->SaveOption('content-tab-perpage', $per_page);
		}

		$columns  = $this->get_columns();
		$hidden   = array();
		$sortable = $this->get_sortable_columns();

		$this->_column_headers = array( $columns, $hidden, $sortable );

		switch ( $this->content_type ) {
		case 'categories':
			$args = array( 'hide_empty' => 0 );
			if ( 'post_title' == trim( wlm_arrval( $_GET, 'orderby' ) ) ) {
				$args['orderby'] = 'name';
				$args['order']   = trim( wlm_arrval( $_GET, 'order' ) );
			}
			$items = array();
			foreach ( $WishListMemberInstance->taxonomies as $taxonomy ) {
				$x = array();
				foreach ( get_terms( $taxonomy, $args ) as $item ) {
					$item                         = ( array ) $item;
					$item['ID']                   = &$item['term_id'];
					$item['post_title']           = &$item['name'];
					$item['wlm_protection']       = array( $WishListMemberInstance->CatProtected( $item['ID'] ), $WishListMemberInstance->SpecialContentLevel( $item['ID'], 'Inherit', null, '~CATEGORY' ) );
					$item['wlm_levels']           = $WishListMemberInstance->GetContentLevels( 'categories', $item['ID'], true, false, $immutable );
					$item['wlm_levels_immutable'] = $immutable;
					$item['children']             = get_term_children( $item['term_id'], $taxonomy );
					$item['taxonomy']			  = ucfirst($item['taxonomy']);
					$x[$item['ID']]               = $item;
				}
				if ( empty( $args['orderby'] ) && empty( $args['order'] ) ) {
					$y = array();
					foreach ( $x as $id => $item ) {
						$item['deep'] = 0;

						$idx = $item['name'] . "\t" . $item['term_id'];
						$z   = $item;

						while ( $z['parent'] ) {
							$item['deep'] ++;
							$z   = $x[$z['parent']];
							$idx = $z['name'] . "\t" . $z['term_id'] . "\t" . $idx;
						}

						$y[$idx] = $item;
					}
					ksort( $y );
					$x = $y;
				}
				$items += $x;
			}

			$this->items   = $items;
			break;
		case 'folders':
			$rootOfFolders = trim( $WishListMemberInstance->GetOption( 'rootOfFolders' ) );
			$folder_protection_full_path = $WishListMemberInstance->folder_protection_full_path( $rootOfFolders );
			$items         = array();
			if ( $rootOfFolders and is_dir( $folder_protection_full_path ) ) {
				foreach ( glob( $folder_protection_full_path . '/*', GLOB_ONLYDIR ) as $dir_name ) {
					$item     = array();
					$dir_name = basename( $dir_name );
					$fullpath = $folder_protection_full_path . '/' . $dir_name;
					if ( is_dir( $fullpath ) ) {
						$folder_id          = $WishListMemberInstance->FolderID( $dir_name );
						$item['full_path']  = $fullpath;
						$item['post_title'] = basename( $fullpath );

						$item['writable']          = is_writable( $fullpath );
						$item['htaccess_exists']   = file_exists( $fullpath . '/.htaccess' );
						$item['htaccess_writable'] = is_writable( $fullpath . '/.htaccess' );
						$item['wlm_protection']    = array( $WishListMemberInstance->FolderProtected( $folder_id ) );
						$item['force_download']    = $WishListMemberInstance->FolderForceDownload( $folder_id );
						$item['wlm_levels']        = $WishListMemberInstance->GetContentLevels( '~FOLDER', $folder_id, true, false );

						$item['ID'] = $folder_id;

						$items[] = $item;
					}
				}
			}
			$this->items = $items;
			break;
		default:
			$s_status = wlm_arrval($_GET, 's_status') . '';
			$post_status = array('publish', 'pending', 'draft', 'future', 'private');
			if($this->content_type == 'attachment') {
				$post_status[] = 'inherit';
				if($s_status == 'publish') {
					$s_status = 'inherit';
				}
			}
			if($s_status && in_array($s_status, $post_status)) {
				$post_status = array($s_status);
			}

			$args        = array( 'post_type' => $this->content_type, 'post__not_in' => $WishListMemberInstance->ExcludePages( array() ) );

			$s_level = trim(wlm_arrval($_GET,'s_level'));
			if($s_level) {
				$args['post__in'] = $WishListMemberInstance->GetMembershipContent($this->content_type, $s_level);
				$args['post__in'][] = 0;
			}


			$args['post_status'] = $post_status;
			$args['offset'] = !empty($_GET['paged']) ? (wlm_arrval($_GET,'paged') - 1) * $per_page: 0;

			$orderby = trim( wlm_arrval( $_GET, 'orderby' ) );
			$order   = trim( wlm_arrval( $_GET, 'order' ) );
			switch ( $orderby ) {
			case 'post_title':
			case 'post_date':
				$args['orderby'] = $orderby;
				$args['order']   = $order;
				break;
			default:
				$args['orderby'] = 'menu_order';
			}
			$args['posts_per_page'] = $per_page;
			if(isset($_REQUEST['s'])) {
				$args['s'] = $_REQUEST['s'];
			}

			$the_posts = new WP_Query($args);
			$this->items = $the_posts->posts;

			foreach ( $this->items as &$item ) {
				$item                         = ( array ) $item;
				$item['deep']                 = empty( $orderby ) && empty( $order ) ? count( get_post_ancestors( $item['ID'] ) ) : 0;
				if ( $this->manage_comments ) {
					$item['wlm_protection']       = array( $WishListMemberInstance->SpecialContentLevel( $item['ID'], 'Protection', null, '~COMMENT' ), $WishListMemberInstance->SpecialContentLevel( $item['ID'], 'Inherit', null, '~COMMENT' ) );
					$item['wlm_levels']           = $WishListMemberInstance->GetContentLevels( '~COMMENT', $item['ID'], true, false, $immutable );
				} else {
					$item['wlm_protection']       = array( $WishListMemberInstance->Protect( $item['ID'] ), $WishListMemberInstance->SpecialContentLevel( $item['ID'], 'Inherit' ) );
					$item['wlm_payperpost']       = array( $WishListMemberInstance->PayPerPost( $item['ID'] ), $WishListMemberInstance->Free_PayPerPost( $item['ID'] ) );
					$item['wlm_payperpost_users'] = $WishListMemberInstance->count_post_users( $item['ID'], $item['post_type'] );
					$item['wlm_levels']           = $WishListMemberInstance->GetContentLevels( $item['post_type'], $item['ID'], true, false, $immutable );
				}
				$item['wlm_levels_immutable'] = $immutable;
			}
			unset( $item );
		}

		$total_items = $the_posts->found_posts;

		$this->set_pagination_args( array(
				'total_items' => $total_items, //WE have to calculate the total number of items
				'per_page'    => $per_page, //WE have to determine how many items to show on a page
				'total_pages' => ceil( $total_items / $per_page )   //WE have to calculate the total number of pages
			) );
	}

}
