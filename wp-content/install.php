<?php

function wp_install_defaults() {

         update_option( 'default_comment_status', '' );

         update_option('active_plugins', array('sandstorm/sandstorm.php',
                                               'sqlite-integration/sqlite-integration.php'));


         global $wp_rewrite;
         $wp_rewrite->set_permalink_structure('/%year%/%monthnum%/%postname%/');
         $wp_rewrite->flush_rules();


         // Set up default widgets for default theme.
         update_option( 'widget_recent-posts', array ( 2 => array ( 'title' => '', 'number' => 5 ), '_multiwidget' => 1 ) );
         update_option( 'widget_archives', array ( 2 => array ( 'title' => '', 'count' => 0, 'dropdown' => 0 ), '_multiwidget' => 1 ) );
         update_option( 'sidebars_widgets', array ( 'wp_inactive_widgets' => array (), 'sidebar-1' => array ( 0 => 'search-2', 1 => 'recent-posts-2', 2 => 'recent-comments-2', 3 => 'archives-2', 4 => 'categories-2', 5 => 'meta-2', ), 'sidebar-2' => array (), 'sidebar-3' => array (), 'array_version' => 3 ) );


}