<?php
/**
 * @package Sandstorm
 * @version 0.0.1
 */
/*
Plugin Name: Sandstorm Integration
Plugin URI: http://github.com/dwrensha/wordpress.git
Description: Sandstorm things.
Author: Sandstorm Development Group, Inc.
Version: 0.0.1
Author URI: https://sandstorm.io
*/

// don't redirect to wp-login.php
function auth_redirect() {}

function auto_login() {
    if (!is_user_logged_in()) {
       $headers = apache_request_headers();
       $permissions = $headers['X-Sandstorm-Permissions'];
       $sandstorm_user_id = $headers['X-Sandstorm-User-Id'];
       if (!(FALSE === strpos($permissions, 'admin'))) {
           $user_login = 'Admin';
           $user = get_userdatabylogin($user_login);
           $user_id = $user->ID;
           error_log($user_id);
           wp_set_current_user($user_id, $user_login);
           wp_set_auth_cookie($user_id);
           do_action('wp_login', $user_login);
       } else if ($sandstorm_user_id) {
           $user_id = username_exists($sandstorm_user_id);
           if (!$user_id) {
               $random_password = wp_generate_password( $length=12, $include_standard_special_chars=false );
               $user_id = wp_create_user($sandstorm_user_id, $random_password, $sandstorm_user_id . '@example.com');
               $username = $headers['X-Sandstorm-Username'];
               wp_update_user( array( 'ID' => $user_id,
                                      'nickname' => $username,
                                     'display_name' => $username,
                                     'role' => 'editor'));

           }
           wp_set_current_user($user_id, $sandstorm_user_id);
           wp_set_auth_cookie($user_id);
           do_action('wp_login', $sandstorm_user_id);
       }
    }
}
add_action('init', 'auto_login');

function sandstorm_refuse_login() {
  wp_redirect(wp_guess_url() . '/index.php');
  die();
}

add_action('login_init', 'sandstorm_refuse_login');

function sandstorm_publish() {
   $result = shell_exec('/publish-it.sh');
}

add_action('publish_post', 'sandstorm_publish');


function sandstorm_publishing_info() {
  $lines = array();
  $result = exec('/sandstorm/bin/getPublicId', $lines);

  echo "<p>Your site is available at: <a target='_blank' href='$lines[2]'>$lines[2]</a></p>";

  if ($lines[3] == 'true') {
    echo "<p>If you weren't using a demo account, you could additionally publish the site to an arbitrary domain you control.</p>";
    return;
  }

  ?>

  <p> To set up your domain to point at your published site,
  add the following DNS records to your domain. Replace <code>host.example.com</code> with your site's hostname.
  </p>
  <p/>
  <?php

  echo "<code>host.example.com IN CNAME $lines[1] \n";
  echo "sandstorm-www.host.example.com IN TXT $lines[0] </code>";
  ?>
  <p/>
  <p>
  Note: If your site may get a lot of traffic, consider putting it behind a CDN.
  <a href="https://cloudflare.com" target="_blank">CloudFlare</a>, for example, can do this for free.
  </p>
  <form name="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post" id="generate-static" class="initial-form hide-if-no-js">
   <p class="submit">
    <input type="hidden" name="action" value="generate_static">
   <?php wp_nonce_field( 'generate-static' ); ?>
   <?php submit_button(__('Publish'), 'primary', 'generate', false); ?>
   <br class="clear"/>
   </p>
  </form>
  <?php
}

add_action('admin_post_generate_static', 'sandstorm_generate_static');

function sandstorm_generate_static() {
   sandstorm_publish();
   wp_redirect(wp_guess_url() . '/wp-admin/index.php');
   die();
}

function add_sandstorm_dashboard_widget() {
  remove_meta_box( 'dashboard_primary', 'dashboard', 'side' );
  remove_meta_box( 'dashboard_activity', 'dashboard', 'normal');

  wp_add_dashboard_widget( 'sandstorm_dashboard_widget', 'Sandstorm Publishing Information',
                           'sandstorm_publishing_info');

}

add_action( 'wp_dashboard_setup', 'add_sandstorm_dashboard_widget' );


// Disable plugin deactivation.
add_filter( 'plugin_action_links', 'disable_plugin_deactivation', 10, 4 );
function disable_plugin_deactivation( $actions, $plugin_file, $plugin_data, $context ) {

  $vital_plugins = array('sandstorm/sandstorm.php',
                         'sqlite-integration/sqlite-integration.php',
                         'root-relative-urls/sb_root_relative_urls.php');

  if (in_array($plugin_file, $vital_plugins)) {
    // Remove edit link.
    if ( array_key_exists( 'edit', $actions ) ) {
        unset( $actions['edit'] );
    }
    // Remove deactivate link.
    if ( array_key_exists( 'deactivate', $actions ) ) {
      unset( $actions['deactivate'] );
    }
  }
  return $actions;
}

