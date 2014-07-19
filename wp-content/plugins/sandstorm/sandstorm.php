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
    if (!is_user_logged_in() && isset(apache_request_headers()['X-Sandstorm-Username'])) {
        $user_login = 'User';
        $user = get_userdatabylogin($user_login);
        $user_id = $user->ID;
        wp_set_current_user($user_id, $user_login);
        wp_set_auth_cookie($user_id);
        do_action('wp_login', $user_login);
    }
}
add_action('init', 'auto_login');


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
  <?php
}

function add_sandstorm_dashboard_widget() {
  remove_meta_box( 'dashboard_primary', 'dashboard', 'side' );
  remove_meta_box( 'dashboard_activity', 'dashboard', 'normal');

  wp_add_dashboard_widget( 'sandstorm_dashboard_widget', 'Sandstorm Publishing Information',
                           'sandstorm_publishing_info');

}

add_action( 'wp_dashboard_setup', 'add_sandstorm_dashboard_widget' );

