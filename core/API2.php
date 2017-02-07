<?php

/*
 * WishList Member Application Programming Interface
 * Version 2.0
 * @author Mike Lopez <mike@wishlistproducts.com>
 */

class WLMAPI2 {

	const marker = '/wlmapi/2.0/';

	var $request;
	var $actual_request;
	var $method;
	var $data;
	var $return_type;
	var $result;
	var $base;
	var $wpm_levels;
	var $custom_post_types;
	var $request_aliases = array(
		array( '/^protected\/([^\/]+)\/{0,1}$/', 'levels/protected/\1' ),
		array( '/^protected\/([^\/]+)\/(\d+)\/{0,1}$/', 'levels/protected/\1/\2' ),
		array( '/^content\/([^\/]+)\/(\d+)\/{0,1}$/', 'content/\1/protection/\2' ),
		array( '/^content\/([^\/]+)\/(\d+)\/protection\/{0,1}$/', 'content/\1/protection/\2' ),
		array( '/^categories\/([^\/]+)\/(\d+)\/{0,1}$/', 'taxonomies/\1/protection/\2' ),
		array( '/^taxonomies\/([^\/]+)\/(\d+)\/{0,1}$/', 'taxonomies/\1/protection/\2' ),
	);

	const ERROR_ACCESS_DENIED             = 0x00010000;
	const ERROR_INVALID_AUTH              = 0x00010001;
	const ERROR_INVALID_REQUEST           = 0x00010002;
	const ERROR_INVALID_RETURN_FORMAT     = 0x00010004;
	const ERROR_INVALID_RESOURCE          = 0x00010008;
	const ERROR_FORMAT_NOT_SUPPORTED_JSON = 0x00020001;
	const ERROR_FORMAT_NOT_SUPPORTED_XML  = 0x00020002;
	const ERROR_METHOD_NOT_SUPPORTED      = 0x00040001;

	/**
	 * Constructor
	 * @global WishListMember $WishListMemberInstance
	 * @param string $request Resource
	 * @param string $method (optional) POST, GET, PUT, DELETE. Default GET.
	 * @param array $data (optional) Data to pass
	 */
	function __construct( $request, $method = 'GET', $data = null ) {
		global $WishListMemberInstance;
		/*
		 * special processing for external (remote) requests
		 */
		$external = $request == 'EXTERNAL';
		if ( $external ) {
			// get the requested resource
			$request                = $_SERVER['REQUEST_URI'];
			$this->original_request = $request;
			// get the requested method
			$method                 = $_SERVER['REQUEST_METHOD'];
			if ( $method == 'POST' && ($_POST['____FAKE____'] == 'PUT' OR $_POST['____METHOD_EMULATION____']) == 'PUT' ) {
				$method = 'PUT';
			}
			if ( $method == 'POST' && ($_POST['____FAKE____'] == 'DELETE' OR $_POST['____METHOD_EMULATION____']) == 'DELETE' ) {
				$method = 'DELETE';
			}
			/*
			 * set $data
			 */
			switch ( $method ) {
				case 'GET':
					$data = $_GET;
					break;
				case 'POST':
					$data = $_POST;
					break;
				default:
					if ( ! empty( $_POST ) ) {
						$data = $_POST;
					} else {
						/*
						 * if $method is neither POST or GET then we get the data
						 * from the raw post data in php://input
						 */
						parse_str( file_get_contents( 'php://input' ), $data );
					}
					break;
			}
			list($request) = explode( '&', $request );
			// handling pagination and query limits
			if ( $method == 'GET' ) {
				if ( ! empty( $data['__page__'] ) ) {
					$this->__page__ = ( int ) $data['__page__'];
				}
				if ( ! empty( $data['__per_page__'] ) ) {
					$this->__per_page__ = ( int ) $data['__per_page__'];
				}
				if ( ! empty( $data['__pagination__'] ) && ! empty( $this->__page__ ) ) {
					$this->__pagination__ = ( bool ) $data['__pagination__'];
				}
				unset( $data['__page__'] );
				unset( $data['__per_page__'] );
				unset( $data['__pagination__'] );
			}
		}
		/*
		 * split the requested resource by forward slash
		 */
		$request               = explode( '/', array_pop( explode( WLMAPI2::marker, $request, 2 ) ) );
		/*
		 * the first part is the return type
		 */
		$return_type           = strtoupper( array_shift( $request ) );
		/*
		 * return type verification
		 */
		$accepted_return_types = array( 'XML', 'JSON', 'PHP', 'RAW' );
		/*
		 * return error for invalid return type format
		 */
		if ( $external && ! in_array( $return_type, $accepted_return_types ) ) {
			$this->process_result( $this->error( WLMAPI2::ERROR_INVALID_RETURN_FORMAT ) );
		}
		/*
		 * check if the JSON return type requested is supported by the server
		 * return error if not and it's requested
		 */
		if ( $return_type == 'JSON' ) {
			if ( ! function_exists( 'json_encode' ) ) {
				$this->process_result( $this->error( WLMAPI2::ERROR_FORMAT_NOT_SUPPORTED_JSON ) );
			}
		}
		/*
		 * check if the XML return type requested is supported by the server
		 * return error if not and it's requested
		 */
		if ( $return_type != 'XML' ) {
			if ( ! class_exists( 'SimpleXMLElement' ) ) {
				$this->process_result( $this->error( WLMAPI2::ERROR_FORMAT_NOT_SUPPORTED_XML ) );
			}
		}
		/*
		 * set $request and $actual_request properties
		 */
		$this->request           = implode( '/', $request );
		$this->actual_request    = $this->request;
		/*
		 * set $method property
		 */
		$this->method            = $method;
		/*
		 * set $data property
		 */
		$this->data              = $data;
		/*
		 * set $return_type property
		 */
		$this->return_type       = $return_type;
		/*
		 * set $base property
		 */
		$this->base              = get_bloginfo( 'url' ) . '/?' . WLMAPI2::marker . $this->return_type . '/';
		/*
		 * set $wpm_levels property
		 */
		$this->wpm_levels        = $WishListMemberInstance->GetOption( 'wpm_levels' );
		/*
		 * add custom post types to aliases
		 */
		$this->custom_post_types = array_keys( get_post_types( array( '_builtin' => false ), 'object' ) );
		foreach ( $this->custom_post_types AS $custom_post_type ) {
			$this->request_aliases[] = array(
				'/^levels\/([^\/]+)\/' . $custom_post_type . '\/{0,1}$/', 'levels/\1/posts'
			);
			$this->request_aliases[] = array(
				'/^levels\/([^\/]+)\/' . $custom_post_type . '\/(\d+)\/{0,1}$/', 'levels/\1/posts/\2'
			);
		}
		/*
		 * process request aliases
		 */
		foreach ( $this->request_aliases AS $alias ) {
			if ( preg_match( $alias[0], $this->request ) ) {
				$this->actual_request = preg_replace( $alias[0], $alias[1], $this->request );
				$request              = explode( '/', $this->actual_request );
			}
		}
		/*
		 * assemble the function name and the parameters to pass based
		 * on the structure of the requested resource
		 */
		$functions  = array();
		$parameters = array();
		while ( ! empty( $request ) ) {
			$functions[] = trim( strtolower( array_shift( $request ) ) );
			if ( ! empty( $request ) ) {
				$parameters[] = trim( array_shift( $request ) );
			}
		}
		$functions = array_diff( $functions, array( '' ) );
		$function  = '_' . implode( '_', $functions );
		/*
		 * *********************************************** *
		 * AT THIS POINT, THE FUNCTION NAME IS NOW IN $function
		 * AND THE PARAMETERS IN $parameters
		 * *********************************************** *
		 */
		/*
		 * if $function is a valid resource method then we call it
		 */
		if ( method_exists( $this, $function ) ) {
			/*
			 * authentication processing
			 *
			 * if we're not making an authentication request
			 * then we check if we are already authenticated
			 *
			 * an exception to this is /resources
			 */
			if ( $function == '_resources' ) {
				$result = call_user_func( array( $this, $function ) );
			} else {
				$auth = true;
				if ( $function != '_auth' ) {
					$key    = $this->auth_key();
					$cookie = $this->auth_cookie();
					if ( empty( $GLOBALS['wlm_cookies']->$cookie ) || $GLOBALS['wlm_cookies']->$cookie != $key ) {
						$auth = false;
					}
				}
				/*
				 * if we're authenticated then we call $function
				 * if not, we return an ACCESS DENIED error
				 */
				if ( $auth || ! $external ) {
					$result = call_user_func_array( array( $this, $function ), $parameters );
				} else {
					$result = $this->error( WLMAPI2::ERROR_ACCESS_DENIED );
				}
			}
			/*
			 * let's process the request
			 */
			$this->process_result( $result );
		} else {
			/*
			 * why on earth are we here?
			 *
			 * this means that the requested resource is invalid
			 * so we return appropriate error message
			 */
			$this->process_result( $this->error( WLMAPI2::ERROR_INVALID_REQUEST ) );
		}
	}

