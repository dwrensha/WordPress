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
wp_enqueue_script('user-profile');


require_once( ABSPATH . 'wp-admin/admin-header.php' );


echo '<p> To add a new user, share with them the URL of this grain.'.
     ' When they visit it, they will be automatically added with a role of <i>contributor</i>,' .
     ' allowing them to compose posts but not to publish.' .
     ' You may update their role at any time.</p>'.
     '<p>Anonymous users are not allowed to access the WordPress admin area at all.</p>';

include( ABSPATH . 'wp-admin/admin-footer.php' );
