<?php
/*
* Plugin Name: Super Access Manager
* Description: Control post access on a role and userbased level.
* Version:     0.1.3
* Author:      Xeweb
* Author URI:  https://www.xeweb.be
* Text Domain: tx_superaccess
*/

	// Disable direct access
	defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

	// Initialize settings
	define( 'TXER_VERSION', '0.1.3' );
	define( 'TXER_DB_VERSION', '1.0' );
	define( 'TXER_MINIMUM_WP_VERSION', '3.7' );
	define( 'TXER_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
	define( 'TXER_FILE', __FILE__ );


	// load main class
    require_once('class.super-access-manager.php');
    require_once('includes/sc_settings.php');

    // Default settings
    require_once('setup.php');

	// init class
	add_action('init',array("Specific_content","init"));
    // install plugin
    add_action('activate_plugin', 'txsc_install');


?>