<?php
/**
 * @package Sandstorm
 * @version 0.0.1
 */
/*
Plugin Name: Sandstorm
Plugin URI: http://github.com/dwrensha/wordpress.git
Description: Sandstorm things.
Author: The Sandstorm Team
Version: 0.0.1
Author URI: https://sandstorm.io
*/

// don't redirect to wp-login.php
function auth_redirect() {}

function auto_login() {
    $permissions = apache_request_headers()['X-Sandstorm-Permissions'];
    if (!is_user_logged_in() && !(FALSE === strpos($permissions, 'admin'))) {
        $user_login = 'Admin';
        $user = get_userdatabylogin($user_login);
        $user_id = $user->ID;
        wp_set_current_user($user_id, $user_login);
        wp_set_auth_cookie($user_id);
        do_action('wp_login', $user_login);
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
  ?>
  <p> To set up your domain to point at your published site,
  add the following DNS records to your domain. Replace host.example.com with your site's hostname.
  </p>
  <p/>
  <?php
  $lines = array();
  $result = exec('/sandstorm/bin/getPublicId', $lines);
  echo "<p> host.example.com IN CNAME $lines[1] </p>";
  echo "<p> sandstorm-www.host.example.com IN TXT $lines[0] </p>";
  ?>
  <p/>
  <p>
Note: If your site may get a lot of traffic, consider putting it behind a CDN.
  </p>
  <form name="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post" id="generate-static" class="initial-form hide-if-no-js">
   <p class="submit">
    <input type="hidden" name="action" value="generate_static">
   <?php wp_nonce_field( 'generate-static' ); ?>
   <?php submit_button(__('Generate Static Site Now'), 'primary', 'generate', false); ?>
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

