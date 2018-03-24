<?php
// create custom plugin settings menu
add_action('admin_menu', 'txsc_create_menu');

$txsc = new Specific_content();

function txsc_create_menu() {

    //create new top-level menu
    add_submenu_page('users.php','Specific content', 'Specific content', 'administrator', __FILE__, 'txsc_settingspage' , false);

    //call register settings function
    add_action( 'admin_init', 'txsc_register_settings' );
}


function txsc_register_settings() {
    //register our settings
    register_setting( 'txsc-settings-group', 'txsc_admin_see_all_pages' );
    register_setting( 'txsc-settings-group', 'txsc_shortcode_all_pages' );
    register_setting( 'txsc-settings-group', 'txsc_message_no_posts' );
    register_setting( 'txsc-settings-group', 'txsc_post_limit_widget' );
    register_setting( 'txsc-settings-group', 'txsx_list_posts_text' );

}

function txsc_settingspage() {


    ?>
    <div class="wrap metabox-holder">
        <h1>Specific content instellingen</h1>

        <form method="post" action="options.php">
            <?php settings_fields( 'txsc-settings-group' ); ?>
            <?php do_settings_sections( 'txsc-settings-group' ); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><?php echo __("Administrators can always see all pages","super_access");?></th>
                    <td>
                        <input type="checkbox" name="txsc_admin_see_all_pages" <?php if(get_option('txsc_admin_see_all_pages') == "on"){echo "checked";} ?> />
                        <p class="description">(default checked)</p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php echo __("Amount of personal pages to show with shortcode","super_access");?></th>
                    <td>
                        <input type="number" name="txsc_post_limit_widget" value="<?php echo get_option('txsc_post_limit_widget'); ?>" />
                        <p class="description">(default 4)</p>
                    </td>
                </tr>
                <tr valign="top">

                    <th scope="row"><?php echo __("User has no personal pages message","super_access");?></th>
                    <td>
                        <?php wp_editor(get_option('txsc_message_no_posts'),'txsc_message_no_posts'); ?>
                        <p class="description"></p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Overzicht posts header.</th>
                    <td>

                        <?php wp_editor(get_option('txsx_list_posts_text'),'txsx_list_posts_text'); ?>
                        <p class="description"></p>
                    </td>
                </tr>
            </table>


            <?php submit_button(); ?>

        </form>
        </div>
<?php } ?>