<?php
/**
 * New User Administration Screen.
 *
 * @package WordPress
 * @subpackage Administration
 */

/** WordPress Administration Bootstrap */
require_once( dirname( __FILE__ ) . '/admin.php' );

$title = __('Add New User');
$parent_file = 'users.php';

wp_enqueue_script('wp-ajax-response');
wp_enqueue_script( 'user-profile' );


require_once( ABSPATH . 'wp-admin/admin-header.php' );


echo '<p> To add a new user, click the "Share" button in the Sandstorm top bar.</p>';

include( ABSPATH . 'wp-admin/admin-footer.php' );