	/**
	 * Error Processing
	 * @param mixed $error Can be any of the WLMAPI2 defined ERROR constants or an error message
	 * @return apiResult
	 */
	private function error( $error ) {
		switch ( $error ) {
			case WLMAPI2::ERROR_ACCESS_DENIED:
			case WLMAPI2::ERROR_INVALID_AUTH:
				header( "Status: 401", false, 401 );
				break;
			case WLMAPI2::ERROR_INVALID_RETURN_FORMAT:
			case WLMAPI2::ERROR_INVALID_REQUEST:
			case WLMAPI2::ERROR_INVALID_RESOURCE:
				header( "Status: 404", false, 404 );
				break;
			case WLMAPI2::ERROR_FORMAT_NOT_SUPPORTED_JSON:
			case WLMAPI2::ERROR_FORMAT_NOT_SUPPORTED_XML:
			case WLMAPI2::ERROR_METHOD_NOT_SUPPORTED:
				header( "Status: 415", false, 415 );
				break;
		}
		return array( 'ERROR_CODE' => $error, 'ERROR' => $this->get_error_msg( $error ) );
	}

	/**
	 * Fetch the correct error message if specified
	 *
	 * @staticvar string $error_messages
	 * @param mixed $error Can be any of the WLMAPI2 defined ERROR constants or an error message
	 * @return string Error Message
	 */
	private function get_error_msg( $error ) {
		static $error_messages = array(
			WLMAPI2::ERROR_ACCESS_DENIED             => 'Access Denied - Not authenticated',
			WLMAPI2::ERROR_INVALID_AUTH              => 'Access denied - Invalid authentication',
			WLMAPI2::ERROR_INVALID_REQUEST           => 'Page not found - Invalid method',
			WLMAPI2::ERROR_INVALID_RETURN_FORMAT     => 'Page not found - Invalid return format requested',
			WLMAPI2::ERROR_INVALID_RESOURCE          => 'Page not found - Invalid resource',
			WLMAPI2::ERROR_FORMAT_NOT_SUPPORTED_XML  => 'Unsupported Media Type - Server configuration does not support XML encoding',
			WLMAPI2::ERROR_FORMAT_NOT_SUPPORTED_JSON => 'Unsupported Media Type - Server configuration does not support JSON encoding',
			WLMAPI2::ERROR_METHOD_NOT_SUPPORTED      => 'Method Not Supported',
		);
		if ( isset( $error_messages[$error] ) ) {
			$error = $error_messages[$error];
		}
		return $error;
	}

	/**
	 * Format apiResult based on $return_type
	 * @param apiResult $result apiResult array
	 * @return string formatted apiResult
	 */
	private function process_result( $result ) {
		$success = empty( $result['ERROR_CODE'] ) ? 1 : 0;
		if ( empty( $result ) ) {
			$result = array();
		}
		$result = array( 'success' => $success ) + $result;
		if ( $this->__pagination__ && $this->paginate_total_pages ) {
			$result['pagination'] = array(
				'page'           => $this->paginate_page,
				'total_pages'    => $this->paginate_total_pages,
				'items_per_page' => $this->paginate_per_page,
				'total_items'    => $this->paginate_total_items
			);
		}
		if ( ! empty( $this->selfdoc ) && $success ) {
			$result['supported_verbs'] = $this->selfdoc;
		}
		switch ( $this->return_type ) {
			case 'JSON':
				$result = json_encode( $result );
				break;
			case 'PHP':
				$result = serialize( $result );
				break;
			case 'XML':
				$xml    = $this->toXML( $result );
				$result = $xml->asXML();
				break;
		}
		$this->result = $result;
		return $result;
	}

	/**
	 * Converts array to XML
	 * @param array $array
	 * @param SimpleXMLElement $xml
	 * @param string $xname Node Name
	 * @return SimpleXMLElement
	 */
	private function toXML( $array, $xml = null, $xname = null ) {
		$array = ( array ) $array;
		if ( is_null( $xml ) ) {
			$xml = new SimpleXMLElement( '<root/>' );
		}
		foreach ( $array AS $name => $value ) {
//			$name = strtolower(preg_replace('/[^a-z_]/i', '', $name));
			$name = preg_replace( '/[^a-zA-Z_]/i', '', $name );
			if ( empty( $name ) ) {
				$name = $xname;
			}
			if ( is_object( $value ) ) {
				$value = ( array ) $value;
			}
			if ( is_array( $value ) ) {
				$this->toXML( $value, is_numeric( key( $value ) ) ? $xml : $xml->addChild( $name ), $name );
			} else {
				$xml->addChild( $name, $value );
			}
		}
		return $xml;
	}

	/**
	 * Generates the Public Authentication Key
	 * @global WishListMember $WishListMemberInstance
	 * @staticvar string $hash
	 * @return string Hash (Auth Key)
	 */
	private function auth_key() {
		global $WishListMemberInstance;
		static $hash = 0;
		if ( empty( $hash ) ) {
			$key  = $WishListMemberInstance->GetAPIKey();
			$lock = $GLOBALS['wlm_cookies']->lock;
			if ( empty( $lock ) ) {
				return false;
			}
			$hash = md5( $lock . $key );
		}
		return $hash;
	}

	/**
	 * Returns name of Cookie to use
	 * @staticvar string $cookie
	 * @return string Cookie name
	 */
	private function auth_cookie() {
		static $cookie = 0;
		if ( empty( $cookie ) ) {
			$cookie = md5( 'WLMAPI2' . $this->auth_key() );
		}
		return $cookie;
	}

	private function prepare_found_rows_stuff( &$__limit__, &$__found_rows__ ) {
		$__limit__      = $__found_rows__ = '';
		if ( empty( $this->__page__ ) ) {
			return;
		}
		if ( empty( $this->__per_page__ ) ) {
			$this->__per_page__ = 50;
		}
		$per_page  = $this->__per_page__;
		$page      = ($this->__page__ - 1) * $per_page;
		$__limit__ = sprintf( ' LIMIT %d,%d ', $page, $per_page );
		if ( ! empty( $this->__pagination__ ) ) {
			$__found_rows__ = ' SQL_CALC_FOUND_ROWS ';
		}
	}

	private function set_found_rows() {
		global $wpdb;
		unset( $this->paginate_page );
		unset( $this->paginate_per_page );
		unset( $this->paginate_total_pages );
		unset( $this->paginate_total_items );
		if ( $this->__pagination__ ) {
			$rows                       = $wpdb->get_var( 'SELECT FOUND_ROWS()' );
			$this->paginate_page        = $this->__page__;
			$this->paginate_per_page    = $this->__per_page__;
			$this->paginate_total_items = $rows;
			$this->paginate_total_pages = ceil( $rows / $this->paginate_per_page );
		}
	}

	/*
	 * *********************************************** *
	 * API Methods Start at this Point
	 * IMPORTANT;
	 * None of these methods can be called publicly
	 * *********************************************** *
	 */

	/**
	 * Lists all available resources and their accepted methods
	 *
	 * Resource:
	 * 	/resources : GET
	 *
	 * @return apiResult
	 */
	private function _resources() {
		if ( $this->method == 'GET' ) {
			$resources = get_class_methods( $this );
			foreach ( $resources AS $k => $v ) {
				if ( ! (substr( $v, 0, 1 ) == '_' && substr( $v, 1, 1 ) != '_') || $v == '_resources' || $v == '_auth' ) {
					unset( $resources[$k] );
				}
			}
			$resources         = array_values( $resources );
			$classname         = get_class( $this );
			$resource_variants = array();
			foreach ( $resources AS $key => $resource ) {
				$reflection     = new ReflectionMethod( $classname, $resource );
				$resource_parts = explode( '_', substr( $resource, 1 ) );
				$params         = array();
				foreach ( $reflection->getParameters() AS $param ) {
					$params[] = '{$' . $param->name . '}';
				}
				$required = $reflection->getNumberOfRequiredParameters();
				$variant  = '';
				foreach ( $resource_parts AS $ctr => $part ) {
					$variant.='/' . $part;
					if ( $required <= $ctr ) {
						$resource_variants[] = $variant;
					}
					if ( $params[$ctr] ) {
						$variant.='/' . $params[$ctr];
						$resource_variants[] = $variant;
					}
				}
			}
			$resources    = array_unique( $resource_variants );
			$this->method = 'INFO';
			foreach ( $resources AS $key => $resource ) {
				$function       = $params         = array();
				$resource_split = explode( '/', substr( $resource, 1 ) );
				while ( count( $resource_split ) ) {
					$function[] = array_shift( $resource_split );
					if ( count( $resource_split ) ) {
						$params[] = array_shift( $resource_split );
					}
				}
				$function        = '_' . implode( '_', $function );
				$methods         = call_user_func_array( array( $this, $function ), $params );
				$resource        = array(
					'name'            => $resource,
					'supported_verbs' => array( 'verb' => $methods )
				);
				$resources[$key] = $resource;
			}
//			$this->method = 'GET';
			$this->selfdoc = array();
			return array( 'resources' => array( 'resource' => $resources ) );
		} else {
			return $this->error( WLMAPI2::ERROR_METHOD_NOT_SUPPORTED );
		}
	}

