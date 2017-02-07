<?php
/*
 * bbPress Forum and Categroy Protection
 * Original Author: Peter Indiola
 * Version: $Id:
 */
return;
if (!class_exists('WLM_OTHER_INTEGRATION_bbPress')) {

  class WLM_OTHER_INTEGRATION_bbPress {

    private $wlm;
    private $settings;

    function __construct() {
      global $WishListMemberInstance;

      if(empty($WishListMemberInstance))
        return;
      $this->wlm = $WishListMemberInstance;
    }

    public function allow_level_to_forums($forum_id) {
      global $current_user;

      if(!is_admin()) {
        $levels = $this->wlm->GetMembershipLevels($current_user->ID);
        $nonmemberredirect = $this->wlm->NonMembersURL();
        $settings = $this->wlm->GetOption('bbpsettings');

        if(!empty($settings) && !empty($levels)) {
          foreach($settings as $setting) {
            if($setting['id'] == $forum_id && $setting['protection'] == 'Protected') {
              if(!in_array($setting['sku'], $levels)) {
                header('Location:' . $nonmemberredirect);
                exit;
              }
            }
          }
        }
      }
      return $forum_id;
    }

    public function get_all_forums() {
      $bbp_f = bbp_parse_args( $args, array(
        'post_type'           => bbp_get_forum_post_type(),
        'post_parent'         => 'any',
        'post_status'         => bbp_get_public_status_id(),
        'posts_per_page'      => get_option( '_bbp_forums_per_page', 50 ),
        'ignore_sticky_posts' => true,
        'orderby'             => 'menu_order title',
        'order'               => 'ASC'
      ), 'has_forums' );

      $bbp              = bbpress();
      $bbp->forum_query = new WP_Query( $bbp_f );

      $data = array();
      foreach($bbp->forum_query->posts as $post) {
        $type = 'forum';
        $is_parent = false;
        $is_category = bbp_forum_get_subforums($post->ID);
        if($is_category) {
          $type = 'category';
          $parent = true;
        }

        $settings = $this->wlm->GetOption('bbpsettings');

        if($settings && count($settings) > 0){
          foreach($settings as $setting) {
            if($setting["id"] == $post->ID) {
              $data[$post->ID] = array(
                "id" => $post->ID,
                "name" => $post->post_title,
                "level" => $setting["sku"],
                "protection" => $setting["protection"],
                "type" => $type,
                "parent" => $parent,
                "date" => ""
              );
            }
          }
          if(!isset($data[$post->ID])){
            $data[$post->ID] = array(
              "id" => $post->ID,
              "name" => $post->post_title,
              "level" => "",
              "protection" => "",
              "type" => $type,
              "parent" => $parent,
              "date" => ""
            );
          }
        } else {
          $data[$post->ID] = array(
            "id" => $post->ID,
            "name" => $post->post_title,
            "level" => "",
            "protection" => "",
            "type" => $type,
            "parent" => $parent,
            "date" => ""
          );
        }
      }
      echo json_encode($data);
      die();
    }

    public function save_bb_settings() {
      $id = $_POST['id'];
      $subscription = $_POST;
      $this->settings[$id] = $subscription;
      $data = array(
        'protection' => $_POST['protection'],
        'sku' => $_POST['sku'],
        'id' => $_POST['id']
      );

      $settings = $this->wlm->GetOption('bbpsettings');

      if(!empty($settings)) {
        $found = false;
        foreach($settings as $row => $setting) {
          if($setting['id'] == $_POST['id']) {
            $settings[$row]['protection'] = $data['protection'];
            $settings[$row]['sku'] = $data['sku'];
            $found = true;
          }
        }
        if(!$found) {
          $settings[] = $data;
        }
      } else {
        $settings[] = $data;
      }
      $status = $this->wlm->SaveOption("bbpsettings", $settings);

      echo json_encode($status);
      die();
    }

  }
  // add_action('bbp_get_forum_id', array(new WLM_OTHER_INTEGRATION_bbPress, 'allow_level_to_forums'));
  add_action('wp_ajax_wlm_bbpress_all-forums', array(new WLM_OTHER_INTEGRATION_bbPress, 'get_all_forums'));
  add_action('wp_ajax_wlm_bbpress_save-settings', array(new WLM_OTHER_INTEGRATION_bbPress, 'save_bb_settings'));
}
