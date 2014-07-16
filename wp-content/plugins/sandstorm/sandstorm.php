<?php
/**
 * @package Sandstorm
 * @version 0.0.1
 */
/*
Plugin Name: Sandstorm
Plugin URI: http://github.com/dwrensha/wordpress.git
Description: Sandstorm things.
Author: David Renshaw
Version: 0.0.1
Author URI: https://sandstorm.io
*/


function sandstorm_publish() {
  error_log("PUBLISHING");
}

add_action('publish_post', 'sandstorm_publish');