	/**
	 * Resource:
	 *   /auth : GET or POST
	 *
	 * @return apiResult
	 */
	private function _auth() {
		$data        = $this->data;
		$hash        = $this->auth_key();
		$cookiestuff = parse_url( home_url() );
		if ( empty( $cookiestuff['path'] ) ) {
			$cookiestuff['path'] = '/';
		}
		switch ( $this->method ) {
			case 'GET':
				$lock     = md5( strrev( md5( $_SERVER['REMOTE_ADDR'] . microtime() ) ) );
				wlm_setcookie( 'lock', $lock, 0, $cookiestuff['path'] ); // <- set cookie path to make it work with bugged cURL versions
				$response = array(
					'lock' => $lock
				);
				return $response;
			case 'POST':
				if ( $data['key'] !== $hash ) {
					return $this->error( WLMAPI2::ERROR_INVALID_AUTH );
				}
				$cookie_name = $this->auth_cookie();
				wlm_setcookie( $cookie_name, $hash, 0, $cookiestuff['path'] ); // <- set cookie path to make it work with bugged cURL versions
				$response    = array(
					'key' => $hash
				);
				if ( ! empty( $data['support_emulation'] ) ) {
					$response['support_emulation'] = 1;
				}
				return $response;
			default:
				return $this->error( WLMAPI2::ERROR_METHOD_NOT_SUPPORTED );
		}
	}

	/*
	 * *********************************************** *
	 * CONTENT PROTECTION METHODS
	 * *********************************************** *
	 */

	function protected_content( $type, $content_id = null ) {
		global $WishListMemberInstance, $wpdb;
		if ( empty( $content_id ) ) {
			$content_id = null;
		}
		$this->selfdoc = is_null( $content_id ) ? array( 'GET', 'POST' ) : array( 'DELETE' );
		if ( $this->method == 'INFO' ) {
			return $this->selfdoc;
		}
		$types = array_merge( array( 'post', 'page', 'category' ), $this->custom_post_types );
		if ( ! in_array( $type, $types ) ) {
			return $this->error( WLMAPI2::ERROR_INVALID_RESOURCE );
		}
		if ( ! in_array( $type, $this->custom_post_types ) ) {
			$otype = $type == 'category' ? 'categories' : $type . 's';
		} else {
			$otype = $type;
		}
		if ( is_null( $content_id ) ) {
			switch ( $this->method ) {
				case 'GET':
					$content_ids = array();
					switch ( $type ) {
						case 'category':
							$content_ids = $wpdb->get_col( "SELECT `content_id` FROM `{$WishListMemberInstance->Tables->contentlevels}` WHERE `level_id`='Protection' AND `type`='~CATEGORY'" );
							$content     = get_categories( array( 'include' => $content_ids, 'hide_empty' => 0 ) );
							foreach ( $content AS $k => $v ) {
								$content[$k] = array(
									'ID'   => $v->term_id,
									'name' => $v->name
								);
							}
							break;
						default:
							$content_ids   = $wpdb->get_col( $wpdb->prepare( "SELECT `content_id` FROM `{$WishListMemberInstance->Tables->contentlevels}` WHERE `level_id`='Protection' AND `type`=%s", $type ) );
							$content_ids[] = 0;
							$content_ids   = implode( ',', $content_ids );
							$this->prepare_found_rows_stuff( $__limit__, $__found_rows__ );
							$content       = $wpdb->get_results( "SELECT {$__found_rows__} `ID`,`post_title` AS `name` FROM {$wpdb->posts} WHERE `post_type`='{$type}' AND `ID` IN ({$content_ids}) {$__limit__}" );
							$this->set_found_rows();
							if ( $type == 'post' ) {
								$content_ids = array_diff( ( array ) $content_ids, ( array ) $pages );
							} else {
								$content_ids = array_intersect( ( array ) $content_ids, ( array ) $pages );
							}
							break;
					}
					return array( $otype => array( $type => $content ) );
					break;
				case 'POST':
					$ids = ( array ) $this->data['ContentIds'];
					switch ( $type ) {
						case 'category':
							foreach ( $ids AS $content_id ) {
								if ( get_cat_name( $content_id ) ) {
									$WishListMemberInstance->CatProtected( $content_id, 'Y' );
								}
							}
							break;
						case 'page':
						case 'post':
							foreach ( $ids AS $content_id ) {
								if ( get_post_type( $content_id ) == $type ) {
									$WishListMemberInstance->Protect( $content_id, 'Y' );
								}
							}
							break;
					}
					$this->method = 'GET';
					return $this->protected_content( $type );
					break;
				default:
					return $this->error( WLMAPI2::ERROR_METHOD_NOT_SUPPORTED );
			}
		} else {
			switch ( $this->method ) {
				case 'DELETE':
					switch ( $type ) {
						case 'category':
							$WishListMemberInstance->CatProtected( $content_id, 'N' );
							break;
						case 'page':
						case 'post':
							$WishListMemberInstance->Protect( $content_id, 'N' );
							break;
					}
					break;
				default:
					return $this->error( WLMAPI2::ERROR_METHOD_NOT_SUPPORTED );
			}
		}
	}

	/*
	 * *********************************************** *
	 * MEMBERSHIP LEVEL METHODS
	 * *********************************************** *
	 */

