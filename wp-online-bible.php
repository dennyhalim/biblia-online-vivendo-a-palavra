<?php
/*
Plugin Name: Online Bible VP for Wordpress 
Plugin URI: http://www.vivendoapalavra.org/
Description: Plugin for implementation of Online Bible in your Wordpress blog. With it, you can make available the Word of God and bless your website's users. The plugin allows to consult all 66 books of the Holy Bible versions: King James Edition - English, Almeida Corrigida Fiel - Português (1994), Spanish Reina Valera (1960).
Author: André Brum Sampaio
Version: 1.5.1
Author URI: http://www.vivendoapalavra.org/
*/

require_once plugin_dir_path(__FILE__) . '/functions.php'; // Plugin functions.

//activation
register_activation_hook(__FILE__,'PluginSoftInstall');
register_deactivation_hook(__FILE__,'PluginSoftUninstall');

// filters
add_filter('query_vars','PluginIncludeVars'); //include query vars for bovp.
add_filter('the_content','PluginShowBible'); //show the bible in the select page.
add_filter('the_content','PluginShowBible'); //show the bible in the select page.

//add_filter("the_content","bovp_link_generator"); // Generate link in the content to bible page.

// hooks
add_action('admin_menu', 'PluginAdminMenu');
add_action('admin_head', 'PluginAdminStyles');
add_action('plugins_loaded', 'PluginActiveTranslate');
add_action('wp_enqueue_scripts', 'PluginDependences');
add_action('widgets_init', 'bovp_widgets_init');

?>