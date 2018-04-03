<?php
/**
 *
 */
function xeweb_sam_install(){

	// if shortcode is zero, add to options
	if(get_option('xeweb-sam_shortcode_all_pages') == null){
		update_option('xeweb-sam_shortcode_all_pages', 'on' );
	}

	// if shortcode is zero, add to options
	if(get_option('xeweb-sam_admin_remove_empty_cats') == null){
		update_option('xeweb-sam_admin_remove_empty_cats', 'on' );
	}

	// if shortcode is zero, add to options
	if(get_option('xeweb-sam_message_no_posts') == null){
		update_option('xeweb-sam_message_no_posts', __("You don't have personal pages at the moment.","super_access") );
	}

	// if shortcode is zero, add to options
	if(get_option('xeweb-sam_post_limit_widget') == null){
		update_option('xeweb-sam_post_limit_widget', '4' );
	}

	// if shortcode is zero, add to options
	if(get_option('xeweb-sam_list_posts_text') == null){
		update_option('xeweb-sam_list_posts_text', '' );
	}

	// if shortcode is zero, add to options
	if(get_option('xeweb-sam_list_posts_link') == null){
		update_option('xeweb-sam_list_posts_link', '#' );
	}

}
