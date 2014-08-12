<?php
/**
 * @package Sandstorm
 * @version 0.0.1
 */
/*
Plugin Name: Sandstorm Integration
Plugin URI: http://github.com/dwrensha/wordpress.git
Description: Lets Sandstorm handle authentication and static web publishing.
Author: Sandstorm Development Group, Inc.
Version: 0.0.1
Author URI: https://sandstorm.io
*/

// don't redirect to wp-login.php
if (!function_exists('auth_redirect')) {
  function auth_redirect() {}
}

function auto_login() {
    if (!is_user_logged_in()) {
       $headers = apache_request_headers();
       $permissions = $headers['X-Sandstorm-Permissions'];
       $sandstorm_user_id = $headers['X-Sandstorm-User-Id'];

       if ($sandstorm_user_id) {
           $user = get_user_by('login', $sandstorm_user_id);
           $user_role = '';
           $user_id = '';
           if (!$user) {
               $username = $headers['X-Sandstorm-Username'];
               $user_role = 'contributor';
               $user_id = wp_insert_user(
                               array( 'user_login' => $sandstorm_user_id,
                                      'user_pass' => 'garply',
                                      'nickname' => $username,
                                      'display_name' => $username,
                                      'role' => $user_role,
                                      'user_email' => ($sandstorm_user_id . '@example.com')));
           } else {
               $user_id = $user->ID;
               $user_role = $user->role;
           }

           if ($user_role !== 'administrator' && !(FALSE === strpos($permissions, 'admin'))) {
                 // If user is not admin but does own the grain, make them an admin.
                 wp_update_user( array( 'ID' => $user_id,
                                        'role' => 'administrator'));
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


function sandstorm_publishing_info() {
  $lines = array();
  $result = exec('/sandstorm/bin/getPublicId', $lines);

  echo "<p>Your public site is available at: <a target='_blank' href='$lines[2]'>$lines[2]</a></p>";

  if ($lines[3] == 'true') {
    echo "<p>If you weren't using a demo account, you could additionally publish the site to an arbitrary domain you control.</p>";
    return;
  }

  ?>

  <p> To set up your domain to point at your public site,
  add the following DNS records to your domain. Replace <code>host.example.com</code> with your site's hostname.
  </p>
  <p/>
  <?php

  echo "<code>host.example.com IN CNAME $lines[1] <br>";
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
   <?php submit_button(__('Regenerate Public Site'), 'primary', 'generate', false); ?>
   <br class="clear"/>
   </p>
  </form>
  <?php
}

add_action('admin_post_generate_static', 'sandstorm_generate_static');

function sandstorm_generate_static() {
  if (current_user_can('publish_pages')) {
    sandstorm_publish();
    wp_redirect(wp_guess_url() . '/wp-admin/index.php');
    die();
  }
}

function add_sandstorm_dashboard_widget() {
  remove_meta_box( 'dashboard_primary', 'dashboard', 'side' );
  remove_meta_box( 'dashboard_activity', 'dashboard', 'normal');

  if (current_user_can('publish_pages')) {
      wp_add_dashboard_widget( 'sandstorm_dashboard_widget', 'Sandstorm Publishing Information',
                               'sandstorm_publishing_info');
  }

}

add_action( 'wp_dashboard_setup', 'add_sandstorm_dashboard_widget' );


// Disable plugin deactivation.
add_filter( 'plugin_action_links', 'disable_plugin_deactivation', 10, 4 );
function disable_plugin_deactivation( $actions, $plugin_file, $plugin_data, $context ) {

  $vital_plugins = array('sandstorm/sandstorm.php',
                         'sqlite-integration/sqlite-integration.php',
                         'root-relative-urls/sb_root_relative_urls.php');

  if (in_array($plugin_file, $vital_plugins)) {

    // Remove deactivate link.
    if ( array_key_exists( 'deactivate', $actions ) ) {
      unset( $actions['deactivate'] );
    }

    $actions['warning'] = 'Needed by Sandstorm.';

  }
  return $actions;
}


add_filter('get_search_form', 'sandstorm_search_form');
function sandstorm_search_form($orig) {
    if (isset(apache_request_headers()['X-Sandstorm-Username'])) {
       // We can submit the usual WordPress search query.
       return $orig;
    } else {
       // Use Google search for the published static site.

      $form = '<form action="http://google.com/search" id="searchform" class="search-form" method="get" name="google-search" target="_blank">'.
            '<input type="hidden" name="sitesearch" class="google-search-input">'.
            '<input type="text" name="q" id="s" placeholder="Search" class="search-field">'.
            '</form>'.
            '<script type="text/javascript">'.
            'Array.prototype.forEach.call(document.getElementsByClassName("google-search-input"), function (x) {x.value=window.location.host});'.
            '</script>';
      return $form;
   }
}

// Remove a bunch of things we don't want.
remove_action('wp_head', 'rsd_link');
remove_action('wp_head', 'wlwmanifest_link');
remove_action('wp_head', 'wp_generator');
remove_action('wp_head', 'wp_shortlink_wp_head');
remove_action( 'wp_head', 'feed_links_extra', 3 );

# Feeds mostly work, but they get published at feed/index.html.
#remove_action( 'wp_head', 'feed_links', 2 );


