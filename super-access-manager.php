<?php
/*
* Plugin Name: Super Access Manager
* Description: Control post access on a role and userbased level.
* Version:     0.1.6.1
* Author:      Xeweb
* Author URI:  https://www.xeweb.be
* Text Domain: xeweb_sam
*/

	// Disable direct access
	defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

	// load main class
    require_once('class.super-access-manager.php');
    require_once('includes/sc_settings.php');

    // Default settings
    require_once('setup.php');

	// init class
	add_action('init',array("Xeweb_sam_main","init"));

    // install plugin settings
    add_action('activate_plugin', 'xeweb_sam_install');


?>