	/**
	 * Resource:
	 *   /levels : GET, POST
	 *   /levels/{id} : GET, PUT, DELETE
	 *
	 * @global WishListMember $WishListMemberInstance
	 * @param integer $level_id Optional Membership Level ID
	 * @return apiResult
	 */
	private function _levels( $level_id = null ) {
		global $WishListMemberInstance;
		/*
		 * selfdoc
		 */
		$this->selfdoc = is_null( $level_id ) ? array( 'GET', 'POST' ) : array( 'GET', 'PUT', 'DELETE' );
		if ( $this->method == 'INFO' ) {
			return $this->selfdoc;
		}
		/*
		 * map internal variable names to "beautiful" external ones
		 */
		$level_map      = array(
			'id'                       => 'id',
			'name'                     => 'name',
			'url'                      => 'registration_url',
			'loginredirect'            => 'after_login_redirect',
			'afterregredirect'         => 'after_registration_redirect',
			'allpages'                 => 'access_all_pages',
			'allcategories'            => 'access_all_categories',
			'allposts'                 => 'access_all_posts',
			'allcomments'              => 'access_all_comments',
			'noexpire'                 => 'no_expiry',
			'expire'                   => 'expiry',
			'calendar'                 => 'expiry_period',
			'upgradeTo'                => 'sequential_upgrade_to',
			'upgradeAfter'             => 'sequential_upgrade_after',
			'upgradeMethod'            => 'sequential_upgrade_method',
			'count'                    => 'member_count',
			'requirecaptcha'           => 'require_captcha',
			'requireemailconfirmation' => 'require_email_confirmation',
			'requireadminapproval'     => 'require_admin_approval',
			'isfree'                   => 'grant_continued_access',
			'disableexistinglink'      => 'disable_existing_users_link',
			'registrationdatereset'    => 'registration_date_reset',
			'uncancelonregistration'   => 'uncancel_on_registration',
			'role'                     => 'wordpress_role',
			'levelOrder'               => 'level_order',
			'removeFromLevel'          => 'remove_from_levels'
		);
		/*
		 * flip $level_map so we also have a mirror copy
		 */
		$level_map_flip = array_flip( $level_map );
		/*
		 * level data default values
		 */
		$level_defaults = array(
			'name'                     => '',
			'url'                      => '',
			'loginredirect'            => '---',
			'afterregredirect'         => '---',
			'allpages'                 => '',
			'allcategories'            => '',
			'allposts'                 => '',
			'allcomments'              => '',
			'noexpire'                 => '1',
			'expire'                   => '',
			'calendar'                 => 'Days',
			'upgradeTo'                => '',
			'upgradeAfter'             => '',
			'upgradeMethod'            => '',
			'requirecaptcha'           => '',
			'requireemailconfirmation' => '',
			'requireadminapproval'     => '',
			'isfree'                   => '',
			'disableexistinglink'      => '',
			'registrationdatereset'    => '',
			'uncancelonregistration'   => '',
			'role'                     => 'subscriber',
			'levelOrder'               => '',
			'removeFromLevel'          => ''
		);
		/*
		 * go through each membership level and
		 * re-format the values for outputting
		 */
		$wpm_levels     = $this->wpm_levels;
		foreach ( $wpm_levels AS $id => $level ) {
			$xlevel       = array_fill_keys( $level_map, '' );
			$xlevel['id'] = $id;
			foreach ( $level_map AS $key => $value ) {
				$xkey  = $value;
				$value = $level[$key];
				switch ( $xkey ) {
					case access_all_pages:
					case access_all_categories:
					case access_all_posts:
					case access_all_comments:
					case require_captcha:
					case require_email_confirmation:
					case require_admin_approval:
					case grant_continued_access:
					case disable_existing_users_link:
					case registration_date_reset:
					case uncancel_on_registration:
					case no_expiry:
						$value = empty( $value ) ? 0 : 1;
						break;
					case 'after_login_redirect':
					case 'after_registration_redirect':
						switch ( $value ) {
							case '':
								$value = 'homepage';
								break;
							case '---':
								$value = 'global';
								break;
						}
						break;
					case 'remove_from_levels':
						if ( is_array( $value ) && ! empty( $value ) ) {
							$value = array( 'remove_from_level' => array_keys( $value ) );
						}
						break;
				}
				$xlevel[$xkey] = $value;
			}
			$wpm_levels[$id] = $xlevel;
		}
		/*
		 * if $level_id parameter is not passed then we
		 * expect either a GET or a POST
		 */
		if ( empty( $level_id ) ) {
			switch ( $this->method ) {
				/*
				 * list all levels
				 */
				case 'GET':
					$levels  = array_keys( $wpm_levels );
					$xlevels = array();
					foreach ( $levels AS $level ) {
						$xlevels[] = array( 'id' => $level, 'name' => $wpm_levels[$level]['name'], '_more_' => '/levels/' . $level );
					}
					$wpm_levels = array( 'levels' => array( 'level' => $xlevels ) );
					return $wpm_levels;
					break;
				/*
				 * add new level
				 */
				case 'POST':
					$wpm_levels = $this->wpm_levels;
					$level      = $level_defaults;
					if ( empty( $this->data['name'] ) ) {
						return $this->error( 'You must specify at least the name of the level that you wish to add' );
					}
					foreach ( $wpm_levels AS $xxx ) {
						if ( $xxx['name'] == $this->data['name'] ) {
							return $this->error( 'The name of the level that you wish to add is already in use. Please specify a different one' );
						}
						if ( $xxx['url'] == $this->data['registration_url'] ) {
							return $this->error( 'The registration URL of the level that you wish to add is already in use. Please specify a different one OR leave it blank to have it auto-generated' );
						}
					}
					while ( isset( $wpm_levels[$id = time()] ) ) {
						sleep( 1 );
					}
					foreach ( $this->data AS $key => $value ) {
						switch ( $key ) {
							case 'after_login_redirect':
							case 'after_registration_redirect':
								switch ( strtolower( $value ) ) {
									case 'global':
									case '':
										$value = '---';
										break;
									case 'homepage':
										$value = '';
										break;
								}
								break;
						}
						$key = $level_map_flip[$key];
						if ( isset( $level[$key] ) ) {
							$level[$key] = $value;
						}
					}
					if ( empty( $level['url'] ) ) {
						$level['url'] = $WishListMemberInstance->MakeRegURL();
					}
					if ( ! empty( $level['removeFromLevel'] ) ) {
						$r                        = array_intersect( ( array ) $level['removeFromLevel'], array_keys( $wpm_levels ) );
						$level['removeFromLevel'] = empty( $r ) ? '' : array_fill_keys( $r, 1 );
					}
					if ( ! empty( $level['addToLevel'] ) ) {
						$r                        = array_intersect( ( array ) $level['addToLevel'], array_keys( $wpm_levels ) );
						$level['addToLevel'] = empty( $r ) ? '' : array_fill_keys( $r, 1 );
					}
					$level            = array_diff( $level, array( '' ) );
					$wpm_levels[$id]  = $level;
					$this->wpm_levels = $wpm_levels;
					$WishListMemberInstance->SaveOption( 'wpm_levels', $wpm_levels );
					$this->method     = 'GET';
					return $this->_levels( $id );
					break;
				/*
				 * error because it's neither GET or POST and there's no $level_id
				 */
				default:
					return $this->error( WLMAPI2::ERROR_METHOD_NOT_SUPPORTED );
					break;
			}
			/*
			 * if $level_id is specified then we expect either
			 * GET, PUT, or DELETE
			 */
		} else {
			/*
			 * return error if $level_id is not valid
			 */
			if ( ! isset( $this->wpm_levels[$level_id] ) ) {
				return $this->error( 'Invalid Level ID' );
			}
			switch ( $this->method ) {
				/*
				 * update membership level
				 */
				case 'PUT':
					$wpm_levels = $this->wpm_levels;
					$level      = array_merge( $level_defaults, $wpm_levels[$level_id] );
					foreach ( $this->data AS $key => $value ) {
						switch ( $key ) {
							case 'after_login_redirect':
							case 'after_registration_redirect':
								switch ( strtolower( $value ) ) {
									case 'global':
									case '':
										$value = '---';
										break;
									case 'homepage':
										$value = '';
										break;
								}
								break;
						}
						$key = $level_map_flip[$key];
						if ( isset( $level[$key] ) ) {
							$level[$key] = $value;
						}
					}
					if ( ! empty( $level['removeFromLevel'] ) ) {
						$r                        = array_intersect( ( array ) $level['removeFromLevel'], array_keys( $wpm_levels ) );
						$level['removeFromLevel'] = empty( $r ) ? '' : array_fill_keys( $r, 1 );
					}
					$level                 = array_diff( $level, array( '' ) );
					$wpm_levels[$level_id] = $level;
					$this->wpm_levels      = $wpm_levels;
					$WishListMemberInstance->SaveOption( 'wpm_levels', $wpm_levels );
					$this->method          = 'GET';
					return $this->_levels( $level_id );
					break;
				/*
				 * delete level (only if it does not have any members in it)
				 */
				case 'DELETE':
					if ( $this->wpm_levels[$level_id]['count'] < 1 ) {
						unset( $this->wpm_levels[$level_id] );
						$WishListMemberInstance->SaveOption( 'wpm_levels', $this->wpm_levels );
						$this->method = 'GET';
						return $this->_levels();
					} else {
						return $this->error( 'Cannot delete levels that have members' );
					}
					break;
				/*
				 * get full information for a level
				 */
				case 'GET': // get level information
					$level           = array( 'id' => $level_id ) + $wpm_levels[$level_id];
					$level['_more_'] = array(
						"/levels/{$level_id}/members",
						"/levels/{$level_id}/posts",
						"/levels/{$level_id}/pages",
						"/levels/{$level_id}/comments",
						"/levels/{$level_id}/taxonomies"
					);
					foreach($this->custom_post_types AS $custom_post_type) {
						$level['_more_'][] = "/levels/{$level_id}/{$custom_post_type}";
					}
					$wpm_levels      = array( 'level' => $level );
					return $wpm_levels;
					break;
				/*
				 * return error if method is neither GET, PUT or DELETE
				 */
				default:
					return $this->error( WLMAPI2::ERROR_METHOD_NOT_SUPPORTED );
					break;
			}
		}
	}

