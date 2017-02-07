<?php

/**
 * Methods Class for WishList Member API
 * @author John Morris <john@wishlistproducts.com>
 * @package WLMAPIMethods
 *
 * @version 1.0
 * John Morris
 * 4/04/2012
 */
require_once( dirname( __FILE__ ) . '/wlmapiclass.php' );

//Our main class
if ( !class_exists( 'WLMAPIMethods' ) ) {

	class WLMAPIMethods {

		/**
		 * Constructor class
		 *
		 */
		public function __construct( $cache_time = 3600, $url = false, $api_key = false ) {
			$this->cache_time = $cache_time;
			$this->api_key = $api_key;

			//Set the site url used for connection to the API
			if ( $url == false ) {
				$this->site_url = site_url();
			} else {
				$this->site_url = $url;
			}

			//If we're connecting to the API locally, we'll use the internal API connection
			if ( $this->site_url == site_url() ) {
				$this->internal = true;
			} else {
				$this->internal = false;
			}
		}

		/**
		 * Internal Methods
		 *
		 * This section contains internal methods for use within the class. Mostly set
		 * as private functions, although, some are public as they are useful outside
		 * the scope of this class.
		 */

		/**
		 * Gets the API key
		 *
		 * @return string $key - the API key of the current WishList Member install
		 */
		public function getAPIkey( $raw = false ) {
			if ( $this->api_key !== false ) {
				return $this->api_key;
			}

			global $wpdb;

			if ( !$raw ) {
				$cache = get_transient( 'wlm_api_' . __FUNCTION__ );
			}

			if ( !$cache ) {
				$table = $wpdb->prefix . 'wlm_options';

				$query =
						"
					SELECT option_value
					FROM $table
					WHERE option_name = 'WLMAPIKey'
					";

				$key = $wpdb->get_results( $query );
				$api_key = $key[0]->option_value;

				//Cache the request
				set_transient( 'wlm_api_' . __FUNCTION__, $api_key, $this->cache_time );
			} else {
				$api_key = $cache;
			}

			return $api_key;
		}

		/**
		 * Loads the API
		 *
		 * @return object $api - an instance of the API class
		 */
		public function loadAPI() {
			$api = new wlmapiclass( $this->site_url . '/', $this->getAPIkey(), dirname( __FILE__ ) . '/temp/' );
			$api->return_format = 'php';

			return $api;
		}

		/**
		 * Get Methods
		 *
		 * This section contains methods that call the API Class directly. These methods
		 * are "smart-cached" to ensure optimum performance without wonkiness when
		 * creating, updating, or deleting data.
		 */

		/**
		 * Retrieves an array list of all membership levels
		 *
		 * @return integer $success - Returns 1 if the request was successful and 0 if not
		 * @return array $levels - An array containing data for each membership level
		 * @return array $supported_verbs - An array list of the verbs supported by this resource
		 */
		public function get_levels( $raw = false ) {
			//Run our request internally if available
			if ( $this->internal == true && function_exists( 'WishListMemberAPIRequest' ) ) {
				$response = WishListMemberAPIRequest( '/levels' );

				return $response;
			}

			if ( !$raw ) {
				$cache = get_transient( 'wlm_api_' . __FUNCTION__ );
			}

			if ( !$cache ) {
				//Load the API.
				$api = $this->loadAPI();

				//Retrieve the membership levels from the API and unseralize the response
				$response = $api->get( '/levels' );
				$response = unserialize( $response );

				//Cache the request
				set_transient( 'wlm_api_' . __FUNCTION__, $response, $this->cache_time );
			} else {
				$response = $cache;
			}

			return $response;
		}

		/**
		 * Retrieves full information about an individual membership level
		 *
		 * @param integer $level_id - The level ID of the requested membership level
		 *
		 * @return integer $success - Returns 1 if the request was successful and 0 if not
		 * @return array $level - An array containing the data for the membership level
		 * @return array $supported_verbs - An array list of the verbs supported by this resource
		 */
		public function get_level( $level_id, $raw = false ) {
			//Run our request internally if available
			if ( $this->internal == true && function_exists( 'WishListMemberAPIRequest' ) ) {
				$response = WishListMemberAPIRequest( '/levels/' . $level_id );

				return $response;
			}

			if ( !$raw ) {
				$cache = get_transient( 'wlm_api_' . __FUNCTION__ . $level_id );
			}

			if ( !$cache ) {
				//Load the API.
				$api = $this->loadAPI();

				//Retrieve the passed level
				$response = $api->get( '/levels/' . $level_id );
				$response = unserialize( $response );

				//Cache the request
				set_transient( 'wlm_api_' . __FUNCTION__ . $level_id, $response, $this->cache_time );
			} else {
				$response = $cache;
			}

			return $response;
		}

		/**
		 * Creates a new membership level
		 *
		 * @param array $args - An array containing the arguments for the membership level to be created
		 *
		 * @return integer $success - Returns 1 if the request was successful and 0 if not
		 * @return array $level - An array containing the data for the new membership level
		 * @return array $supported_verbs - An array list of the verbs supported by this resource
		 */
		public function create_level( $args ) {
			//Run our request internally if available
			if ( $this->internal == true && function_exists( 'WishListMemberAPIRequest' ) ) {
				$response = WishListMemberAPIRequest( '/levels', 'POST', $args );

				return $response;
			}

			//Load the API.
			$api = $this->loadAPI();

			//Create the level with the specified arguments
			$response = $api->post( '/levels', $args );
			$response = unserialize( $response );

			//Reset the cache
			delete_transient( 'wlm_api_get_levels' );
			delete_transient( 'wlm_api_get_levels_admin' );
			delete_transient( 'wlm_api_get_user_levels_admin' );

			return $response;
		}

		/**
		 * Updates the membership level specified by $level_id in the resource URL
		 *
		 * @param integer $level_id - The ID of the level to update
		 * @param array $args - An array containing the arguments for the membership level to be updated
		 *
		 * @return integer $success - Returns 1 if the request was successful and 0 if not
		 * @return array $level - An array containing the updated data for the membership level
		 * @return array $supported_verbs - An array list of the verbs supported by this resource
		 */
		public function update_level( $level_id, $args ) {
			//Run our request internally if available
			if ( $this->internal == true && function_exists( 'WishListMemberAPIRequest' ) ) {
				$response = WishListMemberAPIRequest( '/levels/' . $level_id, 'PUT', $args );

				return $response;
			}

			//Load the API.
			$api = $this->loadAPI();

			//Update the level with the specified arguments
			$response = $api->put( '/levels/' . $level_id, $args );
			$response = unserialize( $response );

			//Reset the cache
			delete_transient( 'wlm_api_get_levels' );
			delete_transient( 'wlm_api_get_level' . $level_id );
			delete_transient( 'wlm_api_get_levels_admin' );
			delete_transient( 'wlm_api_get_user_levels_admin' );

			return $response;
		}

		/**
		 * Deletes the membership level specified by $level_id in the resource URL
		 *
		 * @param integer $level_id - The ID of the level to be deleted
		 *
		 * @return integer $success - Returns 1 if the request was successful and 0 if not
		 * @return array $levels - An array containing data for each of the remaining membership levels
		 * @return array $supported_verbs - An array list of the verbs supported by this resource
		 */
		public function delete_level( $level_id ) {
			//Run our request internally if available
			if ( $this->internal == true && function_exists( 'WishListMemberAPIRequest' ) ) {
				$response = WishListMemberAPIRequest( '/levels/' . $level_id, 'DELETE' );

				return $response;
			}

			//Load the API.
			$api = $this->loadAPI();

			//Delete the specified level
			$response = $api->delete( '/levels/' . $level_id );
			$response = unserialize( $response );

			//Reset the cache
			delete_transient( 'wlm_api_get_levels' );
			delete_transient( 'wlm_api_get_level' . $level_id );
			delete_transient( 'wlm_api_get_levels_admin' );
			delete_transient( 'wlm_api_get_user_levels_admin' );

			return $response;
		}

		/**
		 * Retrieves an array list of all members in a membership level
		 *
		 * @param integer $level_id - The level ID of the requested membership level
		 *
		 * @return integer $success - Returns 1 if the request was successful and 0 if not
		 * @return array $members - An array containing the data for each member of the membership level
		 * @return array $supported_verbs - An array list of the verbs supported by this resource
		 */
		public function get_level_members( $level_id, $raw = false ) {
			//Run our request internally if available
			if ( $this->internal == true && function_exists( 'WishListMemberAPIRequest' ) ) {
				$response = WishListMemberAPIRequest( '/levels/' . $level_id . '/members' );

				return $response;
			}

			if ( !$raw ) {
				$cache = get_transient( 'wlm_api_' . __FUNCTION__ . $level_id );
			}

			if ( !$cache ) {
				//Load the API.
				$api = $this->loadAPI();

				//Retrieve the users in the specified level
				$response = $api->get( '/levels/' . $level_id . '/members' );
				$response = unserialize( $response );

				//Cache the request
				set_transient( 'wlm_api_' . __FUNCTION__ . $level_id, $response, $this->cache_time );
			} else {
				$response = $cache;
			}

			return $response;
		}

		/**
		 * Adds a user to a membership level
		 *
		 * @param integer - The level ID to add users to
		 * @param array $args - An array of arguments for this resource.
		 *
		 * @return integer $success - Returns 1 if the request was successful and 0 if not
		 * @return array $members - An array of members successfully added to the membership level
		 * @return array $supported_verbs - An array list of the verbs supported by this resource
		 */
		public function add_member_to_level( $level_id, $args ) {
			//Run our request internally if available
			if ( $this->internal == true && function_exists( 'WishListMemberAPIRequest' ) ) {
				$response = WishListMemberAPIRequest( '/levels/' . $level_id . '/members', 'POST', $args );

				return $response;
			}

			//Load the API.
			$api = $this->loadAPI();

			//Add the user to the level
			$response = $api->post( '/levels/' . $level_id . '/members', $args );
			$response = unserialize( $response );

			///Reset the cache
			delete_transient( 'wlm_api_get_level_members' . $level_id );

			//Grab our members array
			$member_ids = $args['Users'];

			//Delete the cache for each member
			foreach ( $member_ids as $member_id ) {
				delete_transient( 'wlm_api_get_level_member_data' . $level_id . '_' . $member_id );
			}

			return $response;
		}

		/**
		 * Removes the member from the membership level
		 *
		 * @param integer $level_id - The ID of the level to remove the user from
		 * @param integer $member_id - The user ID of the user to be removed
		 *
		 * @return integer $success - Returns 1 if the request was successful and 0 if not
		 * @return array $levels - An array containing data for each of the remaining membership levels
		 * @return array $supported_verbs - An array list of the verbs supported by this resource
		 */
		public function remove_member_from_level( $level_id, $member_id ) {
			//Run our request internally if available
			if ( $this->internal == true && function_exists( 'WishListMemberAPIRequest' ) ) {
				$response = WishListMemberAPIRequest( '/levels/' . $level_id . '/members/' . $member_id, 'DELETE' );

				return $response;
			}

			//Load the API.
			$api = $this->loadAPI();

			//Remove the user from the level
			$response = $api->delete( '/levels/' . $level_id . '/members/' . $member_id );
			$response = unserialize( $response );

			//Reset the cache
			delete_transient( 'wlm_api_get_level_members' . $level_id );
			delete_transient( 'wlm_api_get_level_member_data' . $level_id . '_' . $member_id );

			return $response;
		}

		/**
		 * Retrieves membership level information for an individual member
		 *
		 * @param integer $level_id - The level ID of the requested membership level
		 * @param integer $member_id - The user ID of the requested user
		 *
		 * @return integer $success - Returns 1 if the request was successful and 0 if not
		 * @return array $member - An array containing the data for each member of the membership level
		 * @return array $supported_verbs - An array list of the verbs supported by this resource
		 */
		public function get_level_member_data( $level_id, $member_id, $raw = false ) {
			//Run our request internally if available
			if ( $this->internal == true && function_exists( 'WishListMemberAPIRequest' ) ) {
				$response = WishListMemberAPIRequest( '/levels/' . $level_id . '/members/' . $member_id );

				return $response;
			}

			if ( !$raw ) {
				$cache = get_transient( 'wlm_api_' . __FUNCTION__ . $level_id . '_' . $member_id );
			}

			if ( !$cache ) {
				//Load the API.
				$api = $this->loadAPI();

				//Retrieve the user data
				$response = $api->get( '/levels/' . $level_id . '/members/' . $member_id );
				$response = unserialize( $response );

				//Cache the request
				set_transient( 'wlm_api_' . __FUNCTION__ . $level_id . '_' . $member_id, $response, $this->cache_time );
			} else {
				$response = $cache;
			}

			return $response;
		}

		/**
		 * Updates membership level information for an individual member
		 *
		 * @param integer $level_id - The ID of the level to update
		 * @param integer $member_id - The user ID of the requested user
		 * @param array $args - An array containing the arguments for the user to be updated
		 *
		 * @return integer $success - Returns 1 if the request was successful and 0 if not
		 * @return array $member - An array containing the updated data for the user
		 * @return array $supported_verbs - An array list of the verbs supported by this resource
		 */
		public function update_level_member_data( $level_id, $member_id, $args ) {
			//Run our request internally if available
			if ( $this->internal == true && function_exists( 'WishListMemberAPIRequest' ) ) {
				$response = WishListMemberAPIRequest( '/levels/' . $level_id . '/members/' . $member_id, 'PUT', $args );

				return $response;
			}

			//Load the API.
			$api = $this->loadAPI();

			//Update the user with the specified arguments
			$response = $api->put( '/levels/' . $level_id . '/members/' . $member_id, $args );
			$response = unserialize( $response );

			//Reset the cache
			delete_transient( 'wlm_api_get_level_members' . $level_id );
			delete_transient( 'wlm_api_get_level_member_data' . $level_id . '_' . $member_id );

			return $response;
		}

		/**
		 * Retrieves an array list of all posts in a membership level
		 *
		 * @param integer $level_id - The ID of the level to retrieve
		 *
		 * @return integer $success - Returns 1 if the request was successful and 0 if not
		 * @return array $posts - An array containing data for each of the posts in the membership level
		 * @return array $supported_verbs - An array list of the verbs supported by this resource
		 */
		public function get_level_posts( $level_id, $raw = false ) {
			//Run our request internally if available
			if ( $this->internal == true && function_exists( 'WishListMemberAPIRequest' ) ) {
				$response = WishListMemberAPIRequest( '/levels/' . $level_id . '/posts' );

				return $response;
			}

			if ( !$raw ) {
				$cache = get_transient( 'wlm_api_' . __FUNCTION__ . $level_id );
			}

			if ( !$cache ) {
				//Load the API.
				$api = $this->loadAPI();

				//Retrieve the posts
				$response = $api->get( '/levels/' . $level_id . '/posts' );
				$response = unserialize( $response );

				//Cache the request
				set_transient( 'wlm_api_' . __FUNCTION__ . $level_id, $response, $this->cache_time );
			} else {
				$response = $cache;
			}

			return $response;
		}

		/* Retrieves an array list of all posts in a membership level of the specified post type
		 *
		 * @param integer $level_id - The ID of the level to retrieve
		 * @param string $post_type - The slug of the post type to retrieve
		 *
		 * @return integer $success - Returns 1 if the request was successful and 0 if not
		 * @return array $posts - An array containing data for each of the posts in the membership level
		 * @return array $supported_verbs - An array list of the verbs supported by this resource
		 */

		public function get_level_post_type( $level_id, $post_type, $raw = false ) {
			//Run our request internally if available
			if ( $this->internal == true && function_exists( 'WishListMemberAPIRequest' ) ) {
				$response = WishListMemberAPIRequest( '/levels/' . $level_id . '/' . $post_type );

				return $response;
			}

			if ( !$raw ) {
				$cache = get_transient( 'wlm_api_' . __FUNCTION__ . $level_id );
			}

			if ( !$cache ) {
				//Load the API.
				$api = $this->loadAPI();

				//Retrieve the posts
				$response = $api->get( '/levels/' . $level_id . '/posts' );
				$response = unserialize( $response );

				//Cache the request
				set_transient( 'wlm_api_' . __FUNCTION__ . $level_id, $response, $this->cache_time );
			} else {
				$response = $cache;
			}

			return $response;
		}

		/**
		 * Adds a post to a membership level
		 *
		 * @param integer $level_id - The ID of the level to add posts to
		 * @param array $args - An array of arguments for this resource
		 *
		 * @return integer $success - Returns 1 if the request was successful and 0 if not
		 * @return array $posts - An array of posts belonging to the membership level
		 * @return array $supported_verbs - An array list of the verbs supported by this resource
		 */
		public function add_post_to_level( $level_id, $args ) {
			//Run our request internally if available
			if ( $this->internal == true && function_exists( 'WishListMemberAPIRequest' ) ) {
				$response = WishListMemberAPIRequest( '/levels/' . $level_id . '/posts', 'POST', $args );

				return $response;
			}

			//Load the API.
			$api = $this->loadAPI();

			//Add the post to the level
			$response = $api->post( '/levels/' . $level_id . '/posts', $args );
			$response = unserialize( $response );

			//Reset the cache
			delete_transient( 'wlm_api_get_level_posts' . $level_id );

			return $response;
		}

		/**
		 * Removes a post from a membership level
		 *
		 * @param integer $level_id - The ID of the level
		 * @param integer $post_id - The ID of the post to remove
		 *
		 * @return integer $success - Returns 1 if the request was successful and 0 if not
		 * @return array $supported_verbs - An array list of the verbs supported by this resource
		 */
		public function remove_post_from_level( $level_id, $post_id ) {
			//Run our request internally if available
			if ( $this->internal == true && function_exists( 'WishListMemberAPIRequest' ) ) {
				$response = WishListMemberAPIRequest( '/levels/' . $level_id . '/posts/' . $post_id, 'DELETE' );

				return $response;
			}

			//Load the API.
			$api = $this->loadAPI();

			//Remove the post from the level
			$response = $api->delete( '/levels/' . $level_id . '/posts/' . $post_id );
			$response = unserialize( $response );

			//Reset the cache
			delete_transient( 'wlm_api_get_level_posts' . $level_id );

			return $response;
		}

		/**
		 * Retrieves an array list of all pages in a membership level
		 *
		 * @param integer $level_id - The ID of the level to retrieve
		 *
		 * @return integer $success - Returns 1 if the request was successful and 0 if not
		 * @return array $pages - An array containing data for each of the pages in the membership level
		 * @return array $supported_verbs - An array list of the verbs supported by this resource
		 */
		public function get_level_pages( $level_id, $raw = false ) {
			//Run our request internally if available
			if ( $this->internal == true && function_exists( 'WishListMemberAPIRequest' ) ) {
				$response = WishListMemberAPIRequest( '/levels/' . $level_id . '/pages' );

				return $response;
			}

			if ( !$raw ) {
				$cache = get_transient( 'wlm_api_' . __FUNCTION__ . $level_id );
			}

			if ( !$cache ) {
				//Load the API.
				$api = $this->loadAPI();

				//Retrieve the pages
				$response = $api->get( '/levels/' . $level_id . '/pages' );
				$response = unserialize( $response );

				//Cache the request
				set_transient( 'wlm_api_' . __FUNCTION__ . $level_id, $response, $this->cache_time );
			} else {
				$response = $cache;
			}

			return $response;
		}

		/**
		 * Adds a page to a membership level
		 *
		 * @param integer $level_id - The ID of the level to add pages to
		 * @param array $args - An array of arguments for this resource
		 *
		 * @return integer $success - Returns 1 if the request was successful and 0 if not
		 * @return array $posts - An array of pages belonging to the membership level
		 * @return array $supported_verbs - An array list of the verbs supported by this resource
		 */
		public function add_page_to_level( $level_id, $args ) {
			//Run our request internally if available
			if ( $this->internal == true && function_exists( 'WishListMemberAPIRequest' ) ) {
				$response = WishListMemberAPIRequest( '/levels/' . $level_id . '/pages', 'POST', $args );

				return $response;
			}

			//Load the API.
			$api = $this->loadAPI();

			//Add the page to the level
			$response = $api->post( '/levels/' . $level_id . '/pages', $args );
			$response = unserialize( $response );

			//Reset the cache
			delete_transient( 'wlm_api_get_level_pages' . $level_id );

			return $response;
		}

		/**
		 * Removes a page from a membership level
		 *
		 * @param integer $level_id - The ID of the level
		 * @param integer $post_id - The ID of the page to remove
		 *
		 * @return integer $success - Returns 1 if the request was successful and 0 if not
		 * @return array $supported_verbs - An array list of the verbs supported by this resource
		 */
		public function remove_page_from_level( $level_id, $page_id ) {
			//Run our request internally if available
			if ( $this->internal == true && function_exists( 'WishListMemberAPIRequest' ) ) {
				$response = WishListMemberAPIRequest( '/levels/' . $level_id . '/pages/' . $page_id, 'DELETE' );

				return $response;
			}

			//Load the API.
			$api = $this->loadAPI();

			//Remove the page from the level
			$response = $api->delete( '/levels/' . $level_id . '/pages/' . $page_id );
			$response = unserialize( $response );

			//Reset the cache
			delete_transient( 'wlm_api_get_level_pages' . $level_id );

			return $response;
		}

		/**
		 * Retrieves an array list of all posts with comments in a membership level
		 *
		 * @param integer $level_id - The ID of the level to retrieve
		 *
		 * @return integer $success - Returns 1 if the request was successful and 0 if not
		 * @return array $pages - An array containing data for each of the posts with comments in the membership level
		 * @return array $supported_verbs - An array list of the verbs supported by this resource
		 */
		public function get_level_commented_posts( $level_id, $raw = false ) {
			//Run our request internally if available
			if ( $this->internal == true && function_exists( 'WishListMemberAPIRequest' ) ) {
				$response = WishListMemberAPIRequest( '/levels/' . $level_id . '/comments' );

				return $response;
			}

			if ( !$raw ) {
				$cache = get_transient( 'wlm_api_' . __FUNCTION__ . $level_id );
			}

			if ( !$cache ) {
				//Load the API.
				$api = $this->loadAPI();

				//Retrieve the posts
				$response = $api->get( '/levels/' . $level_id . '/comments' );
				$response = unserialize( $response );

				//Cache the request
				set_transient( 'wlm_api_' . __FUNCTION__ . $level_id, $response, $this->cache_time );
			} else {
				$response = $cache;
			}

			return $response;
		}

		/**
		 * Adds a post's comments to a membership level
		 *
		 * @param integer $level_id - The ID of the level to add comments to
		 * @param array $args - An array of arguments for this resource
		 *
		 * @return integer $success - Returns 1 if the request was successful and 0 if not
		 * @return array $posts - An array of posts with comments belonging to the membership level
		 * @return array $supported_verbs - An array list of the verbs supported by this resource
		 */
		public function add_post_comments_to_level( $level_id, $args ) {
			//Run our request internally if available
			if ( $this->internal == true && function_exists( 'WishListMemberAPIRequest' ) ) {
				$response = WishListMemberAPIRequest( '/levels/' . $level_id . '/comments', 'POST', $args );

				return $response;
			}

			//Load the API.
			$api = $this->loadAPI();

			//Add the posts's comments to the level
			$response = $api->post( '/levels/' . $level_id . '/comments', $args );
			$response = unserialize( $response );

			//Reset the cache
			delete_transient( 'get_level_commented_posts' . $level_id );

			return $response;
		}

		/**
		 * Removes a post's comments from a membership level
		 *
		 * @param integer $level_id - The ID of the level
		 * @param integer $post_id - The ID of the post comments to remove
		 *
		 * @return integer $success - Returns 1 if the request was successful and 0 if not
		 * @return array $supported_verbs - An array list of the verbs supported by this resource
		 */
		public function remove_post_comments_from_level( $level_id, $post_id ) {
			//Run our request internally if available
			if ( $this->internal == true && function_exists( 'WishListMemberAPIRequest' ) ) {
				$response = WishListMemberAPIRequest( '/levels/' . $level_id . '/comments', 'DELETE' );

				return $response;
			}

			//Load the API.
			$api = $this->loadAPI();

			//Remove the posts' comments from the level
			$response = $api->delete( '/levels/' . $level_id . '/comments/' . $post_id );
			$response = unserialize( $response );

			//Reset the cache
			delete_transient( 'get_level_commented_posts' . $level_id );

			return $response;
		}

		/**
		 * Retrieves an array list of all categories in a membership level
		 *
		 * @param integer $level_id - The ID of the level to retrieve
		 *
		 * @return integer $success - Returns 1 if the request was successful and 0 if not
		 * @return array $categories - An array containing data for each of the categories in the membership level
		 * @return array $supported_verbs - An array list of the verbs supported by this resource
		 */
		public function get_level_categories( $level_id, $raw = false ) {
			//Run our request internally if available
			if ( $this->internal == true && function_exists( 'WishListMemberAPIRequest' ) ) {
				$response = WishListMemberAPIRequest( '/levels/' . $level_id . '/categories' );

				return $response;
			}

			if ( !$raw ) {
				$cache = get_transient( 'wlm_api_' . __FUNCTION__ . $level_id );
			}

			if ( !$cache ) {
				//Load the API.
				$api = $this->loadAPI();

				//Retrieve the posts
				$response = $api->get( '/levels/' . $level_id . '/categories' );
				$response = unserialize( $response );

				//Cache the request
				set_transient( 'wlm_api_' . __FUNCTION__ . $level_id, $response, $this->cache_time );
			} else {
				$response = $cache;
			}

			return $response;
		}

		/**
		 * Retrives an array list of all terms in a membership level
		 * @global object $wpdb - The WordPress database object
		 * 
		 * @param int $level_id - ID of the level to get terms for
		 * 
		 * @return array $results - Array of terms
		 */
		public function get_level_terms( $level_id ) {
			global $wpdb;

			$table = $wpdb->prefix . 'wlm_contentlevels';
			$query = "SELECT content_id FROM {$table}
					  WHERE type = '~CATEGORY'
					  AND level_id = {$level_id}";

			$results = $wpdb->get_results( $query );

			return $results;
		}

		/**
		 * Adds a category to a membership level
		 *
		 * @param integer $level_id - The ID of the level to categories to
		 * @param array $args - An array of arguments for this resource
		 *
		 * @return integer $success - Returns 1 if the request was successful and 0 if not
		 * @return array $categories - An array of categories belonging to the membership level
		 * @return array $supported_verbs - An array list of the verbs supported by this resource
		 */
		public function add_category_to_level( $level_id, $args ) {
			//Run our request internally if available
			if ( $this->internal == true && function_exists( 'WishListMemberAPIRequest' ) ) {
				$response = WishListMemberAPIRequest( '/levels/' . $level_id . '/categories', 'POST', $args );

				return $response;
			}

			//Load the API.
			$api = $this->loadAPI();

			//Add the posts's comments to the level
			$response = $api->post( '/levels/' . $level_id . '/categories', $args );
			$response = unserialize( $response );

			//Reset the cache
			delete_transient( 'get_level_categories' . $level_id );

			return $response;
		}

		/**
		 * Removes a category from a membership level
		 *
		 * @param integer $level_id - The ID of the level
		 * @param integer $post_id - The ID of the category to remove
		 *
		 * @return integer $success - Returns 1 if the request was successful and 0 if not
		 * @return array $supported_verbs - An array list of the verbs supported by this resource
		 */
		public function remove_category_from_level( $level_id, $category_id ) {
			//Run our request internally if available
			if ( $this->internal == true && function_exists( 'WishListMemberAPIRequest' ) ) {
				$response = WishListMemberAPIRequest( '/levels/' . $level_id . '/categories/' . $category_id, 'DELETE' );

				return $response;
			}

			//Load the API.
			$api = $this->loadAPI();

			//Remove the post's comments from the level
			$response = $api->delete( '/levels/' . $level_id . '/categories/' . $category_id );
			$response = unserialize( $response );

			//Reset the cache
			delete_transient( 'get_level_categories' . $level_id );

			return $response;
		}

		/**
		 * Retrieves an array list of all posts that are protected
		 *
		 * @return integer $success - Returns 1 if the request was successful and 0 if not
		 * @return array $posts - An array containing data for each of the posts
		 * @return array $supported_verbs - An array list of the verbs supported by this resource
		 */
		public function get_protected_posts( $raw = false ) {
			//Run our request internally if available
			if ( $this->internal == true && function_exists( 'WishListMemberAPIRequest' ) ) {
				$response = WishListMemberAPIRequest( '/protected/posts' );

				return $response;
			}

			if ( !$raw ) {
				$cache = get_transient( 'wlm_api_' . __FUNCTION__ );
			}

			if ( !$cache ) {
				//Load the API.
				$api = $this->loadAPI();

				//Retrieve the posts
				$response = $api->get( '/protected/posts' );
				$response = unserialize( $response );

				//Cache the request
				set_transient( 'wlm_api_' . __FUNCTION__, $response, $this->cache_time );
			} else {
				$response = $cache;
			}

			return $response;
		}

		/**
		 * Protects a post
		 *
		 * @param array $args - An array of arguments for this resource
		 *
		 * @return integer $success - Returns 1 if the request was successful and 0 if not
		 * @return array $posts - An array containing data for each of the protected posts
		 * @return array $supported_verbs - An array list of the verbs supported by this resource
		 */
		public function protect_post( $args ) {
			//Run our request internally if available
			if ( $this->internal == true && function_exists( 'WishListMemberAPIRequest' ) ) {
				$response = WishListMemberAPIRequest( '/protected/posts', 'POST', $args );

				return $response;
			}

			//Load the API.
			$api = $this->loadAPI();

			//Add the post to the level
			$response = $api->post( '/protected/posts', $args );
			$response = unserialize( $response );

			//Reset the cache
			delete_transient( 'get_protected_posts' );

			return $response;
		}

		/**
		 * Unprotects a post
		 *
		 * @param integer $post_id - The ID of the post to unprotect
		 *
		 * @return integer $success - Returns 1 if the request was successful and 0 if not
		 * @return array $supported_verbs - An array list of the verbs supported by this resource
		 */
		public function unprotect_post( $post_id ) {
			//Run our request internally if available
			if ( $this->internal == true && function_exists( 'WishListMemberAPIRequest' ) ) {
				$response = WishListMemberAPIRequest( '/protected/posts/' . $post_id, 'DELETE' );

				return $response;
			}

			//Load the API.
			$api = $this->loadAPI();

			//Remove the post from the level
			$response = $api->delete( '/protected/posts/' . $post_id );
			$response = unserialize( $response );

			//Reset the cache
			delete_transient( 'get_protected_posts' );

			return $response;
		}

		/**
		 * Retrieves an array list of all pages that are protected
		 *
		 * @return integer $success - Returns 1 if the request was successful and 0 if not
		 * @return array $posts - An array containing data for each of the pages
		 * @return array $supported_verbs - An array list of the verbs supported by this resource
		 */
		public function get_protected_pages( $raw = false ) {
			//Run our request internally if available
			if ( $this->internal == true && function_exists( 'WishListMemberAPIRequest' ) ) {
				$response = WishListMemberAPIRequest( '/protected/pages' );

				return $response;
			}

			if ( !$raw ) {
				$cache = get_transient( 'wlm_api_' . __FUNCTION__ );
			}

			if ( !$cache ) {
				//Load the API.
				$api = $this->loadAPI();

				//Retrieve the posts
				$response = $api->get( '/protected/pages' );
				$response = unserialize( $response );

				//Cache the request
				set_transient( 'wlm_api_' . __FUNCTION__, $response, $this->cache_time );
			} else {
				$response = $cache;
			}

			return $response;
		}

		/**
		 * Protects a page
		 *
		 * @param array $args - An array of arguments for this resource
		 *
		 * @return integer $success - Returns 1 if the request was successful and 0 if not
		 * @return array $posts - An array containing data for each of the protected pages
		 * @return array $supported_verbs - An array list of the verbs supported by this resource
		 */
		public function protect_page( $args ) {
			//Run our request internally if available
			if ( $this->internal == true && function_exists( 'WishListMemberAPIRequest' ) ) {
				$response = WishListMemberAPIRequest( '/protected/pages', 'POST', $args );

				return $response;
			}

			//Load the API.
			$api = $this->loadAPI();

			//Add the post to the level
			$response = $api->post( '/protected/pages', $args );
			$response = unserialize( $response );

			//Reset the cache
			delete_transient( 'get_protected_pages' );

			return $response;
		}

		/**
		 * Unprotects a page
		 *
		 * @param integer $post_id - The ID of the page to unprotect
		 *
		 * @return integer $success - Returns 1 if the request was successful and 0 if not
		 * @return array $supported_verbs - An array list of the verbs supported by this resource
		 */
		public function unprotect_page( $page_id ) {
			//Run our request internally if available
			if ( $this->internal == true && function_exists( 'WishListMemberAPIRequest' ) ) {
				$response = WishListMemberAPIRequest( '/protected/pages/' . $page_id, 'DELETE' );

				return $response;
			}

			//Load the API.
			$api = $this->loadAPI();

			//Remove the post from the level
			$response = $api->delete( '/protected/pages/' . $page_id );
			$response = unserialize( $response );

			//Reset the cache
			delete_transient( 'get_protected_pages' );

			return $response;
		}

		/**
		 * Retrieves an array list of all categories that are protected
		 *
		 * @return integer $success - Returns 1 if the request was successful and 0 if not
		 * @return array $posts - An array containing data for each of the categories
		 * @return array $supported_verbs - An array list of the verbs supported by this resource
		 */
		public function get_protected_categories( $raw = false ) {
			//Run our request internally if available
			if ( $this->internal == true && function_exists( 'WishListMemberAPIRequest' ) ) {
				$response = WishListMemberAPIRequest( '/protected/categories' );

				return $response;
			}

			if ( !$raw ) {
				$cache = get_transient( 'wlm_api_' . __FUNCTION__ );
			}

			if ( !$cache ) {
				//Load the API.
				$api = $this->loadAPI();

				//Retrieve the posts
				$response = $api->get( '/protected/categories' );
				$response = unserialize( $response );

				//Cache the request
				set_transient( 'wlm_api_' . __FUNCTION__, $response, $this->cache_time );
			} else {
				$response = $cache;
			}

			return $response;
		}

		/**
		 * Protects a category
		 *
		 * @param array $args - An array of arguments for this resource
		 *
		 * @return integer $success - Returns 1 if the request was successful and 0 if not
		 * @return array $posts - An array containing data for each of the protected categories
		 * @return array $supported_verbs - An array list of the verbs supported by this resource
		 */
		public function protect_category( $args ) {
			//Run our request internally if available
			if ( $this->internal == true && function_exists( 'WishListMemberAPIRequest' ) ) {
				$response = WishListMemberAPIRequest( '/protected/categories', 'POST', $args );

				return $response;
			}

			//Load the API.
			$api = $this->loadAPI();

			//Add the post to the level
			$response = $api->post( '/protected/categories', $args );
			$response = unserialize( $response );

			//Reset the cache
			delete_transient( 'get_protected_categories' );

			return $response;
		}

		/**
		 * Unprotects a category
		 *
		 * @param integer $post_id - The ID of the category to unprotect
		 *
		 * @return integer $success - Returns 1 if the request was successful and 0 if not
		 * @return array $supported_verbs - An array list of the verbs supported by this resource
		 */
		public function unprotect_category( $category_id ) {
			//Run our request internally if available
			if ( $this->internal == true && function_exists( 'WishListMemberAPIRequest' ) ) {
				$response = WishListMemberAPIRequest( '/protected/categories/' . $category_id, 'DELETE' );

				return $response;
			}

			//Load the API.
			$api = $this->loadAPI();

			//Remove the post from the level
			$response = $api->delete( '/protected/categories/' . $category_id );
			$response = unserialize( $response );

			//Reset the cache
			delete_transient( 'get_protected_categories' );

			return $response;
		}

		/**
		 * Retrieves an array list of all members from the database
		 *
		 * @return integer $success - Returns 1 if the request was successful and 0 if not
		 * @return array $members - An array containing data for each of the members
		 * @return array $supported_verbs - An array list of the verbs supported by this resource
		 */
		public function get_members( $raw = false ) {
			//Run our request internally if available
			if ( $this->internal == true && function_exists( 'WishListMemberAPIRequest' ) ) {
				$response = WishListMemberAPIRequest( '/members' );

				return $response;
			}

			if ( !$raw ) {
				$cache = get_transient( 'wlm_api_' . __FUNCTION__ );
			}

			if ( !$cache ) {
				//Load the API.
				$api = $this->loadAPI();

				//Retrieve the posts
				$response = $api->get( '/members' );
				$response = unserialize( $response );

				//Cache the request
				set_transient( 'wlm_api_' . __FUNCTION__, $response, $this->cache_time );
			} else {
				$response = $cache;
			}

			return $response;
		}

		/**
		 * Adds a new member to the database
		 *
		 * @param array $args - An array of arguments for this resource
		 *
		 * @return integer $success - Returns 1 if the request was successful and 0 if not
		 * @return array $members - An array containing data for each of the members
		 * @return array $supported_verbs - An array list of the verbs supported by this resource
		 */
		public function add_member( $args ) {
			//Run our request internally if available
			if ( $this->internal == true && function_exists( 'WishListMemberAPIRequest' ) ) {
				$response = WishListMemberAPIRequest( '/members', 'POST', $args );

				return $response;
			}

			//Load the API.
			$api = $this->loadAPI();

			//Retrieve the posts
			$response = $api->post( '/members', $args );
			$response = unserialize( $response );

			//Reset the cache
			delete_transient( 'get_members' );

			return $response;
		}

		/**
		 * Retrieves membership details from the database
		 *
		 * @param integer $user_id - ID of the user to retrieve
		 *
		 * @return integer $success - Returns 1 if the request was successful and 0 if not
		 * @return array $member - An array containing data for the member
		 * @return array $supported_verbs - An array list of the verbs supported by this resource
		 */
		public function get_member( $user_id, $raw = false ) {
			//Run our request internally if available
			if ( $this->internal == true && function_exists( 'WishListMemberAPIRequest' ) ) {
				$response = WishListMemberAPIRequest( '/members/' . $user_id );

				return $response;
			}

			if ( !$raw ) {
				$cache = get_transient( 'wlm_api_' . __FUNCTION__ . $user_id );
			}

			if ( !$cache ) {
				//Load the API.
				$api = $this->loadAPI();

				//Retrieve the posts
				$response = $api->get( '/members/' . $user_id );
				$response = unserialize( $response );

				//Cache the request
				set_transient( 'wlm_api_' . __FUNCTION__ . $user_id, $response, $this->cache_time );
			} else {
				$response = $cache;
			}

			return $response;
		}

		/**
		 * Retrieves membership details based on specified field
		 *
		 * @param string $field - Field to retrieve member by. Accepts "user_email" or "user_login"
		 * $param string $value - Value of field specified in $field e.g. johndoe@gmail.com
		 *
		 * @return integer $success - Returns 1 if the request was successful and 0 if not
		 * @return array $member - An array containing data for the member
		 * @return array $supported_verbs - An array list of the verbs supported by this resource
		 */
		public function get_member_by( $field, $value, $raw = false ) {
			//Run our request internally if available
			if ( $this->internal == true && function_exists( 'get_user_by' ) ) {
				$field = str_replace( 'user_', '', $field );
				$data = get_user_by( $field, $value );

				if ( !empty( $data ) ) {
					$response = array(
						'success' => 1,
						'members' => array(
							'member' => array(
								array(
									'id' => $data->ID,
									'user_login' => $data->user_login,
									'user_email' => $data->user_email,
									'_more_' => '/members/' . $data->ID
								)
							)
						),
						'supported_verbs' => array( 'GET', 'POST' )
					);
				}

				return $response;
			}

			if ( !$raw ) {
				$cache = get_transient( 'wlm_api_' . __FUNCTION__ . $user_id );
			}

			if ( !$cache ) {
				//Load the API.
				$api = $this->loadAPI();

				//Retrieve the posts
				$response = $api->get( '/members&filter[' . $field . ']=' . $value );
				$response = unserialize( $response );

				//Cache the request
				set_transient( 'wlm_api_' . __FUNCTION__ . $user_id, $response, $this->cache_time );
			} else {
				$response = $cache;
			}

			return $response;
		}

		/**
		 * Retrieves membership level details for the specified user
		 *
		 * @param integer $user_id - ID of the user to retrieve
		 *
		 * @return integer $success - Returns 1 if the request was successful and 0 if not
		 * @return array $response - An array containing level data for the member
		 * @return array $supported_verbs - An array list of the verbs supported by this resource
		 */
		public function get_member_levels( $user_id, $raw = false ) {
			//Run our request internally if available
			if ( $this->internal == true && function_exists( 'WishListMemberAPIRequest' ) ) {
				$response = WishListMemberAPIRequest( '/members/' . $user_id );
				$response = $response['member'][0]['Levels'];

				return $response;
			}

			if ( !$raw ) {
				$cache = get_transient( 'wlm_api_' . __FUNCTION__ . $user_id );
			}

			if ( !$cache ) {
				//Load the API.
				$api = $this->loadAPI();

				//Retrieve the posts
				$response = $api->get( '/members/' . $user_id );
				$response = unserialize( $response );
				$response = $response['member'][0]['Levels'];

				//Cache the request
				set_transient( 'wlm_api_' . __FUNCTION__ . $user_id, $response, $this->cache_time );
			} else {
				$response = $cache;
			}

			return $response;
		}

		/**
		 * Updates a member's details
		 *
		 * @param integer $user_id - ID of the user to retrieve
		 * @param array $args - An array of arguments for this resource
		 *
		 * @return integer $success - Returns 1 if the request was successful and 0 if not
		 * @return array $member - An array containing data for the member
		 * @return array $supported_verbs - An array list of the verbs supported by this resource
		 */
		public function update_member( $user_id, $args ) {
			//Run our request internally if available
			if ( $this->internal == true && function_exists( 'WishListMemberAPIRequest' ) ) {
				$response = WishListMemberAPIRequest( '/members/' . $user_id, 'PUT', $args );

				return $response;
			}

			//Load the API.
			$api = $this->loadAPI();

			//Retrieve the posts
			$response = $api->put( '/members/' . $user_id, $args );
			$response = unserialize( $response );

			//Reset the cache
			delete_transient( 'get_members' );
			delete_transient( 'get_member' );

			return $response;
		}

		/**
		 * Deletes a member from the database
		 *
		 * @param integer $user_id - ID of the user to delete
		 *
		 * @return integer $success - Returns 1 if the request was successful and 0 if not
		 * @return array $supported_verbs - An array list of the verbs supported by this resource
		 */
		public function delete_member( $user_id ) {
			//Run our request internally if available
			if ( $this->internal == true && function_exists( 'WishListMemberAPIRequest' ) ) {
				$response = WishListMemberAPIRequest( '/members/' . $user_id, 'DELETE' );

				return $response;
			}

			//Load the API.
			$api = $this->loadAPI();

			//Retrieve the posts
			$response = $api->delete( '/members/' . $user_id );
			$response = unserialize( $response );

			//Reset the cache
			delete_transient( 'get_members' );
			delete_transient( 'get_member' );

			return $response;
		}

		/**
		 * Display Methods
		 *
		 * This section contains methods that call internal get methods typically in
		 * order to display data, but not always. These methods are non-cached as
		 * all the requisite get methods are already cached.
		 */

		/**
		 * Filters the get_levels array list
		 *
		 * @return string $filtered_levels - Returns the filtered output
		 */
		public function the_levels() {
			//Get the levels
			$levels = $this->get_levels();

			//Filter the levels
			$filtered_levels = apply_filters( 'wlmapi_the_levels', $levels );

			return $filtered_levels;
		}

		/**
		 * Filters the get_level array list
		 *
		 * @return string $filtered_level - Returns the filtered output
		 */
		public function the_level( $level_id ) {
			//Get the level
			$level = $this->get_level( $level_id );

			//Filter the level display
			$filtered_level = apply_filters( 'wlmapi_the_level', $level );

			return $filtered_level;
		}

		/**
		 * Filters the get_level_members array list
		 *
		 * @return string $filtered_level - Returns the filtered output
		 */
		public function the_level_members( $level_id ) {
			//Get the members
			$members = $this->get_level_members( $level_id );

			//Filter the members list display
			$filtered_members = apply_filters( 'wlmapi_the_level_members', $members );

			return $filtered_members;
		}

		/**
		 * Filters the get_level_posts array list
		 *
		 * @return string $filtered_posts - Returns the filtered output
		 */
		public function the_level_posts( $level_id ) {
			//Get the level's posts
			$posts = $this->get_level_posts( $level_id );

			//Filter the display
			$filtered_posts = apply_filters( 'wlmapi_the_level_posts', $posts );

			return $filtered_posts;
		}

		/**
		 * Filters the get_members array list
		 *
		 * @return string $filtered_members - Returns the filtered output
		 */
		public function the_members() {
			//Get the members
			$members = $this->get_members();

			//Filter the members list display
			$filtered_members = apply_filters( 'wlmapi_the_members', $members );

			return $filtered_members;
		}

		/**
		 * Checks if the user belongs to the membership level
		 *
		 * @param integer $level_id - ID of the membership level
		 * @param integer $user_id - ID of the user. Defaults to current user if empty
		 *
		 * @return boolean - Returns true if user belongs to the level and false if not
		 */
		public function is_user_a_member( $level_id, $user_id ) {
			//If $user_id isn't passed, get the current user ID
			if ( empty( $user_id ) ) {
				global $current_user;

				get_currentuserinfo();

				$user_id = $current_user->ID;
			}

			//Get the members array for the membership level
			$members = $this->get_level_members( $level_id );
			$members = $members['members']['member'];

			//Loop through and check for a match
			foreach ( $members as $member ) {
				if ( $user_id == $member['id'] ) {
					return true;
				}
			}

			return false;
		}

		/**
		 * Checks if a member can access an object
		 * 
		 * @param int $user_id - The ID of the user to check againts
		 * @param string $object_type - The type of object (post, page, category)
		 * @param int $object_id - The ID of the object
		 * 
		 * @return boolean - True if user has access. False if not
		 */
		public function member_can_access( $user_id, $object_type, $object_id, $raw = false ) {
			//Setup
			$format = sprintf( '/content/%s/%d', $object_type, $object_id );
			$taxonomies = get_taxonomies();

			//If we're checking a taxonomy, we change the request format
			if ( in_array( $object_type, $taxonomies ) ) {
				$format = sprintf( '/categories/%s/%d', $object_type, $object_id );
			}

			//Run our request internally if available
			if ( $this->internal == true && function_exists( 'WishListMemberAPIRequest' ) ) {
				$response = WishListMemberAPIRequest( $format );
			} else {
				if ( !$raw ) {
					$cache = get_transient( 'wlm_api_' . __FUNCTION__ );
				}

				if ( !$cache ) {
					//Load the API.
					$api = $this->loadAPI();

					//Retrieve the membership levels from the API and unseralize the response
					$response = $api->get( $format );
					$response = unserialize( $response );

					//Cache the request
					set_transient( 'wlm_api_' . __FUNCTION__, $response, $this->cache_time );
				} else {
					$response = $cache;
				}
			}

			//Check if the user has access to object via levels
			$levels = $response['content'][$object_type][0]['Levels'];

			foreach ( $levels as $level ) {
				if ( $this->is_user_a_member( $level, $user_id ) ) {
					return true;
				}
			}

			//Check if the user has access to object via Pay Per Post
			$payperpost = $response['content'][$object_type][0]['PayPerPost'];

			if ( $payperpost ) {
				$payperpostusers = $response['content'][$object_type][0]['PayPerPostUsers'];

				if ( in_array( 'U-' . $user_id, $payperpostusers ) ) {
					return true;
				}
			}

			return false;
		}

		/**
		 * Protects posts and adds them to the membership level
		 *
		 * @param integer $level_id - ID of the membership level
		 * @param array|string $post_ids - Accepts an array or comma-seprated list of post IDs
		 *
		 * @return array $add_to_level - Returns an array containing post information for successfully processed posts
		 */
		public function manage_post( $level_id, $post_ids ) {
			//Format our $post_ids array for passing to the API
			if ( !is_array( $post_ids ) ) {
				$post_ids = explode( ',', $post_ids );
			}

			//Set up our arguments array
			$args = array( 'ContentIds' => $post_ids );

			//Protect the posts
			$protect = $this->protect_post( $args );

			//If the query failed, return the error array
			if ( !$protect['success'] ) {
				return $protect;
			}

			//Add posts to the level
			$add_to_level = $this->add_post_to_level( $level_id, $args );

			return $add_to_level;
		}

		/**
		 * Unprotects posts and removes them from the membership level
		 *
		 * @param integer $level_id - ID of the membership level
		 * @param array|string $post_ids - Accepts an array or comma-seprated list of post IDs
		 *
		 * @return array $add_to_level - Returns an array containing post information for successfully processed posts
		 */
		public function unmanage_post( $level_id, $post_ids ) {
			//Format our $post_ids array for passing to the API
			if ( !is_array( $post_ids ) ) {
				$post_ids = explode( ',', $post_ids );
			}

			//Set up our arguments array
			$args = array( 'ContentIds' => $post_ids );

			//Loop through IDs, unprotect each post, and remove it from the level
			foreach ( $post_ids as $post_id ) {
				$unprotect[] = $this->unprotect_post( $post_id );
				$remove[] = $this->remove_post_from_level( $level_id, $post_id );
			}

			return $remove;
		}

		/**
		 * Check if a post type is protected is protected
		 * 
		 * @param integer $post_id - ID of the post to check
		 * @param string $post_type - Post type to check. Default: posts
		 * 
		 * @return boolean true/false - True if post is protected. False if not
		 */
		public function is_protected( $post_id, $post_type = 'posts', $raw = false ) {
			//Run our request internally if available
			if ( $this->internal == true && function_exists( 'WishListMemberAPIRequest' ) ) {
				$response = WishListMemberAPIRequest( '/content/' . $post_type . '/' . $post_id );
				$is_protected = $response['content'][$post_type][0]['Protected'];

				return $is_protected;
			}

			if ( !$raw ) {
				$cache = get_transient( 'wlm_api_' . __FUNCTION__ );
			}

			if ( !$cache ) {
				//Load the API.
				$api = $this->loadAPI();

				//Retrieve the membership levels from the API and unseralize the response
				$response = $api->get( '/content/' . $post_type . '/' . $post_id );
				$response = unserialize( $response );

				//Cache the request
				set_transient( 'wlm_api_' . __FUNCTION__, $response, $this->cache_time );
			} else {
				$response = $cache;
			}

			$is_protected = $response['content'][$post_type][0]['Protected'];

			return $is_protected;
		}

	}

}
?>