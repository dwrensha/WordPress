<?php
/**
 * WordPress Installer
 *
 * @package WordPress
 * @subpackage Administration
 */

// Sanity check.
if ( false ) {
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>Error: PHP is not running</title>
</head>
<body class="wp-core-ui">
	<h1 id="logo"><a href="https://wordpress.org/">WordPress</a></h1>
	<h2>Error: PHP is not running</h2>
	<p>WordPress requires that your web server is running PHP. Your server does not have PHP installed, or PHP is turned off.</p>
</body>
</html>
<?php
}

/**
 * We are installing WordPress.
 *
 * @since 1.5.1
 * @var bool
 */
define( 'WP_INSTALLING', true );

/** Load WordPress Bootstrap */
require_once( dirname( dirname( __FILE__ ) ) . '/wp-load.php' );

/** Load WordPress Administration Upgrade API */
require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

/** Load wpdb */
require_once( ABSPATH . 'wp-includes/wp-db.php' );



// Let's check to make sure WP isn't already installed.
if ( is_blog_installed() ) {
  error_log('tried to install, but blog is already installed!');
  wp_redirect(wp_guess_url() . '/wp-admin/index.php');
  die();
}


/*
 Sandstorm: provide the installation data without prompting the user.
*/

$headers = apache_request_headers();

$user_login = $headers['X-Sandstorm-User-Id'];

wp_install("example blog", $user_login, $user_login . "@example.com", 1, '', "garply" );

$username = $headers['X-Sandstorm-Username'];
if (!isset($username)) {
  $username = 'sandstorm user';
}

$user_id = wp_update_user( array( 'ID' => get_userdatabylogin($user_login),
                                  'nickname' => $username,
                                  'display_name' => $username));

if ( is_wp_error( $user_id ) ) {
    error_log("error updating ");
}


update_option('active_plugins', array(
                                'root-relative-urls/sb_root_relative_urls.php',
                                'sqlite-integration/sqlite-integration.php',
                                'wordpress-importer/wordpress-importer.php'));

//activate_plugin('root-relative-urls/sb_root_relative_urls.php');


global $wp_rewrite;
$wp_rewrite->set_permalink_structure('/%year%/%monthnum%/%postname%/');
$wp_rewrite->flush_rules();

update_option( 'default_comment_status', '' );

$link = wp_guess_url() . '/wp-admin/index.php';
wp_redirect( $link );
die();