	/**
	 * Resource:
	 *   /levels/{level_id}/members : GET, POST
	 *   /levels/{level_id}/members/{member_id} : GET, PUT, DELETE
	 *
	 * @global WishListMember $WishListMemberInstance
	 * @param integer $level_id Membership Level ID
	 * @param integer $member_id User ID
	 * @return apiResult
	 */
	private function _levels_members( $level_id, $member_id = null ) {
		global $WishListMemberInstance, $wpdb;
		$this->selfdoc = is_null( $member_id ) ? array( 'GET', 'POST' ) : array( 'GET', 'PUT', 'DELETE' );
		if ( $this->method == 'INFO' ) {
			return $this->selfdoc;
		}
		switch ( $this->method ) {
			case 'GET': // list members for level
				if ( ! empty( $member_id ) ) {
					$x = $WishListMemberInstance->GetMembershipLevels( $member_id );
					if ( in_array( $level_id, $x ) ) {
						$member_ids = $member_id + 0;
						$full       = true;
					} else {
						return $this->error( WLMAPI2::ERROR_INVALID_RESOURCE );
					}
				} else {
					if ( empty( $this->data['filter']['ID'] ) ) {
						if($this->data['filter']['status']) {
							$member_ids = $WishListMemberInstance->MemberIDsByStatus($this->data['filter']['status'], explode( ',', $level_id ) );
						} else {
							$member_ids = $WishListMemberInstance->MemberIDs( explode( ',', $level_id ) );
						}

					} else {
						$member_ids = ( array ) $this->data['filter']['ID'];
						foreach ( $member_ids AS &$member_id ) {
							$member_id += 0;
						}
						unset( $member_id );
					}
					$member_ids[] = 0;
					$member_ids   = implode( ',', $member_ids );
					$full         = false;
				}
				$this->prepare_found_rows_stuff( $__limit__, $__found_rows__ );

				$member_ids = $wpdb->get_results( "SELECT {$__found_rows__} `ID` AS `id`,`user_login`,`user_email` FROM {$wpdb->users} WHERE `ID` IN ({$member_ids}) {$__limit__}", ARRAY_A );
				$this->set_found_rows();
				$members    = array( 'members' => array( 'member' => array() ) );
				foreach ( $member_ids AS $member ) {
					$uid = $member['id'];
					if ( $full ) {
						$user            = new WishListMemberUser( $uid );
						$member['level'] = $user->Levels[$level_id];
						unset( $member['level']->Level_ID );
						unset( $member['level']->Name );
						$members         = array( 'member' => $member );
					} else {
						if(!empty($this->data['additional_data'])) {
							$sub_q = array();
							foreach($this->data['additional_data'] as $v) {
								$sub_q[] = $wpdb->prepare('%s', $v);
							}
							$additional_data = $wpdb->get_results($wpdb->prepare("SELECT um.meta_key, um.meta_value FROM {$wpdb->usermeta} um WHERE um.user_id=%d AND meta_key IN (".implode(', ',$sub_q).")", $member['id']));
							//format
							$fmt_additional_data = array();
							foreach($additional_data as $d) {
								$fmt_additional_data[$d->meta_key] = maybe_unserialize($d->meta_value);
							}
							$member['additional_data'] = $fmt_additional_data;
						}
						$member['_more_']               = "/levels/{$level_id}/members/{$uid}";
						$members['members']['member'][] = $member;
					}
				}
				return $members;
				break;
			case 'POST':
//				if (is_array($this->data) && isset($this->data['TxnID']))
//					unset($this->data['TxnID']);
				foreach ( ( array ) $this->data['Users'] AS $uid ) {
					if ( $wpdb->get_var( $wpdb->prepare( "SELECT `ID` FROM {$wpdb->users} WHERE `ID`=%d", $uid ) ) ) {
						$levels       = $WishListMemberInstance->GetMembershipLevels( $uid );
						$levels[]     = $level_id;
						$WishListMemberInstance->SetMembershipLevels( $uid, $levels );
						$this->method = 'PUT';
						$this->_levels_members( $level_id, $uid );
					}
				}
				$this->method = 'GET';
				$data         = array( 'filter' => array( 'ID' => $this->data['Users'] ) );
				$this->data   = $data;
				return $this->_levels_members( $level_id );
				break;
			case 'PUT':
				if ( $wpdb->get_var( $wpdb->prepare( "SELECT `ID` FROM {$wpdb->users} WHERE `ID`=%d", $member_id ) ) ) {
					extract( $this->data, EXTR_OVERWRITE | EXTR_PREFIX_ALL, 'data' );
					if ( isset( $data_Cancelled ) ) {
						if ( $data_Cancelled && $data_ShoppingCartRequest && !empty ( $this->wpm_levels[$level_id]['isfree'] ) ) {
                            $WishListMemberInstance->LevelSequentialCancelled ( $level_id, $member_id, true );
						} else {
							$WishListMemberInstance->LevelCancelled( $level_id, $member_id, ( bool ) $data_Cancelled );
						}
					}
					if ( isset( $data_CancelDate ) ) {
						if ( ! is_int( $data_CancelDate ) ) {
							$data_CancelDate = strtotime( $data_CancelDate );
						}
						$WishListMemberInstance->ScheduleLevelDeactivation( $level_id, ( array ) $member_id, $data_CancelDate );
					}
					if ( isset( $data_Pending ) ) {
						$WishListMemberInstance->LevelForApproval( $level_id, $member_id, ( bool ) $data_Pending );
					}
					if ( isset( $data_UnConfirmed ) ) {
						$WishListMemberInstance->LevelUnConfirmed( $level_id, $member_id, ( bool ) $data_UnConfirmed );
					}
					if ( isset( $data_Timestamp ) ) {
						$WishListMemberInstance->UserLevelTimestamp( $member_id, $level_id, $data_Timestamp, true );
					}
					if ( ! empty( $data_TxnID ) ) {
						$WishListMemberInstance->SetMembershipLevelTxnID( $member_id, $level_id, $data_TxnID );
					}
					$this->method = 'GET';
					return $this->_levels_members( $level_id, $member_id );
				} else {
					return $this->error( WLMAPI2::ERROR_INVALID_RESOURCE );
				}
				$this->method = 'GET';
				return $this->_levels_members( $level_id, $member_id );
				break;
			case 'DELETE':
				$levels       = array_diff( $WishListMemberInstance->GetMembershipLevels( $member_id ), array( $level_id ) );
				$WishListMemberInstance->SetMembershipLevels( $member_id, $levels, null, null, null, true );
				$WishListMemberInstance->SyncMembership(true);

				return;
				break;
		}
	}

	/*
	  private function _levels_files() {
	  if ($this->method == 'INFO') {
	  return $this->selfdoc;
	  }
	  return $this->error('This Request is in the Works');
	  }
	  private function _levels_folders() {
	  if ($this->method == 'INFO') {
	  return $this->selfdoc;
	  }
	  return $this->error('This Request is in the Works');
	  }
	 */

	/**
	 * Resource:
	 *   /txnid/{txn_id}/members : GET
	 *
	 * @param string $txn_id Transaction Id
	 * @return apiResult
	 */
	private function _txnid( $txn_id = null ) {
		$txn['txn'] = array();
		if ( is_null( $txn_id ) )
			return null;
		global $WishListMemberInstance, $wpdb;
		$this->prepare_found_rows_stuff( $__limit__, $__found_rows__ );
		$query      = "SELECT {$__found_rows__} `userlevel_id`,`option_value` FROM `{$WishListMemberInstance->Tables->userlevel_options}` WHERE `option_value` LIKE '{$txn_id}%' {$__limit__}";
		$trans      = $wpdb->get_results( $query );
		$this->set_found_rows();
		foreach ( $trans as $tran ) {
			$query        = "SELECT `user_id`,`level_id` FROM `{$WishListMemberInstance->Tables->userlevels}` WHERE ID={$tran->userlevel_id}";
			$userlvl      = $wpdb->get_row( $query );
			$txn['txn'][] = array( "txnid" => $tran->option_value, "user_id" => $userlvl->user_id, "level_id" => $userlvl->level_id );
		}
		return $txn;
	}

	private function _levels_posts( $level_id, $post_id = null ) {
		$x    = explode( '/', $this->request );
		$type = $x[0] == 'levels' ? $x[2] : $x[1];
		if ( $type == 'posts' ) {
			$type = 'post';
		}
		if ( $level_id == 'protected' ) {
			return $this->protected_content( $type, $post_id );
		} else {
			return $this->level_content( $type, $level_id, $post_id );
		}
	}

	private function _levels_pages( $level_id, $page_id = null ) {
		if ( $level_id == 'protected' ) {
			return $this->protected_content( 'page', $page_id );
		} else {
			return $this->level_content( 'page', $level_id, $page_id );
		}
	}

	private function _levels_comments( $level_id, $post_id = null ) {
		return $this->level_content( 'comment', $level_id, $post_id );
	}

	private function _levels_categories( $level_id, $category_id = null ) {
		if ( $level_id == 'protected' ) {
			return $this->protected_content( 'category', $category_id );
		} else {
			return $this->level_content( 'category', $level_id, $category_id );
		}
	}

