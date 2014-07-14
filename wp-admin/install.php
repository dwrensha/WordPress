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

/** Load WordPress Translation Install API */
require_once( ABSPATH . 'wp-admin/includes/translation-install.php' );

/** Load wpdb */
require_once( ABSPATH . WPINC . '/wp-db.php' );

nocache_headers();

<<<<<<< HEAD
$step = isset( $_GET['step'] ) ? (int) $_GET['step'] : 0;

/**
 * Display install header.
 *
 * @since 2.5.0
 */
function display_header( $body_classes = '' ) {
	header( 'Content-Type: text/html; charset=utf-8' );
	if ( is_rtl() ) {
		$body_classes .= 'rtl';
	}
	if ( $body_classes ) {
		$body_classes = ' ' . $body_classes;
	}
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>
<head>
	<meta name="viewport" content="width=device-width" />
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title><?php _e( 'WordPress &rsaquo; Installation' ); ?></title>
	<?php
	wp_admin_css( 'install', true );
	?>
</head>
<body class="wp-core-ui<?php echo $body_classes ?>">
<h1 id="logo"><a href="<?php echo esc_url( __( 'https://wordpress.org/' ) ); ?>" tabindex="-1"><?php _e( 'WordPress' ); ?></a></h1>

<?php
} // end display_header()

/**
 * Display installer setup form.
 *
 * @since 2.8.0
 */
function display_setup_form( $error = null ) {
	global $wpdb;

	$sql = $wpdb->prepare( "SHOW TABLES LIKE %s", $wpdb->esc_like( $wpdb->users ) );
	$user_table = ( $wpdb->get_var( $sql ) != null );

	// Ensure that Blogs appear in search engines by default.
	$blog_public = 1;
	if ( isset( $_POST['weblog_title'] ) ) {
		$blog_public = isset( $_POST['blog_public'] );
	}

	$weblog_title = isset( $_POST['weblog_title'] ) ? trim( wp_unslash( $_POST['weblog_title'] ) ) : '';
	$user_name = isset($_POST['user_name']) ? trim( wp_unslash( $_POST['user_name'] ) ) : '';
	$admin_email  = isset( $_POST['admin_email']  ) ? trim( wp_unslash( $_POST['admin_email'] ) ) : '';

	if ( ! is_null( $error ) ) {
?>
<p class="message"><?php echo $error; ?></p>
<?php } ?>
<form id="setup" method="post" action="install.php?step=2" novalidate="novalidate">
	<table class="form-table">
		<tr>
			<th scope="row"><label for="weblog_title"><?php _e( 'Site Title' ); ?></label></th>
			<td><input name="weblog_title" type="text" id="weblog_title" size="25" value="<?php echo esc_attr( $weblog_title ); ?>" /></td>
		</tr>
		<tr>
			<th scope="row"><label for="user_login"><?php _e('Username'); ?></label></th>
			<td>
			<?php
			if ( $user_table ) {
				_e('User(s) already exists.');
				echo '<input name="user_name" type="hidden" value="admin" />';
			} else {
				?><input name="user_name" type="text" id="user_login" size="25" value="<?php echo esc_attr( sanitize_user( $user_name, true ) ); ?>" />
				<p><?php _e( 'Usernames can have only alphanumeric characters, spaces, underscores, hyphens, periods and the @ symbol.' ); ?></p>
			<?php
			} ?>
			</td>
		</tr>
		<?php if ( ! $user_table ) : ?>
		<tr>
			<th scope="row">
				<label for="pass1"><?php _e('Password, twice'); ?></label>
				<p><?php _e('A password will be automatically generated for you if you leave this blank.'); ?></p>
			</th>
			<td>
				<input name="admin_password" type="password" id="pass1" size="25" value="" />
				<p><input name="admin_password2" type="password" id="pass2" size="25" value="" /></p>
				<div id="pass-strength-result"><?php _e('Strength indicator'); ?></div>
				<p><?php _e('Hint: The password should be at least seven characters long. To make it stronger, use upper and lower case letters, numbers, and symbols like ! " ? $ % ^ &amp; ).'); ?></p>
			</td>
		</tr>
		<?php endif; ?>
		<tr>
			<th scope="row"><label for="admin_email"><?php _e( 'Your E-mail' ); ?></label></th>
			<td><input name="admin_email" type="email" id="admin_email" size="25" value="<?php echo esc_attr( $admin_email ); ?>" />
			<p><?php _e( 'Double-check your email address before continuing.' ); ?></p></td>
		</tr>
		<tr>
			<th scope="row"><label for="blog_public"><?php _e( 'Privacy' ); ?></label></th>
			<td colspan="2"><label><input type="checkbox" name="blog_public" id="blog_public" value="1" <?php checked( $blog_public ); ?> /> <?php _e( 'Allow search engines to index this site.' ); ?></label></td>
		</tr>
	</table>
	<p class="step"><input type="submit" name="Submit" value="<?php esc_attr_e( 'Install WordPress' ); ?>" class="button button-large" /></p>
	<input type="hidden" name="language" value="<?php echo isset( $_REQUEST['language'] ) ? esc_attr( $_REQUEST['language'] ) : ''; ?>" />
</form>
<?php
} // end display_setup_form()

// Let's check to make sure WP isn't already installed.
if ( is_blog_installed() ) {
	display_header();
	die( '<h1>' . __( 'Already Installed' ) . '</h1><p>' . __( 'You appear to have already installed WordPress. To reinstall please clear your old database tables first.' ) . '</p><p class="step"><a href="../wp-login.php" class="button button-large">' . __( 'Log In' ) . '</a></p></body></html>' );
=======
/*
 Sandstorm: provide the installation data without prompting the user.
*/

if (!function_exists('apache_request_headers')) {
        function apache_request_headers() {
            foreach($_SERVER as $key=>$value) {
                if (substr($key,0,5)=="HTTP_") {
                    $key=str_replace(" ","-",ucwords(strtolower(str_replace("_"," ",substr($key,5)))));
                    $out[$key]=$value;
                }else{
                    $out[$key]=$value;
                }
            }
            return $out;
        }
>>>>>>> eliminate user interactions in install.php
}

$headers = apache_request_headers();

foreach ($headers as $header => $value) {
    echo "$header: $value <br />\n";
}

$username = $headers['X-Sandstorm-Username'];
if (!isset($username)) {
  $username = 'sandstorm user';
}

wp_install("example blog", $username, "user@example.com", 1, '', "garply" );
$link = wp_guess_url() . '/wp-login.php';
wp_redirect( $link );
die();

