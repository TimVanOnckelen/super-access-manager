<?php
/**
 *
 */
function txsc_install(){

	// if shortcode is zero, add to options
	if(get_option('txsc_shortcode_all_pages') == null){
		update_option('txsc_shortcode_all_pages', 'on' );
	}

	// if shortcode is zero, add to options
	if(get_option('txsc_message_no_posts') == null){
		update_option('txsc_message_no_posts', __("You don't have personal pages at the moment.","tx_superaccess") );
	}

	// if shortcode is zero, add to options
	if(get_option('txsc_post_limit_widget') == null){
		update_option('txsc_post_limit_widget', '4' );
	}

	// if shortcode is zero, add to options
	if(get_option('txsx_list_posts_text') == null){
		update_option('txsx_list_posts_text', '' );
	}

	// if shortcode is zero, add to options
	if(get_option('txsx_list_posts_link') == null){
		update_option('txsx_list_posts_link', '#' );
	}

}