	function level_content( $type, $level_id, $content_id = null ) {
		global $WishListMemberInstance, $wpdb;
		if ( empty( $content_id ) ) {
			$content_id = null;
		}
		$this->selfdoc = is_null( $content_id ) ? array( 'GET', 'POST' ) : array( 'GET', 'DELETE' );
		if ( $this->method == 'INFO' ) {
			return $this->selfdoc;
		}
		$types = array_merge( array( 'post', 'page', 'comment', 'category' ), $this->custom_post_types );
		if ( ! in_array( $type, $types ) ) {
			return $this->error( WLMAPI2::ERROR_INVALID_RESOURCE );
		}
		if ( ! in_array( $type, $this->custom_post_types ) ) {
			$otype = $type == 'category' ? 'categories' : $type . 's';
		} else {
			$otype = $type;
		}
		if ( $type == 'comment' ) {
			$type = 'post';
		}
		$wpm_levels = $this->wpm_levels;
		if ( ! isset( $wpm_levels[$level_id] ) && $WishListMemberInstance->IsPPPLevel( $level_id ) ) {
			return $this->error( WLMAPI2::ERROR_INVALID_RESOURCE );
		}
		if ( is_null( $content_id ) || $this->method == 'GET' ) {
			switch ( $this->method ) {
				case 'GET':
					$content_ids = $WishListMemberInstance->GetMembershipContent( $otype, $level_id );
					if ( ! is_null( $content_id ) ) {
						$content_ids = array_intersect( $content_ids, ( array ) $content_id );
					}
					$content_ids[] = 0;
					$content_ids   = implode( ',', $content_ids );
					if ( $type == 'category' ) {
						$content = get_categories( array( 'include' => $content_ids, 'hide_empty' => 0 ) );
						foreach ( $content AS $k => $v ) {
							$content[$k] = array(
								'ID'   => $v->term_id,
								'name' => $v->name
							);
							if ( is_null( $content_id ) ) {
								$content[$k]['_more_'] = "/levels/{$level_id}/{$otype}/{$v->term_id}";
							}
						}
					} else {
						$where = "`post_type`='{$type}' AND `post_status`='publish'";
						if ( ! $wpm_levels[$level_id]['all' . $otype] ) {
							$where = "`ID` IN ({$content_ids}) AND " . $where;
						}
						$more    = is_null( $content_id ) ? ", CONCAT('/levels/{$level_id}/{$otype}/',`ID`) AS `_more_`" : '';
						$this->prepare_found_rows_stuff( $__limit__, $__found_rows__ );
						$query   = "SELECT {$__found_rows__} `ID`,`post_title` AS `name` {$more} FROM `{$wpdb->posts}` WHERE {$where} ORDER BY `post_date` DESC {$__limit__}";
						$content = $wpdb->get_results( $query, ARRAY_A );
						$this->set_found_rows();
					}
					return array( $otype => array( $type => $content ) );
					break;
				case 'POST':
					if ( ! empty( $this->data['ContentIds'] ) ) {
						$Ids  = array_values( ( array ) $this->data['ContentIds'] );
						$data = array(
							'Checked'     => array_combine( array_values( $Ids ), array_fill( 0, count( $Ids ), 1 ) ),
							'ID'          => array_combine( array_values( $Ids ), array_fill( 0, count( $Ids ), 0 ) ),
							'ContentType' => $otype,
							'Level'       => $level_id
						);
						$WishListMemberInstance->SaveMembershipContent( $data );
						$WishListMemberInstance->SyncContent( $otype );
					}
					$this->method = 'GET';
					return $this->level_content( $type, $level_id );
					break;
				default:
					return $this->error( WLMAPI2::ERROR_METHOD_NOT_SUPPORTED );
					break;
			}
		} else {
			switch ( $this->method ) {
				case 'DELETE':
					$data = array(
						'Checked'     => array(),
						'ID'          => array( $content_id => 0 ),
						'ContentType' => $otype,
						'Level'       => $level_id
					);
					$WishListMemberInstance->SaveMembershipContent( $data );
					$WishListMemberInstance->SyncContent( $otype );
					break;
				default:
					return $this->error( WLMAPI2::ERROR_METHOD_NOT_SUPPORTED );
					break;
			}
		}
	}

	/*
	 * *********************************************** *
	 * MEMBER METHODS
	 * *********************************************** *
	 */

	/**
	 * Resource:
	 *   /members : GET, POST
	 *   /members/{id} : GET, PUT, DELETE
	 *
	 * @global <type> $wpdb
	 * @global WishListMember $WishListMemberInstance
	 * @param integer $member_id User ID
	 * @return apiResult
	 */
	private function _members( $member_id = null ) {
		global $wpdb, $WishListMemberInstance;
		$this->selfdoc = is_null( $member_id ) ? array( 'GET', 'POST' ) : array( 'GET', 'PUT', 'DELETE' );
		if ( $this->method == 'INFO' ) {
			return $this->selfdoc;
		}
		$data = $this->data;
		/*
		 * separate Levels, RemoveLevels and Sequential
		 * from user data if method is either POST or PUT
		 */
		if ( $this->method == 'POST' || $this->method == 'PUT' ) {
			$nlevels    = $rlevels    = array();
			$sequential = null;
			if ( isset( $data['Levels'] ) ) {
				$nlevels = empty($data['Levels']) ? array() : (array) $data['Levels'];
				unset( $data['Levels'] );
			}
			if ( isset( $data['RemoveLevels'] ) ) {
				$rlevels = empty($data['RemoveLevels']) ? array() : (array) $data['RemoveLevels'];
				unset( $data['RemoveLevels'] );
			}
			if ( isset( $data['Sequential'] ) ) {
				$sequential = $data['Sequential'];
				if ( empty( $sequential ) && ! is_numeric( $sequential ) ) {
					$sequential = 1;
				}
				unset( $data['Sequential'] );
			}
			/*
			 * determine if transaction ID and timestamp
			 * is specified for each level to be added
			 * and add each to $txns and $times respectively
			 */
			if ( ! empty( $nlevels ) ) {
				$levels = $times  = $txns   = array();
				foreach ( $nlevels AS $level ) {
					if ( ! empty( $level ) ) {
						$level = array_pad( array_values( ( array ) $level ), 3, 0 );
						list($level_id, $transaction_id, $timestamp) = $level;
						if ( $level_id ) {
							$levels[] = $level_id;
							/*
							 * a value of -1 for transaction_id and timestamp
							 * means that we just leave the current one in database
							 *
							 * a value of 0 generates an internal WishList Member transaction ID for
							 * transaction ID and current timestamp for timestamp
							 */
							if ( $transaction_id != -1 ) {
								$txns[$level_id] = $transaction_id;
							}
							if ( $timestamp != -1 ) {
								if ( empty( $timestamp ) ) {
									$timestamp = time();
								}
								$times[$level_id] = $timestamp;
							}
						}
					}
				}
				$nlevels = $levels;
			}
		}
		/*
		 * let's go through the methods
		 */
		switch ( $this->method ) {
			/*
			 * List members
			 */
			case 'GET':
				/*
				 * list all members if $member_id no specified
				 */
				if ( empty( $member_id ) ) {
					$filter     = $this->data['filter'];
					$filter_sql = '';
					if ( is_array( $filter ) && ! empty( $filter ) ) {
						// accepted filters
						$accepted_filters = array_flip( array( 'user_login', 'user_email' ) );
						$filter           = array_intersect_key( $filter, $accepted_filters );
						if ( $filter ) {
							$filter_sql = array();
							foreach ( $filter AS $k => $v ) {
								$filter_sql[] = "`{$k}` LIKE '" . $wpdb->escape( $v ) . "'";
							}
							$filter_sql = ' WHERE ' . implode( ' AND ', $filter_sql );
						}
					}
					$this->prepare_found_rows_stuff( $__limit__, $__found_rows__ );
					$result = $wpdb->get_results( "SELECT {$__found_rows__} `ID` AS `id`, `user_login`, `user_email`, CONCAT('/members/',`ID`) AS `_more_` FROM {$wpdb->users} {$filter_sql} {$__limit__}", ARRAY_A );
					$this->set_found_rows();
					if ( count( $result ) ) {
						return array( 'members' => array( 'member' => $result ) );
					}
					/*
					 * get full user information if $member_id is specified
					 */
				} else {
					$user           = new WishListMemberUser( $member_id, true );
					$user->UserInfo = array_merge( ( array ) $user->UserInfo, ( array ) $user->UserInfo->data );
					unset( $user->UserInfo['data'] );
					unset( $user->UserInfo['user_pass'] );
					unset( $user->UserInfo['wlm_reg_post'] );
					unset( $user->UserInfo['wlm_reg_get'] );
					unset( $user->WL );
					$result         = array( ( array ) $user );
					return array( 'member' => $result );
				}
				break;
			/*
			 * create new user
			 */
			case 'POST':
				$user_login = trim( $data['user_login'] );
				$user_email = trim( $data['user_email'] );
				$user_pass  = trim( $data['user_pass'] );
				if ( $user_login == '' ) {
					return $this->error( 'Empty username' );
				}
				if ( $user_email == '' ) {
					return $this->error( 'Empty email' );
				}
				if ( username_exists( $user_login ) ) {
					return $this->error( 'Username already exists' );
				}
				if ( email_exists( $user_email ) ) {
					return $this->error( 'Email already exists' );
				}
				if ( empty( $user_pass ) ) {
					$user_pass = wp_generate_password( 12, false );
				}
				$member_id = wp_create_user( $user_login, $user_pass, $user_email );

				$WishListMemberInstance->IsSequential( $member_id, true );

				add_filter( 'send_password_change_email', create_function('', 'return false;') ); // added to prevent WP from sending the password change email (since WP 4.3) 
				if ( is_wp_error( $member_id ) ) {
					return $this->error( 'Cannot create user. ' . $member_id->get_error_message() );
				}
				if ( $member_id ) {
					unset( $data['user_login'] );
					unset( $data['user_email'] );
					unset( $data['user_pass'] );
				}
			/* we now pass control to PUT to handle the rest of the data */
			/*
			 * Update existing user
			 */
			case 'PUT':
				$uid = $wpdb->get_var( $z   = $wpdb->prepare( "SELECT `ID` FROM `{$wpdb->users}` WHERE `ID`=%d", $member_id ) );
				if ( empty( $uid ) ) {
					return $this->error( WLMAPI2::ERROR_INVALID_RESOURCE );
				}
				$data       = $this->data;
				$data['ID'] = $member_id;
				if ( ! user_can( $member_id, 'administrator' ) ) {
					// set user role from $nlevels;
					if ( is_array( $nlevels ) && ! empty( $nlevels ) ) {
						$new_levels = array_intersect_key( $this->wpm_levels, array_flip( $nlevels ) );
						foreach ( $new_levels AS $key => $val ) {
							$levelOrder[$key] = $val['levelOrder'] + 0;
						}
						$new_level_keys = array_keys( $new_levels );
						array_multisort( $levelOrder, SORT_ASC, $new_level_keys, SORT_ASC, $new_levels );
						$level          = array_pop( $new_levels );
						if ( $level['role'] ) {
							$data['role'] = $level['role'];
						}
					}
				}
				if(!empty($data['new_user_login'])) {
					if ( username_exists( $data['new_user_login'] ) ) {
						return $this->error( sprintf( 'Cannot change username to %s because it already exists', $data['new_user_login'] ) );
					}

					$where  = array('user_login' => $data['user_login'], 'ID' => $data['ID']);
					$update = array('user_login' => $data['new_user_login']);
					$status = $wpdb->update($wpdb->users, $update, $where);
					if( false === $status ) {
						return $this->error( sprintf( 'An error occured while trying to change the username to %s', $data['new_user_login'] ) );
					}
				}
				$member_id = wp_update_user( $data );
				if( is_wp_error( $member_id ) ) {
					return $this->error( $member_id->get_error_message() );
				}
				$wpm_useraddress = $WishListMemberInstance->Get_UserMeta( $member_id, 'wpm_useraddress' );
				foreach ( $data AS $meta => $value ) {
					if ( substr( $meta, 0, 7 ) == 'custom_' || $meta == 'wpm_login_limit' || $meta == 'wpm_registration_ip' ) {
						$WishListMemberInstance->Update_UserMeta( $member_id, $meta, $value );
					}
					if ( in_array( $meta, array( 'company', 'address1', 'address2', 'city', 'state', 'zip', 'country' ) ) ) {
						$wpm_useraddress[$meta] = $value;
					}
				}
				$WishListMemberInstance->Update_UserMeta( $member_id, 'wpm_useraddress', $wpm_useraddress );
				if ( isset( $sequential ) ) {
					$WishListMemberInstance->IsSequential( $member_id, ( bool ) $sequential );
				}
				if ( ! empty( $nlevels ) || ! empty( $rlevels ) ) {
					$clevels = $WishListMemberInstance->GetMembershipLevels( $member_id );
					$clevels = array_diff( $clevels, ( array ) $rlevels );
					$clevels = array_unique( array_merge( $clevels, ( array ) $nlevels ) );
					$WishListMemberInstance->SetMembershipLevels( $member_id, $clevels, NULL, true, true, false );
					$WishListMemberInstance->SetMembershipLevelTxnIDs( $member_id, $txns );
					$WishListMemberInstance->UserLevelTimestamps( $member_id, $times );
				}
				$all_levels     = $confirm_levels = $pending_levels = array();
				if ( $this->data['ObeyRegistrationRequirements'] ) {
					foreach ( $nlevels AS $key => $_nlevel ) {
						$_lname        = trim( $this->wpm_levels[$_nlevel]['name'] );
						$nlevels[$key] = $_lname;
						$all_levels[]  = $_lname;
						if ( $this->wpm_levels[$_nlevel]['requireemailconfirmation'] ) {
							$WishListMemberInstance->LevelUnConfirmed( $_nlevel, $member_id, true );
							$confirm_levels[] = $_lname;
							unset( $nlevels[$key] );
						}
						if ( $this->wpm_levels[$_nlevel]['requireadminapproval'] ) {
							$WishListMemberInstance->LevelForApproval( $_nlevel, $member_id, true );
							$pending_levels[] = $_lname;
							unset( $nlevels[$key] );
						}
					}
				} else { //just set the level names
					foreach ( $nlevels AS $key => $_nlevel ) {
						$_lname        = trim( $this->wpm_levels[$_nlevel]['name'] );
						$nlevels[$key] = $_lname;
						$all_levels[]  = $_lname;
					}
				}
				if ( $this->data['SendMail'] ) {
					$email_macros = array(
						'[password]' => $user_pass ? $user_pass : '********',
					);
					if ( ! empty( $nlevels ) ) {
						$email_macros['[memberlevel]'] = implode( ', ', $nlevels );
						$WishListMemberInstance->send_email_template( 'registration', $member_id, $email_macros );
					}
					if ( ! empty( $confirm_levels ) ) {
						$email_macros['[memberlevel]'] = implode( ', ', $confirm_levels );
						$WishListMemberInstance->send_email_template( 'email_confirmation', $member_id, $email_macros );
					}
					if ( ! empty( $pending_levels ) ) {
						$email_macros['[memberlevel]'] = implode( ', ', $pending_levels );
						$WishListMemberInstance->send_email_template( 'require_admin_approval', $member_id, $email_macros );
					}
					$email_macros['[password]']    = '********';
					$email_macros['[memberlevel]'] = implode( ', ', $all_levels );
					$WishListMemberInstance->send_email_template( 'admin_new_member_notice', $member_id, $email_macros, $WishListMemberInstance->GetOption( 'newmembernotice_email_recipient' ) );
				}
				$this->method = 'GET';
				return $this->_members( $data['ID'] );
				break;
			/*
			 * Delete existing user except for #1 admin
			 */
			case 'DELETE':
				if ( $member_id == 1 ) {
					return $this->error( WLMAPI2::ERROR_INVALID_RESOURCE );
				}
				wp_delete_user( $member_id );
				/*
				  $this->method = 'GET';
				  return $this->_members();
				 */
				return;
				break;
		}
	}

