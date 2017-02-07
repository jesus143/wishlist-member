<?php

/**
 * Main Functions for the WishList Member API
 * @author John Morris <john@wishlistproducts.com>
 *
 * @version 1.0
 * John Morris
 * 12/05/2011
 */
/**
 * Include our API Class
 */
require_once( dirname( __FILE__ ) . '/class-api-methods.php' );

/**
 * Instantiate the API Class
 */
$wlm_api_methods = new WLMAPIMethods( 3600 );

/**
 * Retrieves an array list of all membership levels
 *
 * @return integer $success - Returns 1 if the request was successful and 0 if not
 * @return array $levels - An array containing data for each membership level
 * @return array $supported_verbs - An array list of the verbs supported by this resource
 */
function wlmapi_get_levels() {
	global $wlm_api_methods;

	return $wlm_api_methods->get_levels();
}

/**
 * Filters the wlmapi_get_levels array list
 *
 * @return string $filtered_levels - Returns the filtered output
 */
function wlmapi_the_levels() {
	global $wlm_api_methods;

	return $wlm_api_methods->the_levels();
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
function wlmapi_get_level( $level_id ) {
	global $wlm_api_methods;

	return $wlm_api_methods->get_level( $level_id );
}

/**
 * Filters the wlmapi_get_level array list
 *
 * @return string $filtered_level - Returns the filtered output
 */
function wlmapi_the_level( $level_id ) {
	global $wlm_api_methods;

	return $wlm_api_methods->the_level( $level_id );
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
function wlmapi_create_level( $args ) {
	global $wlm_api_methods;

	return $wlm_api_methods->create_level( $args );
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
function wlmapi_update_level( $level_id, $args ) {
	global $wlm_api_methods;

	return $wlm_api_methods->update_level( $level_id, $args );
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
function wlmapi_delete_level( $level_id ) {
	global $wlm_api_methods;

	return $wlm_api_methods->delete_level( $level_id );
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
function wlmapi_get_level_members( $level_id ) {
	global $wlm_api_methods;

	return $wlm_api_methods->get_level_members( $level_id );
}

/**
 * Filters the wlmapi_get_level_members array list
 *
 * @return string $filtered_level - Returns the filtered output
 */
function wlmapi_the_level_members( $level_id ) {
	global $wlm_api_methods;

	return $wlm_api_methods->the_level_members( $level_id );
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
function wlmapi_add_member_to_level( $level_id, $args ) {
	global $wlm_api_methods;

	return $wlm_api_methods->add_member_to_level( $level_id, $args );
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
function wlmapi_get_level_member_data( $level_id, $member_id ) {
	global $wlm_api_methods;

	return $wlm_api_methods->get_level_member_data( $level_id, $member_id );
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
function wlmapi_update_level_member_data( $level_id, $member_id, $args ) {
	global $wlm_api_methods;

	return $wlm_api_methods->update_level_member_data( $level_id, $member_id, $args );
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
function wlmapi_remove_member_from_level( $level_id, $member_id ) {
	global $wlm_api_methods;

	return $wlm_api_methods->remove_member_from_level( $level_id, $member_id );
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
function wlmapi_get_level_posts( $level_id ) {
	global $wlm_api_methods;

	return $wlm_api_methods->get_level_posts( $level_id );
}

/**
 * Retrieves an array list of all posts in a membership level of a specified post type
 *
 * @param integer $level_id - The ID of the level to retrieve
 * @param string $post_type - The slug of the post type to retrieve
 *
 * @return integer $success - Returns 1 if the request was successful and 0 if not
 * @return array $posts - An array containing data for each of the posts in the membership level
 * @return array $supported_verbs - An array list of the verbs supported by this resource
 */
function wlmapi_get_level_post_type( $level_id, $post_type ) {
	global $wlm_api_methods;

	return $wlm_api_methods->get_level_post_type( $level_id, $post_type );
}

/**
 * Filters the wlmapi_get_level_posts array list
 *
 * @return string $filtered_posts - Returns the filtered output
 */
function wlmapi_the_level_posts( $level_id ) {
	global $wlm_api_methods;

	return $wlm_api_methods->the_level_posts( $level_id );
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
function wlmapi_add_post_to_level( $level_id, $args ) {
	global $wlm_api_methods;

	return $wlm_api_methods->add_post_to_level( $level_id, $args );
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
function wlmapi_remove_post_from_level( $level_id, $post_id ) {
	global $wlm_api_methods;

	return $wlm_api_methods->remove_post_from_level( $level_id, $post_id );
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
function wlmapi_get_level_pages( $level_id ) {
	global $wlm_api_methods;

	return $wlm_api_methods->get_level_pages( $level_id );
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
function wlmapi_add_page_to_level( $level_id, $args ) {
	global $wlm_api_methods;

	return $wlm_api_methods->add_page_to_level( $level_id, $args );
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
function wlmapi_remove_page_from_level( $level_id, $page_id ) {
	global $wlm_api_methods;

	return $wlm_api_methods->remove_page_from_level( $level_id, $page_id );
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
function wlmapi_get_level_commented_posts( $level_id ) {
	global $wlm_api_methods;

	return $wlm_api_methods->get_level_commented_posts( $level_id );
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
function wlmapi_add_post_comments_to_level( $level_id, $args ) {
	global $wlm_api_methods;

	return $wlm_api_methods->add_post_comments_to_level( $level_id, $args );
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
function wlmapi_remove_post_comments_from_level( $level_id, $post_id ) {
	global $wlm_api_methods;

	return $wlm_api_methods->remove_post_comments_from_level( $level_id, $post_id );
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
function wlmapi_get_level_categories( $level_id ) {
	global $wlm_api_methods;

	return $wlm_api_methods->get_level_categories( $level_id );
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
function wlmapi_add_category_to_level( $level_id, $args ) {
	global $wlm_api_methods;

	return $wlm_api_methods->add_category_to_level( $level_id, $args );
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
function wlmapi_remove_category_from_level( $level_id, $category_id ) {
	global $wlm_api_methods;

	return $wlm_api_methods->remove_category_from_level( $level_id, $category_id );
}

/**
 * Retrieves an array list of all posts that are protected
 *
 * @return integer $success - Returns 1 if the request was successful and 0 if not
 * @return array $posts - An array containing data for each of the posts
 * @return array $supported_verbs - An array list of the verbs supported by this resource
 */
function wlmapi_get_protected_posts() {
	global $wlm_api_methods;

	return $wlm_api_methods->get_protected_posts();
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
function wlmapi_protect_post( $args ) {
	global $wlm_api_methods;

	return $wlm_api_methods->protect_post( $args );
}

/**
 * Unprotects a post
 *
 * @param integer $post_id - The ID of the post to unprotect
 *
 * @return integer $success - Returns 1 if the request was successful and 0 if not
 * @return array $supported_verbs - An array list of the verbs supported by this resource
 */
function wlmapi_unprotect_post( $post_id ) {
	global $wlm_api_methods;

	return $wlm_api_methods->unprotect_post( $post_id );
}

/**
 * Retrieves an array list of all pages that are protected
 *
 * @return integer $success - Returns 1 if the request was successful and 0 if not
 * @return array $posts - An array containing data for each of the pages
 * @return array $supported_verbs - An array list of the verbs supported by this resource
 */
function wlmapi_get_protected_pages() {
	global $wlm_api_methods;

	return $wlm_api_methods->get_protected_pages();
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
function wlmapi_protect_page( $args ) {
	global $wlm_api_methods;

	return $wlm_api_methods->protect_page( $args );
}

/**
 * Unprotects a page
 *
 * @param integer $post_id - The ID of the page to unprotect
 *
 * @return integer $success - Returns 1 if the request was successful and 0 if not
 * @return array $supported_verbs - An array list of the verbs supported by this resource
 */
function wlmapi_unprotect_page( $page_id ) {
	global $wlm_api_methods;

	return $wlm_api_methods->unprotect_page( $page_id );
}

/**
 * Retrieves an array list of all categories that are protected
 *
 * @return integer $success - Returns 1 if the request was successful and 0 if not
 * @return array $posts - An array containing data for each of the categories
 * @return array $supported_verbs - An array list of the verbs supported by this resource
 */
function wlmapi_get_protected_categories() {
	global $wlm_api_methods;

	return $wlm_api_methods->get_protected_categories();
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
function wlmapi_protect_category( $args ) {
	global $wlm_api_methods;

	return $wlm_api_methods->protect_category( $args );
}

/**
 * Unprotects a category
 *
 * @param integer $post_id - The ID of the category to unprotect
 *
 * @return integer $success - Returns 1 if the request was successful and 0 if not
 * @return array $supported_verbs - An array list of the verbs supported by this resource
 */
function wlmapi_unprotect_category( $category_id ) {
	global $wlm_api_methods;

	return $wlm_api_methods->unprotect_category( $category_id );
}

/**
 * Retrieves an array list of all members from the database
 *
 * @return integer $success - Returns 1 if the request was successful and 0 if not
 * @return array $members - An array containing data for each of the members
 * @return array $supported_verbs - An array list of the verbs supported by this resource
 */
function wlmapi_get_members() {
	global $wlm_api_methods;

	return $wlm_api_methods->get_members();
}

/**
 * Filters the wlmapi_get_members array list
 *
 * @return string $filtered_members - Returns the filtered output
 */
function wlmapi_the_members() {
	global $wlm_api_methods;

	return $wlm_api_methods->the_members();
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
function wlmapi_add_member( $args ) {
	global $wlm_api_methods;

	return $wlm_api_methods->add_member( $args );
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
function wlmapi_get_member( $member_id ) {
	global $wlm_api_methods;

	return $wlm_api_methods->get_member( $member_id );
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
function wlmapi_get_member_by( $field, $value ) {
	global $wlm_api_methods;

	return $wlm_api_methods->get_member_by( $field, $value );
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
function wlmapi_get_member_levels( $member_id ) {
	global $wlm_api_methods;

	return $wlm_api_methods->get_member_levels( $member_id );
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
function wlmapi_update_member( $user_id, $args ) {
	global $wlm_api_methods;

	return $wlm_api_methods->update_member( $user_id, $args );
}

/**
 * Deletes a member from the database
 *
 * @param integer $user_id - ID of the user to delete
 *
 * @return integer $success - Returns 1 if the request was successful and 0 if not
 * @return array $supported_verbs - An array list of the verbs supported by this resource
 */
function wlmapi_delete_member( $member_id ) {
	global $wlm_api_methods;

	return $wlm_api_methods->delete_member( $member_id );
}

/**
 * Checks if the user belongs to the membership level
 *
 * @param integer $level_id - ID of the membership level
 * @param integer $user_id - ID of the user. Defaults to current user if empty
 *
 * @return boolean - Returns true if user belongs to the level and false if not
 */
function wlmapi_is_user_a_member( $level_id, $member_id ) {
	global $wlm_api_methods;

	return $wlm_api_methods->is_user_a_member( $level_id, $member_id );
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
function wlmapi_member_can_access( $user_id, $object_type, $object_id ) {
	global $wlm_api_methods;

	return $wlm_api_methods->member_can_access( $user_id, $object_type, $object_id );
}

/**
 * Protects posts and adds them to the membership level
 *
 * @param integer $level_id - ID of the membership level
 * @param array|string $post_ids - Accepts an array or comma-seprated list of post IDs
 *
 * @return array $add_to_level - Returns an array containing post information for successfully processed posts
 */
function wlmapi_manage_post( $level_id, $post_ids ) {
	global $wlm_api_methods;

	return $wlm_api_methods->manage_post( $level_id, $post_ids );
}

/**
 * Unprotects posts and removes them from the membership level
 *
 * @param integer $level_id - ID of the membership level
 * @param array|string $post_ids - Accepts an array or comma-seprated list of post IDs
 *
 * @return array $add_to_level - Returns an array containing post information for successfully processed posts
 */
function wlmapi_unmanage_post( $level_id, $post_ids ) {
	global $wlm_api_methods;

	return $wlm_api_methods->unmanage_post( $level_id, $post_ids );
}

/**
 * Check if a post type is protected is protected
 * 
 * @param integer $post_id - ID of the post to check
 * @param string $post_type - Post type to check. Default: posts
 * 
 * @return boolean true/false - True if post is protected. False if not
 */
function wlmapi_is_protected( $post_id, $post_type = 'posts' ) {
	global $wlm_api_methods;

	return $wlm_api_methods->is_protected( $post_id, $post_type );
}

?>