<?php
/*
* Plugin Name: Super Access Manager
* Description: Control post access on a role and userbased level.
* Version:     0.2.1
* Author:      Xeweb
* Author URI:  https://www.xeweb.be
* Text Domain: xeweb_sam
*/

	// Disable direct access
	defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
	define( 'XE_SAM_PLUGIN_DIR', plugin_dir_url( __FILE__ ) );
	define( 'XE_SAM_FILE', __FILE__ );
	define( 'XE_SAM_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );

	// Include the autoloader so we can dynamically include the rest of the classes.
	require_once( trailingslashit( dirname( __FILE__ ) ) . 'inc/autoloader.php' );

    require_once('config/sc_settings.php');

    // Default settings
    require_once('setup.php');

	// init class
	add_action('init','xe_sam_load');

    // install plugin settings
    add_action('activate_plugin', 'xeweb_sam_install');

    function xe_sam_load(){
    	new \Xe_SuperAcessManager\Inc\Main();
    }

?>