	/*
	 * *********************************************** *
	 * CONTENT METHODS
	 * *********************************************** *
	 */

	/**
	 * Resource:
	 *   /content : GET
	 *   /content/{post_type} : GET
	 *
	 * @param string $content_type Post Type
	 * @return apiResult
	 */
	private function _content( $content_type = null ) {
		return $this->content( $content_type );
	}

	/**
	 * Resource:
	 *   /content/{post_type}/{post_id} : GET, PUT
	 *
	 * @param string $content_type User ID
	 * @param integer $content_id Post ID
	 * @return apiResult
	 */
	private function _content_protection( $content_type, $content_id ) {
		// $content_id += 0;
		if ( empty( $content_id ) ) {
			return $this->error( WLMAPI2::ERROR_INVALID_RESOURCE );
		}
		return $this->content( $content_type, $content_id );
	}

	/**
	 * Called by _content and _content_protection
	 *
	 * @global type $wpdb
	 * @global WishListMember $WishListMemberInstance
	 * @param type $content_type
	 * @param int $content_id
	 * @return apiResult
	 */
	function content( $content_type = null, $content_id = null ) {
		global $wpdb;
		global $WishListMemberInstance;
		$this->selfdoc = is_null( $content_id ) ? array( 'GET' ) : array( 'GET', 'PUT' );
		if ( $this->method == 'INFO' ) {
			return $this->selfdoc;
		}
		if ( ! in_array( $this->method, $this->selfdoc ) ) {
			return $this->error( WLMAPI2::ERROR_METHOD_NOT_SUPPORTED );
		}
		/*
		 * get all custom post types
		 * and also append posts and pages
		 */
		$valid_content_types = array_values( get_post_types( array( '_builtin' => false ) ) );
		array_unshift( $valid_content_types, 'posts', 'pages', 'post', 'page' );
		/*
		 * no content type specified?
		 * let's give all possible content types
		 */
		if ( empty( $content_type ) ) {
			foreach ( $valid_content_types AS &$c ) {
				$c = array(
					'name'   => $c,
					'_more_' => sprintf( '/content/%s', $c )
				);
			}
			unset( $c );
			return array( 'content' => array( 'type' => $valid_content_types ) );
		}
		/*
		 * this section only runs if content type is specified
		 */
		if ( ! in_array( $content_type, $valid_content_types ) ) {
			// abort for invalid content types
			return $this->error( WLMAPI2::ERROR_INVALID_RESOURCE );
		}
		$ct = $content_type;
		if ( strtolower( $content_type ) == 'posts' ) {
			$ct = 'post';
		}
		if ( strtolower( $content_type ) == 'pages' ) {
			$ct = 'page';
		}
		/*
		 * by this point, we're sure that the content type is valid
		 */
		/*
		 * no content id specified?
		 * let's return all posts for the specified content type
		 */

		$content_id += 0;
		if ( empty( $content_id ) ) {
			$where   = "`post_type`='{$ct}' AND `post_status`='publish'";
			$more    = ", CONCAT('/content/{$ct}','/protection/',`ID`) AS `_more_`";
			$this->prepare_found_rows_stuff( $__limit__, $__found_rows__ );
			$query   = "SELECT {$__found_rows__} `ID`,`post_title` AS `name` {$more} FROM `{$wpdb->posts}` WHERE {$where} ORDER BY `post_date` DESC {$__limit__}";
			$content = $wpdb->get_results( $query, ARRAY_A );
			$this->set_found_rows();
			return array( 'content' => array( $content_type => $content ) );
		}
		/*
		 * if we get to this point then we know that a non-empty content id was passed
		 */
		$content_id += 0;
		$where   = "`post_type`='{$ct}' AND `post_status`='publish' AND `ID`={$content_id}";
		$query   = "SELECT `ID`,`post_title` AS `name` FROM `{$wpdb->posts}` WHERE {$where} ORDER BY `post_date` DESC";
		$content = $wpdb->get_row( $query, ARRAY_A );
		if ( ! $content ) {
			return $this->error( WLMAPI2::ERROR_INVALID_RESOURCE );
		}
		$protection       = $WishListMemberInstance->GetContentLevels( $ct, $content_id );
		$_Protected       = ( int ) in_array( 'Protection', $protection );
		$_PayPerPost      = ( int ) in_array( 'PayPerPost', $protection );
		$protection       = array_diff( $protection, array( 'Protection', 'PayPerPost' ) );
		$_Levels          = preg_grep( '/^\d+$/', $protection );
		$_PayPerPostUsers = preg_grep( '/^U-\d+$/', $protection );
		if ( $this->method == 'PUT' ) {
			$data = $this->data;
			if ( isset( $data['Protected'] ) ) {
				$WishListMemberInstance->Protect( $content_id, $data['Protected'] + 0 ? 'Y' : 'N'  );
			}
			if ( isset( $data['PayPerPost'] ) ) {
				$WishListMemberInstance->PayPerPost( $content_id, $data['PayPerPost'] + 0 ? 'Y' : 'N'  );
			}
			$setlevels = false;
			$levels    = $_Levels;
			if ( isset( $data['Levels'] ) ) {
				$levels    = array_unique( array_merge( $levels, preg_grep( '/^\d+$/', ( array ) $data['Levels'] ) ) );
				$setlevels = true;
			}
			if ( isset( $data['RemoveLevels'] ) ) {
				$levels    = array_diff( $levels, ( array ) $data['RemoveLevels'] );
				$setlevels = true;
			}
			if ( $setlevels ) {
				$WishListMemberInstance->SetContentLevels( $ct, $content_id, $levels );
			}
			$n_ppp_users = array();
			if ( isset( $data['PayPerPostUsers'] ) ) {
				$n_ppp_users = ( array ) $data['PayPerPostUsers'];
			}
			$r_ppp_users = array();
			if ( isset( $data['RemovePayPerPostUsers'] ) ) {
				$r_ppp_users = ( array ) $data['RemovePayPerPostUsers'];
			}
			$n_ppp_users = array_diff( $n_ppp_users, $r_ppp_users );
			if ( count( $n_ppp_users ) ) {
				$WishListMemberInstance->AddPostUsers( $ct, $content_id, $n_ppp_users );
			}
			if ( count( $r_ppp_users ) ) {
				$WishListMemberInstance->RemovePostUsers( $ct, $content_id, $r_ppp_users );
			}
			$this->method = 'GET';
			return $this->content( $content_type, $content_id );
		}
		$content['Protected']       = $_Protected;
		$content['Levels']          = array_values( $_Levels );
		$content['PayPerPost']      = $_PayPerPost;
		$content['PayPerPostUsers'] = array_values( $_PayPerPostUsers );
		return array( 'content' => array( $content_type => array( $content ) ) );
	}

