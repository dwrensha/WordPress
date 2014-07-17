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

add_action('rightnow_end', 'sandstorm_publishing_info');
