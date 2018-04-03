<?php
// create custom plugin settings menu
add_action('admin_menu', 'xeweb_sam_create_menu');

$xewebSam = new Xeweb_sam_main();

function xeweb_sam_create_menu() {

    //create new top-level menu
    add_submenu_page('users.php','Super access manager', 'Super access manager', 'administrator', 'xeweb_sam_settings', 'xeweb_sam_settingspage' , false);

    //call register settings function
    add_action( 'admin_init', 'xeweb_sam_register_settings' );
}


function xeweb_sam_register_settings() {
    //register our settings
    register_setting( 'xeweb-sam-settings-group', 'xeweb-sam_admin_see_all_pages' );
    register_setting( 'xeweb-sam-settings-group', 'xeweb-sam_shortcode_all_pages' );
    register_setting( 'xeweb-sam-settings-group', 'xeweb-sam_message_no_posts' );
    register_setting( 'xeweb-sam-settings-group', 'xeweb-sam_post_limit_widget' );
    register_setting( 'xeweb-sam-settings-group', 'xeweb-sam_list_posts_text' );
	register_setting( 'xeweb-sam-settings-group', 'xeweb-sam_allowed_post_types' );
	register_setting( 'xeweb-sam-settings-group', 'xeweb-sam_admin_remove_empty_cats' );
}

function xeweb_sam_settingspage(){

	if(isset($_GET["legacyupdate"])){

	    // Dry run or not
	    $dry = true;

	    if(isset($_GET["legacyupdate"])){
	        $dry = $_GET["dry"];
	    }

		xeweb_sam_legacy_update($dry);

    }else {

		// Show the settings page
		xeweb_sam_show_settingspage();

	}
}
function xeweb_sam_show_settingspage() {

    ?>
    <div class="wrap metabox-holder">
        <h1><?php echo __("Access Management","xeweb_sam");?></h1>

        <form method="post" action="options.php">
            <?php settings_fields( 'xeweb-sam-settings-group' ); ?>
            <?php do_settings_sections( 'xeweb-sam-settings-group' ); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><?php echo __("Administrators can always see all pages","xeweb_sam");?></th>
                    <td>
                        <input type="checkbox" name="xeweb-sam_admin_see_all_pages" <?php if(get_option('xeweb-sam_admin_see_all_pages') == "on"){echo "checked";} ?> />
                        <p class="description">(default checked)</p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php echo __("Amount of personal pages to show with shortcode","xeweb_sam");?></th>
                    <td>
                        <input type="number" name="xeweb-sam_post_limit_widget" value="<?php echo get_option('xeweb-sam_post_limit_widget'); ?>" />
                        <p class="description">(default 4)</p>
                    </td>
                </tr>
                <tr valign="top">

                    <th scope="row"><?php echo __("User has no personal pages message","xeweb_sam");?></th>
                    <td>
                        <?php wp_editor(get_option('xeweb-sam_message_no_posts'),'xeweb-sam_message_no_posts'); ?>
                        <p class="description"></p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php echo __("Overview post header","xeweb_sam");?></th>
                    <td>

                        <?php wp_editor(get_option('xeweb-sam_list_posts_text'),'xeweb-sam_list_posts_text'); ?>
                        <p class="description"></p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php echo __("Remove empty categories from list","xeweb_sam");?></th>
                    <td>
                        <input type="checkbox" name="xeweb-sam_admin_remove_empty_cats" <?php if(get_option('xeweb-sam_admin_remove_empty_cats') == "on"){echo "checked";} ?> />
                        <p class="description">(default checked)</p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php echo __("Allow acces management for the following post types","xeweb_sam");?></th>
                    <td>
	                        <?php
	                        $post_types = get_post_types();
	                        foreach ($post_types as $type){
		                        ?>
                                <input type="checkbox" name="xeweb-sam_allowed_post_types[]" value="<?php echo $type; ?>" <?php if(in_array($type,get_option('xeweb-sam_allowed_post_types'))): echo 'CHECKED'; endif; ?>><?php echo $type; ?></input>
		                        <?php
	                        }
	                        ?>
			            <?php  ?>
                        <p class="description"></p>
                    </td>
                </tr>
            </table>


            <?php submit_button(); ?>

        </form>
        </div>
<?php }

/*
 * Update older keys with new keys */
function xeweb_sam_legacy_update($dry=true){

	global $wpdb;

	// get all meta data from this plugin by old key
	$metas = $wpdb->get_results(
		$wpdb->prepare("SELECT meta_value,post_id FROM $wpdb->postmeta where meta_key = %s ORDER BY post_id DESC", 'txsc_allowed_users')
	);

	if(!empty($metas)) {
		// Update to new data
		foreach ( $metas as $meta ) {

		    // Dry run text
		    $dry_text = __("dry update","xeweb_sam");

		    if($dry == false) {
			    // Add new data to new key
			    add_post_meta( $meta->post_id, "xeweb_sam-allowed_users", $meta->meta_value );

			    // remove the old data
			    // delete_post_meta( $meta->post_id, $meta->meta_key, $meta->meta_value );

			    $dry_text = __("updated","xeweb_sam");;

		    }

            // Echo the dry text
		    echo $meta->post_id." (".$dry_text.")";

		}
	}else{
		// No data to update :)
		echo __("No older data needs to be updated.","super_access");
	}

}
?>