	/**
	 * Resource:
	 *   /taxonomies : GET
	 *   /taxonomies/{taxononmy} : GET
	 *
	 * @param string $taxonomy Taxonomy
	 * @return apiResult
	 */
	private function _taxonomies( $taxonomy = null ) {
		return $this->categories( $taxonomy );
	}

	private function _categories( $taxonomy = null ) {
		return $this->categories( $taxonomy );
	}

	/**
	 * Resource:
	 *   /taxonomies/{taxonomy}/{taxonomy_id} : GET, PUT
	 *
	 * @param string $taxonomy User ID
	 * @param integer $taxonomy_id Post ID
	 * @return apiResult
	 */
	private function _taxonomies_protection( $taxonomy, $taxonomy_id ) {
		// $taxonomy_id += 0;
		if ( empty( $taxonomy_id ) ) {
			return $this->error( WLMAPI2::ERROR_INVALID_RESOURCE );
		}
		return $this->categories( $taxonomy, $taxonomy_id );
	}

	/**
	 * Called by _categories and _categories_protection
	 *
	 * @global type $wpdb
	 * @global WishListMember $WishListMemberInstance
	 * @param type $taxonomy
	 * @param int $taxonomy_id
	 * @return apiResult
	 */
	function categories( $taxonomy = null, $taxonomy_id = null ) {
		global $wpdb;
		global $WishListMemberInstance;
		$this->selfdoc = is_null( $taxonomy_id ) ? array( 'GET' ) : array( 'GET', 'PUT' );
		if ( $this->method == 'INFO' ) {
			return $this->selfdoc;
		}
		if ( ! in_array( $this->method, $this->selfdoc ) ) {
			return $this->error( WLMAPI2::ERROR_METHOD_NOT_SUPPORTED );
		}
		/*
		 * get all custom taxonomies
		 * and also append category
		 */
		$valid_taxonomies = array_values( get_taxonomies( array( '_builtin' => false ) ) );
		array_unshift( $valid_taxonomies, 'category' );
		/*
		 * no taxonomy specified?
		 * let's give all possible taxonomies
		 */
		if ( empty( $taxonomy ) ) {
			foreach ( $valid_taxonomies AS &$c ) {
				$c = array(
					'name'   => $c,
					'_more_' => sprintf( '/taxonomies/%s', $c )
				);
			}
			unset( $c );
			return array( 'content' => array( 'type' => $valid_taxonomies ) );
		}
		/*
		 * this section only runs if taxonomy is specified
		 */
		if ( ! in_array( $taxonomy, $valid_taxonomies ) ) {
			// abort for invalid content types
			return $this->error( WLMAPI2::ERROR_INVALID_RESOURCE );
		}
		/*
		 * by this point, we're sure that the taxonomy is valid
		 */
		/*
		 * no taxonomy id specified?
		 * let's return all categories (terms) for the specified taxonomy
		 */
		if ( empty( $taxonomy_id ) ) {
			$content = get_terms( $taxonomy, array( 'hide_empty' => false ) );
			if ( is_wp_error( $content ) ) {
				$content = array();
			}
			foreach ( $content AS &$c ) {
				$c = ( array ) $c;
				$c = array(
					'ID'     => $c['term_id'],
					'name'   => $c['name'],
					'_more_' => sprintf( '/taxonomies/%s/%d', $taxonomy, $c['term_id'] )
				);
			}
			unset( $c );
			return array( 'content' => array( $taxonomy => $content ) );
		}
		/*
		 * if we get to this point then we know that a non-empty taxonomy id was passed
		 */
		$taxonomy_id += 0;
		$content = get_term( $taxonomy_id, $taxonomy );
		if ( ! $content ) {
			return $this->error( WLMAPI2::ERROR_INVALID_RESOURCE );
		}
		$content    = ( array ) $content;
		$protection = $WishListMemberInstance->GetContentLevels( 'categories', $taxonomy_id );
		$_Protected = ( int ) in_array( 'Protection', $protection );
		$protection = array_diff( $protection, array( 'Protection', 'PayPerPost' ) );
		$_Levels    = preg_grep( '/^\d+$/', $protection );
		if ( $this->method == 'PUT' ) {
			$data = $this->data;
			if ( isset( $data['Protected'] ) ) {
				$WishListMemberInstance->CatProtected( $taxonomy_id, $data['Protected'] + 0 ? 'Y' : 'N'  );
			}
			$setlevels = false;
			$levels    = $_Levels;
			if ( isset( $data['Levels'] ) ) {
				$levels    = array_unique( array_merge( $levels, preg_grep( '/^\d+$/', ( array ) $data['Levels'] ) ) );
				$setlevels = true;
			}
			if ( isset( $data['RemoveLevels'] ) ) {
				$levels    = array_diff( $levels, ( array ) $data['RemoveLevels'] );
				$setlevels = true;
			}
			if ( $setlevels ) {
				$WishListMemberInstance->SetContentLevels( 'categories', $taxonomy_id, $levels );
			}
			$this->method = 'GET';
			return $this->categories( $taxonomy, $taxonomy_id );
		}
		$content['Protected'] = $_Protected;
		$content['Levels']    = array_values( $_Levels );
		return array( 'content' => array( $taxonomy => array( $content ) ) );
	}

	private function _api1( $api1function ) {
		$this->selfdoc = array( 'GET' );
		if ( $this->method == 'INFO' ) {
			return $this->selfdoc;
		}
		if ( $this->method == 'GET' ) {
			if ( method_exists( WLMAPI, $api1function ) && substr( $api1function, 0, 1 ) != '_' ) {
				$data   = ( array ) $this->data['Params'];
				$output = call_user_func_array( array( WLMAPI, $api1function ), $data );
				return array( 'Result' => $output );
			} else {
				return $this->error( WLMAPI2::ERROR_INVALID_RESOURCE );
			}
		} else {
			return $this->error( WLMAPI2::ERROR_METHOD_NOT_SUPPORTED );
		}
	}

}
