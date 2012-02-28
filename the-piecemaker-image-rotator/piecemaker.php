<?php  
 /* 
 Plugin Name: Piecemaker 2 WordPress Plugin
 Plugin URI: http://www.modularweb.net/piecemaker/
 Description: This is a Piecemaker 2 WordPress Plugin
 Version: 1.0 
 Author: Modularweb and massiveProCreation
 Author URI: http://www.modularweb.net/piecemaker/development/
 */  
 
include_once("piecemaker-main.php");
$pm = new PiecemakerMain();

if(isset($pm)) {
	register_activation_hook(__FILE__, array($pm, 'PiecemakerMain'));
	register_deactivation_hook(__FILE__, array($pm, 'PiecemakerMainDeactivation'));
	global $wpdb;
	
	$pm->menu_title = 'Piecemaker Plugin';
	$pm->add_page_to = 1;
	$pm->table_name = $wpdb->prefix."piecemakers"; // database table name for books 
	$pm->table_img_name = $wpdb->prefix."piecemaker_img"; // database table name for files
	$pm->books_dir = "piecemakers"; // define where piecemaker (xml) will be placed 
	$pm->images_dir = "piecemaker-images"; // define where images, swf-s, and flv files will be stored
	$pm->path_to_img = get_bloginfo('wpurl')."/wp-content/uploads/piecemaker-images/";
	$pm->path_to_assets = get_bloginfo('wpurl')."/wp-content/uploads/";
	//$pm->path_to_img = get_bloginfo('wpurl')."/wp-includes/piecemaker-images/";
	//$pm->path_to_assets = get_bloginfo('wpurl')."/wp-includes/";

	$pm->path_to_plugin = get_bloginfo('wpurl')."/wp-content/plugins/the-piecemaker-image-rotator/";
	$pm->piecemakerSWF = get_bloginfo('wpurl')."/wp-content/plugins/the-piecemaker-image-rotator/swf/piecemaker.swf"; 
	$pm->piecemakerJS = get_bloginfo('wpurl')."/wp-content/plugins/the-piecemaker-image-rotator/js/JavaScriptFlashGateway.js";
	$pm->piecemakerGateway = get_bloginfo('wpurl')."/wp-content/plugins/the-piecemaker-image-rotator/swf/JavaScriptFlashGateway.swf";
	$pm->width = "900";
	$pm->height = "360";
//	$pm->thumb_width = "280";
//	$pm->thumb_height = "80";
	add_action('admin_menu', array($pm, 'piecemaker_plugin_menu'));
	add_action('admin_init', array($pm, 'add_piecemaker_css'));
	add_shortcode('piecemaker', array($pm, 'replaceBooks'));
	
} 
?>